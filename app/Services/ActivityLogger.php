<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    protected $firestore;
    protected $collection = 'activity_logs';

    private static $firestoreInstance = null;
    
    public function __construct()
    {
        // Use singleton pattern to prevent multiple connections
        if (self::$firestoreInstance === null) {
            try {
                // Check if service account file exists
                $keyFilePath = config('firestore.credentials');
                if (!file_exists($keyFilePath)) {
                    \Log::warning('Firebase service account file not found: ' . $keyFilePath);
                    $this->firestore = null;
                    return;
                }
                
                self::$firestoreInstance = new FirestoreClient([
                    'projectId' => config('firestore.project_id'),
                    'keyFilePath' => $keyFilePath,
                    'databaseId' => config('firestore.database_id'),
                    'timeout' => config('firestore.timeout', 15),
                    'maxConnections' => config('firestore.max_connections', 5),
                ]);
                
                $this->collection = config('firestore.collection', 'activity_logs');
            } catch (\Exception $e) {
                \Log::error('Failed to initialize ActivityLogger: ' . $e->getMessage());
                self::$firestoreInstance = null;
            }
        }
        
        $this->firestore = self::$firestoreInstance;
    }

    /**
     * Log an activity to Firestore
     *
     * @param mixed $user The authenticated user
     * @param string $module The module name (e.g., 'cuisines', 'orders')
     * @param string $action The action performed (e.g., 'created', 'updated', 'deleted')
     * @param string $description Description of the action
     * @param Request|null $request The HTTP request object
     * @return bool
     */
    public function log($user, $module, $action, $description, Request $request = null)
    {
        try {
            // Check if Firestore is available
            if (!$this->firestore) {
                \Log::warning('ActivityLogger: Firestore not available, skipping log');
                return false;
            }
            
            // Get user information
            $userType = $this->getUserType($user);
            $role = $this->getUserRole($user);
            
            // Get request information
            $ipAddress = $request ? $request->ip() : request()->ip();
            $userAgent = $request ? $request->userAgent() : request()->userAgent();

            // Prepare log data
            $logData = [
                'user_id' => $user->id ?? $user->uid ?? 'unknown',
                'user_name' => $this->getUserName($user),
                'user_type' => $userType,
                'role' => $role,
                'module' => $module,
                'action' => $action,
                'description' => $description,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'created_at' => new \Google\Cloud\Core\Timestamp(new \DateTime())
            ];

            // Add to Firestore
            $this->firestore->collection($this->collection)->add($logData);

            return true;
        } catch (\Exception $e) {
            \Log::error('Activity Logger Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user type based on user object
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserType($user)
    {
        if (!$user) {
            return 'unknown';
        }

        // Check if user has role_id (admin user)
        if (isset($user->role_id)) {
            return 'admin';
        }

        // Check for other user types based on user properties
        if (isset($user->user_type)) {
            return $user->user_type;
        }

        // Default to admin if we can't determine
        return 'admin';
    }

    /**
     * Get user role
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserRole($user)
    {
        if (!$user) {
            return 'unknown';
        }

        // If user has role_id, get role name from database
        if (isset($user->role_id)) {
            $role = \App\Models\Role::find($user->role_id);
            return $role ? $role->role_name : 'unknown';
        }

        // Check for role property
        if (isset($user->role)) {
            return $user->role;
        }

        return 'unknown';
    }

    /**
     * Get user name
     *
     * @param mixed $user
     * @return string
     */
    protected function getUserName($user)
    {
        if (!$user) {
            return 'Unknown User';
        }

        // Check for name property
        if (isset($user->name) && !empty($user->name)) {
            return $user->name;
        }

        // Check for first_name and last_name properties
        if (isset($user->first_name) && isset($user->last_name)) {
            return trim($user->first_name . ' ' . $user->last_name);
        }

        // Check for username property
        if (isset($user->username) && !empty($user->username)) {
            return $user->username;
        }

        // Check for email property
        if (isset($user->email) && !empty($user->email)) {
            return $user->email;
        }

        // Fallback to user ID
        if (isset($user->id)) {
            return 'User ' . $user->id;
        }

        return 'Unknown User';
    }

    /**
     * Get logs for a specific module
     *
     * @param string $module
     * @param int $limit
     * @return array
     */
    public function getLogsByModule($module, $limit = 100)
    {
        try {
            if (!$this->firestore) {
                \Log::warning('ActivityLogger: Firestore not available, cannot fetch logs');
                return [];
            }
            
            $query = $this->firestore->collection($this->collection)
                ->where('module', '=', $module)
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            $documents = $query->documents();
            $logs = [];

            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();
                $logs[] = $data;
            }

            return $logs;
        } catch (\Exception $e) {
            \Log::error('Error fetching activity logs: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all logs with pagination
     *
     * @param int $limit
     * @param string|null $startAfter
     * @return array
     */
    public function getAllLogs($limit = 50, $startAfter = null)
    {
        try {
            if (!$this->firestore) {
                \Log::warning('ActivityLogger: Firestore not available, cannot fetch logs');
                return [];
            }
            
            $query = $this->firestore->collection($this->collection)
                ->orderBy('created_at', 'desc')
                ->limit($limit);

            if ($startAfter) {
                $query = $query->startAfter($startAfter);
            }

            $documents = $query->documents();
            $logs = [];

            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();
                $logs[] = $data;
            }

            return $logs;
        } catch (\Exception $e) {
            \Log::error('Error fetching all activity logs: ' . $e->getMessage());
            return [];
        }
    }
}
