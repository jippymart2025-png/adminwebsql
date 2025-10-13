<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kreait\Firebase\Factory;
use Google\Cloud\Firestore\FieldPath;

class FirebaseOrderController extends Controller
{
    protected $firestore; // Changed from $database to $firestore for consistency

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
        $cursor = $request->query('cursor'); // documentId to start after
        $limit = (int) $request->query('limit', 10);
        $withCounts = (int) $request->query('with_counts', 0) === 1;

        $cacheKey = "firebase_orders_v2_status_" . ($statusFilter ?: 'any') . "_vendor_" . ($vendorID ?: 'any') . "_cursor_" . ($cursor ?: 'start') . "_limit_{$limit}";

        $data = Cache::remember($cacheKey, 30, function () use ($statusFilter, $vendorID, $cursor, $limit) {
            $query = $this->firestore->collection('restaurant_orders')->orderBy(FieldPath::documentId());

            if (!empty($statusFilter)) {
                $query = $query->where('status', '==', $statusFilter);
            }

            if (!empty($vendorID)) {
                $query = $query->where('vendorID', '==', $vendorID);
            }

            if (!empty($cursor)) {
                $query = $query->startAfter([$cursor]);
            }

            $query = $query->limit($limit);

            $documents = $query->documents();

            $orders = [];
            $nextCursor = null;

            foreach ($documents as $document) {
                $row = $document->data();
                $row['id'] = $document->id();

                array_walk_recursive($row, function (&$value) {
                    if (is_float($value) && (is_nan($value) || is_infinite($value))) {
                        $value = 0;
                    }
                });

                $orders[] = $row;
                $nextCursor = $document->id();
            }

            return [
                'orders' => $orders,
                'next_cursor' => $nextCursor,
            ];
        });

        // Optional counters (cached)
        $countersCacheKey = "firebase_orders_counts_v2_status_" . ($statusFilter ?: 'any') . "_vendor_" . ($vendorID ?: 'any');
        $counters = Cache::get($countersCacheKey);
        if ($withCounts) {
            $counters = Cache::remember($countersCacheKey, 120, function () use ($statusFilter, $vendorID) {
                $base = $this->firestore->collection('restaurant_orders');
                if (!empty($statusFilter)) {
                    $base = $base->where('status', '==', $statusFilter);
                }
                if (!empty($vendorID)) {
                    $base = $base->where('vendorID', '==', $vendorID);
                }

                $total = 0; $activeOrders = 0; $completed = 0; $pending = 0; $cancelled = 0;
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
                    'activeOrders' => $activeOrders,
                    'completed' => $completed,
                    'pending' => $pending,
                    'cancelled' => $cancelled,
                ];
            });
        }

        $counters = $data['counters'] ?? [
            'total' => 0,
            'activeOrders' => 0,
            'completed' => 0,
            'pending' => 0,
            'cancelled' => 0,
        ];

        return response()->json([
            'status' => true,
            'message' => 'Orders fetched successfully',
            'meta' => [
                'limit' => $limit,
                'count' => count($data['orders']),
                'cursor' => $cursor,
                'next_cursor' => $data['next_cursor'],
                'status' => $statusFilter,
                'vendorID' => $vendorID,
                'with_counts' => $withCounts,
            ],
            'counters' => $counters,
            'data' => $data['orders'],
        ]);
    }
    }
