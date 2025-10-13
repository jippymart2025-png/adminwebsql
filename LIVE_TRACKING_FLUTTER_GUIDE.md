# Live Tracking API - Flutter Integration Guide

Complete guide for integrating live tracking features in your Flutter app using REST API endpoints.

---

## 📌 Overview

The Live Tracking API provides real-time data for tracking:
- **In-transit orders** with driver, customer, and restaurant details
- **Available drivers** with GPS locations
- **Real-time driver location updates**

**Base URL:** `https://web.jippymart.in/api` (or your server URL)

---

## 🚀 Quick Start

### 1. Add HTTP Package to `pubspec.yaml`

```yaml
dependencies:
  http: ^1.1.0
```

### 2. Create API Service Class

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class LiveTrackingService {
  static const String baseUrl = 'https://web.jippymart.in/api';
  
  // Get all live tracking data
  Future<LiveTrackingResponse> getLiveTrackingData() async {
    final response = await http.get(
      Uri.parse('$baseUrl/firebase/live-tracking'),
    );
    
    if (response.statusCode == 200) {
      return LiveTrackingResponse.fromJson(jsonDecode(response.body));
    } else {
      throw Exception('Failed to load live tracking data');
    }
  }
  
  // Get single driver location
  Future<DriverLocation> getDriverLocation(String driverId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/firebase/drivers/$driverId/location'),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return DriverLocation.fromJson(data['data']);
    } else {
      throw Exception('Failed to load driver location');
    }
  }
  
  // Batch get driver locations
  Future<List<DriverLocation>> batchGetDriverLocations(List<String> driverIds) async {
    final response = await http.post(
      Uri.parse('$baseUrl/firebase/drivers/locations'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'driver_ids': driverIds}),
    );
    
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return (data['data'] as List)
          .map((item) => DriverLocation.fromJson(item))
          .toList();
    } else {
      throw Exception('Failed to load driver locations');
    }
  }
}
```

---

## 📦 Data Models

### Complete Models Package

```dart
// lib/models/live_tracking_models.dart

class LiveTrackingResponse {
  final bool status;
  final String message;
  final LiveTrackingMeta meta;
  final LiveTrackingData data;

  LiveTrackingResponse({
    required this.status,
    required this.message,
    required this.meta,
    required this.data,
  });

  factory LiveTrackingResponse.fromJson(Map<String, dynamic> json) {
    return LiveTrackingResponse(
      status: json['status'] ?? false,
      message: json['message'] ?? '',
      meta: LiveTrackingMeta.fromJson(json['meta'] ?? {}),
      data: LiveTrackingData.fromJson(json['data'] ?? {}),
    );
  }
}

class LiveTrackingMeta {
  final int inTransitCount;
  final int availableDriversCount;
  final int totalCount;
  final int cacheTtlSeconds;

  LiveTrackingMeta({
    required this.inTransitCount,
    required this.availableDriversCount,
    required this.totalCount,
    required this.cacheTtlSeconds,
  });

  factory LiveTrackingMeta.fromJson(Map<String, dynamic> json) {
    return LiveTrackingMeta(
      inTransitCount: json['in_transit_count'] ?? 0,
      availableDriversCount: json['available_drivers_count'] ?? 0,
      totalCount: json['total_count'] ?? 0,
      cacheTtlSeconds: json['cache_ttl_seconds'] ?? 10,
    );
  }
}

class LiveTrackingData {
  final List<InTransitOrder> inTransitOrders;
  final List<AvailableDriver> availableDrivers;

  LiveTrackingData({
    required this.inTransitOrders,
    required this.availableDrivers,
  });

  factory LiveTrackingData.fromJson(Map<String, dynamic> json) {
    return LiveTrackingData(
      inTransitOrders: (json['in_transit_orders'] as List?)
              ?.map((item) => InTransitOrder.fromJson(item))
              .toList() ??
          [],
      availableDrivers: (json['available_drivers'] as List?)
              ?.map((item) => AvailableDriver.fromJson(item))
              .toList() ??
          [],
    );
  }
}

class InTransitOrder {
  final String orderId;
  final String flag;
  final DriverInfo driver;
  final CustomerInfo customer;
  final RestaurantInfo restaurant;
  final String pickupLocation;
  final String destination;
  final String orderType;
  final String status;

  InTransitOrder({
    required this.orderId,
    required this.flag,
    required this.driver,
    required this.customer,
    required this.restaurant,
    required this.pickupLocation,
    required this.destination,
    required this.orderType,
    required this.status,
  });

  factory InTransitOrder.fromJson(Map<String, dynamic> json) {
    return InTransitOrder(
      orderId: json['order_id'] ?? '',
      flag: json['flag'] ?? 'in_transit',
      driver: DriverInfo.fromJson(json['driver'] ?? {}),
      customer: CustomerInfo.fromJson(json['customer'] ?? {}),
      restaurant: RestaurantInfo.fromJson(json['restaurant'] ?? {}),
      pickupLocation: json['pickup_location'] ?? '',
      destination: json['destination'] ?? '',
      orderType: json['order_type'] ?? 'Delivery',
      status: json['status'] ?? 'In Transit',
    );
  }
}

class DriverInfo {
  final String id;
  final String name;
  final String phone;
  final Location location;

  DriverInfo({
    required this.id,
    required this.name,
    required this.phone,
    required this.location,
  });

  factory DriverInfo.fromJson(Map<String, dynamic> json) {
    return DriverInfo(
      id: json['id'] ?? '',
      name: json['name'] ?? '',
      phone: json['phone'] ?? '',
      location: Location.fromJson(json['location'] ?? {}),
    );
  }
}

class CustomerInfo {
  final String id;
  final String name;
  final String phone;

  CustomerInfo({
    required this.id,
    required this.name,
    required this.phone,
  });

  factory CustomerInfo.fromJson(Map<String, dynamic> json) {
    return CustomerInfo(
      id: json['id'] ?? '',
      name: json['name'] ?? '',
      phone: json['phone'] ?? '',
    );
  }
}

class RestaurantInfo {
  final String id;
  final String name;

  RestaurantInfo({
    required this.id,
    required this.name,
  });

  factory RestaurantInfo.fromJson(Map<String, dynamic> json) {
    return RestaurantInfo(
      id: json['id'] ?? '',
      name: json['name'] ?? '',
    );
  }
}

class AvailableDriver {
  final String id;
  final String flag;
  final String name;
  final String phone;
  final Location location;
  final bool isActive;
  final bool online;

  AvailableDriver({
    required this.id,
    required this.flag,
    required this.name,
    required this.phone,
    required this.location,
    required this.isActive,
    required this.online,
  });

  factory AvailableDriver.fromJson(Map<String, dynamic> json) {
    return AvailableDriver(
      id: json['id'] ?? '',
      flag: json['flag'] ?? 'available',
      name: json['name'] ?? '',
      phone: json['phone'] ?? '',
      location: Location.fromJson(json['location'] ?? {}),
      isActive: json['is_active'] ?? false,
      online: json['online'] ?? false,
    );
  }
}

class Location {
  final double latitude;
  final double longitude;

  Location({
    required this.latitude,
    required this.longitude,
  });

  factory Location.fromJson(Map<String, dynamic> json) {
    return Location(
      latitude: (json['latitude'] ?? 0.0).toDouble(),
      longitude: (json['longitude'] ?? 0.0).toDouble(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'latitude': latitude,
      'longitude': longitude,
    };
  }
}

class DriverLocation {
  final String id;
  final Location location;
  final String name;
  final String phone;
  final bool? isActive;

  DriverLocation({
    required this.id,
    required this.location,
    required this.name,
    required this.phone,
    this.isActive,
  });

  factory DriverLocation.fromJson(Map<String, dynamic> json) {
    return DriverLocation(
      id: json['id'] ?? '',
      location: Location.fromJson(json['location'] ?? {}),
      name: json['name'] ?? '',
      phone: json['phone'] ?? '',
      isActive: json['is_active'],
    );
  }
}
```

---

## 🗺️ Complete Live Tracking Screen Example

### Full Flutter Implementation with Google Maps

```dart
// lib/screens/live_tracking_screen.dart

import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'dart:async';

class LiveTrackingScreen extends StatefulWidget {
  const LiveTrackingScreen({Key? key}) : super(key: key);

  @override
  State<LiveTrackingScreen> createState() => _LiveTrackingScreenState();
}

class _LiveTrackingScreenState extends State<LiveTrackingScreen> {
  final LiveTrackingService _service = LiveTrackingService();
  GoogleMapController? _mapController;
  Timer? _updateTimer;
  
  Set<Marker> _markers = {};
  LiveTrackingData? _trackingData;
  bool _isLoading = true;
  
  // Default map position
  static const CameraPosition _initialPosition = CameraPosition(
    target: LatLng(15.9129, 79.7400), // Ongole, Andhra Pradesh
    zoom: 12,
  );

  @override
  void initState() {
    super.initState();
    _loadLiveTrackingData();
    _startAutoRefresh();
  }

  @override
  void dispose() {
    _updateTimer?.cancel();
    _mapController?.dispose();
    super.dispose();
  }

  // Load initial data
  Future<void> _loadLiveTrackingData() async {
    try {
      setState(() => _isLoading = true);
      
      final response = await _service.getLiveTrackingData();
      
      setState(() {
        _trackingData = response.data;
        _updateMarkers();
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      _showError('Failed to load tracking data: $e');
    }
  }

  // Auto-refresh every 10 seconds
  void _startAutoRefresh() {
    _updateTimer = Timer.periodic(const Duration(seconds: 10), (timer) {
      _loadLiveTrackingData();
    });
  }

  // Update map markers
  void _updateMarkers() {
    final Set<Marker> markers = {};

    // Add in-transit order markers (drivers on delivery)
    if (_trackingData?.inTransitOrders != null) {
      for (var order in _trackingData!.inTransitOrders) {
        markers.add(
          Marker(
            markerId: MarkerId('driver_${order.driver.id}'),
            position: LatLng(
              order.driver.location.latitude,
              order.driver.location.longitude,
            ),
            icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueRed),
            infoWindow: InfoWindow(
              title: '🚗 ${order.driver.name}',
              snippet: 'Order: ${order.orderId}\n'
                  'Customer: ${order.customer.name}\n'
                  'Status: ${order.status}',
            ),
            onTap: () => _showOrderDetails(order),
          ),
        );
      }
    }

    // Add available driver markers
    if (_trackingData?.availableDrivers != null) {
      for (var driver in _trackingData!.availableDrivers) {
        markers.add(
          Marker(
            markerId: MarkerId('available_${driver.id}'),
            position: LatLng(
              driver.location.latitude,
              driver.location.longitude,
            ),
            icon: BitmapDescriptor.defaultMarkerWithHue(BitmapDescriptor.hueGreen),
            infoWindow: InfoWindow(
              title: '✅ ${driver.name} (Available)',
              snippet: 'Phone: ${driver.phone}\n'
                  'Online: ${driver.online ? "Yes" : "No"}',
            ),
          ),
        );
      }
    }

    setState(() => _markers = markers);
  }

  // Show order details bottom sheet
  void _showOrderDetails(InTransitOrder order) {
    showModalBottomSheet(
      context: context,
      builder: (context) => Container(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Order Details',
              style: Theme.of(context).textTheme.headlineSmall,
            ),
            const SizedBox(height: 16),
            _buildDetailRow('Order ID', order.orderId),
            _buildDetailRow('Status', order.status),
            _buildDetailRow('Type', order.orderType),
            const Divider(),
            _buildDetailRow('Driver', order.driver.name),
            _buildDetailRow('Driver Phone', order.driver.phone),
            const Divider(),
            _buildDetailRow('Customer', order.customer.name),
            _buildDetailRow('Customer Phone', order.customer.phone),
            const Divider(),
            _buildDetailRow('Restaurant', order.restaurant.name),
            _buildDetailRow('Pickup', order.pickupLocation),
            _buildDetailRow('Destination', order.destination),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ),
          Expanded(
            child: Text(value),
          ),
        ],
      ),
    );
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Live Tracking'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadLiveTrackingData,
          ),
        ],
      ),
      body: Stack(
        children: [
          GoogleMap(
            initialCameraPosition: _initialPosition,
            markers: _markers,
            onMapCreated: (controller) {
              _mapController = controller;
            },
            myLocationEnabled: true,
            myLocationButtonEnabled: true,
          ),
          
          // Stats card
          Positioned(
            top: 16,
            left: 16,
            right: 16,
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildStatItem(
                      '🚗 In Transit',
                      _trackingData?.inTransitOrders.length.toString() ?? '0',
                      Colors.red,
                    ),
                    _buildStatItem(
                      '✅ Available',
                      _trackingData?.availableDrivers.length.toString() ?? '0',
                      Colors.green,
                    ),
                  ],
                ),
              ),
            ),
          ),
          
          // Loading indicator
          if (_isLoading)
            const Center(
              child: CircularProgressIndicator(),
            ),
        ],
      ),
    );
  }

  Widget _buildStatItem(String label, String count, Color color) {
    return Column(
      children: [
        Text(
          count,
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(label),
      ],
    );
  }
}
```

---

## 🔄 Real-Time Updates Pattern

### Strategy 1: Polling (Simple)

```dart
class LiveTrackingProvider extends ChangeNotifier {
  final LiveTrackingService _service = LiveTrackingService();
  Timer? _timer;
  LiveTrackingData? _data;
  bool _isLoading = false;

  LiveTrackingData? get data => _data;
  bool get isLoading => _isLoading;

  void startTracking() {
    // Initial load
    _loadData();
    
    // Poll every 10 seconds
    _timer = Timer.periodic(const Duration(seconds: 10), (timer) {
      _loadData();
    });
  }

  void stopTracking() {
    _timer?.cancel();
    _timer = null;
  }

  Future<void> _loadData() async {
    _isLoading = true;
    notifyListeners();
    
    try {
      final response = await _service.getLiveTrackingData();
      _data = response.data;
    } catch (e) {
      print('Error loading tracking data: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  @override
  void dispose() {
    stopTracking();
    super.dispose();
  }
}
```

### Strategy 2: Batch Update Specific Drivers

```dart
// Update only visible drivers on map
Future<void> updateVisibleDrivers(List<String> visibleDriverIds) async {
  try {
    final locations = await _service.batchGetDriverLocations(visibleDriverIds);
    
    // Update markers for these drivers only
    for (var location in locations) {
      _updateDriverMarker(location);
    }
  } catch (e) {
    print('Error updating driver locations: $e');
  }
}
```

---

## 📱 API Endpoints Reference

### 1. Get Live Tracking Data

**Endpoint:** `GET /api/firebase/live-tracking`

**Response:**
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
    "in_transit_orders": [...],
    "available_drivers": [...]
  }
}
```

**Cache:** 10 seconds

---

### 2. Get Single Driver Location

**Endpoint:** `GET /api/firebase/drivers/{driverId}/location`

**Example:** `GET /api/firebase/drivers/abc123/location`

**Response:**
```json
{
  "status": true,
  "message": "Driver location fetched successfully",
  "data": {
    "id": "abc123",
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

---

### 3. Batch Get Driver Locations

**Endpoint:** `POST /api/firebase/drivers/locations`

**Request Body:**
```json
{
  "driver_ids": ["driver1", "driver2", "driver3"]
}
```

**Response:**
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
      "id": "driver1",
      "location": {
        "latitude": 15.9129,
        "longitude": 79.7400
      },
      "name": "John Doe",
      "phone": "+919876543210"
    }
  ]
}
```

---

## 🎨 UI Components

### Driver List Widget

```dart
class DriverListWidget extends StatelessWidget {
  final List<InTransitOrder> inTransitOrders;
  final List<AvailableDriver> availableDrivers;
  final Function(String driverId, double lat, double lng)? onDriverTap;

  const DriverListWidget({
    Key? key,
    required this.inTransitOrders,
    required this.availableDrivers,
    this.onDriverTap,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return ListView(
      children: [
        if (inTransitOrders.isNotEmpty) ...[
          const Padding(
            padding: EdgeInsets.all(16),
            child: Text(
              '🚗 In Transit',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
          ),
          ...inTransitOrders.map((order) => _buildInTransitCard(order)),
        ],
        if (availableDrivers.isNotEmpty) ...[
          const Padding(
            padding: EdgeInsets.all(16),
            child: Text(
              '✅ Available',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
          ),
          ...availableDrivers.map((driver) => _buildAvailableCard(driver)),
        ],
      ],
    );
  }

  Widget _buildInTransitCard(InTransitOrder order) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        leading: const CircleAvatar(
          backgroundColor: Colors.red,
          child: Icon(Icons.local_shipping, color: Colors.white),
        ),
        title: Text(order.driver.name),
        subtitle: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Order: ${order.orderId}'),
            Text('Customer: ${order.customer.name}'),
            Text('To: ${order.destination}'),
          ],
        ),
        trailing: Chip(
          label: Text(order.orderType),
          backgroundColor: order.orderType == 'Delivery'
              ? Colors.blue.shade100
              : Colors.orange.shade100,
        ),
        onTap: () => onDriverTap?.call(
          order.driver.id,
          order.driver.location.latitude,
          order.driver.location.longitude,
        ),
      ),
    );
  }

  Widget _buildAvailableCard(AvailableDriver driver) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: driver.online ? Colors.green : Colors.grey,
          child: const Icon(Icons.person, color: Colors.white),
        ),
        title: Text(driver.name),
        subtitle: Text(driver.phone),
        trailing: driver.online
            ? const Chip(
                label: Text('Online'),
                backgroundColor: Colors.green,
              )
            : const Chip(
                label: Text('Offline'),
                backgroundColor: Colors.grey,
              ),
        onTap: () => onDriverTap?.call(
          driver.id,
          driver.location.latitude,
          driver.location.longitude,
        ),
      ),
    );
  }
}
```

---

## ⚡ Performance Tips

### 1. Optimize Polling Interval

```dart
// Adjust based on app state
Duration _getPollingInterval() {
  if (_isAppInForeground && _isOnTrackingScreen) {
    return const Duration(seconds: 10); // Active tracking
  } else if (_isAppInForeground) {
    return const Duration(seconds: 30); // Background in app
  } else {
    return const Duration(minutes: 5); // App in background
  }
}
```

### 2. Use Batch Updates for Multiple Drivers

```dart
// Instead of multiple single requests
for (var driverId in driverIds) {
  await getDriverLocation(driverId); // ❌ Slow
}

// Use batch request
await batchGetDriverLocations(driverIds); // ✅ Fast
```

### 3. Cancel Requests on Dispose

```dart
@override
void dispose() {
  _timer?.cancel();
  _cancelPendingRequests();
  super.dispose();
}
```

---

## 🐛 Error Handling

### Robust Error Handling Example

```dart
Future<LiveTrackingResponse?> getLiveTrackingDataSafe() async {
  try {
    final response = await _service.getLiveTrackingData()
        .timeout(const Duration(seconds: 15));
    return response;
  } on TimeoutException {
    _showError('Request timeout. Please check your internet connection.');
    return null;
  } on http.ClientException {
    _showError('Network error. Please try again.');
    return null;
  } catch (e) {
    _showError('An error occurred: $e');
    return null;
  }
}
```

---

## 🔐 Security Notes

1. **Rate Limiting:** API has rate limits (5 requests/min). Adjust polling accordingly.
2. **Authentication:** Add authentication headers if required in production.
3. **HTTPS Only:** Always use HTTPS in production.

---

## 📝 Complete Example Summary

### Minimal Working Example

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:async';

void main() => runApp(const MyApp());

class MyApp extends StatelessWidget {
  const MyApp({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Live Tracking',
      home: LiveTrackingPage(),
    );
  }
}

class LiveTrackingPage extends StatefulWidget {
  @override
  State<LiveTrackingPage> createState() => _LiveTrackingPageState();
}

class _LiveTrackingPageState extends State<LiveTrackingPage> {
  Timer? _timer;
  Map<String, dynamic>? _data;

  @override
  void initState() {
    super.initState();
    _loadData();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) => _loadData());
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _loadData() async {
    try {
      final response = await http.get(
        Uri.parse('https://web.jippymart.in/api/firebase/live-tracking'),
      );
      
      if (response.statusCode == 200) {
        setState(() => _data = jsonDecode(response.body));
      }
    } catch (e) {
      print('Error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final inTransit = (_data?['data']?['in_transit_orders'] as List?) ?? [];
    final available = (_data?['data']?['available_drivers'] as List?) ?? [];

    return Scaffold(
      appBar: AppBar(title: const Text('Live Tracking')),
      body: ListView(
        children: [
          ListTile(
            title: Text('In Transit: ${inTransit.length}'),
            subtitle: Text('Available: ${available.length}'),
          ),
          ...inTransit.map((order) => ListTile(
                title: Text(order['driver']['name'] ?? 'Unknown'),
                subtitle: Text('Order: ${order['order_id']}'),
              )),
        ],
      ),
    );
  }
}
```

---

## 🎯 Next Steps

1. ✅ Copy the models to your Flutter project
2. ✅ Create the `LiveTrackingService` class
3. ✅ Implement the UI based on the examples
4. ✅ Test with your API endpoints
5. ✅ Add Google Maps integration for visual tracking
6. ✅ Implement auto-refresh with proper timing

---

## 📞 Support

For API issues, check:
- Server logs for errors
- Network connectivity
- Rate limiting (5 req/min default)
- Response status codes

**Cache TTL:** 10 seconds (data refreshes automatically)

---

## 📚 Additional Resources

- [API_USAGE.md](./API_USAGE.md) - Complete API documentation
- [FLUTTER_API_GUIDE.md](./FLUTTER_API_GUIDE.md) - General Flutter API guide
- [Google Maps Flutter Plugin](https://pub.dev/packages/google_maps_flutter)

---

**Made with ❤️ for JippyMart Live Tracking**

