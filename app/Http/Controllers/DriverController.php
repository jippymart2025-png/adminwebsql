<?php

namespace App\Http\Controllers;

use Google\Cloud\Firestore\FirestoreClient;

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
}


