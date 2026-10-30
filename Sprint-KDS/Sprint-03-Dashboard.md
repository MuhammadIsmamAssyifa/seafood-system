# Web Kitchen Display System Sprint 03

# Kitchen Dashboard & Queue

## Status

Completed

---

# Tujuan Sprint

Membangun halaman utama Kitchen Display System yang digunakan oleh staf dapur untuk melihat kondisi operasional dapur secara real-time.

Pada akhir sprint ini, staff kitchen dapat:

- Melihat jumlah order berdasarkan status.
- Melihat daftar order yang masuk ke dapur.
- Melihat queue dapur berdasarkan prioritas.
- Membuka detail singkat order.
- Melakukan monitoring pekerjaan dapur.

Sprint ini belum membuat aksi perubahan status.

Belum termasuk:

- Accept Order
- Preparing
- Ready
- Served

Fitur tersebut dibuat pada sprint berikutnya.

---

# 1. Business Context

Pada sistem restoran seafood:

Alur order:

```text
Kasir

↓

Submit Order Batch

↓

Kitchen Queue

↓

Kitchen Process
```

KDS berfungsi sebagai layar operasional dapur.

Tujuan utama:

> Menghilangkan penggunaan kertas order dan memberikan informasi pekerjaan dapur secara jelas.

---

# 2. Dashboard Flow

Flow pengguna:

```text
Login Kitchen

↓

Dashboard

↓

Melihat Summary

↓

Melihat Queue

↓

Pilih Order

↓

Lihat Detail
```

---

# 3. Dashboard Layout

Tampilan utama:

```text
+------------------------------------------------+

 Header

--------------------------------------------------

 Summary Card


 Waiting     Preparing     Ready     Completed

--------------------------------------------------

 Kitchen Queue


 Order #001

 Meja 5

 10 menit


 Order #002

 Meja 3

 5 menit


--------------------------------------------------

+------------------------------------------------+
```

---

# 4. Dashboard Components

Struktur komponen:

```text
resources/js/kitchen/

├── components/

│
├── dashboard/

│   ├── StatusCard.vue
│   ├── QueueCard.vue
│   ├── QueueList.vue
│   └── OrderBadge.vue

```

---

# 5. Backend Integration

Menggunakan endpoint backend yang sudah tersedia:

```http
GET /api/v1/kitchen/orders
```

Endpoint ini berasal dari:

```text
KitchenController@index
```

---

# 6. API Response Expected

Contoh response:

```json
{
    "data": [
        {
            "id": 1,
            "order_number": "ORD-001",
            "table": {
                "number": "5"
            },
            "customer_name": "Budi",
            "status": "submitted",
            "items": [
                {
                    "name": "Ikan Etong Bakar",
                    "quantity": 2,
                    "unit": "kg"
                }
            ]
        }
    ]
}
```

---

# 7. Kitchen Store

Membuat state management menggunakan Pinia.

File:

```text
stores/kitchen.store.js
```

---

## State

```javascript
{
    orders: [],

    loading:false,

    error:null,

    lastUpdated:null
}
```

---

## Actions

```text
fetchOrders()

refreshOrders()

clearOrders()
```

---

# 8. Kitchen Service

Buat service khusus API.

Lokasi:

```text
kitchen/services/kitchen.service.js
```

---

Contoh:

```javascript
getOrders();
```

Tanggung jawab:

- Request API.
- Return data.
- Handle error.

---

# 9. Queue Logic

Urutan queue mengikuti kebutuhan dapur.

Prioritas:

1. Order paling lama masuk.
2. Batch yang belum diproses.
3. Status waiting/submitted.

Sorting:

```text
created_at ASC
```

Contoh:

```text
Order 001
10:01

Order 002
10:05

Order 003
10:10
```

Maka tampil:

```text
001

002

003
```

---

# 10. Status Summary

Dashboard menampilkan:

```text
Waiting

Preparing

Ready

Completed
```

Contoh:

```text
Waiting

12 Orders


Preparing

5 Orders


Ready

3 Orders


Completed

20 Orders
```

---

# 11. Status Mapping

Backend:

```text
Order Status

draft
submitted
preparing
ready
completed
cancelled
```

Frontend hanya menampilkan status operasional:

```text
Waiting

Preparing

Ready

Completed
```

---

# 12. Queue Card

Setiap order card menampilkan:

```text
Order Number

Table Number

Customer Name

Guest Count

Order Time

Items Count

Current Status
```

---

Contoh:

```text
--------------------

ORD-001

Table 5

Budi

4 Guest


3 Items


Waiting

--------------------
```

---

# 13. Auto Refresh

Sebelum realtime dibuat, gunakan mekanisme refresh sederhana.

Interval:

```text
30 detik
```

Flow:

```text
Dashboard Open

↓

Timer Start

↓

Fetch Orders

↓

Update UI
```

---

Catatan:

Ini hanya sementara.

Sprint Notification akan mengganti dengan Pusher.

---

# 14. Loading State

Saat pertama membuka dashboard:

```text
Loading Kitchen Queue...

██████████
```

---

# 15. Empty State

Jika tidak ada order:

```text
Tidak ada pesanan masuk.

Dapur siap menerima order.
```

---

# 16. Error State

Jika API gagal:

```text
Gagal mengambil data kitchen.

[Coba Lagi]
```

---

# 17. Responsive Design

Target utama:

## Landscape Monitor

```text
1366x768
```

## Tablet Kitchen

```text
1024x768
```

---

Tidak fokus mobile karena KDS digunakan di area dapur.

---

# 18. Navigation Update

Tambah route:

```text
/kitchen/dashboard
```

Flow:

```text
Login

↓

Dashboard
```

---

# 19. Task Checklist

## Backend Integration

- [x] Test endpoint kitchen orders
- [x] Setup kitchen service
- [x] Setup Axios request

---

## State Management

- [x] Create Kitchen Store
- [x] Fetch orders
- [x] Handle loading
- [x] Handle error

---

## Dashboard UI

- [x] Create Dashboard Page
- [x] Create Status Card
- [x] Create Queue List
- [x] Create Queue Card
- [x] Create Order Badge

---

## Queue

- [x] Display order list
- [x] Sorting FIFO
- [x] Display order information

---

## UX

- [x] Loading State
- [x] Empty State
- [x] Error State

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Staff kitchen setelah login masuk dashboard

✅ Dashboard menampilkan jumlah order

✅ Kitchen queue tampil

✅ Order card menampilkan informasi penting

✅ Data berasal dari Laravel API

✅ Loading state tersedia

✅ Error handling tersedia

✅ Empty state tersedia

---

# Output Sprint

Hasil akhir Sprint 03:

```text
Kitchen Display System

Login

↓

Dashboard

↓

Kitchen Queue


Menampilkan:

- Waiting Orders
- Preparing Orders
- Ready Orders
- Completed Orders
```

Staff kitchen sudah dapat melihat pekerjaan dapur, tetapi belum dapat mengubah status order.

---

# Sprint Berikutnya

## Web Kitchen Display System Sprint 04 — Order Detail

Fitur yang akan dibuat:

- Halaman detail order
- Detail item
- Quantity seafood berdasarkan berat
- Cooking method
- Kitchen station
- Catatan order
- Informasi customer dan meja

Sprint ini akan membuat koki mendapatkan seluruh informasi yang dibutuhkan sebelum mulai memasak.
