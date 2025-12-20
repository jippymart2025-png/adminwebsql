<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\MySQLImpersonationService;
use Illuminate\Support\Facades\Cache;

class ImpersonationController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new MySQLImpersonationService();
    }

    public function generateToken(Request $request)
    {
        try {
            $request->validate([
                'restaurant_id' => 'required'
            ]);

            $admin = Auth::user();

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not authenticated'
                ], 401);
            }

            $restaurantId = $request->restaurant_id;

            // Generate mysql-based impersonation token
            $result = $this->service->generateToken($restaurantId, $admin->id);

            if (!$result['success']) {
                return response()->json($result, 400);
            }

//        $restaurantPanel = config('app.restaurant_panel_url', 'http://127.0.0.1:8001');
            $restaurantPanel = env('RESTAURANT_PANEL_URL', 'http://127.0.0.1:8001');
            $url = $restaurantPanel . '/login/impersonate?key=' . $result['key'];

            return response()->json([
                'success' => true,
                'impersonation_url' => $url,
                'message' => "Redirecting to restaurant owner " . $result['owner_name']
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Impersonation token generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'restaurant_id' => $request->input('restaurant_id'),
                'admin_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server error. Please try again later.'
            ], 500);
        }
    }
}
