# Firebase API Endpoints Documentation

## Overview
Optimized Firebase API endpoints for fetching users and orders with efficient cursor-based pagination, sorting by `createdAt` (descending - most recent first), and optional total counts.

---

## 1. Users Endpoint

### Endpoint
```
GET /api/firebase/users
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `role` | string | **Yes** | - | User role: `customer`, `vendor`, or `driver` |
| `limit` | integer | No | 10 | Number of records per page |
| `page` | integer | No | 1 | Current page number (for reference only) |
| `last_created_at` | timestamp | No | - | CreatedAt timestamp from previous page's last record |
| `last_doc_id` | string | No | - | Document ID from previous page's last record |
| `with_total` | string | No | - | Set to `1` to include total count (slower, cached 5 min) |

### Response Structure

```json
{
  "status": true,
  "message": "Users fetched successfully",
  "meta": {
    "role": "customer",
    "page": 1,
    "limit": 10,
    "count": 10,
    "total": 245,
    "has_more": true,
    "next_created_at": "2025-05-02 17:30:00",
    "next_doc_id": "2e0e0b9921a04ea4b4d5"
  },
  "data": [...]
}
```

### Role-Specific Fields

#### Customer (`role=customer`)
```json
{
  "id": "2e0e0b9921a04ea4b4d5",
  "userName": "Jane Smith",
  "email": "jane.smith@example.com",
  "phoneNumber": "1234567890",
  "countryCode": "+1",
  "zoneId": "zone123",
  "createdAt": "2024-05-02 17:30:00",
  "active": true,
  "profilePictureURL": "https://..."
}
```

#### Vendor (`role=vendor`)
```json
{
  "id": "A3SAuGgMLJVJInSYwjXj6Og06Nx2",
  "vendorName": "Naga Sarika",
  "vendorID": "daMWPC85zS5DArdq17yX",
  "email": "nagasarika8@gmail.com",
  "phoneNumber": "8106211309",
  "countryCode": "+91",
  "zoneId": "zone456",
  "vType": "restaurant",
  "subscriptionPlanId": null,
  "subscriptionExpiryDate": null,
  "createdAt": "2025-07-13 00:55:05",
  "active": true,
  "isDocumentVerify": false,
  "wallet_amount": 0
}
```

#### Driver (`role=driver`)
```json
{
  "id": "cfHF2ZnCl0NzIgUK18OhOUCehcW2",
  "name": "Priyanka Oraw",
  "email": "debasishbarman4445@gmail.com",
  "phoneNumber": "6297399228",
  "countryCode": "+91",
  "zoneId": "BmSTwRFzmP13PnVNFJZJ",
  "createdAt": "2025-09-20 20:41:58",
  "active": false,
  "isDocumentVerify": false,
  "isActive": null,
  "online": true,
  "wallet_amount": 0,
  "orderCompleted": 0,
  "inProgressOrderID": null
}
```

### Usage Examples

#### First Page (with total count)
```
GET /api/firebase/users?role=customer&limit=10&with_total=1
```

#### Next Page (using cursor from previous response)
```
GET /api/firebase/users?role=customer&limit=10&page=2&last_created_at=2024-05-02%2017:30:00&last_doc_id=2e0e0b9921a04ea4b4d5
```

#### Vendors Only
```
GET /api/firebase/users?role=vendor&limit=10
```

#### Drivers Only
```
GET /api/firebase/users?role=driver&limit=10
```

---

## 2. Orders Endpoint

### Endpoint
```
GET /api/firebase/orders
```

### Query Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `limit` | integer | No | 10 | Number of records per page |
| `page` | integer | No | 1 | Current page number (for reference) |
| `status` | string | No | - | Filter by order status (e.g., "order placed", "order completed") |
| `vendorID` | string | No | - | Filter by vendor ID |
| `last_created_at` | timestamp | No | - | CreatedAt timestamp from previous page's last record |
| `last_doc_id` | string | No | - | Document ID from previous page's last record |
| `with_total` | string | No | - | Set to `1` to include counters (slower, cached 5 min) |

### Response Structure

```json
{
  "status": true,
  "message": "Orders fetched successfully",
  "meta": {
    "page": 1,
    "limit": 10,
    "count": 10,
    "total": 1523,
    "has_more": true,
    "next_created_at": "2025-10-13 14:00:00",
    "next_doc_id": "order_abc123",
    "status_filter": null,
    "vendor_id": null
  },
  "counters": {
    "total": 1523,
    "active_orders": 245,
    "completed": 1100,
    "pending": 78,
    "cancelled": 100
  },
  "data": [
    {
      "order_id": "Jippy30000487",
      "restaurant": {
        "id": "pDfX6GSJgyldWcPnB8KS",
        "name": "ss foods",
        "photo": "https://firebasestorage.googleapis.com/..."
      },
      "driver": {
        "id": null,
        "name": null,
        "phone": null
      },
      "client": {
        "id": "user_1945",
        "name": "Banu Maradana",
        "phone": "+918374059477",
        "email": "bhanuprasadmaradana9610@gmail.com"
      },
      "date": "2025-10-13 16:45:09",
      "created_at_raw": "...",
      "amount": "58.00",
      "amount_breakdown": {
        "subtotal": "45.00",
        "delivery_charge": "23.00",
        "tip": "0.00",
        "discount": "10.00"
      },
      "order_type": "Delivery",
      "status": "Driver Pending",
      "payment_method": "cod",
      "products_count": 1,
      "address": "37-1-413/14, Ramnagar, Ambedkar Colony, Ramnagar, Ongole, Andhra Pradesh 523001, India"
    }
  ]
}
```

### Order Status Categories

- **Active Orders**: `order placed`, `order accepted`, `order shipped`, `in transit`, `driver pending`
- **Completed**: `order completed`
- **Pending**: `order placed`, `driver pending`, `in transit`
- **Cancelled**: `order rejected`, `driver rejected`, `order cancelled`, `cancelled`

### Usage Examples

#### First Page (all orders with counters)
```
GET /api/firebase/orders?limit=10&with_total=1
```

#### Next Page (using cursor)
```
GET /api/firebase/orders?limit=10&page=2&last_created_at=2025-10-13%2014:00:00&last_doc_id=order_abc123
```

#### Filter by Status
```
GET /api/firebase/orders?status=order%20placed&limit=10
```

#### Filter by Vendor
```
GET /api/firebase/orders?vendorID=daMWPC85zS5DArdq17yX&limit=10
```

#### Combined Filters
```
GET /api/firebase/orders?status=order%20completed&vendorID=daMWPC85zS5DArdq17yX&limit=10&with_total=1
```

---

## Performance Notes

### Caching
- **Page results**: Cached for 30 seconds
- **Total counts**: Cached for 5 minutes (300 seconds)
- Cache keys include all filter parameters and cursor position

### Optimization Tips

1. **First Load**: Request `with_total=1` only on the first page to get total counts
2. **Pagination**: Always use `last_created_at` and `last_doc_id` from the previous response's `meta` object for efficient cursor-based pagination
3. **Sorting**: All results are sorted by `createdAt` in **descending order** (most recent first)
4. **Field Selection**: Only necessary fields are returned based on role/context to minimize payload size

### Load More Pattern (Frontend)

```javascript
let lastCreatedAt = null;
let lastDocId = null;
let currentPage = 1;

// First page
async function loadFirstPage() {
  const response = await fetch('/api/firebase/users?role=customer&limit=10&with_total=1');
  const data = await response.json();
  
  lastCreatedAt = data.meta.next_created_at;
  lastDocId = data.meta.next_doc_id;
  currentPage = 1;
  
  return data;
}

// Load more
async function loadMore() {
  if (!lastCreatedAt || !lastDocId) return;
  
  currentPage++;
  const response = await fetch(
    `/api/firebase/users?role=customer&limit=10&page=${currentPage}&last_created_at=${encodeURIComponent(lastCreatedAt)}&last_doc_id=${lastDocId}`
  );
  const data = await response.json();
  
  lastCreatedAt = data.meta.next_created_at;
  lastDocId = data.meta.next_doc_id;
  
  return data;
}

// Check if more pages exist
function hasMore(response) {
  return response.meta.has_more === true;
}
```

---

## Error Handling

### Invalid Role
```json
{
  "status": false,
  "message": "Valid role is required (customer, vendor, driver)"
}
```

### General Error Response
```json
{
  "status": false,
  "message": "Error description here"
}
```

---

## Rate Limiting

The endpoints are protected with throttling:
```php
Route::middleware(['throttle:5,1'])->group(function () {
    Route::get('/firebase/users', [FirebaseUserController::class, 'index']);
    Route::get('/firebase/orders', [FirebaseOrderController::class, 'index']);
});
```

**Limit**: 5 requests per minute per IP

---

## Server Deployment Checklist

Before the optimized endpoints work in production:

1. ✅ Upload updated controllers to server
2. ✅ Run: `composer dump-autoload -o`
3. ✅ Run: `php artisan config:clear`
4. ✅ Run: `php artisan cache:clear`
5. ✅ Run: `php artisan route:clear`
6. ✅ Run: `php artisan optimize:clear`
7. ✅ Reload PHP-FPM: `sudo systemctl reload php-fpm` (if applicable)
8. ✅ Verify `app/Http/Middleware/TrustProxies.php` exists on server
9. ✅ Ensure Firestore credentials are correctly configured in `.env`
10. ✅ Test endpoints with different roles and pagination scenarios

