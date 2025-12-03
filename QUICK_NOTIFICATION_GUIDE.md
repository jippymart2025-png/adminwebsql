# Quick Start: Send Notifications to Customers

## 🚀 Quick Steps

### Method 1: Create and Send
1. Go to **Dynamic Notifications** page
2. Click **"Create New"** (or edit existing)
3. Fill in:
   - Type: `order_placed`, `restaurant_accepted`, etc.
   - Subject: Your notification title
   - Message: Your notification text
4. Click **"Save"**
5. Click **"Send to All Customers"** button
6. Confirm and wait for results

### Method 2: Send from List
1. Go to **Dynamic Notifications** page
2. Find the notification you want to send
3. Click the **📧 send icon**
4. Confirm and wait

## ⚙️ How It Works

1. System fetches all active customers from `users` table
2. Filters customers with valid `fcmToken`
3. Sends push notification to each customer via Firebase
4. Shows you results:
   - ✅ Success count
   - ❌ Failure count
   - 📊 Total customers contacted

## 📋 Prerequisites

- [x] Firebase `credentials.json` in `storage/app/firebase/`
- [x] `FIREBASE_PROJECT_ID` in `.env` file
- [x] Customers have `fcmToken` in database
- [x] Customers are active (`active = 1`)
- [x] Admin has `dynamic-notifications` permission

## 🔍 Database Fields Used

### From `users` table:
- `fcmToken` - Customer's Firebase token (required)
- `active` - Must be 1 (required)
- `id`, `firstName`, `lastName`, `email` - For logging

### Stored in `dynamic_notification` table:
- `id` - UUID
- `type` - Notification type
- `subject` - Title
- `message` - Body text
- `createdAt` - Timestamp

## 🎯 Notification Types

Common types:
- `order_placed` - New order placed
- `restaurant_accepted` - Restaurant accepted order
- `restaurant_rejected` - Restaurant rejected order
- `driver_accepted` - Driver accepted order
- `driver_completed` - Order delivered
- `takeaway_completed` - Takeaway ready
- `dinein_accepted` - Dine-in booking accepted
- `dinein_canceled` - Dine-in booking canceled
- `schedule_order` - Scheduled order reminder
- `payment_received` - Payment confirmation
- `driver_reached_doorstep` - Driver arrived

## 🛠️ Technical Details

### Routes Added:
```
POST /dynamic-notification/send/{id}  - Send to all customers
GET  /dynamic-notification/data       - Get notifications list
POST /dynamic-notification/upsert     - Create/update notification
GET  /api/dynamic-notification/{id}   - Get single notification
```

### Controller Method:
```php
DynamicNotificationController::sendToCustomers($id)
```

### SQL Query Used:
```sql
SELECT id, fcmToken, firstName, lastName, email 
FROM users 
WHERE fcmToken IS NOT NULL 
  AND fcmToken != '' 
  AND active = 1
```

## ⚠️ Important Notes

1. **Bulk Sending**: Sending to many customers may take 1-2 minutes
2. **Cannot Undo**: Once sent, notifications cannot be recalled
3. **Failed Sends**: Some failures are normal (uninstalled apps, invalid tokens)
4. **Logging**: All activity is logged in `storage/logs/laravel.log`
5. **Timeout**: Request timeout is 2 minutes for bulk operations

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| "No customers found" | Check if customers have `fcmToken` and `active=1` |
| "Firebase file not found" | Add `credentials.json` to `storage/app/firebase/` |
| "Authentication failed" | Check if `credentials.json` is valid |
| High failure rate | Check logs for specific errors, may be invalid tokens |

## 📊 Response Format

```json
{
  "success": true,
  "message": "Notifications sent: 150 successful, 10 failed",
  "stats": {
    "total_customers": 160,
    "success": 150,
    "failed": 10,
    "errors": ["Customer 123: Invalid token", ...]
  }
}
```

## 🔐 Permissions Required

Admin user must have:
- `dynamic-notifications` permission
- `dynamic-notification.save` sub-permission

## 📱 Mobile App Requirements

Customer mobile app must:
1. Register for FCM notifications
2. Store token in database via API
3. Update `users.fcmToken` field
4. Handle incoming notifications

## ✅ Testing

1. **Test with one customer first**:
   - Verify customer has valid `fcmToken`
   - Send notification
   - Check if received on device

2. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Verify in Firebase Console**:
   - Go to Firebase Cloud Messaging
   - Check sent messages

## 🎨 UI Features

- **List Page**: Send icon next to each notification
- **Edit Page**: "Send to All Customers" button after saving
- **Real-time Feedback**: Shows success/failure statistics
- **Confirmation**: Double-check before sending

## 💡 Best Practices

1. ✍️ Write clear, concise messages
2. 🕐 Send at appropriate times
3. 📝 Test with small group first
4. 🔍 Monitor logs for issues
5. 🚫 Don't spam customers
6. 📊 Track success rates
7. 🧹 Clean up invalid tokens periodically

## 📞 Need Help?

Check:
1. `storage/logs/laravel.log` - Application logs
2. Firebase Console - FCM errors
3. Server error logs - PHP errors
4. This guide - Common solutions

---

**That's it! You're ready to send notifications to your customers! 🎉**

