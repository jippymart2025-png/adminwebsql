<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kreait\Firebase\Factory;

class FirebaseOrderController extends Controller
{
    protected $firestore;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

        $this->firestore = $factory->createFirestore()->database();
    }

    public function index(Request $request)
    {
        $statusFilter = $request->query('status');
        $vendorID = $request->query('vendorID');
        $limit = (int) $request->query('limit', 10);
        $page = (int) $request->query('page', 1);

        // Calculate offset for page-based pagination
        $offset = ($page - 1) * $limit;

        $cacheKey = "firebase_orders_v5_status_" . ($statusFilter ?: 'any') . "_vendor_" . ($vendorID ?: 'any') . "_page_{$page}_limit_{$limit}";

        $data = Cache::remember($cacheKey, 30, function () use ($statusFilter, $vendorID, $limit, $offset, $page) {
            $query = $this->firestore
                ->collection('restaurant_orders')
                ->orderBy('createdAt', 'DESCENDING');

            // Apply filters
            if (!empty($statusFilter)) {
                $query = $query->where('status', '==', $statusFilter);
            }

            if (!empty($vendorID)) {
                $query = $query->where('vendorID', '==', $vendorID);
            }

            // For page-based pagination, fetch all documents up to current page
            // and then slice the results (Firestore doesn't support offset directly)
            $totalToFetch = $offset + $limit + 1; // +1 to check if there's more
            $query = $query->limit($totalToFetch);

            $documents = $query->documents();

            $allOrders = [];
            $count = 0;

            foreach ($documents as $document) {
                $allOrders[] = [
                    'data' => $document->data(),
                    'id' => $document->id(),
                ];
                $count++;
            }

            // Skip to the offset position
            $orders = [];
            $hasMore = false;
            $nextCreatedAt = null;
            $nextDocId = null;

            for ($i = $offset; $i < count($allOrders) && $i < $offset + $limit; $i++) {
                $rawData = $allOrders[$i]['data'];
                $docId = $allOrders[$i]['id'];

                // Transform to include only necessary fields
                $orderData = $this->transformOrderData($rawData, $docId);
                $orders[] = $orderData;

                // Store last document info for next page
                $nextCreatedAt = $rawData['createdAt'] ?? null;
                $nextDocId = $docId;
            }

            // Check if there are more results beyond current page
            $hasMore = count($allOrders) > ($offset + $limit);

            return [
                'orders' => $orders,
                'has_more' => $hasMore,
                'next_created_at' => $hasMore ? $nextCreatedAt : null,
                'next_doc_id' => $hasMore ? $nextDocId : null,
            ];
        });

        // Get total count and status breakdown (cached)
        $totalCount = null;
        $countersCacheKey = "firebase_orders_counts_v4_status_" . ($statusFilter ?: 'any') . "_vendor_" . ($vendorID ?: 'any');

        // Always try to get counters from cache first
        $counters = Cache::get($countersCacheKey);

        // If not in cache AND (page 1 OR with_total requested), calculate it
        if ($counters === null && ($page === 1 || $request->query('with_total') === '1')) {
            $counters = Cache::remember($countersCacheKey, 300, function () use ($statusFilter, $vendorID) {
                $base = $this->firestore->collection('restaurant_orders');

                if (!empty($statusFilter)) {
                    $base = $base->where('status', '==', $statusFilter);
                }
                if (!empty($vendorID)) {
                    $base = $base->where('vendorID', '==', $vendorID);
                }

                $total = 0;
                $activeOrders = 0;
                $completed = 0;
                $pending = 0;
                $cancelled = 0;

                foreach ($base->select(['__name__', 'status'])->documents() as $doc) {
                    $total++;
                    $status = strtolower(trim((string) ($doc->data()['status'] ?? '')));

                    if (in_array($status, ['order placed', 'order accepted', 'order shipped', 'in transit', 'driver pending'])) {
                        $activeOrders++;
                    }
                    if ($status === 'order completed') {
                        $completed++;
                    }
                    if (in_array($status, ['order placed', 'driver pending', 'in transit'])) {
                        $pending++;
                    }
                    if (in_array($status, ['order rejected', 'driver rejected', 'order cancelled', 'cancelled'])) {
                        $cancelled++;
                    }
                }

                return [
                    'total' => $total,
                    'active_orders' => $activeOrders,
                    'completed' => $completed,
                    'pending' => $pending,
                    'cancelled' => $cancelled,
                ];
            });
        }

        // Set total count if counters are available
        if ($counters !== null) {
            $totalCount = $counters['total'] ?? 0;
        }

        return response()->json([
            'status' => true,
            'message' => 'Orders fetched successfully',
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'count' => count($data['orders']),
                'total' => $totalCount,
                'has_more' => $data['has_more'],
                'next_created_at' => $data['next_created_at'],
                'next_doc_id' => $data['next_doc_id'],
                'status_filter' => $statusFilter,
                'vendor_id' => $vendorID,
            ],
            'counters' => $counters,
            'data' => $data['orders'],
        ]);
    }

    /**
     * Transform order data to return only necessary fields
     * Fields: restaurantorders ID, Restaurant, Drivers, Client, Date, Amount, restaurantorders Type, restaurantorders Status
     */
    private function transformOrderData(array $data, string $docId): array
    {
        // Calculate total amount
        $productTotal = 0;
        if (isset($data['products']) && is_array($data['products'])) {
            foreach ($data['products'] as $product) {
                $price = $this->sanitizeNumber($product['price'] ?? 0);
                $discountPrice = $this->sanitizeNumber($product['discountPrice'] ?? 0);
                $quantity = (int) ($product['quantity'] ?? 1);
                $extrasPrice = $this->sanitizeNumber($product['extras_price'] ?? 0);

                $itemPrice = $discountPrice > 0 ? $discountPrice : $price;
                $productTotal += ($itemPrice + $extrasPrice) * $quantity;
            }
        }

        $deliveryCharge = $this->sanitizeNumber($data['deliveryCharge'] ?? 0);
        $discount = $this->sanitizeNumber($data['discount'] ?? 0);
        $tipAmount = $this->sanitizeNumber($data['tip_amount'] ?? 0);
        $specialDiscount = $this->sanitizeNumber($data['specialDiscount']['special_discount'] ?? 0);

        $totalAmount = $productTotal + $deliveryCharge + $tipAmount - $discount - $specialDiscount;

        // Extract restaurant info
        $restaurantName = $data['vendor']['title'] ?? 'N/A';
        $restaurantId = $data['vendorID'] ?? '';
        $restaurantPhoto = $data['vendor']['photo'] ?? null;

        // Extract client info
        $clientName = trim(($data['author']['firstName'] ?? '') . ' ' . ($data['author']['lastName'] ?? ''));
        $clientId = $data['authorID'] ?? '';
        $clientPhone = ($data['author']['countryCode'] ?? '') . ($data['author']['phoneNumber'] ?? '');
        $clientEmail = $data['author']['email'] ?? '';

        // Extract driver info
        $driverId = $data['driverID'] ?? null;
        $driverName = null;
        $driverPhone = null;

        // Note: Driver details might need to be fetched separately if not in order doc
        // For now, we just return the driverID

        // restaurantorders type (takeaway or delivery)
        $orderType = isset($data['takeAway']) && $data['takeAway'] === true ? 'Takeaway' : 'Delivery';

        // Format date
        $createdAt = $this->formatTimestamp($data['createdAt'] ?? null);

        return [
            // restaurantorders ID
            'order_id' => $data['id'] ?? $docId,

            // Restaurant details
            'restaurant' => [
                'id' => $restaurantId,
                'name' => $restaurantName,
                'photo' => $restaurantPhoto,
            ],

            // Driver details
            'driver' => [
                'id' => $driverId,
                'name' => $driverName, // May need separate query to populate
                'phone' => $driverPhone,
            ],

            // Client details
            'client' => [
                'id' => $clientId,
                'name' => $clientName,
                'phone' => $clientPhone,
                'email' => $clientEmail,
            ],

            // Date
            'date' => $createdAt,
            'created_at_raw' => $data['createdAt'] ?? null,

            // Amount
            'amount' => number_format($totalAmount, 2, '.', ''),
            'amount_breakdown' => [
                'subtotal' => number_format($productTotal, 2, '.', ''),
                'delivery_charge' => number_format($deliveryCharge, 2, '.', ''),
                'tip' => number_format($tipAmount, 2, '.', ''),
                'discount' => number_format($discount + $specialDiscount, 2, '.', ''),
            ],

            // restaurantorders Type
            'order_type' => $orderType,

            // restaurantorders Status
            'status' => $data['status'] ?? 'Unknown',

            // Payment method
            'payment_method' => $data['payment_method'] ?? '',

            // Additional useful fields
            'products_count' => isset($data['products']) ? count($data['products']) : 0,
            'address' => $data['address']['locality'] ?? '',
        ];
    }

    /**
     * Sanitize numeric values to prevent NaN/Infinity
     */
    private function sanitizeNumber($value): float
    {
        if (is_numeric($value)) {
            $float = (float) $value;
            if (is_nan($float) || is_infinite($float)) {
                return 0.0;
            }
            return $float;
        }
        return 0.0;
    }

    /**
     * Format Firestore timestamp to readable format
     */
    private function formatTimestamp($timestamp): ?string
    {
        if (empty($timestamp)) {
            return null;
        }

        // Handle Firestore Timestamp object
        if (is_object($timestamp) && method_exists($timestamp, 'toDateTime')) {
            return $timestamp->toDateTime()->format('Y-m-d H:i:s');
        }

        // Handle Unix timestamp
        if (is_numeric($timestamp)) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        // Handle string timestamp
        if (is_string($timestamp)) {
            return $timestamp;
        }

        return null;
    }
}
