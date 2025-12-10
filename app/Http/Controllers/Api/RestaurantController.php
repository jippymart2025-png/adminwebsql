<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RestaurantController extends Controller
{
    /**
     * Get Nearest Restaurants (Stream/Real-time)
     * GET /api/restaurants/nearest
     *
     * Query Parameters:
     * - zone_id (required): Current zone ID
     * - latitude (required): User's latitude
     * - longitude (required): User's longitude
     * - radius (required): Search radius in km
     * - is_dining (optional): Filter for dine-in restaurants (default: false)
     * - user_id (optional): For subscription filtering
     */
    public function nearest(Request $request)
    {
        // Validate request
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0',
            'is_dining' => 'nullable|boolean',
            'user_id' => 'nullable|string',
            'filter' => 'nullable|string|in:distance,rating',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $zoneId = $request->input('zone_id');
        $userLat = $request->input('latitude');
        $userLon = $request->input('longitude');
        $radius = $request->has('radius') ? $request->input('radius') : null;
        $isDining = $request->input('is_dining', false);
        $userId = $request->input('user_id');
        $filter = $request->input('filter', 'distance'); // default filter

        try {
            // Build query
            $query = Vendor::query()
                ->select('vendors.*')
                ->where('zoneId', $zoneId)
                ->where(function ($q) {
                    $q->where('publish', true)->orWhereNull('publish');
                });

            $hasCoordinates = $request->filled('latitude') && $request->filled('longitude');

            if ($hasCoordinates) {
                $query->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->selectRaw(
                        '(6371 * acos(cos(radians(?)) * cos(radians(latitude))
                    * cos(radians(longitude) - radians(?))
                    + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                        [$userLat, $userLon, $userLat]
                    );

                if ($radius !== null && $filter !== 'rating') {
                    $query->havingRaw('distance <= ?', [$radius]);
                }
            }

            // Dine-in filter
            if ($isDining) {
                $query->where('enabledDiveInFuture', true);
            }

            // Type filter (exclude marts)
            if (DB::getSchemaBuilder()->hasColumn('vendors', 'vType')) {
                $query->where(function ($q) {
                    $q->where('vType', 'restaurant')
                        ->orWhere('vType', 'food')
                        ->orWhereNull('vType');
                })
                    ->where('vType', '!=', 'mart');
            }

            // Apply sorting based on filter
            switch ($filter) {
                case 'rating':
                    $query->orderByRaw('CASE WHEN COALESCE(reviewsCount, 0) > 0 THEN COALESCE(reviewsSum, 0) / NULLIF(reviewsCount, 0) ELSE 0 END DESC')
                          ->orderByRaw('COALESCE(reviewsCount, 0) DESC');
                    break;

                case 'distance':
                default:
                    if ($hasCoordinates) {
                        $query->orderBy('distance', 'asc');
                    } else {
                        $query->orderBy('title', 'asc');
                    }
                    break;
            }

        // Fetch restaurants
        $restaurants = $query->get();

        // Format and filter subscriptions
        $data = $restaurants->map(function ($restaurant) use ($userId) {
            return $this->formatRestaurantResponse($restaurant, $userId);
        })->filter(function ($restaurant) {
            return $this->isSubscriptionValid($restaurant);
        })->values();

        // Apply rating sort if requested
        if ($filter === 'rating') {
            $data = $data->sortByDesc(function ($restaurant) {
                return $restaurant['reviewsAverage'] ?? 0;
            })->values();
        }

        // Always sort closed restaurants to bottom while preserving current order
        $sortedData = $data->sortBy(function ($r, $index) {
            return [$r['isOpen'] ? 0 : 1, $index];
        })->values();

        // Count restaurants where isOpen is true
        $openCount = $sortedData->filter(fn ($restaurant) => isset($restaurant['isOpen']) && $restaurant['isOpen'] === true)->count();

            return response()->json([
                'success' => true,
                'filter' => $filter,
                'availableFilters' => ['distance','rating'],
                'count' => $sortedData->count(),
                'openCount' => $openCount,
                'data' => $sortedData,
            ]);

        } catch (\Exception $e) {
            Log::error('Nearest Restaurants Error: ' . $e->getMessage(), [
                'zone_id' => $zoneId,
                'latitude' => $userLat,
                'longitude' => $userLon,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch nearest restaurants',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Check if subscription is valid (Business Logic #3)
     *
     * Rules:
     * - Include if subscriptionTotalOrders = "-1" (unlimited)
     * - OR if subscription is valid (not expired) AND subscriptionTotalOrders > 0
     * - Exclude if subscription expired or orders exhausted
     * - Include if no subscription (free/commission model)
     */
    private function isSubscriptionValid($restaurant)
    {
        // If no subscription data, include restaurant (free/commission model)
        if (empty($restaurant['subscriptionPlan'])) {
            return true;
        }

        $totalOrders = $restaurant['subscriptionTotalOrders'] ?? '0';
        $expiryDate = $restaurant['subscriptionExpiryDate'] ?? null;

        // Unlimited orders (-1 means unlimited)
        if ($totalOrders === '-1' || (int)$totalOrders === -1) {
            return true;
        }

        // Check if subscription is not expired
        $isNotExpired = true;
        if ($expiryDate !== null) {
            try {
                $expiry = new \DateTime($expiryDate);
                $now = new \DateTime();
                $isNotExpired = $expiry >= $now;
            } catch (\Exception $e) {
                // If date parsing fails, assume not expired
                $isNotExpired = true;
            }
        }

        // Check if orders available and not expired
        $ordersAvailable = (int)$totalOrders > 0;

        return $isNotExpired && $ordersAvailable;
    }

    /**
     * Format restaurant data for API response
     */
    private function formatRestaurantResponse($restaurant, $userId = null)
    {
        // Get subscription data if exists
        $subscriptionPlan = null;
        $subscriptionTotalOrders = null;
        $subscriptionExpiryDate = null;

        if (DB::getSchemaBuilder()->hasTable('subscription_history')) {
            $subscription = DB::table('subscription_history')
                ->where('user_id', $restaurant->id) // user_id contains vendor IDs
                ->where(function($q) {
                    $q->where('expiry_date', '>=', now())
                      ->orWhereNull('expiry_date'); // NULL means unlimited
                })
                ->orderBy('expiry_date', 'desc')
                ->first();

            if ($subscription) {
                // Parse subscription_plan JSON if it exists
                $plan = null;
                if (!empty($subscription->subscription_plan)) {
                    $plan = json_decode($subscription->subscription_plan, true);
                }

                if ($plan) {
                    $subscriptionPlan = [
                        'id' => $plan['id'] ?? null,
                        'expiryDay' => $plan['expiryDay'] ?? null,
                        'expiryDate' => $subscription->expiry_date ?? null
                    ];
                    $subscriptionTotalOrders = $plan['orderLimit'] ?? null;
                    $subscriptionExpiryDate = $subscription->expiry_date ?? null;
                }
            }
        }

        // Calculate review average
        $reviewsAverage = 0;
        if ($restaurant->reviewsCount > 0 && isset($restaurant->reviewsSum)) {
            $reviewsAverage = round($restaurant->reviewsSum / $restaurant->reviewsCount, 1);
        }

        return [
            'id' => $restaurant->id,
            'title' => $restaurant->title ?? '',
            'zoneId' => $restaurant->zoneId ?? '',
            'latitude' => (float) $restaurant->latitude,
            'longitude' => (float) $restaurant->longitude,
            'distance' => round($restaurant->distance ?? 0, 2),
            'vType' => $restaurant->vType ?? 'restaurant',
            'isActive' => (bool) ($restaurant->publish ?? true), // Using publish field (NULL/TRUE = active)
            'isOpen' => $this->calculateActualIsOpen(
                $restaurant->isOpen == 1 || $restaurant->isOpen === 'true' || $restaurant->isOpen === true,
                $restaurant->workingHours ? json_decode($restaurant->workingHours, true) : []
            ),
            'subscriptionPlan' => $subscriptionPlan,
            'author' => $restaurant->author,
            'subscriptionTotalOrders' => $subscriptionTotalOrders,
            'subscriptionExpiryDate' => $subscriptionExpiryDate,
            'reviewsCount' => (int) ($restaurant->reviewsCount ?? 0),
            'reviewsSum' => (float) ($restaurant->reviewsSum ?? 0),
            'reviewsAverage' => $reviewsAverage,
            'workingHours' => json_decode($restaurant->workingHours ?? ''),
            'restaurantCost' => $restaurant->restaurantCost ?? $restaurant->DeliveryCharge ?? '0',
            'createdAt' => $restaurant->createdAt ?? $restaurant->created_at ?? now()->toISOString(),
            'photo' => $restaurant->photo ?? $restaurant->categoryPhoto ?? $restaurant->photos ?? '',
            'location' => $restaurant->location ?? '',
            'enabledDiveInFuture' => (bool) ($restaurant->enabledDiveInFuture ?? false),
            'description' => $restaurant->description ?? '',
            'phonenumber' => $restaurant->phonenumber ?? '',
            'adminCommission' => $restaurant->adminCommission ?? 0,
            'specialDiscountEnable' => (bool) ($restaurant->specialDiscountEnable ?? false),
        ];
    }

    /**
     * Get Restaurant by ID
     * GET /api/restaurants/{id}
     */
    public function show($id)
    {
        try {
            $restaurant = Vendor::find($id);

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatRestaurantResponse($restaurant)
            ]);

        } catch (\Exception $e) {
            Log::error('Get Restaurant Error: ' . $e->getMessage(), ['id' => $id]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch restaurant',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get Restaurants by Zone
     * GET /api/restaurants/by-zone/{zone_id}
     */
    public function byZone($zoneId)
    {
        try {
            $restaurants = Vendor::where('zoneId', $zoneId)
                ->where(function($q) {
                    // Treat NULL and TRUE as published, only FALSE as not published
                    $q->where('publish', true)->orWhereNull('publish');
                })
                ->get();

            $data = $restaurants->map(function ($restaurant) {
                return $this->formatRestaurantResponse($restaurant);
            });

            // Count restaurants where isOpen is true
            $openCount = $data->filter(fn ($restaurant) => isset($restaurant['isOpen']) && $restaurant['isOpen'] === true)->count();

            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count(),
                'openCount' => $openCount
            ]);

        } catch (\Exception $e) {
            Log::error('Get Restaurants by Zone Error: ' . $e->getMessage(), ['zone_id' => $zoneId]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch restaurants',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Search Restaurants
     * GET /api/restaurants/search
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'zone_id' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Vendor::where(function($q) {
                    // Treat NULL and TRUE as published, only FALSE as not published
                    $q->where('publish', true)->orWhereNull('publish');
                })
                ->where(function($q) use ($request) {
                    $searchTerm = $request->input('query');
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%")
                      ->orWhere('location', 'like', "%{$searchTerm}%");
                });

            // Filter by zone if provided
            if ($request->has('zone_id')) {
                $query->where('zoneId', $request->input('zone_id'));
            }

            // Add distance calculation if lat/lon provided
            if ($request->has('latitude') && $request->has('longitude')) {
                $lat = $request->input('latitude');
                $lon = $request->input('longitude');

                $query->selectRaw(
                    'vendors.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$lat, $lon, $lat]
                )->orderBy('distance', 'asc');
            } else {
                $query->orderBy('title', 'asc');
            }

            $restaurants = $query->get();
            $data = $restaurants->map(function ($restaurant) {
                return $this->formatRestaurantResponse($restaurant);
            });
            
            // Count restaurants where isOpen is true
            $openCount = $data->filter(fn ($restaurant) => isset($restaurant['isOpen']) && $restaurant['isOpen'] === true)->count();
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'count' => $data->count(),
                'openCount' => $openCount
            ]);

        } catch (\Exception $e) {
            Log::error('Search Restaurants Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search restaurants',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Calculate actual isOpen status based on isOpen flag and working hours
     * 
     * Logic:
     * 1. If working hours are DISABLED (empty/null or all timeslots empty) → always return false (closed)
     * 2. If working hours are ENABLED:
     *    - If isOpen flag is false → return false (closed)
     *    - If isOpen flag is true → check working hours:
     *      - If current time is within working hours → return true (open)
     *      - If current time is outside working hours → return false (closed)
     * 
     * @param bool $isOpenFlag The isOpen flag from database
     * @param array|null $workingHours The working hours array from database
     * @return bool
     */
    protected function calculateActualIsOpen(bool $isOpenFlag, ?array $workingHours): bool
    {
        // Check if working hours are disabled (empty/null or all timeslots are empty)
        $hasValidWorkingHours = false;
        if (!empty($workingHours) && is_array($workingHours)) {
            foreach ($workingHours as $daySchedule) {
                if (isset($daySchedule['timeslot']) && is_array($daySchedule['timeslot']) && !empty($daySchedule['timeslot'])) {
                    // Check if at least one timeslot has valid from/to
                    foreach ($daySchedule['timeslot'] as $timeslot) {
                        $fromRaw = isset($timeslot['from']) ? trim((string)$timeslot['from']) : '';
                        $toRaw = isset($timeslot['to']) ? trim((string)$timeslot['to']) : '';
                        if ($fromRaw !== '' && $toRaw !== '') {
                            $hasValidWorkingHours = true;
                            break 2; // Break out of both loops
                        }
                    }
                }
            }
        }

        // Rule 3 & 4: If working hours are disabled (no valid timeslots), restaurant is always closed
        if (!$hasValidWorkingHours) {
            return false;
        }

        // Rule 2: If working hours enabled but isOpen flag is false, restaurant is closed
        if (!$isOpenFlag) {
            return false;
        }

        // Rule 1: Working hours enabled and isOpen flag is true - check if within working hours
        // Get current day and time in app timezone
        $tz = config('app.timezone') ?: 'UTC';
        $now = Carbon::now($tz);
        $currentDay = $now->format('l'); // e.g., Monday
        $currentMinutes = (int)$now->format('H') * 60 + (int)$now->format('i');

        // Check if current time is within any timeslot for today
        foreach ($workingHours as $daySchedule) {
            if (!isset($daySchedule['day']) || $daySchedule['day'] !== $currentDay) {
                continue;
            }

            if (!isset($daySchedule['timeslot']) || !is_array($daySchedule['timeslot']) || empty($daySchedule['timeslot'])) {
                continue;
            }

            // Check each timeslot for today
            foreach ($daySchedule['timeslot'] as $timeslot) {
                $fromRaw = isset($timeslot['from']) ? trim((string)$timeslot['from']) : '';
                $toRaw = isset($timeslot['to']) ? trim((string)$timeslot['to']) : '';
                if ($fromRaw === '' || $toRaw === '') {
                    continue;
                }

                $fromMinutes = $this->parseTimeToMinutes($fromRaw, $tz);
                $toMinutes = $this->parseTimeToMinutes($toRaw, $tz);

                if ($fromMinutes === null || $toMinutes === null) {
                    continue; // skip invalid time formats
                }

                if ($currentMinutes >= $fromMinutes && $currentMinutes <= $toMinutes) {
                    return true; // Within working hours - restaurant is open
                }
            }
        }

        // Not within working hours - restaurant is closed
        return false;
    }

    /**
     * Parse a time string (supports "H:i" or "h:i A") into minutes since midnight.
     * Expects 24-hour format "HH:MM" with leading zeros (e.g., "09:30", "11:30", "22:00").
     */
    protected function parseTimeToMinutes(string $timeString, string $timezone = 'UTC'): ?int
    {
        $timeString = trim($timeString);
        if ($timeString === '') {
            return null;
        }

        $formats = ['H:i', 'G:i', 'h:i A', 'g:i A', 'H:i:s', 'h:i:s A'];

        foreach ($formats as $format) {
            try {
                $dt = Carbon::createFromFormat($format, $timeString, $timezone);
                if ($dt !== false) {
                    return $dt->format('H') * 60 + (int)$dt->format('i');
                }
            } catch (\Exception $e) {
                // try next format
            }
        }

        // Fallback: try Carbon::parse (may be less strict)
        try {
            $dt = Carbon::parse($timeString, $timezone);
            return $dt->format('H') * 60 + (int)$dt->format('i');
        } catch (\Exception $e) {
            return null;
        }
    }

}




