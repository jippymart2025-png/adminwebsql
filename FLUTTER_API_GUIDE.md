# Flutter API Integration Guide

Complete guide for integrating Jippy Mart Firebase APIs in your Flutter application.

---

## 📦 Setup

### 1. Add Dependencies

Add to `pubspec.yaml`:

```yaml
dependencies:
  http: ^1.1.0
  flutter_riverpod: ^2.4.9  # For state management (optional)
```

Run:
```bash
flutter pub get
```

### 2. Create API Service

Create `lib/services/firebase_api_service.dart`:

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class FirebaseApiService {
  static const String baseUrl = 'https://web.jippymart.in/api/firebase';
  
  // Headers for all requests
  Map<String, String> get headers => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  // Generic GET request handler
  Future<Map<String, dynamic>> _get(String endpoint, Map<String, String>? params) async {
    final uri = Uri.parse('$baseUrl/$endpoint').replace(queryParameters: params);
    
    try {
      final response = await http.get(uri, headers: headers);
      
      if (response.statusCode == 200) {
        return json.decode(response.body);
      } else {
        throw Exception('Failed to load data: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Network error: $e');
    }
  }

  // ==================== USERS API ====================
  
  /// Fetch users by role with pagination
  /// 
  /// [role] - 'customer', 'vendor', or 'driver'
  /// [limit] - Number of records per page (default: 10)
  /// [page] - Page number (default: 1)
  /// [withTotal] - Include total count (default: false)
  /// [lastCreatedAt] - Cursor for pagination
  /// [lastDocId] - Document ID for pagination
  Future<UsersResponse> getUsers({
    required String role,
    int limit = 10,
    int page = 1,
    bool withTotal = false,
    String? lastCreatedAt,
    String? lastDocId,
  }) async {
    final params = {
      'role': role,
      'limit': limit.toString(),
      'page': page.toString(),
      if (withTotal) 'with_total': '1',
      if (lastCreatedAt != null) 'last_created_at': lastCreatedAt,
      if (lastDocId != null) 'last_doc_id': lastDocId,
    };

    final response = await _get('users', params);
    return UsersResponse.fromJson(response);
  }

  // ==================== ORDERS API ====================
  
  /// Fetch orders with pagination and filters
  /// 
  /// [limit] - Number of records per page (default: 10)
  /// [page] - Page number (default: 1)
  /// [status] - Filter by order status
  /// [vendorID] - Filter by vendor ID
  /// [withTotal] - Include counters (default: false)
  /// [lastCreatedAt] - Cursor for pagination
  /// [lastDocId] - Document ID for pagination
  Future<OrdersResponse> getOrders({
    int limit = 10,
    int page = 1,
    String? status,
    String? vendorID,
    bool withTotal = false,
    String? lastCreatedAt,
    String? lastDocId,
  }) async {
    final params = {
      'limit': limit.toString(),
      'page': page.toString(),
      if (status != null) 'status': status,
      if (vendorID != null) 'vendorID': vendorID,
      if (withTotal) 'with_total': '1',
      if (lastCreatedAt != null) 'last_created_at': lastCreatedAt,
      if (lastDocId != null) 'last_doc_id': lastDocId,
    };

    final response = await _get('orders', params);
    return OrdersResponse.fromJson(response);
  }
}
```

---

## 📋 Models

### User Models

Create `lib/models/user_models.dart`:

```dart
class UsersResponse {
  final bool status;
  final String message;
  final UsersMeta meta;
  final List<User> data;

  UsersResponse({
    required this.status,
    required this.message,
    required this.meta,
    required this.data,
  });

  factory UsersResponse.fromJson(Map<String, dynamic> json) {
    return UsersResponse(
      status: json['status'] ?? false,
      message: json['message'] ?? '',
      meta: UsersMeta.fromJson(json['meta'] ?? {}),
      data: (json['data'] as List?)
          ?.map((item) => User.fromJson(item))
          .toList() ?? [],
    );
  }
}

class UsersMeta {
  final String role;
  final int page;
  final int limit;
  final int count;
  final int? total;
  final bool hasMore;
  final String? nextCreatedAt;
  final String? nextDocId;

  UsersMeta({
    required this.role,
    required this.page,
    required this.limit,
    required this.count,
    this.total,
    required this.hasMore,
    this.nextCreatedAt,
    this.nextDocId,
  });

  factory UsersMeta.fromJson(Map<String, dynamic> json) {
    return UsersMeta(
      role: json['role'] ?? '',
      page: json['page'] ?? 1,
      limit: json['limit'] ?? 10,
      count: json['count'] ?? 0,
      total: json['total'],
      hasMore: json['has_more'] ?? false,
      nextCreatedAt: json['next_created_at'],
      nextDocId: json['next_doc_id'],
    );
  }
}

class User {
  final String id;
  final String firstName;
  final String lastName;
  final String email;
  final String phoneNumber;
  final String countryCode;
  final String createdAt;
  final bool active;
  
  // Customer specific
  final String? userName;
  final String? zoneId;
  final String? profilePictureURL;
  
  // Vendor specific
  final String? vendorName;
  final String? vendorID;
  final String? vType;
  final String? subscriptionPlanId;
  final String? subscriptionExpiryDate;
  final bool? isDocumentVerify;
  final double? walletAmount;
  
  // Driver specific
  final String? name;
  final bool? online;
  final int? orderCompleted;
  final String? inProgressOrderID;

  User({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.email,
    required this.phoneNumber,
    required this.countryCode,
    required this.createdAt,
    required this.active,
    this.userName,
    this.zoneId,
    this.profilePictureURL,
    this.vendorName,
    this.vendorID,
    this.vType,
    this.subscriptionPlanId,
    this.subscriptionExpiryDate,
    this.isDocumentVerify,
    this.walletAmount,
    this.name,
    this.online,
    this.orderCompleted,
    this.inProgressOrderID,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? '',
      firstName: json['firstName'] ?? '',
      lastName: json['lastName'] ?? '',
      email: json['email'] ?? '',
      phoneNumber: json['phoneNumber'] ?? '',
      countryCode: json['countryCode'] ?? '',
      createdAt: json['createdAt'] ?? '',
      active: json['active'] ?? false,
      userName: json['userName'],
      zoneId: json['zoneId'],
      profilePictureURL: json['profilePictureURL'],
      vendorName: json['vendorName'],
      vendorID: json['vendorID'],
      vType: json['vType'],
      subscriptionPlanId: json['subscriptionPlanId'],
      subscriptionExpiryDate: json['subscriptionExpiryDate'],
      isDocumentVerify: json['isDocumentVerify'],
      walletAmount: json['wallet_amount']?.toDouble(),
      name: json['name'],
      online: json['online'],
      orderCompleted: json['orderCompleted'],
      inProgressOrderID: json['inProgressOrderID'],
    );
  }

  String get fullName => userName ?? name ?? '$firstName $lastName';
}
```

### Order Models

Create `lib/models/order_models.dart`:

```dart
class OrdersResponse {
  final bool status;
  final String message;
  final OrdersMeta meta;
  final OrderCounters? counters;
  final List<Order> data;

  OrdersResponse({
    required this.status,
    required this.message,
    required this.meta,
    this.counters,
    required this.data,
  });

  factory OrdersResponse.fromJson(Map<String, dynamic> json) {
    return OrdersResponse(
      status: json['status'] ?? false,
      message: json['message'] ?? '',
      meta: OrdersMeta.fromJson(json['meta'] ?? {}),
      counters: json['counters'] != null 
          ? OrderCounters.fromJson(json['counters']) 
          : null,
      data: (json['data'] as List?)
          ?.map((item) => Order.fromJson(item))
          .toList() ?? [],
    );
  }
}

class OrdersMeta {
  final int page;
  final int limit;
  final int count;
  final int? total;
  final bool hasMore;
  final String? nextCreatedAt;
  final String? nextDocId;
  final String? statusFilter;
  final String? vendorId;

  OrdersMeta({
    required this.page,
    required this.limit,
    required this.count,
    this.total,
    required this.hasMore,
    this.nextCreatedAt,
    this.nextDocId,
    this.statusFilter,
    this.vendorId,
  });

  factory OrdersMeta.fromJson(Map<String, dynamic> json) {
    return OrdersMeta(
      page: json['page'] ?? 1,
      limit: json['limit'] ?? 10,
      count: json['count'] ?? 0,
      total: json['total'],
      hasMore: json['has_more'] ?? false,
      nextCreatedAt: json['next_created_at'],
      nextDocId: json['next_doc_id'],
      statusFilter: json['status_filter'],
      vendorId: json['vendor_id'],
    );
  }
}

class OrderCounters {
  final int total;
  final int activeOrders;
  final int completed;
  final int pending;
  final int cancelled;

  OrderCounters({
    required this.total,
    required this.activeOrders,
    required this.completed,
    required this.pending,
    required this.cancelled,
  });

  factory OrderCounters.fromJson(Map<String, dynamic> json) {
    return OrderCounters(
      total: json['total'] ?? 0,
      activeOrders: json['active_orders'] ?? 0,
      completed: json['completed'] ?? 0,
      pending: json['pending'] ?? 0,
      cancelled: json['cancelled'] ?? 0,
    );
  }
}

class Order {
  final String orderId;
  final Restaurant restaurant;
  final Driver? driver;
  final Client client;
  final String date;
  final String amount;
  final AmountBreakdown amountBreakdown;
  final String orderType;
  final String status;
  final String paymentMethod;
  final int productsCount;
  final String address;

  Order({
    required this.orderId,
    required this.restaurant,
    this.driver,
    required this.client,
    required this.date,
    required this.amount,
    required this.amountBreakdown,
    required this.orderType,
    required this.status,
    required this.paymentMethod,
    required this.productsCount,
    required this.address,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      orderId: json['order_id'] ?? '',
      restaurant: Restaurant.fromJson(json['restaurant'] ?? {}),
      driver: json['driver'] != null ? Driver.fromJson(json['driver']) : null,
      client: Client.fromJson(json['client'] ?? {}),
      date: json['date'] ?? '',
      amount: json['amount'] ?? '0.00',
      amountBreakdown: AmountBreakdown.fromJson(json['amount_breakdown'] ?? {}),
      orderType: json['order_type'] ?? '',
      status: json['status'] ?? '',
      paymentMethod: json['payment_method'] ?? '',
      productsCount: json['products_count'] ?? 0,
      address: json['address'] ?? '',
    );
  }
}

class Restaurant {
  final String id;
  final String name;
  final String? photo;

  Restaurant({required this.id, required this.name, this.photo});

  factory Restaurant.fromJson(Map<String, dynamic> json) {
    return Restaurant(
      id: json['id'] ?? '',
      name: json['name'] ?? '',
      photo: json['photo'],
    );
  }
}

class Driver {
  final String? id;
  final String? name;
  final String? phone;

  Driver({this.id, this.name, this.phone});

  factory Driver.fromJson(Map<String, dynamic> json) {
    return Driver(
      id: json['id'],
      name: json['name'],
      phone: json['phone'],
    );
  }

  bool get isAssigned => id != null;
}

class Client {
  final String id;
  final String name;
  final String phone;
  final String email;

  Client({
    required this.id,
    required this.name,
    required this.phone,
    required this.email,
  });

  factory Client.fromJson(Map<String, dynamic> json) {
    return Client(
      id: json['id'] ?? '',
      name: json['name'] ?? '',
      phone: json['phone'] ?? '',
      email: json['email'] ?? '',
    );
  }
}

class AmountBreakdown {
  final String subtotal;
  final String deliveryCharge;
  final String tip;
  final String discount;

  AmountBreakdown({
    required this.subtotal,
    required this.deliveryCharge,
    required this.tip,
    required this.discount,
  });

  factory AmountBreakdown.fromJson(Map<String, dynamic> json) {
    return AmountBreakdown(
      subtotal: json['subtotal'] ?? '0.00',
      deliveryCharge: json['delivery_charge'] ?? '0.00',
      tip: json['tip'] ?? '0.00',
      discount: json['discount'] ?? '0.00',
    );
  }
}
```

---

## 🚀 Usage Examples

### 1. Basic Usage

```dart
import 'package:flutter/material.dart';
import 'services/firebase_api_service.dart';
import 'models/user_models.dart';

class CustomersPage extends StatefulWidget {
  @override
  _CustomersPageState createState() => _CustomersPageState();
}

class _CustomersPageState extends State<CustomersPage> {
  final FirebaseApiService _api = FirebaseApiService();
  List<User> customers = [];
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    loadCustomers();
  }

  Future<void> loadCustomers() async {
    setState(() => isLoading = true);
    
    try {
      final response = await _api.getUsers(
        role: 'customer',
        limit: 10,
        withTotal: true,
      );
      
      setState(() {
        customers = response.data;
        isLoading = false;
      });
      
      print('Loaded ${response.meta.count} of ${response.meta.total} customers');
    } catch (e) {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Center(child: CircularProgressIndicator());
    }

    return ListView.builder(
      itemCount: customers.length,
      itemBuilder: (context, index) {
        final customer = customers[index];
        return ListTile(
          leading: CircleAvatar(
            backgroundImage: customer.profilePictureURL != null
                ? NetworkImage(customer.profilePictureURL!)
                : null,
            child: customer.profilePictureURL == null
                ? Text(customer.firstName[0])
                : null,
          ),
          title: Text(customer.fullName),
          subtitle: Text(customer.email),
          trailing: Chip(
            label: Text(customer.active ? 'Active' : 'Inactive'),
            backgroundColor: customer.active ? Colors.green : Colors.grey,
          ),
        );
      },
    );
  }
}
```

### 2. Infinite Scroll / Load More

```dart
class OrdersPage extends StatefulWidget {
  @override
  _OrdersPageState createState() => _OrdersPageState();
}

class _OrdersPageState extends State<OrdersPage> {
  final FirebaseApiService _api = FirebaseApiService();
  final ScrollController _scrollController = ScrollController();
  
  List<Order> orders = [];
  bool isLoading = false;
  bool isLoadingMore = false;
  bool hasMore = true;
  
  String? lastCreatedAt;
  String? lastDocId;
  int currentPage = 1;

  @override
  void initState() {
    super.initState();
    loadOrders();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= 
        _scrollController.position.maxScrollExtent * 0.8) {
      if (!isLoadingMore && hasMore) {
        loadMore();
      }
    }
  }

  Future<void> loadOrders() async {
    setState(() => isLoading = true);
    
    try {
      final response = await _api.getOrders(
        limit: 10,
        withTotal: true,
      );
      
      setState(() {
        orders = response.data;
        hasMore = response.meta.hasMore;
        lastCreatedAt = response.meta.nextCreatedAt;
        lastDocId = response.meta.nextDocId;
        currentPage = 1;
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      _showError(e.toString());
    }
  }

  Future<void> loadMore() async {
    if (!hasMore || lastCreatedAt == null || lastDocId == null) return;
    
    setState(() => isLoadingMore = true);
    
    try {
      final response = await _api.getOrders(
        limit: 10,
        page: currentPage + 1,
        lastCreatedAt: lastCreatedAt,
        lastDocId: lastDocId,
      );
      
      setState(() {
        orders.addAll(response.data);
        hasMore = response.meta.hasMore;
        lastCreatedAt = response.meta.nextCreatedAt;
        lastDocId = response.meta.nextDocId;
        currentPage++;
        isLoadingMore = false;
      });
    } catch (e) {
      setState(() => isLoadingMore = false);
      _showError(e.toString());
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Error: $message')),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Center(child: CircularProgressIndicator());
    }

    return Column(
      children: [
        Expanded(
          child: ListView.builder(
            controller: _scrollController,
            itemCount: orders.length + (isLoadingMore ? 1 : 0),
            itemBuilder: (context, index) {
              if (index == orders.length) {
                return Center(
                  child: Padding(
                    padding: EdgeInsets.all(16),
                    child: CircularProgressIndicator(),
                  ),
                );
              }
              
              final order = orders[index];
              return OrderCard(order: order);
            },
          ),
        ),
      ],
    );
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }
}

class OrderCard extends StatelessWidget {
  final Order order;

  const OrderCard({required this.order});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.all(8),
      child: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  order.orderId,
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                Chip(
                  label: Text(order.status),
                  backgroundColor: _getStatusColor(order.status),
                ),
              ],
            ),
            SizedBox(height: 8),
            Text('Restaurant: ${order.restaurant.name}'),
            Text('Client: ${order.client.name}'),
            Text('Date: ${order.date}'),
            Text(
              'Amount: ₹${order.amount}',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
            Text('Type: ${order.orderType}'),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'order completed':
        return Colors.green;
      case 'driver pending':
      case 'order placed':
        return Colors.orange;
      case 'order cancelled':
      case 'order rejected':
        return Colors.red;
      default:
        return Colors.blue;
    }
  }
}
```

### 3. Filtered Orders

```dart
class FilteredOrdersPage extends StatefulWidget {
  @override
  _FilteredOrdersPageState createState() => _FilteredOrdersPageState();
}

class _FilteredOrdersPageState extends State<FilteredOrdersPage> {
  final FirebaseApiService _api = FirebaseApiService();
  
  List<Order> orders = [];
  OrderCounters? counters;
  String? selectedStatus;
  bool isLoading = false;

  final List<String> orderStatuses = [
    'Driver Pending',
    'Order Placed',
    'Order Accepted',
    'Order Completed',
    'Order Cancelled',
  ];

  Future<void> loadOrders() async {
    setState(() => isLoading = true);
    
    try {
      final response = await _api.getOrders(
        limit: 20,
        status: selectedStatus,
        withTotal: true,
      );
      
      setState(() {
        orders = response.data;
        counters = response.counters;
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  @override
  void initState() {
    super.initState();
    loadOrders();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Orders'),
        actions: [
          PopupMenuButton<String>(
            onSelected: (status) {
              setState(() => selectedStatus = status);
              loadOrders();
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                value: null,
                child: Text('All Orders'),
              ),
              ...orderStatuses.map((status) => PopupMenuItem(
                value: status,
                child: Text(status),
              )),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          if (counters != null)
            Container(
              padding: EdgeInsets.all(16),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildCounter('Total', counters!.total, Colors.blue),
                  _buildCounter('Active', counters!.activeOrders, Colors.orange),
                  _buildCounter('Completed', counters!.completed, Colors.green),
                  _buildCounter('Cancelled', counters!.cancelled, Colors.red),
                ],
              ),
            ),
          Expanded(
            child: isLoading
                ? Center(child: CircularProgressIndicator())
                : ListView.builder(
                    itemCount: orders.length,
                    itemBuilder: (context, index) {
                      return OrderCard(order: orders[index]);
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildCounter(String label, int count, Color color) {
    return Column(
      children: [
        Text(
          count.toString(),
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(label, style: TextStyle(fontSize: 12)),
      ],
    );
  }
}
```

### 4. Pull to Refresh

```dart
class RefreshableUsersPage extends StatefulWidget {
  @override
  _RefreshableUsersPageState createState() => _RefreshableUsersPageState();
}

class _RefreshableUsersPageState extends State<RefreshableUsersPage> {
  final FirebaseApiService _api = FirebaseApiService();
  List<User> users = [];
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    loadUsers();
  }

  Future<void> loadUsers() async {
    setState(() => isLoading = true);
    
    try {
      final response = await _api.getUsers(
        role: 'driver',
        limit: 20,
        withTotal: true,
      );
      
      setState(() {
        users = response.data;
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
    }
  }

  Future<void> _onRefresh() async {
    await loadUsers();
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _onRefresh,
      child: isLoading && users.isEmpty
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: users.length,
              itemBuilder: (context, index) {
                final user = users[index];
                return ListTile(
                  leading: CircleAvatar(
                    backgroundColor: user.active ? Colors.green : Colors.grey,
                    child: Text(user.firstName[0]),
                  ),
                  title: Text(user.fullName),
                  subtitle: Text('${user.email}\n${user.phoneNumber}'),
                  trailing: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      if (user.online == true)
                        Icon(Icons.circle, color: Colors.green, size: 12),
                      Text('Orders: ${user.orderCompleted ?? 0}'),
                    ],
                  ),
                  isThreeLine: true,
                );
              },
            ),
    );
  }
}
```

---

## 🎨 Complete Example App

Create `lib/main.dart`:

```dart
import 'package:flutter/material.dart';
import 'services/firebase_api_service.dart';
import 'models/user_models.dart';
import 'models/order_models.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Jippy Mart Admin',
      theme: ThemeData(primarySwatch: Colors.blue),
      home: HomePage(),
    );
  }
}

class HomePage extends StatefulWidget {
  @override
  _HomePageState createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _selectedIndex = 0;

  final List<Widget> _pages = [
    OrdersListPage(),
    CustomersListPage(),
    VendorsListPage(),
    DriversListPage(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Jippy Mart Admin'),
      ),
      body: _pages[_selectedIndex],
      bottomNavigationBar: BottomNavigationBar(
        type: BottomNavigationBarType.fixed,
        currentIndex: _selectedIndex,
        onTap: (index) => setState(() => _selectedIndex = index),
        items: [
          BottomNavigationBarItem(icon: Icon(Icons.receipt), label: 'Orders'),
          BottomNavigationBarItem(icon: Icon(Icons.people), label: 'Customers'),
          BottomNavigationBarItem(icon: Icon(Icons.store), label: 'Vendors'),
          BottomNavigationBarItem(icon: Icon(Icons.delivery_dining), label: 'Drivers'),
        ],
      ),
    );
  }
}

// Implement OrdersListPage, CustomersListPage, etc. using examples above
```

---

## 📱 Key Features

### ✅ What's Included:
- Complete API service with error handling
- All response models (Users, Orders, Meta, Counters)
- Pagination with load more
- Pull to refresh
- Filtering by status/vendor
- Infinite scroll
- Order counters display
- Status badges with colors

### 🎯 Best Practices:
- Proper error handling
- Loading states
- Null safety
- Type-safe models
- Clean separation of concerns
- Reusable widgets

---

## 🔥 Quick Start Checklist

1. ✅ Add `http` package to `pubspec.yaml`
2. ✅ Copy `firebase_api_service.dart` to your project
3. ✅ Copy `user_models.dart` and `order_models.dart`
4. ✅ Update `baseUrl` in service with your domain
5. ✅ Use examples to build your UI
6. ✅ Test with different roles and filters

---

## 🎯 API Endpoints Summary

| Endpoint | Purpose | Key Parameters |
|----------|---------|----------------|
| `GET /api/firebase/users` | Fetch users by role | `role`, `limit`, `page`, `last_created_at`, `last_doc_id` |
| `GET /api/firebase/orders` | Fetch orders | `limit`, `page`, `status`, `vendorID`, `last_created_at`, `last_doc_id` |

**All endpoints support:**
- Pagination (10 items per page default)
- Cursor-based load more
- Sorted by `createdAt` DESC (most recent first)
- Optional total counts with `with_total=1`

---

## 💡 Tips

1. **First Load**: Always use `withTotal: true` on initial page load
2. **Load More**: Pass `lastCreatedAt` and `lastDocId` from previous response
3. **Filtering**: Combine status/vendorID filters with pagination
4. **Caching**: Responses are cached for 30s server-side
5. **Rate Limit**: 5 requests per minute per IP

---

## 🚀 You're Ready!

This guide covers everything you need to integrate Jippy Mart Firebase APIs in Flutter. Start with the basic examples and customize based on your UI requirements.

For questions or issues, refer to the main `API_USAGE.md` documentation.

