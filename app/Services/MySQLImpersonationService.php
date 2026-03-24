<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MySQLImpersonationService
{
    /**
     * Generate impersonation token for a restaurant owner (MYSQL)
     */

    public function generateToken($restaurantId, $adminId)
    {
        $owner = null;

        try {
            $owner = DB::table('users')
                ->where('vendorID', $restaurantId)
                ->where('role', 'vendor')
                ->first();

            if (!$owner) {
                return ['success' => false, 'error' => 'Owner not found'];
            }

            // Build owner name safely
            $firstName = $owner->firstName ?? '';
            $lastName = $owner->lastName ?? '';
            $ownerName = trim($firstName . ' ' . $lastName);
            if (empty($ownerName)) {
                $ownerName = $owner->email ?? 'Restaurant Owner';
            }

            // Ensure data types match table schema (user_id and restaurant_id are strings)
            $userId = (string)($owner->id ?? '');
            $restaurantIdStr = (string)($restaurantId ?? '');

            if (empty($userId)) {
                return ['success' => false, 'error' => 'Invalid owner ID'];
            }

            // Try to insert token (retry once if duplicate)
            $maxRetries = 2;
            $attempt = 0;
            $lastException = null;

            while ($attempt < $maxRetries) {
                try {
                    $token = 'imp_' . bin2hex(random_bytes(30));
                    $expiresAt = time() + (5 * 60);

                    DB::table('impersonation_tokens')->insert([
                        'token' => $token,
                        'user_id' => $userId,
                        'restaurant_id' => $restaurantIdStr,
                        'expires_at' => $expiresAt,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Success - return token
                    return [
                        'success' => true,
                        'key' => $token,
                        'owner_name' => $ownerName
                    ];
                } catch (\Illuminate\Database\QueryException $e) {
                    $lastException = $e;
                    $errorMessage = $e->getMessage();
                    $errorCode = $e->getCode();

                    // If duplicate token error, retry with new token
                    if ((strpos($errorMessage, 'Duplicate entry') !== false || $errorCode == 23000) && $attempt < $maxRetries - 1) {
                        $attempt++;
                        continue; // Retry with new token
                    }

                    // Other database errors or max retries reached
                    Log::error('MySQLImpersonationService::generateToken database error', [
                        'restaurant_id' => $restaurantId,
                        'admin_id' => $adminId,
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'attempt' => $attempt + 1,
                        'trace' => $e->getTraceAsString()
                    ]);

                    return [
                        'success' => false,
                        'error' => config('app.debug') ? $errorMessage : 'Failed to generate impersonation token. Please try again later.'
                    ];
                }
            }

            // Should not reach here, but just in case
            if ($lastException) {
                throw $lastException;
            }

        } catch (\Exception $e) {
            Log::error('MySQLImpersonationService::generateToken error: ' . $e->getMessage(), [
                'restaurant_id' => $restaurantId,
                'admin_id' => $adminId,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => config('app.debug') ? $e->getMessage() : 'Failed to generate impersonation token. Please try again later.'
            ];
        }
    }


    /**
     * Validate impersonation key
     */
    public function validateKey($key)
    {
        return Cache::get($key);
    }
}
