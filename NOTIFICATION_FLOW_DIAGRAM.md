# Notification System Flow Diagram

## 🔄 Complete System Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          ADMIN USER WORKFLOW                             │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────┐
│ Admin Dashboard  │
└────────┬─────────┘
         │
         ▼
┌──────────────────────────────────────┐
│ Dynamic Notifications Page           │
│ (View all notifications)             │
└────────┬──────────┬──────────────────┘
         │          │
         │          └──────────────────────┐
         │                                 │
         ▼                                 ▼
┌──────────────────┐           ┌──────────────────┐
│ Create New       │           │ Send Existing    │
│ Notification     │           │ (Click 📧 icon)  │
└────────┬─────────┘           └────────┬─────────┘
         │                               │
         ▼                               │
┌──────────────────────────────────┐    │
│ Fill Form:                       │    │
│ - Type (order_placed, etc.)      │    │
│ - Subject (Notification Title)   │    │
│ - Message (Notification Body)    │    │
└────────┬─────────────────────────┘    │
         │                               │
         ▼                               │
┌──────────────────┐                     │
│ Click "Save"     │                     │
└────────┬─────────┘                     │
         │                               │
         ▼                               │
┌──────────────────────────────────┐    │
│ Notification Saved to DB         │    │
│ (dynamic_notification table)     │    │
└────────┬─────────────────────────┘    │
         │                               │
         ▼                               │
┌──────────────────────────────────┐    │
│ Redirected to Edit Page          │    │
│ "Send to All Customers" Button   │    │
└────────┬─────────────────────────┘    │
         │                               │
         └───────────────┬───────────────┘
                         │
                         ▼
┌──────────────────────────────────────────┐
│ Click "Send to All Customers"            │
│ Confirm Dialog: "Are you sure?"          │
└────────┬─────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────┐
│ DynamicNotificationController            │
│ sendToCustomers() Method                 │
└────────┬─────────────────────────────────┘
         │
         ▼

┌─────────────────────────────────────────────────────────────────────────┐
│                        BACKEND PROCESSING                                │
└─────────────────────────────────────────────────────────────────────────┘

         ┌──────────────────────────────────┐
         │ 1. Load Notification from DB     │
         │    (by ID)                       │
         └────────┬─────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────┐
         │ 2. Check Firebase Config         │
         │    serviceAccount.json exists?   │
         └────────┬─────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────┐
         │ 3. Authenticate with Firebase    │
         │    Get OAuth Access Token        │
         └────────┬─────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────────────┐
         │ 4. Query Database                        │
         │    SELECT id, fcmToken, firstName,       │
         │           lastName, email                │
         │    FROM users                            │
         │    WHERE fcmToken IS NOT NULL            │
         │      AND fcmToken != ''                  │
         │      AND active = 1                      │
         └────────┬─────────────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────┐
         │ 5. Loop Through Each Customer    │
         └────────┬─────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────────────┐
         │ For Each Customer:                       │
         │                                          │
         │  ┌─────────────────────────────────┐    │
         │  │ Build FCM Message:              │    │
         │  │ {                               │    │
         │  │   message: {                    │    │
         │  │     notification: {             │    │
         │  │       title: subject,           │    │
         │  │       body: message             │    │
         │  │     },                          │    │
         │  │     token: customer.fcmToken,   │    │
         │  │     android: {...},             │    │
         │  │     apns: {...}                 │    │
         │  │   }                             │    │
         │  │ }                               │    │
         │  └─────────────┬───────────────────┘    │
         │                │                         │
         │                ▼                         │
         │  ┌─────────────────────────────────┐    │
         │  │ POST to FCM API                 │    │
         │  │ https://fcm.googleapis.com/...  │    │
         │  └─────────────┬───────────────────┘    │
         │                │                         │
         │                ▼                         │
         │  ┌─────────────────────────────────┐    │
         │  │ Check Response                  │    │
         │  │ HTTP 200-299 = Success          │    │
         │  │ Otherwise = Failure             │    │
         │  └─────────────┬───────────────────┘    │
         │                │                         │
         │                ▼                         │
         │  ┌─────────────────────────────────┐    │
         │  │ Track Stats                     │    │
         │  │ successCount++  or              │    │
         │  │ failureCount++                  │    │
         │  └─────────────────────────────────┘    │
         │                                          │
         └──────────────────────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────┐
         │ 6. Return Statistics             │
         │    {                             │
         │      success: true,              │
         │      total: 150,                 │
         │      success: 145,               │
         │      failed: 5,                  │
         │      errors: [...]               │
         │    }                             │
         └────────┬─────────────────────────┘
                  │
                  ▼

┌─────────────────────────────────────────────────────────────────────────┐
│                          FRONTEND RESPONSE                               │
└─────────────────────────────────────────────────────────────────────────┘

         ┌──────────────────────────────────┐
         │ Hide Loading Indicator           │
         └────────┬─────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────┐
         │ Display Success Message          │
         │ "Notifications sent:             │
         │  145 successful, 5 failed"       │
         │                                  │
         │ Statistics:                      │
         │ - Total: 150                     │
         │ - Success: 145                   │
         │ - Failed: 5                      │
         └────────┬─────────────────────────┘
                  │
                  ▼
         ┌──────────────────────────────────┐
         │ Admin Reviews Results            │
         └──────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────┐
│                      CUSTOMER MOBILE APP FLOW                            │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐
│ Customer's Mobile Device         │
│ (App installed)                  │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ FCM Token Registered             │
│ (stored in users.fcmToken)       │
└────────┬─────────────────────────┘
         │
         │ [Waiting for notifications...]
         │
         ▼
┌──────────────────────────────────┐
│ FCM Message Arrives              │
│ (From Google FCM servers)        │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ Mobile App Receives Push         │
│ - Title: "New Order!"            │
│ - Body: "You have a new order"   │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ Notification Displayed           │
│ 🔔 Sound plays                   │
│ 📱 Badge updates                 │
│ 📲 Notification bar shows        │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ Customer Taps Notification       │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ App Opens                        │
│ (Navigate to relevant screen)    │
└──────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────┐
│                         DATABASE INTERACTIONS                            │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐
│ dynamic_notification table       │
│ ┌────────────────────────────┐   │
│ │ id          (UUID)         │   │
│ │ type        (string)       │   │
│ │ subject     (string)       │   │
│ │ message     (text)         │   │
│ │ createdAt   (ISO8601)      │   │
│ └────────────────────────────┘   │
└────────┬─────────────────────────┘
         │
         │ READ (when sending)
         │
         ▼
┌──────────────────────────────────┐
│ Notification Content Loaded      │
└──────────────────────────────────┘

┌──────────────────────────────────┐
│ users table                      │
│ ┌────────────────────────────┐   │
│ │ id          (int/uuid)     │   │
│ │ fcmToken    (string)       │◄──┼─ CRITICAL FIELD
│ │ active      (boolean)      │   │
│ │ firstName   (string)       │   │
│ │ lastName    (string)       │   │
│ │ email       (string)       │   │
│ └────────────────────────────┘   │
└────────┬─────────────────────────┘
         │
         │ QUERY (active customers with tokens)
         │
         ▼
┌──────────────────────────────────┐
│ Customer List for Notification   │
└──────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────┐
│                       FIREBASE INFRASTRUCTURE                            │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐
│ storage/app/firebase/            │
│   credentials.json               │
│ ┌────────────────────────────┐   │
│ │ {                          │   │
│ │   "type": "service_account"│   │
│ │   "project_id": "...",     │   │
│ │   "private_key": "...",    │   │
│ │   ...                      │   │
│ │ }                          │   │
│ └────────────────────────────┘   │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ Google OAuth 2.0                 │
│ Get Access Token                 │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────────────────┐
│ Firebase Cloud Messaging API v1              │
│ POST /v1/projects/{project}/messages:send    │
└────────┬─────────────────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ Google FCM Servers               │
│ Route messages to devices        │
└────────┬─────────────────────────┘
         │
         ▼
┌──────────────────────────────────┐
│ Customer Mobile Devices          │
│ Receive push notifications       │
└──────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────┐
│                          ERROR HANDLING FLOW                             │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐
│ Send Attempt                     │
└────────┬─────────────────────────┘
         │
         ▼
         ┌─────────────────────────────────┐
         │ Check: Firebase Config Exists?  │
         └────┬───────────────────┬─────────┘
              │ NO                │ YES
              ▼                   ▼
         ┌─────────────┐   ┌─────────────────┐
         │ Error:      │   │ Continue        │
         │ "Config not │   └────┬────────────┘
         │  found"     │        │
         └─────────────┘        ▼
                         ┌─────────────────────────┐
                         │ Check: Can Authenticate?│
                         └────┬──────────────┬─────┘
                              │ NO           │ YES
                              ▼              ▼
                         ┌─────────────┐  ┌──────────┐
                         │ Error:      │  │ Continue │
                         │ "Auth failed"│  └────┬─────┘
                         └─────────────┘       │
                                               ▼
                                        ┌──────────────────┐
                                        │ Check: Customers │
                                        │ with tokens?     │
                                        └────┬──────┬──────┘
                                             │ NO   │ YES
                                             ▼      ▼
                                        ┌─────────┐ ┌──────┐
                                        │ Error:  │ │ Send │
                                        │ "No     │ └──┬───┘
                                        │ customers"│  │
                                        └─────────┘   ▼
                                                   ┌──────────┐
                                                   │ Per-user │
                                                   │ errors   │
                                                   │ tracked  │
                                                   └──────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                         SUCCESS METRICS                                  │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐
│ Total Customers: 150             │
├──────────────────────────────────┤
│ ✅ Success: 145 (96.7%)          │
│ ❌ Failed:  5   (3.3%)           │
├──────────────────────────────────┤
│ Typical Failure Reasons:         │
│ • Invalid/expired tokens         │
│ • User uninstalled app           │
│ • Device offline                 │
│ • Notifications disabled         │
└──────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────┐
│                         LOGGING & MONITORING                             │
└─────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────┐
│ storage/logs/laravel.log         │
├──────────────────────────────────┤
│ [2025-12-03 10:30:15]            │
│ Send to customers request        │
│ {"id":"abc-123"}                 │
│                                  │
│ [2025-12-03 10:30:16]            │
│ Found customers to notify        │
│ {"count":150}                    │
│                                  │
│ [2025-12-03 10:30:45]            │
│ Notification sent to customer    │
│ {"customer_id":"456"}            │
│                                  │
│ [2025-12-03 10:31:45]            │
│ Notification sending completed   │
│ {"success":145,"failed":5}       │
└──────────────────────────────────┘
```

## 🔍 Key Integration Points

### 1. Admin → Backend
- **Trigger**: Button click in UI
- **Transport**: HTTP POST request
- **Endpoint**: `/dynamic-notification/send/{id}`
- **Authentication**: Laravel session + CSRF token

### 2. Backend → Database
- **Query**: Active users with FCM tokens
- **Table**: `users`
- **Condition**: `WHERE fcmToken IS NOT NULL AND active = 1`

### 3. Backend → Firebase
- **Authentication**: Service account JWT
- **API**: FCM v1 REST API
- **Transport**: HTTPS POST with Bearer token

### 4. Firebase → Mobile
- **Transport**: FCM push protocol (proprietary)
- **Delivery**: Google's infrastructure
- **Reliability**: Google handles retries

## 📊 Data Flow Summary

```
Admin UI → Laravel Controller → SQL Database → FCM API → Google Servers → Mobile Apps
    ↓                ↓               ↓            ↓           ↓              ↓
  Click          Validate       Get Tokens    Auth API   Route Msg    Display Notif
```

## 🎯 Critical Success Factors

1. ✅ **Valid FCM Tokens**: Customers must have valid tokens in database
2. ✅ **Active Users**: Only send to `active = 1` users
3. ✅ **Firebase Auth**: Valid serviceAccount.json file
4. ✅ **API Access**: Server can reach FCM endpoints
5. ✅ **Mobile App**: Properly implements FCM receiver

---

**This diagram illustrates the complete end-to-end flow of the notification system!** 🎉

