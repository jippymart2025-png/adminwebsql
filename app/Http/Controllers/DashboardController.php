<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Show dashboard with cached data
     */
    public function index()
    {
        try {
            // Get cached dashboard stats
            $stats = CacheService::getDashboardStats();
            
            // Log cache hit/miss for monitoring
            Log::info('Dashboard stats retrieved', [
                'cached_at' => $stats['cached_at'] ?? 'Not cached',
                'source' => 'CacheService'
            ]);
            
            return view('home', compact('stats'));
            
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            
            // Fallback to default view if caching fails
            return view('home');
        }
    }

    /**
     * Get dashboard stats via AJAX
     */
    public function getStats(Request $request)
    {
        try {
            $forceRefresh = $request->boolean('refresh', false);
            $stats = CacheService::getDashboardStats($forceRefresh);
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'cached' => !$forceRefresh
            ]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache()
    {
        try {
            CacheService::clearDashboardCache();
            
            return response()->json([
                'success' => true,
                'message' => 'Dashboard cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache clear error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        try {
            $stats = CacheService::getCacheStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving cache stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

