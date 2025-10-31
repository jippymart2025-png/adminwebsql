<?php

namespace App\Services;

use App\Models\vendor_products;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\restaurant_orders;
use App\Models\User;
use App\Models\Vendor;

class CacheService
{
    // Caching disabled: All methods compute directly from the database
    /**
     * Get dashboard statistics (cached for 1 hour)
     */
    public static function getDashboardStats($forceRefresh = false)
    {
        // Ignoring $forceRefresh since caching is disabled
        return [
            'orders' => restaurant_orders::count(),
            'products' => vendor_products::count(),
            'users' => User::where('role', '=', 'customer')->count(),
            'drivers' => User::where('role', '=', 'driver')->count(),
            'vendors' => Vendor::count(),
            'earnings' => self::getTotalEarnings(),
            'admin_commission' => self::getAdminCommission(),
            'orders_by_status' => [
                'placed'     => restaurant_orders::where('status', 'Order Placed')->count(),
                'confirmed'  => restaurant_orders::where('status', 'Order Accepted')->count(),
                'shipped'    => restaurant_orders::where('status', 'Order Shipped')->count(),
                'completed'  => restaurant_orders::where('status', 'Order Completed')->count(),
                'canceled'   => restaurant_orders::where('status', 'Order Rejected')->count(),
                'failed'     => restaurant_orders::where('status', 'Driver Rejected')->count(),
                'pending'    => restaurant_orders::where('status', 'Driver Pending')->count(),
            ],
            'cached_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Calculate total earnings using Eloquent
     */
    private static function getTotalEarnings()
    {
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
    }

    private static function getAdminCommission()
    {
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
    }

    /**
     * Clear dashboard cache
     */
    public static function clearDashboardCache()
    {
        // No-op: caching disabled
    }

    /**
     * Get cache metadata
     */
    public static function getCacheStats()
    {
        return ['exists' => false];
    }
}
