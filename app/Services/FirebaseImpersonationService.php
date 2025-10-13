<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\ImpersonationSecurityMonitor;

class FirebaseImpersonationService
{
    private $firestore;
    private $serviceAccount;
    private $projectId;
    private $securityMonitor;

    public function __construct()
    {
        $this->projectId = config('firestore.project_id');
        $this->firestore = new FirestoreClient([
            'projectId' => $this->projectId,
            'keyFilePath' => config('firestore.credentials'),
        ]);
        
        // Load service account for JWT signing
        $this->serviceAccount = json_decode(
            file_get_contents(config('firestore.credentials')), 
            true
        );
        
        // Initialize security monitor
        $this->securityMonitor = new ImpersonationSecurityMonitor();
    }

    /**
     * Generate a Firebase Custom Token for restaurant impersonation
     * 
     * @param string $restaurantId The restaurant's Firestore document ID
     * @param string $adminUserId The admin user ID who is impersonating
     * @param int $expirationMinutes Token expiration time in minutes (default: 5)
     * @return array
     */
    public function generateImpersonationToken($restaurantId, $adminUserId, $expirationMinutes = 5)
    {
        try {
            // Check cache first for restaurant data
            $cacheKey = "restaurant_data_{$restaurantId}";
            $restaurantData = Cache::remember($cacheKey, 300, function() use ($restaurantId) {
                $restaurantDoc = $this->firestore->collection('vendors')->document($restaurantId)->snapshot();
                
                if (!$restaurantDoc->exists()) {
                    throw new \Exception("Restaurant not found: {$restaurantId}");
                }
                
                return $restaurantDoc->data();
            });
            
            // Check cache for restaurant owner UID
            $ownerCacheKey = "restaurant_owner_{$restaurantId}";
            $restaurantOwnerUid = Cache::remember($ownerCacheKey, 600, function() use ($restaurantId) {
                $userQuery = $this->firestore->collection('users')
                    ->where('vendorID', '=', $restaurantId)
                    ->limit(1)
                    ->documents();

                foreach ($userQuery as $userDoc) {
                    return $userDoc->id();
                }
                
                throw new \Exception("Restaurant owner not found for restaurant: {$restaurantId}");
            });

            // Generate custom token
            $customToken = $this->createCustomToken($restaurantOwnerUid, [
                'admin_impersonation' => true,
                'impersonated_by' => $adminUserId,
                'restaurant_id' => $restaurantId,
                'restaurant_name' => $restaurantData['title'] ?? 'Unknown Restaurant',
                'expires_at' => Carbon::now()->addMinutes($expirationMinutes)->timestamp
            ], $expirationMinutes);

            // Log the impersonation for security audit
            $this->logImpersonation($adminUserId, $restaurantId, $restaurantOwnerUid, $restaurantData['title'] ?? 'Unknown');

            // Monitor security activity
            $this->securityMonitor->monitorActivity(
                $adminUserId, 
                $restaurantId, 
                request()->ip(), 
                request()->userAgent(), 
                true
            );

            // Cache the token for additional security (prevent reuse)
            $cacheKey = "impersonation_token_{$restaurantOwnerUid}_" . time();
            Cache::put($cacheKey, $customToken, now()->addMinutes($expirationMinutes + 1));

            return [
                'success' => true,
                'custom_token' => $customToken,
                'restaurant_uid' => $restaurantOwnerUid,
                'restaurant_name' => $restaurantData['title'] ?? 'Unknown Restaurant',
                'expires_in' => $expirationMinutes * 60, // seconds
                'cache_key' => $cacheKey
            ];

        } catch (\Google\Cloud\Core\Exception\ServiceException $e) {
            Log::error('Firestore Service Error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'restaurant_id' => $restaurantId,
                'admin_user_id' => $adminUserId
            ]);

            // Monitor failed attempt
            $this->securityMonitor->monitorActivity(
                $adminUserId, 
                $restaurantId, 
                request()->ip(), 
                request()->userAgent(), 
                false
            );

            return [
                'success' => false,
                'error' => 'Database service temporarily unavailable. Please try again.',
                'retry_after' => 30
            ];
        } catch (\Exception $e) {
            Log::error('Firebase Impersonation Error', [
                'error' => $e->getMessage(),
                'restaurant_id' => $restaurantId,
                'admin_user_id' => $adminUserId
            ]);

            // Monitor failed attempt
            $this->securityMonitor->monitorActivity(
                $adminUserId, 
                $restaurantId, 
                request()->ip(), 
                request()->userAgent(), 
                false
            );

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create a Firebase Custom Token using JWT
     */
    private function createCustomToken($uid, $customClaims = [], $expirationMinutes = 5)
    {
        $now = time();
        $exp = $now + ($expirationMinutes * 60);

        $payload = [
            'iss' => $this->serviceAccount['client_email'],
            'sub' => $this->serviceAccount['client_email'],
            'aud' => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat' => $now,
            'exp' => $exp,
            'uid' => $uid,
            'claims' => $customClaims
        ];

        return JWT::encode($payload, $this->serviceAccount['private_key'], 'RS256');
    }

    /**
     * Log impersonation activity for security audit
     */
    private function logImpersonation($adminUserId, $restaurantId, $restaurantUid, $restaurantName)
    {
        try {
            $logData = [
                'type' => 'admin_impersonation',
                'admin_user_id' => $adminUserId,
                'restaurant_id' => $restaurantId,
                'restaurant_uid' => $restaurantUid,
                'restaurant_name' => $restaurantName,
                'timestamp' => Carbon::now()->toISOString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];

            // Log to Firestore
            $this->firestore->collection('admin_impersonation_logs')->add($logData);

            // Also log to Laravel logs
            Log::info('Admin Impersonation', $logData);

        } catch (\Exception $e) {
            Log::error('Failed to log impersonation', [
                'error' => $e->getMessage(),
                'admin_user_id' => $adminUserId,
                'restaurant_id' => $restaurantId
            ]);
        }
    }

    /**
     * Validate if a custom token is valid and not expired
     */
    public function validateImpersonationToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->serviceAccount['private_key'], 'RS256'));
            
            // Check if token has impersonation claims
            if (!isset($decoded->claims->admin_impersonation) || !$decoded->claims->admin_impersonation) {
                return ['valid' => false, 'error' => 'Not an impersonation token'];
            }

            // Check expiration
            if ($decoded->exp < time()) {
                return ['valid' => false, 'error' => 'Token expired'];
            }

            return [
                'valid' => true,
                'uid' => $decoded->uid,
                'claims' => $decoded->claims
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get restaurant information by ID
     */
    public function getRestaurantInfo($restaurantId)
    {
        try {
            $restaurantDoc = $this->firestore->collection('vendors')->document($restaurantId)->snapshot();
            
            if (!$restaurantDoc->exists()) {
                return ['success' => false, 'error' => 'Restaurant not found'];
            }

            $restaurantData = $restaurantDoc->data();
            
            // Get restaurant owner info
            $userQuery = $this->firestore->collection('users')
                ->where('vendorID', '=', $restaurantId)
                ->limit(1)
                ->documents();

            $ownerInfo = null;
            foreach ($userQuery as $userDoc) {
                $ownerInfo = [
                    'uid' => $userDoc->id(),
                    'data' => $userDoc->data()
                ];
                break;
            }

            return [
                'success' => true,
                'restaurant' => $restaurantData,
                'owner' => $ownerInfo
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
