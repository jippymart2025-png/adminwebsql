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

        if (!$role || !in_array($role, ['customer', 'vendor', 'driver'])) {
            return response()->json([
                'status' => false,
                'message' => 'Valid role is required (customer, vendor, driver)',
            ], 400);
        }

        // Calculate offset for page-based pagination
        $offset = ($page - 1) * $limit;

        $cacheKey = "firebase_users_v4_role_{$role}_page_{$page}_limit_{$limit}";

        $data = Cache::remember($cacheKey, 30, function () use ($role, $limit, $offset, $page) {
            $query = $this->firestore
                ->collection('users')
                ->where('role', '==', $role)
                ->orderBy('createdAt', 'DESCENDING');

            // For page-based pagination, we need to fetch all documents up to current page
            // and then slice the results (Firestore doesn't support offset directly)
            $totalToFetch = $offset + $limit + 1; // +1 to check if there's more
            $query = $query->limit($totalToFetch);

            $documents = $query->documents();

            $allUsers = [];
            $count = 0;

            foreach ($documents as $document) {
                $allUsers[] = [
                    'data' => $document->data(),
                    'id' => $document->id(),
                ];
                $count++;
            }

            // Skip to the offset position
            $users = [];
            $hasMore = false;
            $nextCreatedAt = null;
            $nextDocId = null;

            for ($i = $offset; $i < count($allUsers) && $i < $offset + $limit; $i++) {
                $docData = $allUsers[$i]['data'];
                $docId = $allUsers[$i]['id'];

                // Transform data based on role
                $userData = $this->transformUserData($role, $docData, $docId);
                $users[] = $userData;

                // Store last document info for next page
                $nextCreatedAt = $docData['createdAt'] ?? null;
                $nextDocId = $docId;
            }

            // Check if there are more results beyond current page
            $hasMore = count($allUsers) > ($offset + $limit);

            return [
                'users' => $users,
                'has_more' => $hasMore,
                'next_created_at' => $hasMore ? $nextCreatedAt : null,
                'next_doc_id' => $hasMore ? $nextDocId : null,
            ];
        });

        // Get detailed statistics (cached separately for performance)
        // Always fetch statistics regardless of page or limit
        $countsCacheKey = "firebase_users_statistics_v2_role_{$role}";
        
        $statistics = Cache::remember($countsCacheKey, 300, function () use ($role) {
            return $this->getDetailedStatistics($role);
        });

        return response()->json([
            'status' => true,
            'message' => 'Users fetched successfully',
            'meta' => [
                'role' => $role,
                'page' => $page,
                'limit' => $limit,
                'count' => count($data['users']),
                'has_more' => $data['has_more'],
                'next_created_at' => $data['next_created_at'],
                'next_doc_id' => $data['next_doc_id'],
            ],
            'statistics' => $statistics,
            'data' => $data['users'],
        ]);
    }

    /**
     * Get detailed statistics for users by role
     * 
     * @param string $role
     * @return array
     */
    private function getDetailedStatistics(string $role): array
    {
        $snapshot = $this->firestore
            ->collection('users')
            ->where('role', '==', $role)
            ->documents();

        $total = 0;
        $active = 0;
        $inactive = 0;
        $verified = 0;

        foreach ($snapshot as $doc) {
            $data = $doc->data();
            $total++;

            // Count active/inactive
            $isActive = $data['active'] ?? false;
            if ($isActive) {
                $active++;
            } else {
                $inactive++;
            }

            // Count verified (for vendors and drivers)
            if (in_array($role, ['vendor', 'driver'])) {
                $isVerified = $data['isDocumentVerify'] ?? false;
                if ($isVerified) {
                    $verified++;
                }
            }
        }

        // Build statistics based on role
        $stats = [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
        ];

        // Add role-specific fields
        switch ($role) {
            case 'customer':
                $stats['total_customers'] = $total;
                $stats['active_customers'] = $active;
                $stats['inactive_customers'] = $inactive;
                break;

            case 'vendor':
                $stats['total_vendors'] = $total;
                $stats['active_vendors'] = $active;
                $stats['inactive_vendors'] = $inactive;
                $stats['verified_vendors'] = $verified;
                break;

            case 'driver':
                $stats['total_drivers'] = $total;
                $stats['active_drivers'] = $active;
                $stats['inactive_drivers'] = $inactive;
                $stats['verified_drivers'] = $verified;
                break;
        }

        return $stats;
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
