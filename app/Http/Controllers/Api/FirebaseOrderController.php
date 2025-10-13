<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Kreait\Firebase\Factory;

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
        // Optional filters
        $statusFilter = $request->query('status', null);
        $vendorID = $request->query('vendorID', null);

        // Pagination setup
        $page = (int) $request->query('page', 1);
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $cacheKey = "firebase_orders_all_fields_{$statusFilter}_{$vendorID}_page_{$page}";

        $data = Cache::remember($cacheKey, 5, function () use ($request, $statusFilter, $vendorID, $limit, $offset) {
            $collection = $this->firestore->collection('restaurant_orders');
            $documents = $collection->documents();

            $orders = [];
            $total = 0;
            $activeOrders = 0;
            $completed = 0;
            $pending = 0;
            $cancelled = 0;

            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();

                array_walk_recursive($data, function (&$value) {
                    if (is_float($value) && (is_nan($value) || is_infinite($value))) {
                        $value = 0;
                    }
                });

                $orders[] = $data;
                $total++;

                if (isset($data['status'])) {
                    $status = strtolower(trim($data['status']));

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
            }

            // Apply pagination
            $pagedOrders = array_slice($orders, $offset, $limit);

            return [
                'orders' => $pagedOrders,
                'counters' => [
                    'total' => $total,
                    'activeOrders' => $activeOrders,
                    'completed' => $completed,
                    'pending' => $pending,
                    'cancelled' => $cancelled,
                ]
            ];
        });

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
                'page' => $page,
                'limit' => $limit,
                'count' => count($data['orders']),
                'total_orders' => $counters['total'],
                'active_orders' => $counters['activeOrders'],
                'completed_orders' => $counters['completed'],
                'pending_orders' => $counters['pending'],
                'cancelled_orders' => $counters['cancelled'],
            ],
            'data' => $data['orders'],
        ]);
    }
    }
