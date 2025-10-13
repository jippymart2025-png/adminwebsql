<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Cache;

class FirebaseUserController extends Controller
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
        $role = $request->query('role', null);
        $limit = (int) $request->query('limit', 10);
        $page = (int) $request->query('page', 1);
        $lastCreatedAt = $request->query('last_created_at'); // Unix timestamp for cursor
        $lastDocId = $request->query('last_doc_id'); // Document ID for tie-breaking

        if (!$role || !in_array($role, ['customer', 'vendor', 'driver'])) {
            return response()->json([
                'status' => false,
                'message' => 'Valid role is required (customer, vendor, driver)',
            ], 400);
        }

        $cacheKey = "firebase_users_v3_role_{$role}_page_{$page}_last_" . ($lastCreatedAt ?: 'start') . "_" . ($lastDocId ?: 'start') . "_limit_{$limit}";

        $data = Cache::remember($cacheKey, 30, function () use ($role, $limit, $lastCreatedAt, $lastDocId) {
            $query = $this->firestore
                ->collection('users')
                ->where('role', '==', $role)
                ->orderBy('createdAt', 'DESCENDING');

            // Cursor-based pagination using createdAt timestamp
            if (!empty($lastCreatedAt) && !empty($lastDocId)) {
                // Get the reference document for startAfter
                $lastDoc = $this->firestore->collection('users')->document($lastDocId)->snapshot();
                if ($lastDoc->exists()) {
                    $query = $query->startAfter([$lastDoc]);
                }
            }

            $query = $query->limit($limit + 1); // Fetch one extra to check if there's a next page

            $documents = $query->documents();

            $users = [];
            $hasMore = false;
            $nextCreatedAt = null;
            $nextDocId = null;
            $count = 0;

            foreach ($documents as $document) {
                $count++;
                
                if ($count > $limit) {
                    $hasMore = true;
                    break;
                }

                $docData = $document->data();
                $docId = $document->id();

                // Transform data based on role
                $userData = $this->transformUserData($role, $docData, $docId);
                $users[] = $userData;

                // Store last document info for next cursor
                $nextCreatedAt = $docData['createdAt'] ?? null;
                $nextDocId = $docId;
            }

            return [
                'users' => $users,
                'has_more' => $hasMore,
                'next_created_at' => $hasMore ? $nextCreatedAt : null,
                'next_doc_id' => $hasMore ? $nextDocId : null,
            ];
        });

        // Get total count (cached separately for performance)
        $totalCount = null;
        $countsCacheKey = "firebase_users_total_count_role_{$role}";
        
        // Only compute total on first page or if explicitly requested
        if ($page === 1 || $request->query('with_total') === '1') {
            $totalCount = Cache::remember($countsCacheKey, 300, function () use ($role) {
                $snapshot = $this->firestore
                    ->collection('users')
                    ->where('role', '==', $role)
                    ->select(['__name__'])
                    ->documents();
                
                $count = 0;
                foreach ($snapshot as $doc) {
                    $count++;
                }
                return $count;
            });
        }

        return response()->json([
            'status' => true,
            'message' => 'Users fetched successfully',
            'meta' => [
                'role' => $role,
                'page' => $page,
                'limit' => $limit,
                'count' => count($data['users']),
                'total' => $totalCount,
                'has_more' => $data['has_more'],
                'next_created_at' => $data['next_created_at'],
                'next_doc_id' => $data['next_doc_id'],
            ],
            'data' => $data['users'],
        ]);
    }

    /**
     * Transform user data based on role to return only necessary fields
     */
    private function transformUserData(string $role, array $data, string $docId): array
    {
        // Common fields
        $transformed = [
            'id' => $data['id'] ?? $docId,
            'firstName' => $data['firstName'] ?? '',
            'lastName' => $data['lastName'] ?? '',
            'email' => $data['email'] ?? '',
            'phoneNumber' => $data['phoneNumber'] ?? '',
            'countryCode' => $data['countryCode'] ?? '',
            'createdAt' => $this->formatTimestamp($data['createdAt'] ?? null),
            'active' => $data['active'] ?? false,
        ];

        switch ($role) {
            case 'customer':
                // Customer fields: Username, Email, Phone Number, Zone Management, Date, Active/Inactive
                $transformed['userName'] = trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
                $transformed['zoneId'] = $data['zoneId'] ?? '';
                $transformed['profilePictureURL'] = $data['profilePictureURL'] ?? null;
                break;

            case 'vendor':
                // Vendor fields: vendorname, Email, Phone Number, Zone Management, Vendor Type, Current Plan, ExpiryDate, Date, Documents, Active
                $transformed['vendorName'] = trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
                $transformed['vendorID'] = $data['vendorID'] ?? '';
                $transformed['zoneId'] = $data['zoneId'] ?? '';
                $transformed['vType'] = $data['vType'] ?? '';
                $transformed['subscriptionPlanId'] = $data['subscriptionPlanId'] ?? $data['subscription_plan'] ?? null;
                $transformed['subscriptionExpiryDate'] = $this->formatTimestamp($data['subscriptionExpiryDate'] ?? null);
                $transformed['isDocumentVerify'] = $data['isDocumentVerify'] ?? false;
                $transformed['wallet_amount'] = $data['wallet_amount'] ?? 0;
                break;

            case 'driver':
                // Driver fields: Name, Email, Phone Number, Date, Documents, Active, Online, Wallet History, Total Orders
                $transformed['name'] = trim(($data['firstName'] ?? '') . ' ' . ($data['lastName'] ?? ''));
                $transformed['isDocumentVerify'] = $data['isDocumentVerify'] ?? false;
                $transformed['isActive'] = $data['isActive'] ?? null;
                $transformed['online'] = !empty($data['fcmToken']); // Presence of FCM token indicates online
                $transformed['wallet_amount'] = $data['wallet_amount'] ?? 0;
                $transformed['orderCompleted'] = $data['orderCompleted'] ?? 0;
                $transformed['zoneId'] = $data['zoneId'] ?? '';
                $transformed['inProgressOrderID'] = $data['inProgressOrderID'] ?? null;
                break;
        }

        return $transformed;
    }

    /**
     * Format Firestore timestamp to readable format
     */
    private function formatTimestamp($timestamp)
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
