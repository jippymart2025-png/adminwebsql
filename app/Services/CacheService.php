<?php

namespace App\Services;

use App\Models\vendor_products;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\restaurant_orders;
use App\Models\User;
use App\Models\Driver;
use App\Models\Vendor;

class CacheService
{
    /**
     * Get dashboard statistics (cached for 1 hour)
     */
    public static function getDashboardStats($forceRefresh = false)
    {
        if ($forceRefresh) {
            Cache::forget('dashboard_stats');
        }

        return Cache::remember('dashboard_stats', 3600, function () {
            return [
                'orders' => restaurant_orders::count(),
                'products' => vendor_products::count(),
                'users' => User::where('role', '=', 'customer')->count(),
                'drivers' => User::where('role', '=', 'driver')->count(),
                'vendors' => Vendor::count(),
                'earnings' => self::getTotalEarnings(),
                'admin_commission' => self::getAdminCommission(),
                'orders_by_status' => [
                    'placed'     => restaurant_orders::where('status', 'order-placed')->count(),
                    'confirmed'  => restaurant_orders::where('status', 'order-confirmed')->count(),
                    'shipped'    => restaurant_orders::where('status', 'order-shipped')->count(),
                    'completed'  => restaurant_orders::where('status', 'order-completed')->count(),
                    'canceled'   => restaurant_orders::where('status', 'order-canceled')->count(),
                    'failed'     => restaurant_orders::where('status', 'order-failed')->count(),
                    'pending'    => restaurant_orders::where('status', 'order-pending')->count(),
                ],
                'cached_at' => now()->toDateTimeString(),
            ];
        });
    }

    /**
     * Calculate total earnings using Eloquent
     */
    private static function getTotalEarnings()
    {
        return Cache::remember('total_earnings', 300, function () {
            try {
                // ✅ Only include orders that are completed or shipped
                $statuses = ['Order Completed', 'Order Shipped'];

                // ✅ Check if the toPayAmount column exists
                if (!Schema::hasColumn('restaurant_orders', 'toPayAmount')) {
                    Log::warning('Column toPayAmount not found in restaurant_orders table.');
                    return 0;
                }

                // ✅ Sum all toPayAmount for given statuses
                $total = restaurant_orders::whereIn('status', $statuses)
                    ->sum('toPayAmount');

                return round((float) $total, 2);
            } catch (\Exception $e) {
                Log::error('Error calculating total earnings: ' . $e->getMessage());
                return 0;
            }
        });
    }

    private static function getAdminCommission()
    {
        return Cache::remember('admin_commission', 300, function () {
            try {
                // ✅ Include only completed/shipped orders
                $statuses = ['Order Completed', 'Order Shipped'];

                // ✅ Check if the adminCommission column exists
                if (!Schema::hasColumn('restaurant_orders', 'adminCommission')) {
                    Log::warning('Column adminCommission not found in restaurant_orders table.');
                    return 0;
                }

                // ✅ Sum adminCommission for completed/shipped orders
                $total = restaurant_orders::whereIn('status', $statuses)
                    ->sum('adminCommission');

                return round((float) $total, 2);
            } catch (\Exception $e) {
                Log::error('Error calculating admin commission: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Clear dashboard cache
     */
    public static function clearDashboardCache()
    {
        Cache::forget('dashboard_stats');
        Cache::forget('total_earnings');
    }

    /**
     * Get cache metadata
     */
    public static function getCacheStats()
    {
        if (Cache::has('dashboard_stats')) {
            return [
                'exists' => true,
                'cached_at' => Cache::get('dashboard_stats')['cached_at'] ?? 'Unknown',
            ];
        }

        return ['exists' => false];
    }
}
