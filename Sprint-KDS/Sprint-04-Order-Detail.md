# Web Kitchen Display System Sprint 04

# Order Detail

## Status

Completed

---

# Tujuan Sprint

Membangun halaman detail order pada Kitchen Display System agar staff dapur dapat melihat seluruh informasi pesanan sebelum melakukan proses memasak.

Pada akhir sprint ini, kitchen staff dapat:

- Membuka detail order dari kitchen queue.
- Melihat daftar item yang harus dibuat.
- Melihat jumlah item berdasarkan satuan penjualan.
- Melihat metode memasak.
- Melihat station dapur.
- Melihat catatan tambahan.
- Memahami prioritas pekerjaan sebelum menerima order.

Sprint ini belum membuat perubahan status order.

Belum termasuk:

- Accept Order
- Preparing
- Ready
- Served

---

# 1. Business Context

Dalam restoran seafood, satu order dapat memiliki banyak item dengan karakteristik berbeda.

Contoh:

```text
Order #ORD-001

Meja 5

Customer: Budi


Item:

1. Ikan Etong Bakar
   2.5 kg

2. Cumi Asam Manis
   1 porsi

3. Es Teh
   4 pcs
```

Kitchen membutuhkan informasi lengkap sebelum mulai memasak.

---

# 2. Order Detail Flow

Flow:

```text
Kitchen Dashboard

↓

Kitchen Queue

↓

Klik Order Card

↓

Order Detail

↓

Melihat Item

↓

Kembali ke Queue
```

---

# 3. Navigation

Tambah route:

```text
/kitchen/orders/:id
```

Contoh:

```text
/kitchen/orders/15
```

---

# 4. Frontend Structure

Tambah struktur:

```text
resources/js/kitchen/

├── pages/

│   ├── Dashboard.vue
│   └── OrderDetail.vue


├── components/

│   └── order/

│       ├── OrderHeader.vue
│       ├── OrderItemCard.vue
│       ├── OrderItemList.vue
│       └── OrderNote.vue
```

---

# 5. Order Detail Layout

Design:

```text
+------------------------------------------------+

 Order #ORD-001

 Table 5

 Customer: Budi

 Guest: 4


--------------------------------------------------

 Items


 [ Ikan Etong Bakar ]

 Quantity:

 2.5 kg


 Method:

 Bakar


 Station:

 Grill


--------------------------------------------------

 Notes:

 Pedas sedang


--------------------------------------------------


        Back


+------------------------------------------------+
```

---

# 6. Backend Integration

Menggunakan endpoint order kitchen.

Jika endpoint detail belum tersedia, tambahkan:

```http
GET /api/v1/kitchen/orders/{order}
```

---

# 7. API Response

Response yang dibutuhkan frontend:

```json
{
    "id": 1,
    "order_number": "ORD-001",

    "table": {
        "number": "5"
    },

    "customer_name": "Budi",

    "guest_count": 4,

    "status": "submitted",

    "items": [
        {
            "id": 10,

            "name": "Ikan Etong Bakar",

            "quantity": 2.5,

            "sales_unit": "kg",

            "cooking_method": "Bakar",

            "station": "Grill",

            "status": "waiting"
        }
    ],

    "note": "Pedas sedang"
}
```

---

# 8. Kitchen Store Update

Tambahkan state:

```javascript
{
    selectedOrder:null,

    detailLoading:false
}
```

---

## Actions

Tambah:

```text
fetchOrderDetail()

clearSelectedOrder()
```

---

# 9. Order Service

File:

```text
kitchen/services/order.service.js
```

Method:

```javascript
getOrderDetail(id);
```

Tanggung jawab:

- Request API.
- Mapping response.
- Handle error.

---

# 10. Order Header Component

Menampilkan:

- Nomor order.
- Nomor meja.
- Nama customer.
- Jumlah tamu.
- Waktu order.
- Status.

Contoh:

```text
ORD-001

Meja 5

Budi

4 Orang

10:15
```

---

# 11. Order Item Component

Setiap item menampilkan:

```text
Nama Menu

Quantity

Unit

Cooking Method

Kitchen Station

Status
```

---

Contoh:

```text
------------------

Ikan Etong Bakar


2.5 kg


Bakar


Station:
Grill


Waiting

------------------
```

---

# 12. Quantity Display Rule

Mengikuti aturan backend.

## Seafood

Menggunakan berat:

```text
kg

gram
```

Contoh:

```text
1.250 kg
```

---

## Drink / Snack

Menggunakan jumlah:

```text
pcs
```

Contoh:

```text
3 pcs
```

---

# 13. Cooking Method Display

Data berasal dari:

```text
menu_variants

↓

cooking_method
```

Contoh:

```text
Bakar

Goreng

Rebus

Steam
```

---

# 14. Kitchen Station Display

Data berasal dari:

```text
menu_variants

↓

kitchen_station
```

Contoh:

```text
Grill

Masak

Minuman
```

---

# 15. Order Notes

Menampilkan catatan khusus.

Contoh:

```text
Catatan:

- Tidak terlalu pedas
- Pisahkan saus
```

---

# 16. Loading State

Saat membuka detail:

```text
Loading order detail...
```

---

# 17. Error State

Jika order tidak ditemukan:

```text
Order tidak ditemukan.
```

---

Jika API gagal:

```text
Gagal mengambil detail order.

[Coba Lagi]
```

---

# 18. Back Navigation

Setelah selesai melihat detail:

```text
Order Detail

↓

Back

↓

Kitchen Queue
```

---

# 19. Performance Consideration

Jangan mengambil semua data order.

Endpoint detail hanya mengirim:

- Informasi order.
- Item kitchen.
- Data yang dibutuhkan dapur.

Hindari:

- Payment.
- User detail.
- Inventory detail.

---

# 20. Task Checklist

## Backend

- [x] Review endpoint kitchen detail
- [x] Tambahkan endpoint jika diperlukan
- [x] Pastikan response sesuai kebutuhan KDS

## Vue

### Routing

- [x] Tambahkan route detail
- [x] Navigation dari queue

### State

- [x] Selected Order State
- [x] Loading State
- [x] Error State

### Components

- [x] Order Header
- [x] Order Item Card
- [x] Item List
- [x] Notes Component

### UX

- [x] Loading
- [x] Empty
- [x] Error
- [x] Back Navigation

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Staff kitchen dapat membuka detail order

✅ Semua item tampil

✅ Quantity seafood tampil dalam berat

✅ Unit tampil benar

✅ Cooking method tampil

✅ Kitchen station tampil

✅ Catatan order tampil

✅ Navigasi kembali ke queue berjalan

---

# Output Sprint

Hasil akhir Sprint 04:

```text
Kitchen Display System

Login

↓

Dashboard

↓

Kitchen Queue

↓

Order Detail


Staff kitchen sudah memahami:

- Apa yang harus dibuat
- Berapa jumlahnya
- Bagaimana cara memasaknya
- Siapa station yang bertanggung jawab
```

---

# Sprint Berikutnya

## Web Kitchen Display System Sprint 05 — Kitchen Workflow

Fitur yang akan dibuat:

- Accept Order
- Preparing
- Ready
- Served
- Integrasi endpoint:

```
PATCH /kitchen/batches/{batch}/accept

PATCH /kitchen/items/{item}/prepare

PATCH /kitchen/items/{item}/ready

PATCH /kitchen/items/{item}/serve
```

Sprint ini akan mulai menghubungkan UI KDS dengan workflow dapur yang sudah selesai dibuat di Laravel backend.
