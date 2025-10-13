# Flutter API Quick Reference

## 🚀 Setup (2 minutes)

```yaml
# pubspec.yaml
dependencies:
  http: ^1.1.0
```

```dart
// lib/services/api.dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class API {
  static const base = 'https://web.jippymart.in/api/firebase';
  
  static Future<Map<String, dynamic>> getUsers(String role, {int page = 1}) async {
    final uri = Uri.parse('$base/users?role=$role&limit=10&page=$page&with_total=1');
    final response = await http.get(uri);
    return json.decode(response.body);
  }
  
  static Future<Map<String, dynamic>> getOrders({String? status, int page = 1}) async {
    var url = '$base/orders?limit=10&page=$page&with_total=1';
    if (status != null) url += '&status=$status';
    final uri = Uri.parse(url);
    final response = await http.get(uri);
    return json.decode(response.body);
  }
}
```

## 📋 Usage

### Load Customers
```dart
final data = await API.getUsers('customer');
final customers = data['data'] as List;
final total = data['meta']['total'];
```

### Load Orders
```dart
final data = await API.getOrders();
final orders = data['data'] as List;
final counters = data['counters'];
```

### Filter Orders
```dart
final data = await API.getOrders(status: 'Driver Pending');
```

### Load More (Pagination)
```dart
String? lastCreatedAt = data['meta']['next_created_at'];
String? lastDocId = data['meta']['next_doc_id'];
bool hasMore = data['meta']['has_more'];

if (hasMore) {
  final uri = Uri.parse(
    '$base/orders?limit=10&page=2&last_created_at=$lastCreatedAt&last_doc_id=$lastDocId'
  );
  final response = await http.get(uri);
  final nextPage = json.decode(response.body);
}
```

## 📦 Response Structure

### Users
```json
{
  "status": true,
  "meta": {
    "total": 245,
    "count": 10,
    "has_more": true,
    "next_created_at": "2024-05-02 17:30:00",
    "next_doc_id": "abc123"
  },
  "data": [
    {
      "id": "user_123",
      "userName": "John Doe",
      "email": "john@example.com",
      "phone": "+919876543210",
      "active": true
    }
  ]
}
```

### Orders
```json
{
  "status": true,
  "meta": {
    "total": 1523,
    "count": 10,
    "has_more": true
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
      "restaurant": {"name": "SS Foods", "id": "..."},
      "client": {"name": "Banu Maradana", "phone": "+918374059477"},
      "driver": {"id": null, "name": null},
      "date": "2025-10-13 16:45:09",
      "amount": "58.00",
      "status": "Driver Pending",
      "order_type": "Delivery"
    }
  ]
}
```

## 🎯 Role Types

- `customer` - Regular users/clients
- `vendor` - Restaurant/store owners
- `driver` - Delivery drivers

## 🔍 Order Statuses

- `Driver Pending`
- `Order Placed`
- `Order Accepted`
- `Order Completed`
- `Order Cancelled`
- `Order Rejected`

## ⚡ Quick Widget Examples

### Simple List
```dart
FutureBuilder(
  future: API.getUsers('customer'),
  builder: (context, snapshot) {
    if (!snapshot.hasData) return CircularProgressIndicator();
    
    final users = snapshot.data!['data'] as List;
    return ListView.builder(
      itemCount: users.length,
      itemBuilder: (context, i) => ListTile(
        title: Text(users[i]['userName']),
        subtitle: Text(users[i]['email']),
      ),
    );
  },
)
```

### With Pagination
```dart
class OrdersList extends StatefulWidget {
  @override
  _OrdersListState createState() => _OrdersListState();
}

class _OrdersListState extends State<OrdersList> {
  List orders = [];
  String? nextCreatedAt, nextDocId;
  bool hasMore = true, loading = false;

  @override
  void initState() {
    super.initState();
    load();
  }

  Future<void> load() async {
    setState(() => loading = true);
    final data = await API.getOrders();
    setState(() {
      orders = data['data'];
      nextCreatedAt = data['meta']['next_created_at'];
      nextDocId = data['meta']['next_doc_id'];
      hasMore = data['meta']['has_more'];
      loading = false;
    });
  }

  Future<void> loadMore() async {
    if (!hasMore || loading) return;
    
    setState(() => loading = true);
    final uri = Uri.parse(
      'https://web.jippymart.in/api/firebase/orders?limit=10&last_created_at=$nextCreatedAt&last_doc_id=$nextDocId'
    );
    final response = await http.get(uri);
    final data = json.decode(response.body);
    
    setState(() {
      orders.addAll(data['data']);
      nextCreatedAt = data['meta']['next_created_at'];
      nextDocId = data['meta']['next_doc_id'];
      hasMore = data['meta']['has_more'];
      loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      itemCount: orders.length + (hasMore ? 1 : 0),
      itemBuilder: (context, i) {
        if (i == orders.length) {
          loadMore();
          return Center(child: CircularProgressIndicator());
        }
        final order = orders[i];
        return ListTile(
          title: Text(order['order_id']),
          subtitle: Text('${order['restaurant']['name']} - ₹${order['amount']}'),
          trailing: Chip(label: Text(order['status'])),
        );
      },
    );
  }
}
```

## 🎨 UI Helpers

### Status Color
```dart
Color getStatusColor(String status) {
  switch (status.toLowerCase()) {
    case 'order completed': return Colors.green;
    case 'driver pending':
    case 'order placed': return Colors.orange;
    case 'order cancelled': return Colors.red;
    default: return Colors.blue;
  }
}
```

### Format Amount
```dart
String formatAmount(String amount) => '₹$amount';
```

### Active Badge
```dart
Widget activeBadge(bool active) => Chip(
  label: Text(active ? 'Active' : 'Inactive'),
  backgroundColor: active ? Colors.green : Colors.grey,
);
```

## 📱 Complete Mini App (Copy & Paste)

```dart
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';

void main() => runApp(MaterialApp(home: OrdersPage()));

class OrdersPage extends StatefulWidget {
  @override
  _OrdersPageState createState() => _OrdersPageState();
}

class _OrdersPageState extends State<OrdersPage> {
  List orders = [];
  bool loading = true;

  @override
  void initState() {
    super.initState();
    loadOrders();
  }

  Future<void> loadOrders() async {
    final uri = Uri.parse('https://web.jippymart.in/api/firebase/orders?limit=20&with_total=1');
    final response = await http.get(uri);
    final data = json.decode(response.body);
    setState(() {
      orders = data['data'];
      loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('Orders')),
      body: loading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: orders.length,
              itemBuilder: (context, i) {
                final o = orders[i];
                return Card(
                  child: ListTile(
                    title: Text(o['order_id']),
                    subtitle: Text('${o['restaurant']['name']}\n${o['client']['name']}'),
                    trailing: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text('₹${o['amount']}', style: TextStyle(fontWeight: FontWeight.bold)),
                        Text(o['status'], style: TextStyle(fontSize: 10)),
                      ],
                    ),
                    isThreeLine: true,
                  ),
                );
              },
            ),
    );
  }
}
```

---

**That's it!** See `FLUTTER_API_GUIDE.md` for detailed examples with models and advanced features.

