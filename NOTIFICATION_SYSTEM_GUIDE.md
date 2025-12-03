# Customer Notification System Guide

This guide explains how to send push notifications to customers using FCM tokens stored in the users table.

## Overview

The system allows you to:
1. Create and manage dynamic notifications
2. Send push notifications to all active customers with FCM tokens
3. Track notification history in the database

## Features Implemented

### 1. Dynamic Notification Management
- Create/Edit/Delete dynamic notifications
- Store notifications in the `dynamic_notification` table
- View notification history with timestamps

### 2. FCM Push Notification Integration
- Send notifications to all active customers
- Uses FCM tokens from the `users.fcmToken` field
- Firebase Cloud Messaging API v1 integration
- Supports both Android and iOS devices

### 3. User Interface
- **List Page**: View all dynamic notifications with "Send" button for each
- **Edit/Create Page**: Edit notification and send to customers directly
- Real-time feedback on send success/failure

## How to Use

### Step 1: Create a Dynamic Notification

1. Navigate to **Dynamic Notifications** section
2. Click **"Create New"** or edit an existing notification
3. Fill in the required fields:
   - **Type**: The notification type (e.g., order_placed, restaurant_accepted, etc.)
   - **Subject**: The notification title
   - **Message**: The notification body text
4. Click **"Save"** to store the notification

### Step 2: Send Notification to Customers

#### Option A: From the Edit Page
1. After saving a notification, you'll see a **"Send to All Customers"** button
2. Click the button
3. Confirm the action (this cannot be undone)
4. Wait for the process to complete (may take 1-2 minutes for large customer bases)
5. View the results:
   - Total customers contacted
   - Number of successful sends
   - Number of failed sends

#### Option B: From the List Page
1. In the notifications list, find the notification you want to send
2. Click the **send icon** (📧) next to the edit button
3. Confirm the action
4. View results in the popup

## Technical Details

### Database Structure

#### Users Table
The system uses the `users` table with the following relevant fields:
- `id`: User ID
- `fcmToken`: Firebase Cloud Messaging token (required for push notifications)
- `active`: User active status (only active users receive notifications)
- `firstName`, `lastName`, `email`: User information

#### Dynamic Notification Table
Stores notification templates:
- `id`: UUID
- `type`: Notification type
- `subject`: Notification title
- `message`: Notification body
- `createdAt`: Creation timestamp

### API Endpoints

#### New Routes Added
```php
// Display notification management pages
GET  /dynamic-notification                    - List all notifications
GET  /dynamic-notification/save/{id?}        - Create/Edit form

// API endpoints
GET  /dynamic-notification/data              - DataTables data endpoint
GET  /api/dynamic-notification/{id}          - Get single notification
POST /dynamic-notification/upsert            - Create/Update notification
POST /dynamic-notification/send/{id}         - Send notification to customers
GET  /dynamic-notification/delete/{id}       - Delete notification
```

### Controllers

#### DynamicNotificationController
Main controller with the following methods:

1. **`index()`**: Display the notification list page
2. **`save($id)`**: Display create/edit form
3. **`data(Request)`**: Server-side DataTables data
4. **`upsert(Request)`**: Create or update notification
5. **`show($id)`**: Get single notification data (API)
6. **`delete($id)`**: Delete notification
7. **`sendToCustomers($id)`**: **NEW** - Send notification to all customers

### Notification Sending Logic

```php
// Pseudocode flow:
1. Get Firebase access token from serviceAccount.json
2. Query all active users with FCM tokens:
   - WHERE fcmToken IS NOT NULL
   - WHERE fcmToken != ''
   - WHERE active = 1
3. For each customer:
   - Build FCM v1 API request
   - Send to customer's FCM token
   - Track success/failure
4. Return statistics:
   - Total customers
   - Successful sends
   - Failed sends
   - Error details (first 10 errors)
```

### Firebase Configuration

The system requires:
- **File**: `storage/app/firebase/credentials.json`
- **Environment Variable**: `FIREBASE_PROJECT_ID` (default: jippymart-27c08)
- **Scope**: `https://www.googleapis.com/auth/firebase.messaging`

### FCM Message Format

```json
{
  "message": {
    "notification": {
      "title": "Notification subject",
      "body": "Notification message"
    },
    "token": "customer_fcm_token",
    "android": {
      "notification": {
        "sound": "default"
      },
      "priority": "high"
    },
    "apns": {
      "payload": {
        "aps": {
          "sound": "default",
          "badge": 1
        }
      }
    }
  }
}
```

## Requirements

### Server Requirements
- PHP 7.4+
- Laravel framework
- Google API PHP Client library
- cURL enabled
- Storage write permissions

### Firebase Setup
1. Firebase project configured
2. Service account JSON file in `storage/app/firebase/serviceAccount.json`
3. FCM API enabled in Firebase Console
4. Project ID configured in `.env`

### Database Requirements
- `users` table with `fcmToken` column (varchar)
- `dynamic_notification` table (already created)
- Active database connection

### Mobile App Requirements
For customers to receive notifications, the mobile app must:
1. Register FCM tokens on login
2. Store tokens in `users.fcmToken` field
3. Handle incoming push notifications
4. Subscribe to relevant topics (optional)

## Security & Permissions

### Admin Permissions Required
```php
'dynamic-notifications' => [
    'dynamic-notification.index',  // View list
    'dynamic-notification.save',   // Create/Edit/Send
    'dynamic-notification.delete'  // Delete
]
```

### Best Practices
1. **Test First**: Test with a small group before sending to all customers
2. **Timing**: Send notifications during appropriate hours
3. **Content**: Keep messages clear and concise
4. **Frequency**: Don't spam customers with too many notifications
5. **Monitoring**: Check logs for failed sends and invalid tokens

## Troubleshooting

### Common Issues

#### 1. "Firebase credentials.json file not found"
**Solution**: Ensure the file exists at `storage/app/firebase/credentials.json`

#### 2. "No active customers with FCM tokens found"
**Solution**: 
- Check if customers have FCM tokens in the database
- Verify customers are marked as active (active = 1)
- Ensure mobile app is properly registering FCM tokens

#### 3. "Failed to authenticate with Firebase"
**Solution**:
- Check credentials.json is valid
- Verify project ID matches in .env
- Ensure Firebase project has FCM API enabled

#### 4. High failure rate when sending
**Solution**:
- Check logs for specific error messages
- Invalid tokens: Users may have uninstalled the app or changed devices
- Consider implementing token cleanup for invalid tokens

### Logs

All notification activities are logged. Check:
```bash
tail -f storage/logs/laravel.log
```

Look for entries like:
- `Send to customers request`
- `Found customers to notify`
- `Notification sent to customer`
- `FCM Send Error for customer`
- `Notification sending completed`

## Code Examples

### Query Active Customers with FCM Tokens
```php
$customers = DB::table('users')
    ->whereNotNull('fcmToken')
    ->where('fcmToken', '!=', '')
    ->where('active', 1)
    ->select('id', 'fcmToken', 'firstName', 'lastName', 'email')
    ->get();
```

### Send Single Notification (Existing Method)
```php
// Use NotificationController::sendNotification()
// Endpoint: POST /sendnotification
// Params: fcm (token), title, message
```

### Send Broadcast to All Customers (New Method)
```php
// Use DynamicNotificationController::sendToCustomers()
// Endpoint: POST /dynamic-notification/send/{id}
// Sends to all active customers with FCM tokens
```

## Future Enhancements

Possible improvements:
1. **Targeted Notifications**: Send to specific customer segments
2. **Scheduled Notifications**: Schedule notifications for future delivery
3. **Rich Notifications**: Add images, action buttons
4. **Token Management**: Auto-cleanup invalid tokens
5. **Analytics**: Track notification open rates
6. **Templates**: Variable substitution in messages (e.g., {firstName})
7. **Test Send**: Send to specific users for testing
8. **Notification History**: Track which customers received which notifications

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check server PHP error logs
3. Review Firebase Console for API errors
4. Test with a single customer first

## Summary

The system now provides a complete solution for:
- ✅ Creating dynamic notification templates
- ✅ Storing notifications in SQL database
- ✅ Sending push notifications to all customers via FCM
- ✅ Tracking success/failure statistics
- ✅ Managing notification history
- ✅ User-friendly admin interface

The integration automatically:
- Fetches FCM tokens from the `users` table
- Filters active customers only
- Handles authentication with Firebase
- Sends notifications in bulk
- Provides detailed feedback on results

