# Firebase API Endpoints Documentation

## Overview
Optimized Firebase API endpoints for fetching users and orders with efficient page-based pagination, sorting by `createdAt` (descending - most recent first), and optional total counts.

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
| `page` | integer | No | 1 | Page number (1 = first page, 2 = second page, etc.) |

**Note:** `with_total` parameter is no longer needed. Statistics are **always included** and cached for 5 minutes.

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
    "has_more": true,
    "next_created_at": "2025-05-02 17:30:00",
    "next_doc_id": "2e0e0b9921a04ea4b4d5"
  },
  "statistics": {
    "total": 245,
    "active": 189,
    "inactive": 56,
    "total_customers": 245,
    "active_customers": 189,
    "inactive_customers": 56
  },
  "data": [...]
}
```

**Note:** Statistics are **always included** in the response regardless of page or limit parameters. They are cached for 5 minutes for optimal performance.

### Role-Specific Fields

#### Customer (`role=customer`)

**Statistics Included:**
```json
{
  "total": 245,
  "active": 189,
  "inactive": 56,
  "total_customers": 245,
  "active_customers": 189,
  "inactive_customers": 56
}
```

**User Data Fields:**
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

**Statistics Included:**
```json
{
  "total": 589,
  "active": 423,
  "inactive": 166,
  "total_vendors": 589,
  "active_vendors": 423,
  "inactive_vendors": 166,
  "verified_vendors": 312
}
```

**User Data Fields:**
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

**Statistics Included:**
```json
{
  "total": 87,
  "active": 62,
  "inactive": 25,
  "total_drivers": 87,
  "active_drivers": 62,
  "inactive_drivers": 25,
  "verified_drivers": 54
}
```

**User Data Fields:**
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

**Note:** All requests now automatically include complete statistics!

#### First Page
```
GET /api/firebase/users?role=customer&limit=10
```
or
```
GET /api/firebase/users?role=customer&limit=10&page=1
```

#### Second Page
```
GET /api/firebase/users?role=customer&limit=10&page=2
```

#### Third Page
```
GET /api/firebase/users?role=customer&limit=10&page=3
```

#### Vendors Only (First Page)
```
GET /api/firebase/users?role=vendor&limit=10
```

#### Vendors Second Page
```
GET /api/firebase/users?role=vendor&limit=10&page=2
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
| `page` | integer | No | 1 | Page number (1 = first page, 2 = second page, etc.) |
| `status` | string | No | - | Filter by order status (e.g., "order placed", "order completed") |
| `vendorID` | string | No | - | Filter by vendor ID |
| `with_total` | string | No | - | Set to `1` to force counters calculation on any page (slower, cached 5 min) |

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

### Counters Field

The `counters` field provides order statistics and is included in the response based on the following logic:

- **Page 1**: Counters are automatically calculated and cached for 5 minutes
- **Page 2+**: Counters are retrieved from cache (if still valid from page 1 request)
- **Cache Expired**: If cache has expired on page 2+, counters will be `null` unless `with_total=1` is added
- **Force Calculation**: Add `with_total=1` to any page to force counter calculation

The counters include:
- `total`: Total number of orders matching the filters
- `active_orders`: Orders in active states
- `completed`: Completed orders
- `pending`: Orders pending action
- `cancelled`: Cancelled/rejected orders

### Order Status Categories

- **Active Orders**: `order placed`, `order accepted`, `order shipped`, `in transit`, `driver pending`
- **Completed**: `order completed`
- **Pending**: `order placed`, `driver pending`, `in transit`
- **Cancelled**: `order rejected`, `driver rejected`, `order cancelled`, `cancelled`

### Usage Examples

#### First Page (counters included automatically)
```
GET /api/firebase/orders?limit=10
```
or
```
GET /api/firebase/orders?limit=10&page=1
```

#### Second Page (uses cached counters from page 1)
```
GET /api/firebase/orders?limit=10&page=2
```
> **Note**: Counters will be included if still in cache (5-minute TTL)

#### Third Page
```
GET /api/firebase/orders?limit=10&page=3
```

#### Filter by Status (First Page)
```
GET /api/firebase/orders?status=order%20placed&limit=10
```

#### Filter by Status (Second Page)
```
GET /api/firebase/orders?status=order%20placed&limit=10&page=2
```

#### Filter by Vendor (First Page)
```
GET /api/firebase/orders?vendorID=daMWPC85zS5DArdq17yX&limit=10
```

#### Filter by Vendor (Second Page)
```
GET /api/firebase/orders?vendorID=daMWPC85zS5DArdq17yX&limit=10&page=2
```

#### Combined Filters
```
GET /api/firebase/orders?status=order%20completed&vendorID=daMWPC85zS5DArdq17yX&limit=10&page=1&with_total=1
```

#### Combined Filters (Next Page)
```
GET /api/firebase/orders?status=order%20completed&vendorID=daMWPC85zS5DArdq17yX&limit=10&page=2
```

---

## Performance Notes

### Caching
- **Page results**: Cached for 30 seconds
- **Total counts**: Cached for 5 minutes (300 seconds)
- Cache keys include all filter parameters and page number
- Each unique combination of filters + page number has its own cache entry

### Optimization Tips

1. **Counters (Orders API)**: Counters are automatically calculated on page 1 and cached for 5 minutes. Subsequent pages use the cached values, reducing load on Firestore.
2. **Statistics (Users API)**: Comprehensive statistics (total, active, inactive, verified) are automatically included in every response and cached for 5 minutes
3. **Pagination**: Use the `page` parameter to navigate through pages (page=1 for first page, page=2 for second page, etc.)
4. **Sorting**: All results are sorted by `createdAt` in **descending order** (most recent first)
5. **Field Selection**: Only necessary fields are returned based on role/context to minimize payload size
6. **Caching**: 
   - Page results are cached for 30 seconds
   - Statistics/counters are cached for 5 minutes (longer since they change less frequently)

### Load More Pattern (Frontend)

```javascript
let currentPage = 1;

// First page
async function loadFirstPage() {
  currentPage = 1;
  const response = await fetch('/api/firebase/users?role=vendor&limit=10&page=1');
  const data = await response.json();
  
  // Access statistics (always available)
  console.log('Total Vendors:', data.statistics.total_vendors);
  console.log('Active Vendors:', data.statistics.active_vendors);
  console.log('Inactive Vendors:', data.statistics.inactive_vendors);
  console.log('Verified Vendors:', data.statistics.verified_vendors);
  
  // Calculate total pages
  const totalPages = Math.ceil(data.statistics.total / data.meta.limit);
  console.log('Total Pages:', totalPages);
  
  return data;
}

// Load next page
async function loadNextPage() {
  currentPage++;
  const response = await fetch(
    `/api/firebase/users?role=vendor&limit=10&page=${currentPage}`
  );
  const data = await response.json();
  
  // Statistics are still available on every page!
  console.log('Statistics:', data.statistics);
  
  // Check if there are more pages
  if (!data.meta.has_more) {
    console.log('No more data available');
  }
  
  return data;
}

// Display statistics in UI
function displayStatistics(stats) {
  const html = `
    <div class="stats-dashboard">
      <div class="stat-card">
        <h3>${stats.total_vendors || stats.total_customers || stats.total_drivers}</h3>
        <p>Total</p>
      </div>
      <div class="stat-card active">
        <h3>${stats.active_vendors || stats.active_customers || stats.active_drivers}</h3>
        <p>Active</p>
      </div>
      <div class="stat-card inactive">
        <h3>${stats.inactive_vendors || stats.inactive_customers || stats.inactive_drivers}</h3>
        <p>Inactive</p>
      </div>
      ${stats.verified_vendors || stats.verified_drivers ? `
      <div class="stat-card verified">
        <h3>${stats.verified_vendors || stats.verified_drivers}</h3>
        <p>Verified</p>
      </div>
      ` : ''}
    </div>
  `;
  document.getElementById('stats-container').innerHTML = html;
}

// Check if more pages exist
function hasMore(response) {
  return response.meta.has_more === true;
}
```

---

## 3. Live Tracking Endpoint

### Endpoint
```
GET /api/firebase/live-tracking
```

### Description
Get real-time data for live tracking map showing:
- **In-transit orders** with driver and customer details
- **Available drivers** with their current locations

This endpoint combines data from orders and drivers to provide a complete view for the live tracking map.

### Query Parameters

No parameters required. This endpoint returns all current live tracking data.

### Response Structure

```json
{
  "status": true,
  "message": "Live tracking data fetched successfully",
  "meta": {
    "in_transit_count": 5,
    "available_drivers_count": 12,
    "total_count": 17,
    "cache_ttl_seconds": 10
  },
  "data": {
    "in_transit_orders": [
      {
        "order_id": "Jippy30000487",
        "flag": "in_transit",
        "driver": {
          "id": "driver123",
          "name": "John Doe",
          "phone": "+919876543210",
          "location": {
            "latitude": 15.9129,
            "longitude": 79.7400
          }
        },
        "customer": {
          "id": "user123",
          "name": "Jane Smith",
          "phone": "+918765432109"
        },
        "restaurant": {
          "id": "rest123",
          "name": "SS Foods"
        },
        "pickup_location": "123 Main St, City",
        "destination": "456 Elm St, City",
        "order_type": "Delivery",
        "status": "In Transit"
      }
    ],
    "available_drivers": [
      {
        "id": "driver456",
        "flag": "available",
        "name": "Bob Johnson",
        "phone": "+917654321098",
        "location": {
          "latitude": 15.9200,
          "longitude": 79.7500
        },
        "is_active": true,
        "online": true
      }
    ]
  }
}
```

### Usage Example

```
GET /api/firebase/live-tracking
```

### Caching
- Results are cached for **10 seconds** (due to real-time nature of location data)
- Auto-refreshes to get latest driver positions

---

## 4. Driver Location Endpoints

### Get Single Driver Location

#### Endpoint
```
GET /api/firebase/drivers/{driverId}/location
```

#### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `driverId` | string | **Yes** | The driver's unique ID |

#### Response

```json
{
  "status": true,
  "message": "Driver location fetched successfully",
  "data": {
    "id": "driver123",
    "location": {
      "latitude": 15.9129,
      "longitude": 79.7400
    },
    "name": "John Doe",
    "phone": "+919876543210",
    "is_active": true
  }
}
```

#### Usage Example

```
GET /api/firebase/drivers/driver123/location
```

### Batch Get Driver Locations

#### Endpoint
```
POST /api/firebase/drivers/locations
```

#### Request Body

```json
{
  "driver_ids": ["driver123", "driver456", "driver789"]
}
```

#### Response

```json
{
  "status": true,
  "message": "Driver locations fetched successfully",
  "meta": {
    "requested": 3,
    "found": 2
  },
  "data": [
    {
      "id": "driver123",
      "location": {
        "latitude": 15.9129,
        "longitude": 79.7400
      },
      "name": "John Doe",
      "phone": "+919876543210"
    },
    {
      "id": "driver456",
      "location": {
        "latitude": 15.9200,
        "longitude": 79.7500
      },
      "name": "Bob Johnson",
      "phone": "+917654321098"
    }
  ]
}
```

#### Usage Example

```javascript
// JavaScript/Flutter example for batch update
async function updateMultipleDriverLocations() {
  const response = await fetch('/api/firebase/drivers/locations', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      driver_ids: ['driver123', 'driver456', 'driver789']
    })
  });
  
  const data = await response.json();
  return data.data; // Array of driver locations
}
```

### Live Tracking Integration Pattern

```javascript
let trackingInterval;

// Initial load
async function initLiveTracking() {
  const response = await fetch('/api/firebase/live-tracking');
  const data = await response.json();
  
  // Render map with initial data
  renderMap(data.data.in_transit_orders, data.data.available_drivers);
  
  // Start polling for updates every 10 seconds
  trackingInterval = setInterval(async () => {
    const updateResponse = await fetch('/api/firebase/live-tracking');
    const updateData = await updateResponse.json();
    
    // Update map markers
    updateMapMarkers(updateData.data.in_transit_orders, updateData.data.available_drivers);
  }, 10000);
}

// Cleanup
function stopLiveTracking() {
  if (trackingInterval) {
    clearInterval(trackingInterval);
  }
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

**Note**: Live tracking endpoints are also rate-limited, but you may need to increase the limit for real-time polling scenarios (e.g., 60 requests per minute).

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
11. ✅ Test live tracking endpoints to ensure real-time data is returned correctly

