# Web Admin / Owner Dashboard Sprint 05

# Order Management

## Status

Completed

---

# Tujuan Sprint

Membangun modul pengelolaan order pada Web Admin / Owner Dashboard agar Owner dapat melihat, memonitor, dan melakukan pengecekan seluruh transaksi restoran.

Sprint ini berfokus pada **visibility dan monitoring**, bukan menggantikan fungsi POS.

POS tetap menjadi tempat utama membuat order.

Web Admin berfungsi sebagai:

- Monitoring transaksi
- Melihat histori order
- Mengecek pembayaran
- Melakukan investigasi jika terjadi masalah

---

# 1. Business Context

Dalam operasional restoran, Owner membutuhkan kemampuan untuk menjawab:

```text
Order mana yang sedang berjalan?

Order mana yang sudah selesai?

Berapa nilai transaksi tertentu?

Apakah pembayaran sudah masuk?

Jika ada komplain customer, bagaimana histori order tersebut?
```

Karena itu Web Admin membutuhkan akses penuh terhadap histori transaksi.

---

# 2. Scope Sprint

## Yang dibuat:

- Order List
- Order Detail
- Order Status Monitoring
- Payment Detail
- Search Order
- Filter Order
- Order History

---

## Tidak termasuk:

- Membuat order baru
- Mengubah order secara langsung
- Pembayaran manual
- Kitchen Workflow

Semua proses tersebut tetap dilakukan melalui:

- Flutter POS
- Kitchen Display System

---

# 3. Order Management Architecture

Flow:

```text
Owner

↓

Order Management

↓

Request Order API

↓

Laravel Backend

↓

Order Data

↓

Display Table / Detail
```

---

# 4. Page Structure

Buat:

```text
resources/js/admin/pages/orders/OrderPage.vue
```

---

Detail:

```text
resources/js/admin/pages/orders/

├── OrderPage.vue

├── OrderDetailPage.vue

└── components/

    ├── OrderTable.vue
    ├── OrderFilter.vue
    ├── OrderStatusBadge.vue
    ├── PaymentInfo.vue
    └── OrderItems.vue
```

---

# 5. Order Service

Buat:

```text
services/order.service.js
```

Berisi:

```javascript
getOrders();

getOrderDetail();

getOrderSummary();
```

---

# 6. Order List API

Endpoint:

```http
GET /orders
```

---

Query:

```http
/orders?

status=completed

&date=2026-10-31

&search=Budi
```

---

Response:

```json
{
    "data": [
        {
            "id": 1,
            "order_number": "ORD-0001",
            "customer_name": "Budi",
            "status": "completed",
            "grand_total": 250000,
            "payment_status": "paid"
        }
    ]
}
```

---

# 7. Order Table

Component:

```text
OrderTable.vue
```

Menampilkan:

| Field        | Description       |
| ------------ | ----------------- |
| Order Number | Nomor transaksi   |
| Customer     | Nama customer     |
| Table        | Nomor meja        |
| Total        | Total transaksi   |
| Status       | Status order      |
| Payment      | Status pembayaran |
| Time         | Waktu transaksi   |

---

# 8. Order Status

Gunakan status dari backend.

Contoh:

```text
draft

submitted

preparing

ready

completed

cancelled
```

---

Komponen:

```text
OrderStatusBadge.vue
```

---

Contoh:

```text
Completed

Preparing

Cancelled
```

---

# 9. Order Filter

Component:

```text
OrderFilter.vue
```

Filter:

## Status

```text
All

Draft

Preparing

Completed

Cancelled
```

---

## Date

```text
Today

Yesterday

Custom Range
```

---

## Search

Berdasarkan:

- Order Number
- Customer Name

---

# 10. Pagination

Order dapat bertambah ribuan.

Frontend tidak mengambil semua data.

Gunakan Laravel pagination.

Contoh:

```json
{
    "current_page": 1,
    "last_page": 10,
    "data": []
}
```

---

UI:

```text
Previous

1 2 3

Next
```

---

# 11. Order Detail Page

Route:

```text
/orders/:id
```

---

Component:

```text
OrderDetailPage.vue
```

---

Menampilkan:

## Customer Information

```text
Customer

Guest Count

Table Number
```

---

## Order Items

Contoh:

```text
Ikan Bakar

2 kg

Rp 160.000
```

---

## Batch Information

Menampilkan:

- Batch order
- Status kitchen
- Waktu dikirim ke dapur

---

## Payment Information

Menampilkan:

- Payment method
- Amount
- Payment status
- Paid time

---

# 12. Order Items Detail

Component:

```text
OrderItems.vue
```

Menampilkan:

```text
Menu

Variant

Quantity

Price

Subtotal

Status
```

---

Contoh:

```text
Cumi Asam Manis

2 porsi

Rp 140.000

Completed
```

---

# 13. Payment Detail

Component:

```text
PaymentInfo.vue
```

Menampilkan:

```text
Payment Method

Cash

Amount

Rp 250.000

Status

Paid
```

---

# 14. Order History

Owner dapat melihat:

- Kapan order dibuat
- Perubahan status
- Pembayaran

Menggunakan:

```text
order_status_histories
```

---

Timeline:

```text
10:00

Order Created


10:05

Sent To Kitchen


10:20

Completed


10:30

Paid
```

---

# 15. Real-time Preparation

Pada sprint ini belum menggunakan realtime.

Data menggunakan:

```text
HTTP Request
```

---

Realtime order update dapat ditambahkan menggunakan:

- Pusher

pada improvement sprint.

---

# 16. Loading State

Saat mengambil order:

```text
Open Page

↓

Loading Table

↓

Show Data
```

---

# 17. Empty State

Jika tidak ada order:

```text
Belum ada transaksi.
```

---

# 18. Error Handling

## API Error

```text
Gagal mengambil data order.
```

---

## Order Tidak Ditemukan

```text
Order tidak tersedia.
```

---

# 19. Performance Consideration

Aturan:

Jangan:

```text
Ambil semua order

↓

Filter di Vue
```

---

Gunakan:

```text
Laravel Query Filter

↓

Pagination

↓

Vue Render
```

---

# 20. Security

Web Admin hanya boleh:

Melihat data.

Tidak boleh:

- Mengubah status order
- Menghapus order
- Mengubah pembayaran

Karena transaksi harus memiliki audit trail.

---

# 21. Testing Scenario

## Scenario 1

Open order page.

Expected:

```text
List order tampil.
```

---

## Scenario 2

Search customer.

Expected:

```text
Order customer ditemukan.
```

---

## Scenario 3

Filter completed order.

Expected:

```text
Hanya order selesai tampil.
```

---

## Scenario 4

Open detail order.

Expected:

```text
Informasi lengkap tampil.
```

---

## Scenario 5

Order tidak ditemukan.

Expected:

```text
Empty state tampil.
```

---

# 22. Task Checklist

## Order List

- [x] Order Page
- [x] Order Table
- [x] Pagination

---

## Filter

- [x] Status Filter
- [x] Date Filter
- [x] Search

---

## Detail

- [x] Order Detail Page
- [x] Order Items
- [x] Payment Detail
- [x] Status History

---

## API

- [x] Order Service
- [x] Order Endpoint Integration

---

## UX

- [x] Loading
- [x] Empty State
- [x] Error State

---

## Testing

- [x] List Order
- [x] Filter
- [x] Search
- [x] Detail

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat melihat seluruh transaksi

✅ Order dapat difilter

✅ Order dapat dicari

✅ Detail order tampil lengkap

✅ Payment information tampil

✅ Status history dapat dilihat

✅ Pagination berjalan

---

# Output Sprint

Hasil akhir Sprint 05:

```text
Owner

↓

Order Management

↓

Transaction Monitoring

↓

Payment Verification

↓

Customer History
```

Setelah sprint ini selesai, Owner sudah memiliki kontrol penuh untuk melakukan monitoring transaksi restoran.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 06 — Inventory Management

Fitur yang akan dibuat:

- Ingredient List
- Current Stock
- Stock Movement
- Add Stock Adjustment
- Inventory History
- Low Stock Monitoring

Sprint berikutnya masuk ke pengelolaan bahan baku dan stok restoran.
