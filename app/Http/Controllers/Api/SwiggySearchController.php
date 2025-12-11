<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Models\VendorCategory;

class SwiggySearchController extends Controller
{
    /**
     * Unified Swiggy-style Search
     */
    public function unifiedSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query'     => 'required|string|min:2',
            'zone_id'   => 'required|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'limit'     => 'nullable|integer|min:1|max:100',
            'page'      => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // FIX: InputBag → string
            $query     = $request->input('query');
            $zoneId    = $request->input('zone_id');
            $latitude  = $request->input('latitude');
            $longitude = $request->input('longitude');

            $limit  = $request->input('limit', 20);
            $page   = $request->input('page', 1);
            $offset = ($page - 1) * $limit;

            // Run all searches
            $restaurants = $this->searchRestaurants($query, $zoneId, $latitude, $longitude, $limit, $offset);
            $products    = $this->searchProducts($query, $zoneId, $limit, $offset);
            $categories  = $this->searchCategories($query, $limit, $offset);

            // Format results
            $formattedRestaurants = $restaurants->map(fn ($r) => $this->formatRestaurantResponse($r));
            $formattedProducts    = $products->map(fn ($p) => $this->formatProductResponse($p));
            $formattedCategories  = $categories->map(fn ($c) => $this->formatCategoryResponse($c));

            // Count restaurants where is_open is true
            $openCount = $formattedRestaurants->filter(fn ($restaurant) => isset($restaurant['is_open']) && $restaurant['is_open'] === true)->count();

            $totalResults =
                $formattedRestaurants->count() +
                $formattedProducts->count() +
                $formattedCategories->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'restaurants'   => $formattedRestaurants,
                    'products'      => $formattedProducts,
                    'categories'    => $formattedCategories,
                    'total_results' => $totalResults,
                ],
                'meta' => [
                    'page'     => $page,
                    'limit'    => $limit,
                    'query'    => $query,
                    'zone_id'  => $zoneId,
                    'has_more' => $totalResults >= $limit,
                    'openCount' => $openCount
                ]
            ]);

        } catch (\Exception $e) {

            Log::error('Unified Search Error : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to perform search',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Restaurant Search (Uses real columns only)
     */
    private function searchRestaurants($query, $zoneId, $latitude, $longitude, $limit, $offset)
    {
        $restaurantQuery = Vendor::where(function ($q) {
            $q->where('publish', 1)->orWhereNull('publish');
        })
            ->where('zoneId', $zoneId)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('location', 'like', "%{$query}%")
                    ->orWhere('vType', 'like', "%{$query}%")
                    ->orWhere('cuisineTitle', 'like', "%{$query}%")
                    ->orWhere('categoryTitle', 'like', "%{$query}%")
                    ->orWhere('restaurant_slug', 'like', "%{$query}%")
                    ->orWhere('zone_slug', 'like', "%{$query}%");
            });

        // Distance sorting
        if ($latitude && $longitude) {
            $restaurantQuery->selectRaw(
                "vendors.*,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )) AS distance",
                [$latitude, $longitude, $latitude]
            )
                ->orderBy('distance', 'asc');
        } else {
            $restaurantQuery->orderBy('title', 'asc');
        }

        return $restaurantQuery->skip($offset)->take($limit)->get();
    }

    /**
     * Product Search
     */
    private function searchProducts($query, $zoneId, $limit, $offset)
    {
        return VendorProduct::where('publish', 1)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('categoryID', 'like', "%{$query}%");
            })
            ->whereHas('vendor', function ($q) use ($zoneId) {
                $q->where('zoneId', $zoneId)
                    ->where(function ($q) {
                        $q->where('publish', 1)->orWhereNull('publish');
                    });
            })
            ->orderBy('name', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Category Search
     */
    private function searchCategories($query, $limit, $offset)
    {
        return VendorCategory::where('publish', 1)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('title', 'asc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    /**
     * Format Restaurant Response
     */
    private function formatRestaurantResponse($r)
    {
        return [
            'id' => $r->id,
            'title' => $r->title ?? '',
            'description' => $r->description ?? '',
            'location' => $r->location ?? '',
            'latitude' => $r->latitude ?? null,
            'longitude' => $r->longitude ?? null,
            'zoneId' => $r->zoneId ?? '',
            'photo' => $r->photo ?? '',
            'cover_photo' => $r->cover_photo ?? '',
            'phonenumber' => $r->phonenumber ?? '',
            'email' => $r->email ?? '',
            'address' => $r->address ?? '',
            'publish' => (bool) ($r->publish ?? true),
            'vType' => $r->vType ?? '',
            'categoryTitle' => $r->categoryTitle ?? [],
            'workingHours' => json_decode($r->workingHours ?? []),
            'rating' => $r->rating ?? 0,
            'total_rating' => $r->total_rating ?? 0,
            'delivery_time' => $r->delivery_time ?? '',
            'delivery_charge' => $r->delivery_charge ?? 0,
            'minimum_order' => $r->minimum_order ?? 0,
            'is_open' => $this->calculateActualIsOpen(
                $r->isOpen == 1 || $r->isOpen === 'true' || $r->isOpen === true || ($r->is_open ?? false),
                $r->workingHours ? json_decode($r->workingHours, true) : []
            ),
            'distance' => $r->distance ?? null,
            'created_at' => $r->created_at ? $r->created_at->toISOString() : null,
            'updated_at' => $r->updated_at ? $r->updated_at->toISOString() : null,
        ];
    }

    /**
     * Format Product Response
     */
    private function formatProductResponse($p)
    {
        return [
            'id'         => $p->id,
            'name'       => $p->name,
            'description'=> $p->description,
            'price'      => $p->price,
            'disPrice'   => $p->disPrice,
            'photo'      => $p->photo,
            'categoryID' => $p->categoryID,
            'vendorID'   => $p->vendorID,
            'veg'        => (bool)$p->veg,
            'nonveg'     => (bool)$p->nonveg,
        ];
    }

    /**
     * Format Category Response
     */
    private function formatCategoryResponse($c)
    {
        return [
            'id'          => $c->id,
            'title'       => $c->title,
            'photo'       => $c->photo,
            'publish'     => (bool)$c->publish,
            'description' => $c->description,
            'vType'       => $c->vType,
        ];
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
