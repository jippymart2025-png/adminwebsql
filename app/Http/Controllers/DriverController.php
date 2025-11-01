<?php

namespace App\Http\Controllers;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AppUser;
use App\Models\Driver;

class DriverController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

	  public function index()
    {
        return view("drivers.index");
    }

    public function edit($id)
    {
    	return view('drivers.edit')->with('id', $id);
    }
     public function create()
    {
        return view('drivers.create');
    }
    public function view($id)
    {
        return view('drivers.view')->with('id', $id);
    }
    public function DocumentList($id)
    {
        return view("drivers.document_list")->with('id', $id);
    }
    public function DocumentUpload($driverId, $id)
    {
        return view("drivers.document_upload", compact('driverId', 'id'));
    }

    public function clearOrderRequestData($id)
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            // Reference to the driver document
            $driverRef = $firestore->collection('users')->document($id);

            // Get the current driver data to check if it exists and get driver name for logging
            $driverDoc = $driverRef->snapshot();

            if (!$driverDoc->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found'
                ], 404);
            }

            $driverData = $driverDoc->data();
            $driverName = ($driverData['firstName'] ?? '') . ' ' . ($driverData['lastName'] ?? 'Unknown');

            // Clear the orderRequestData array by setting it to an empty array
            $driverRef->update([
                ['path' => 'orderRequestData', 'value' => []]
            ]);

            // Log the activity if the function exists
            if (function_exists('logActivity')) {
                logActivity('drivers', 'clear_order_request_data', 'Cleared order request data for driver: ' . $driverName);
            }

            return response()->json([
                'success' => true,
                'message' => 'restaurantorders request data cleared successfully for driver: ' . $driverName
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing order request data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function clearAllOrderRequestData()
    {
        try {
            // Initialize Firestore client
            $firestore = new FirestoreClient([
                'projectId' => config('firestore.project_id'),
                'keyFilePath' => config('firestore.credentials'),
            ]);

            // Get all drivers
            $driversQuery = $firestore->collection('users')->where('role', '=', 'driver');
            $driversSnapshot = $driversQuery->documents();

            $clearedCount = 0;
            $errors = [];

            foreach ($driversSnapshot as $driverDoc) {
                try {
                    $driverData = $driverDoc->data();
                    $driverName = ($driverData['firstName'] ?? '') . ' ' . ($driverData['lastName'] ?? 'Unknown');

                    // Clear the orderRequestData array for this driver
                    $firestore->collection('users')->document($driverDoc->id())->update([
                        ['path' => 'orderRequestData', 'value' => []]
                    ]);

                    $clearedCount++;

                    // Log the activity if the function exists
                    if (function_exists('logActivity')) {
                        logActivity('drivers', 'clear_order_request_data', 'Cleared order request data for driver: ' . $driverName);
                    }

                } catch (\Exception $e) {
                    $errors[] = 'Driver ' . ($driverData['firstName'] ?? 'Unknown') . ': ' . $e->getMessage();
                }
            }

            if ($clearedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully cleared order request data for {$clearedCount} drivers.",
                    'cleared_count' => $clearedCount,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No drivers found or no data was cleared.'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing all drivers order request data: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== SQL-BASED API ENDPOINTS ====================

    /**
     * Get drivers data for DataTable (SQL)
     */
    public function getDriversData(Request $request)
    {
        try {
            $draw = $request->input('draw');
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $searchValue = $request->input('search.value');
            
            // Filters
            $zone = $request->input('zone');
            $isActive = $request->input('isActive');
            $isDocumentVerify = $request->input('isDocumentVerify');
            $startDate = $request->input('startDate');
            $endDate = $request->input('endDate');
            
            // Query from users table where role = 'driver'
            $query = AppUser::where('role', 'driver');
            
            // Use subquery to get unique drivers by firebase_id
            $query->whereIn('id', function($subQuery) {
                $subQuery->select(DB::raw('MAX(id)'))
                    ->from('users')
                    ->where('role', 'driver')
                    ->whereNotNull('firebase_id')
                    ->groupBy('firebase_id');
            });
            
            // Apply filters
            if (!empty($zone)) {
                $query->where('zoneId', $zone);
            }
            
            if ($isActive !== null && $isActive !== '') {
                $query->where('active', $isActive == '1' ? '1' : '0');
            }
            
            if ($isDocumentVerify !== null && $isDocumentVerify !== '') {
                $query->where('isDocumentVerify', $isDocumentVerify == '1' ? '1' : '0');
            }
            
            if (!empty($startDate) && !empty($endDate)) {
                $query->whereRaw("DATE(REPLACE(REPLACE(createdAt, '\"', ''), 'T', ' ')) BETWEEN ? AND ?", 
                    [$startDate, $endDate]);
            }
            
            $totalRecords = $query->count();
            
            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('firstName', 'like', "%{$searchValue}%")
                      ->orWhere('lastName', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('phoneNumber', 'like', "%{$searchValue}%");
                });
            }
            
            $filteredRecords = $query->count();
            
            // Get counts for statistics
            $totalDrivers = AppUser::where('role', 'driver')->count();
            $activeDrivers = AppUser::where('role', 'driver')->where('active', '1')->count();
            $inactiveDrivers = AppUser::where('role', 'driver')->where('active', '0')->count();
            
            // Apply ordering - descending by createdAt
            $drivers = $query->orderByRaw("REPLACE(REPLACE(createdAt, '\"', ''), 'T', ' ') DESC")
                           ->skip($start)
                           ->take($length)
                           ->get();
            
            // Build response data
            $data = [];
            foreach ($drivers as $driver) {
                // Parse createdAt date
                $createdAtFormatted = '';
                if ($driver->createdAt) {
                    try {
                        $dateStr = trim($driver->createdAt, '"');
                        $date = new \DateTime($dateStr);
                        $createdAtFormatted = $date->format('M d, Y h:i A');
                    } catch (\Exception $e) {
                        $createdAtFormatted = $driver->createdAt;
                    }
                }
                
                // Parse location if it's JSON
                $latitude = 0;
                $longitude = 0;
                if ($driver->location) {
                    if (is_string($driver->location)) {
                        try {
                            $location = json_decode($driver->location, true);
                            $latitude = $location['latitude'] ?? 0;
                            $longitude = $location['longitude'] ?? 0;
                        } catch (\Exception $e) {
                            // Keep defaults
                        }
                    }
                }
                
                $driverData = [
                    'id' => $driver->id ?? '',
                    'firebase_id' => $driver->firebase_id ?? $driver->id,
                    '_id' => $driver->_id ?? '',
                    'firstName' => $driver->firstName ?? '',
                    'lastName' => $driver->lastName ?? '',
                    'email' => $driver->email ?? '',
                    'phoneNumber' => $driver->phoneNumber ?? '',
                    'countryCode' => $driver->countryCode ?? '',
                    'profilePictureURL' => $driver->profilePictureURL ?? '',
                    'carName' => $driver->carName ?? '',
                    'carNumber' => $driver->carNumber ?? '',
                    'carPictureURL' => $driver->carPictureURL ?? '',
                    'zoneId' => $driver->zoneId ?? '',
                    'active' => $driver->active == '1' || $driver->active === 'true' || $driver->active === true,
                    'isActive' => $driver->isActive == '1' || $driver->isActive === 'true' || $driver->isActive === true,
                    'isDocumentVerify' => $driver->isDocumentVerify == '1' || $driver->isDocumentVerify === 'true' || $driver->isDocumentVerify === true,
                    'wallet_amount' => $driver->wallet_amount ?? 0,
                    'location' => [
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ],
                    'createdAt' => $createdAtFormatted,
                    'createdAtRaw' => $driver->createdAt ?? '',
                ];
                
                $data[] = $driverData;
            }
            
            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
                'stats' => [
                    'total' => $totalDrivers,
                    'active' => $activeDrivers,
                    'inactive' => $inactiveDrivers
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching drivers data: ' . $e->getMessage());
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get single driver data by ID (SQL)
     */
    public function getDriverById($id)
    {
        try {
            \Log::info('=== Looking for driver with ID: ' . $id);
            
            // Try to find by string ID column first (Firebase-style ID), then by numeric primary key
            $driver = AppUser::where('firebase_id', $id)
                          ->where('role', 'driver')
                          ->first();
            
            \Log::info('Search by firebase_id: ' . ($driver ? 'FOUND' : 'NOT FOUND'));
            
            if (!$driver) {
                $driver = AppUser::where('_id', $id)
                              ->where('role', 'driver')
                              ->first();
                \Log::info('Search by _id: ' . ($driver ? 'FOUND' : 'NOT FOUND'));
            }
            
            if (!$driver && is_numeric($id)) {
                $driver = AppUser::where('id', $id)
                              ->where('role', 'driver')
                              ->first();
                \Log::info('Search by numeric id: ' . ($driver ? 'FOUND' : 'NOT FOUND'));
            }
            
            if (!$driver) {
                // Try one more search with partial match
                \Log::warning('Driver not found with exact match. Trying LIKE search...');
                $driver = AppUser::where('role', 'driver')
                              ->where(function($q) use ($id) {
                                  $q->where('firebase_id', 'like', "%{$id}%")
                                    ->orWhere('_id', 'like', "%{$id}%");
                              })
                              ->first();
                \Log::info('Search by LIKE: ' . ($driver ? 'FOUND' : 'NOT FOUND'));
            }
            
            if (!$driver) {
                \Log::warning('Driver not found with ID: ' . $id);
                
                // Get sample drivers for debugging
                $sampleDrivers = AppUser::where('role', 'driver')
                                      ->limit(5)
                                      ->get(['id', 'firebase_id', '_id', 'firstName', 'lastName']);
                \Log::info('Sample drivers in database: ' . json_encode($sampleDrivers));
                
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found with ID: ' . $id,
                    'searched_id' => $id,
                    'sample_drivers' => $sampleDrivers
                ], 404);
            }
            
            \Log::info('Driver found: ' . ($driver->firstName ?? '') . ' ' . ($driver->lastName ?? '') . ' (firebase_id: ' . $driver->firebase_id . ')');
            
            // Parse location
            $location = null;
            if ($driver->location) {
                if (is_string($driver->location)) {
                    try {
                        $location = json_decode($driver->location, true);
                    } catch (\Exception $e) {
                        $location = ['latitude' => 0, 'longitude' => 0];
                    }
                } else {
                    $location = $driver->location;
                }
            }
            
            // Parse and format data
            $driverData = [
                'id' => $driver->id ?? '',
                'firebase_id' => $driver->firebase_id ?? $driver->id,
                '_id' => $driver->_id ?? '',
                'firstName' => $driver->firstName ?? '',
                'lastName' => $driver->lastName ?? '',
                'email' => $driver->email ?? '',
                'phoneNumber' => $driver->phoneNumber ?? '',
                'countryCode' => $driver->countryCode ?? '',
                'profilePictureURL' => $driver->profilePictureURL ?? '',
                'carName' => $driver->carName ?? '',
                'carNumber' => $driver->carNumber ?? '',
                'carPictureURL' => $driver->carPictureURL ?? '',
                'zoneId' => $driver->zoneId ?? '',
                'active' => $driver->active == '1' || $driver->active === 'true' || $driver->active === true,
                'isActive' => $driver->isActive == '1' || $driver->isActive === 'true' || $driver->isActive === true,
                'isDocumentVerify' => $driver->isDocumentVerify == '1' || $driver->isDocumentVerify === 'true' || $driver->isDocumentVerify === true,
                'wallet_amount' => floatval($driver->wallet_amount ?? 0),
                'location' => $location ?? ['latitude' => 0, 'longitude' => 0],
                'createdAt' => $driver->createdAt ?? '',
                'fcmToken' => $driver->fcmToken ?? '',
                'rotation' => $driver->rotation ?? 0,
                'appIdentifier' => $driver->appIdentifier ?? '',
                'provider' => $driver->provider ?? '',
                'vendorID' => $driver->vendorID ?? '',
                'inProgressOrderID' => $driver->inProgressOrderID ? json_decode($driver->inProgressOrderID, true) : [],
                'orderRequestData' => $driver->orderRequestData ? json_decode($driver->orderRequestData, true) : [],
                'userBankDetails' => $driver->userBankDetails ? json_decode($driver->userBankDetails, true) : null,
            ];
            
            return response()->json([
                'success' => true,
                'data' => $driverData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching driver: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new driver (SQL)
     */
    public function createDriver(Request $request)
    {
        try {
            $driverData = $request->all();
            
            // Generate unique firebase_id if not provided
            $firebase_id = $driverData['firebase_id'] ?? 'driver_' . time() . '_' . uniqid();
            
            // Helper function to convert boolean values
            $toBool = function($value) {
                if (is_bool($value)) return $value ? '1' : '0';
                if (is_string($value)) {
                    $value = strtolower($value);
                    return ($value === 'true' || $value === '1') ? '1' : '0';
                }
                return $value ? '1' : '0';
            };
            
            // Create driver user
            $driver = new AppUser();
            $driver->firebase_id = $firebase_id;
            $driver->_id = $firebase_id;
            $driver->role = 'driver';
            $driver->firstName = $driverData['firstName'] ?? '';
            $driver->lastName = $driverData['lastName'] ?? '';
            $driver->email = $driverData['email'] ?? '';
            $driver->phoneNumber = $driverData['phoneNumber'] ?? '';
            $driver->countryCode = $driverData['countryCode'] ?? '';
            $driver->profilePictureURL = $driverData['profilePictureURL'] ?? '';
            $driver->carName = $driverData['carName'] ?? '';
            $driver->carNumber = $driverData['carNumber'] ?? '';
            $driver->carPictureURL = $driverData['carPictureURL'] ?? '';
            $driver->zoneId = $driverData['zoneId'] ?? '';
            $driver->active = $toBool($driverData['active'] ?? 1);
            $driver->isActive = $toBool($driverData['isActive'] ?? 1);
            $driver->isDocumentVerify = $toBool($driverData['isDocumentVerify'] ?? 0);
            $driver->wallet_amount = floatval($driverData['wallet_amount'] ?? 0);
            
            // Store location as JSON
            if (isset($driverData['location'])) {
                $driver->location = is_string($driverData['location']) ? $driverData['location'] : json_encode($driverData['location']);
            }
            
            $driver->createdAt = '"' . gmdate('Y-m-d\TH:i:s.u\Z') . '"';
            $driver->fcmToken = $driverData['fcmToken'] ?? '';
            $driver->rotation = $driverData['rotation'] ?? 0;
            $driver->appIdentifier = $driverData['appIdentifier'] ?? 'web';
            $driver->provider = $driverData['provider'] ?? 'email';
            $driver->vendorID = $driverData['vendorID'] ?? '';
            
            $driver->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Driver created successfully',
                'driver_id' => $firebase_id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error creating driver: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update driver data (SQL)
     */
    public function updateDriver(Request $request, $id)
    {
        try {
            // Try to find driver by multiple ID fields
            $driver = AppUser::where('firebase_id', $id)
                          ->where('role', 'driver')
                          ->first();
            
            if (!$driver) {
                $driver = AppUser::where('_id', $id)
                              ->where('role', 'driver')
                              ->first();
            }
            
            if (!$driver && is_numeric($id)) {
                $driver = AppUser::where('id', $id)
                              ->where('role', 'driver')
                              ->first();
            }
            
            \Log::info('Updating driver with ID: ' . $id);
            
            if (!$driver) {
                \Log::warning('Driver not found for update with ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found with ID: ' . $id
                ], 404);
            }
            
            // Helper function to convert boolean values
            $toBool = function($value) {
                if (is_bool($value)) return $value ? '1' : '0';
                if (is_string($value)) {
                    $value = strtolower($value);
                    return ($value === 'true' || $value === '1') ? '1' : '0';
                }
                return $value ? '1' : '0';
            };
            
            // Update driver fields
            if ($request->has('firstName')) $driver->firstName = $request->firstName;
            if ($request->has('lastName')) $driver->lastName = $request->lastName;
            if ($request->has('email')) $driver->email = $request->email;
            if ($request->has('phoneNumber')) $driver->phoneNumber = $request->phoneNumber;
            if ($request->has('countryCode')) $driver->countryCode = $request->countryCode;
            if ($request->has('profilePictureURL')) $driver->profilePictureURL = $request->profilePictureURL;
            if ($request->has('carName')) $driver->carName = $request->carName;
            if ($request->has('carNumber')) $driver->carNumber = $request->carNumber;
            if ($request->has('carPictureURL')) $driver->carPictureURL = $request->carPictureURL;
            if ($request->has('zoneId')) $driver->zoneId = $request->zoneId;
            if ($request->has('active')) $driver->active = $toBool($request->active);
            if ($request->has('isActive')) $driver->isActive = $toBool($request->isActive);
            if ($request->has('isDocumentVerify')) $driver->isDocumentVerify = $toBool($request->isDocumentVerify);
            if ($request->has('wallet_amount')) $driver->wallet_amount = floatval($request->wallet_amount);
            
            if ($request->has('location')) {
                $driver->location = is_string($request->location) ? $request->location : json_encode($request->location);
            }
            
            if ($request->has('userBankDetails')) {
                $driver->userBankDetails = is_string($request->userBankDetails) ? $request->userBankDetails : json_encode($request->userBankDetails);
            }
            
            $driver->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Driver updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating driver: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle driver active status (SQL)
     */
    public function toggleDriverStatus($id)
    {
        try {
            $driver = AppUser::where('firebase_id', $id)
                          ->where('role', 'driver')
                          ->first();
            
            if (!$driver) {
                $driver = AppUser::where('_id', $id)
                              ->where('role', 'driver')
                              ->first();
            }
            
            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found with ID: ' . $id
                ], 404);
            }
            
            $driver->active = $driver->active == '1' ? '0' : '1';
            $driver->save();
            
            return response()->json([
                'success' => true,
                'active' => $driver->active == '1'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling driver status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling driver status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete driver (SQL)
     */
    public function deleteDriver($id)
    {
        try {
            $driver = AppUser::where('firebase_id', $id)
                          ->where('role', 'driver')
                          ->first();
            
            if (!$driver) {
                $driver = AppUser::where('_id', $id)
                              ->where('role', 'driver')
                              ->first();
            }
            
            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found with ID: ' . $id
                ], 404);
            }
            
            $driver->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Driver deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting driver: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver documents (SQL)
     */
    public function getDriverDocuments($id)
    {
        try {
            $documents = DB::table('driver_documents')
                ->where('driver_id', $id)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $documents
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching driver documents: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver payouts (SQL)
     */
    public function getDriverPayouts($id)
    {
        try {
            $payouts = DB::table('driver_payouts')
                ->where('driverID', $id)
                ->orderBy('paidDate', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $payouts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching driver payouts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver payouts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver statistics (SQL)
     */
    public function getDriverStats($id)
    {
        try {
            // Get total orders
            $totalOrders = DB::table('restaurant_orders')
                ->where('driverID', $id)
                ->count();

            // Get total earnings
            $completedOrders = DB::table('restaurant_orders')
                ->where('driverID', $id)
                ->where('status', 'Order Completed')
                ->get();

            $totalEarnings = 0;
            foreach ($completedOrders as $order) {
                $deliveryCharge = $order->deliveryCharge ?? 0;
                $tip = $order->tip ?? 0;
                $totalEarnings += ($deliveryCharge + $tip);
            }

            // Get total payouts
            $totalPayouts = DB::table('driver_payouts')
                ->where('driverID', $id)
                ->where('paymentStatus', 'Success')
                ->sum('amount');

            // Get wallet balance
            $driver = AppUser::where('firebase_id', $id)
                          ->where('role', 'driver')
                          ->first();
            
            if (!$driver) {
                $driver = AppUser::where('_id', $id)
                              ->where('role', 'driver')
                              ->first();
            }

            $walletBalance = $driver ? floatval($driver->wallet_amount ?? 0) : 0;

            return response()->json([
                'success' => true,
                'totalOrders' => $totalOrders,
                'totalEarnings' => $totalEarnings,
                'totalPayouts' => $totalPayouts,
                'walletBalance' => $walletBalance,
                'remainingBalance' => $totalEarnings - $totalPayouts
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching driver stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear order request data for a driver (SQL)
     */
    public function clearOrderRequestDataSQL($id)
    {
        try {
            $driver = AppUser::where('firebase_id', $id)
                          ->where('role', 'driver')
                          ->first();
            
            if (!$driver) {
                $driver = AppUser::where('_id', $id)
                              ->where('role', 'driver')
                              ->first();
            }
            
            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found'
                ], 404);
            }

            $driverName = ($driver->firstName ?? '') . ' ' . ($driver->lastName ?? 'Unknown');

            // Clear the orderRequestData array
            $driver->orderRequestData = json_encode([]);
            $driver->save();

            return response()->json([
                'success' => true,
                'message' => 'Order request data cleared successfully for driver: ' . $driverName
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing order request data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear all drivers order request data (SQL)
     */
    public function clearAllOrderRequestDataSQL()
    {
        try {
            $drivers = AppUser::where('role', 'driver')->get();
            
            $clearedCount = 0;
            $errors = [];

            foreach ($drivers as $driver) {
                try {
                    $driver->orderRequestData = json_encode([]);
                    $driver->save();
                    $clearedCount++;
                } catch (\Exception $e) {
                    $errors[] = 'Driver ' . ($driver->firstName ?? 'Unknown') . ': ' . $e->getMessage();
                }
            }

            if ($clearedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully cleared order request data for {$clearedCount} drivers.",
                    'cleared_count' => $clearedCount,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No drivers found or no data was cleared.'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing all drivers order request data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get zones for driver module
     */
    public function getZones()
    {
        try {
            // Get all zones first, then filter for publish = 1
            $allZones = DB::table('zone')
                      ->orderBy('name', 'asc')
                      ->get();
            
            \Log::info('Total zones found: ' . $allZones->count());
            
            // Filter for published zones (handle different data types)
            $zones = $allZones->filter(function($zone) {
                return $zone->publish == 1 || 
                       $zone->publish === '1' || 
                       $zone->publish === true || 
                       $zone->publish === 'true';
            })->values();
            
            \Log::info('Published zones: ' . $zones->count());
            
            return response()->json([
                'success' => true,
                'data' => $zones,
                'total_zones' => $allZones->count(),
                'published_zones' => $zones->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching zones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching zones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all document types for drivers
     */
    public function getDocumentTypes()
    {
        try {
            $documents = DB::table('documents')
                ->where('enable', 1)
                ->where('type', 'driver')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $documents
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching document types: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching document types: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver's document verification status
     */
    public function getDocumentVerification($id)
    {
        try {
            $verification = DB::table('documents_verify')
                ->where('id', $id)
                ->first();
            
            if (!$verification) {
                return response()->json([
                    'success' => true,
                    'data' => null
                ]);
            }
            
            // Parse JSON fields if they exist
            $verificationData = (array) $verification;
            foreach ($verificationData as $key => $value) {
                if (is_string($value) && (str_starts_with($value, '{') || str_starts_with($value, '['))) {
                    try {
                        $verificationData[$key] = json_decode($value, true);
                    } catch (\Exception $e) {
                        // Keep as string if not valid JSON
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $verificationData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching document verification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching document verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update driver's document verification
     */
    public function updateDocumentVerification(Request $request, $id)
    {
        try {
            $documentData = $request->all();
            unset($documentData['_token']);
            
            // Check if record exists
            $exists = DB::table('documents_verify')->where('id', $id)->exists();
            
            if ($exists) {
                // Update existing record
                DB::table('documents_verify')
                    ->where('id', $id)
                    ->update($documentData);
            } else {
                // Insert new record
                $documentData['id'] = $id;
                DB::table('documents_verify')->insert($documentData);
            }
            
            // Check if all required documents are verified
            $this->checkAndUpdateDriverVerificationStatus($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Document verification updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating document verification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating document verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if all documents are verified and update driver status
     */
    private function checkAndUpdateDriverVerificationStatus($driverId)
    {
        try {
            // Get all required documents
            $requiredDocs = DB::table('documents')
                ->where('enable', 1)
                ->where('type', 'driver')
                ->pluck('id');
            
            if ($requiredDocs->isEmpty()) {
                return;
            }
            
            // Get driver's verification record
            $verification = DB::table('documents_verify')
                ->where('id', $driverId)
                ->first();
            
            if (!$verification) {
                // No documents verified yet
                DB::table('users')
                    ->where('firebase_id', $driverId)
                    ->orWhere('_id', $driverId)
                    ->where('role', 'driver')
                    ->update([
                        'isDocumentVerify' => '0',
                        'isActive' => '0'
                    ]);
                return;
            }
            
            // Check if all required documents are verified
            $allVerified = true;
            $verificationArray = (array) $verification;
            
            foreach ($requiredDocs as $docId) {
                if (!isset($verificationArray[$docId]) || 
                    (is_array($verificationArray[$docId]) && (!isset($verificationArray[$docId]['status']) || $verificationArray[$docId]['status'] != 'approved')) ||
                    (is_string($verificationArray[$docId]) && $verificationArray[$docId] != 'approved')) {
                    $allVerified = false;
                    break;
                }
            }
            
            // Update driver verification status
            if ($allVerified) {
                DB::table('users')
                    ->where('firebase_id', $driverId)
                    ->orWhere('_id', $driverId)
                    ->where('role', 'driver')
                    ->update([
                        'isDocumentVerify' => '1',
                        'isActive' => '1'
                    ]);
            } else {
                DB::table('users')
                    ->where('firebase_id', $driverId)
                    ->orWhere('_id', $driverId)
                    ->where('role', 'driver')
                    ->update([
                        'isDocumentVerify' => '0',
                        'isActive' => '0'
                    ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error checking document verification status: ' . $e->getMessage());
        }
    }

    /**
     * Debug endpoint to check driver lookup
     */
    public function debugDriver($id)
    {
        try {
            // Check all possible matches
            $byFirebaseId = AppUser::where('firebase_id', $id)->where('role', 'driver')->first();
            $byUnderscoreId = AppUser::where('_id', $id)->where('role', 'driver')->first();
            $byNumericId = is_numeric($id) ? AppUser::where('id', $id)->where('role', 'driver')->first() : null;
            
            // Get sample drivers
            $sampleDrivers = AppUser::where('role', 'driver')->limit(5)->get(['id', 'firebase_id', '_id', 'firstName', 'lastName', 'email']);
            
            return response()->json([
                'search_id' => $id,
                'found_by_firebase_id' => $byFirebaseId ? true : false,
                'found_by_underscore_id' => $byUnderscoreId ? true : false,
                'found_by_numeric_id' => $byNumericId ? true : false,
                'driver_firebase_id' => $byFirebaseId ? [
                    'firebase_id' => $byFirebaseId->firebase_id,
                    '_id' => $byFirebaseId->_id,
                    'name' => $byFirebaseId->firstName . ' ' . $byFirebaseId->lastName
                ] : null,
                'driver_underscore_id' => $byUnderscoreId ? [
                    'firebase_id' => $byUnderscoreId->firebase_id,
                    '_id' => $byUnderscoreId->_id,
                    'name' => $byUnderscoreId->firstName . ' ' . $byUnderscoreId->lastName
                ] : null,
                'sample_drivers' => $sampleDrivers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}


