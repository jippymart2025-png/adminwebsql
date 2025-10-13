<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Google\Cloud\Firestore\FirestoreClient;

class CacheService
{
    /**
     * Cache Firestore query results with intelligent invalidation
     */
    public static function rememberFirestoreQuery($key, $callback, $ttl = 300)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache dashboard statistics
     */
    public static function getDashboardStats($forceRefresh = false)
    {
        $cacheKey = 'dashboard_stats';
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 300, function () {
            return [
                'total_marts' => self::getTotalMarts(),
                'active_marts' => self::getActiveMarts(),
                'total_orders' => self::getTotalOrders(),
                'total_clients' => self::getTotalClients(),
                'total_drivers' => self::getTotalDrivers(),
                'total_earnings' => self::getTotalEarnings(),
                'admin_commission' => self::getAdminCommission(),
                'cached_at' => now()->toISOString()
            ];
        });
    }

    /**
     * Get total marts count
     */
    private static function getTotalMarts()
    {
        return Cache::remember('total_marts', 600, function () {
            try {
                $firestore = app(FirestoreClient::class);
                $collection = $firestore->collection('vendors');
                $documents = $collection->documents();
                return iterator_count($documents);
            } catch (\Exception $e) {
                \Log::error('Error getting total marts: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get active marts count
     */
    private static function getActiveMarts()
    {
        return Cache::remember('active_marts', 600, function () {
            try {
                $firestore = app(FirestoreClient::class);
                $collection = $firestore->collection('vendors');
                $query = $collection->where('isActive', '=', true);
                $documents = $query->documents();
                return iterator_count($documents);
            } catch (\Exception $e) {
                \Log::error('Error getting active marts: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total orders count
     */
    private static function getTotalOrders()
    {
        return Cache::remember('total_orders', 300, function () {
            try {
                $firestore = app(FirestoreClient::class);
                $collection = $firestore->collection('restaurant_orders');
                $documents = $collection->documents();
                return iterator_count($documents);
            } catch (\Exception $e) {
                \Log::error('Error getting total orders: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total clients count
     */
    private static function getTotalClients()
    {
        return Cache::remember('total_clients', 600, function () {
            try {
                $firestore = app(FirestoreClient::class);
                $collection = $firestore->collection('users');
                $query = $collection->where('role', '=', 'client');
                $documents = $query->documents();
                return iterator_count($documents);
            } catch (\Exception $e) {
                \Log::error('Error getting total clients: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total drivers count
     */
    private static function getTotalDrivers()
    {
        return Cache::remember('total_drivers', 600, function () {
            try {
                $firestore = app(FirestoreClient::class);
                $collection = $firestore->collection('users');
                $query = $collection->where('role', '=', 'driver');
                $documents = $query->documents();
                return iterator_count($documents);
            } catch (\Exception $e) {
                \Log::error('Error getting total drivers: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total earnings
     */
    private static function getTotalEarnings()
    {
        return Cache::remember('total_earnings', 300, function () {
            try {
                $firestore = app(FirestoreClient::class);
                $collection = $firestore->collection('restaurant_orders');
                $query = $collection->where('status', 'in', ['completed', 'delivered']);
                $documents = $query->documents();
                
                $total = 0;
                foreach ($documents as $document) {
                    $data = $document->data();
                    if (isset($data['toPayAmount'])) {
                        $total += (float) $data['toPayAmount'];
                    }
                }
                
                return round($total, 2);
            } catch (\Exception $e) {
                \Log::error('Error getting total earnings: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get admin commission
     */
    private static function getAdminCommission()
    {
        return Cache::remember('admin_commission', 300, function () {
            try {
                $firestore = app(FirestoreClient::class);
                $collection = $firestore->collection('restaurant_orders');
                $query = $collection->where('status', 'in', ['completed', 'delivered']);
                $documents = $query->documents();
                
                $total = 0;
                foreach ($documents as $document) {
                    $data = $document->data();
                    if (isset($data['adminCommission'])) {
                        $total += (float) $data['adminCommission'];
                    }
                }
                
                return round($total, 2);
            } catch (\Exception $e) {
                \Log::error('Error getting admin commission: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Clear all dashboard caches
     */
    public static function clearDashboardCache()
    {
        $keys = [
            'dashboard_stats',
            'total_marts',
            'active_marts',
            'total_orders',
            'total_clients',
            'total_drivers',
            'total_earnings',
            'admin_commission'
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        \Log::info('Dashboard cache cleared');
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats()
    {
        return [
            'total_keys' => Cache::get('cache_stats_total', 0),
            'hit_rate' => Cache::get('cache_stats_hit_rate', 0),
            'last_cleared' => Cache::get('cache_stats_last_cleared', 'Never'),
            'memory_usage' => Cache::get('cache_stats_memory', 'Unknown')
        ];
    }
}

