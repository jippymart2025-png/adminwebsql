<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Google\Client as Google_Client;

class DynamicNotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("dynamic_notifications.index");
    }


    public function save($id = null)
    {
        return view('dynamic_notifications.create')->with('id', $id);
    }

    /**
     * Data for DataTables (SQL-based)
     */
    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        $base = DB::table('dynamic_notification');
        $total = $base->count();

        $q = DB::table('dynamic_notification')->select('id','type','subject','message','createdAt');
        if ($search !== '') {
            $q->where(function($qq) use ($search){
                $qq->where('type','like','%'.$search.'%')
                   ->orWhere('subject','like','%'.$search.'%')
                   ->orWhere('message','like','%'.$search.'%');
            });
        }
        $q->orderBy('createdAt','desc');
        $rows = $q->offset($start)->limit($length)->get();
        $filtered = ($search==='') ? $total : (clone $q)->count();

        $data = [];
        foreach ($rows as $row) {
            $rowArr = [];
            $rowArr[] = e($row->type ?? '');
            $rowArr[] = e($row->subject ?? '');
            $rowArr[] = e($row->message ?? '');
            $createdAt = '-';
            if ($row->createdAt) {
                try { $createdAt = Carbon::parse($row->createdAt)->format('M d, Y h:i A'); }
                catch (\Exception $e) { $createdAt = $row->createdAt; }
            }
            $rowArr[] = $createdAt;
            $editUrl = route('dynamic-notification.save', $row->id);
            $rowArr[] = '<span class="action-btn">
                <a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>
                <a href="javascript:void(0)" class="send-notification-btn" data-id="'.$row->id.'" data-subject="'.htmlspecialchars($row->subject).'" data-message="'.htmlspecialchars($row->message).'">
                    <i class="mdi mdi-send" title="Send to All Customers"></i>
                </a>
            </span>';
            $data[] = $rowArr;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    /** Create or update record */
    public function upsert(Request $request)
    {
        $id = $request->input('id');
        $subject = $request->input('subject');
        $message = $request->input('message');
        $type = $request->input('type');

        if (!$subject || !$message) {
            return response()->json(['success'=>false,'message'=>'Subject and message are required'], 422);
        }

        if ($id) {
            DB::table('dynamic_notification')->where('id',$id)->update([
                'subject' => $subject,
                'message' => $message,
                'type'    => $type,
            ]);
            return response()->json(['success'=>true,'message'=>'Notification updated']);
        } else {
            $newId = (string) Str::uuid();
            DB::table('dynamic_notification')->insert([
                'id' => $newId,
                'subject' => $subject,
                'message' => $message,
                'type'    => $type,
                'createdAt' => now()->toIso8601String(),
            ]);
            return response()->json(['success'=>true,'message'=>'Notification created','id'=>$newId]);
        }
    }

    /**
     * Get single notification for editing (API endpoint)
     */
    public function show($id)
    {
        $notification = DB::table('dynamic_notification')
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        return response()->json([
            'success' => true,
            'id' => $notification->id,
            'type' => $notification->type,
            'subject' => $notification->subject,
            'message' => $notification->message,
            'createdAt' => $notification->createdAt
        ]);
    }

    public function delete($id)
    {
        DB::table('dynamic_notification')->where('id',$id)->delete();
        return response()->json(['success'=>true]);
    }

    /**
     * Send notification to all customers with FCM tokens
     */
    public function sendToCustomers($id = null)
    {
        \Log::info('Send to customers request', ['id' => $id]);

        // Get notification details
        $notification = null;
        if ($id) {
            $notification = DB::table('dynamic_notification')->where('id', $id)->first();
            if (!$notification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found'
                ], 404);
            }
        }

        // Check if Firebase service account exists
        if (!Storage::disk('local')->has('firebase/credentials.json')) {
            \Log::error('Firebase credentials.json file not found in storage/app/firebase/');
            return response()->json([
                'success' => false,
                'message' => 'Firebase credentials.json file not found. Please check your Firebase configuration.'
            ]);
        }

        try {
            // Get Firebase access token
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->setAccessType('offline');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();

            if (!$client_token || !isset($client_token['access_token'])) {
                \Log::error('Failed to get access token from Google Client');
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to authenticate with Firebase.'
                ]);
            }

            $access_token = $client_token['access_token'];
            \Log::info('Successfully obtained Firebase access token for customer notifications');
        } catch (\Exception $e) {
            \Log::error('Google Client authentication error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Authentication error: ' . $e->getMessage()
            ]);
        }

        // Get all active customers with FCM tokens
        $customers = DB::table('users')
            ->whereNotNull('fcmToken')
            ->where('fcmToken', '!=', '')
            ->where('active', 1)
            ->select('id', 'fcmToken', 'firstName', 'lastName', 'email')
            ->get();

        if ($customers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active customers with FCM tokens found'
            ]);
        }

        \Log::info('Found customers to notify', ['count' => $customers->count()]);

        $projectId = env('FIREBASE_PROJECT_ID', 'jippymart-27c08');
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ];

        $successCount = 0;
        $failureCount = 0;
        $errors = [];

        // Send notification to each customer
        foreach ($customers as $customer) {
            if (empty($customer->fcmToken)) {
                continue;
            }

            $data = [
                'message' => [
                    'notification' => [
                        'title' => $notification->subject,
                        'body' => $notification->message,
                    ],
                    'token' => $customer->fcmToken,
                    'android' => [
                        'notification' => [
                            'sound' => 'default'
                        ],
                        'priority' => 'high'
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1
                            ]
                        ]
                    ]
                ],
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($result === FALSE || !empty($curlError)) {
                $failureCount++;
                $errors[] = "Customer {$customer->id}: {$curlError}";
                \Log::error('FCM Send Error for customer', [
                    'customer_id' => $customer->id,
                    'error' => $curlError
                ]);
                continue;
            }

            $resultData = json_decode($result, true);

            if ($httpCode >= 200 && $httpCode < 300) {
                $successCount++;
                \Log::info('Notification sent to customer', [
                    'customer_id' => $customer->id,
                    'email' => $customer->email
                ]);
            } else {
                $failureCount++;
                $errorMsg = $resultData['error']['message'] ?? 'Unknown error';
                $errors[] = "Customer {$customer->id}: {$errorMsg}";
                \Log::error('FCM API Error for customer', [
                    'customer_id' => $customer->id,
                    'http_code' => $httpCode,
                    'error' => $errorMsg
                ]);
            }
        }

        \Log::info('Notification sending completed', [
            'success' => $successCount,
            'failed' => $failureCount
        ]);

        return response()->json([
            'success' => true,
            'message' => "Notifications sent: {$successCount} successful, {$failureCount} failed",
            'stats' => [
                'total_customers' => $customers->count(),
                'success' => $successCount,
                'failed' => $failureCount,
                'errors' => array_slice($errors, 0, 10) // Return first 10 errors
            ]
        ]);
    }

}
