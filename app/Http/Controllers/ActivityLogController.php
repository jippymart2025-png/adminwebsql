<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ActivityLogger;

class ActivityLogController extends Controller
{
    protected $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Display the activity logs page
     */
    public function index()
    {
        return view('activity_logs.index');
    }

    /**
     * Log an activity via API
     */
    public function logActivity(Request $request)
    {
        $request->validate([
            'module' => 'required|string',
            'action' => 'required|string',
            'description' => 'required|string',
        ]);

        // Try to get authenticated user, but create a fallback if not authenticated
        $user = auth()->user();
        if (!$user) {
            // Create a fallback user object for API calls
            $user = new \stdClass();
            $user->id = 'api_user';
            $user->name = 'API User';
        }
        
        $module = $request->input('module');
        $action = $request->input('action');
        $description = $request->input('description');

        $success = $this->activityLogger->log($user, $module, $action, $description, $request);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Activity logged successfully' : 'Failed to log activity'
        ]);
    }

    /**
     * Get activity logs for a specific module
     */
    public function getModuleLogs(Request $request, $module)
    {
        $limit = $request->get('limit', 100);
        $logs = $this->activityLogger->getLogsByModule($module, $limit);
        
        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get all activity logs
     */
    public function getAllLogs(Request $request)
    {
        $limit = $request->get('limit', 50);
        $startAfter = $request->get('start_after');
        $logs = $this->activityLogger->getAllLogs($limit, $startAfter);
        
        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get activity logs for cuisines module (specific endpoint for testing)
     */
    public function getCuisinesLogs(Request $request)
    {
        return $this->getModuleLogs($request, 'cuisines');
    }
}
