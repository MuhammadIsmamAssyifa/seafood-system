# Web Kitchen Display System Sprint 06

# Kitchen Realtime Notification

## Status

Completed

---

# Tujuan Sprint

Mengubah Kitchen Display System dari aplikasi yang harus melakukan refresh manual menjadi aplikasi **realtime kitchen operation system** menggunakan Pusher.

Pada akhir sprint ini:

- Order baru dari kasir muncul otomatis di KDS.
- Perubahan status order/item muncul realtime.
- Kitchen staff mendapatkan notifikasi ketika ada pekerjaan baru.
- Queue dapur selalu sinkron dengan kondisi backend.
- Mengurangi kebutuhan refresh halaman.

Sprint ini fokus pada komunikasi realtime.

Belum termasuk:

- Kitchen Performance
- Analytics
- Reporting
- Advanced Notification Sound

---

# 1. Business Context

Sebelum realtime:

```text
Kasir submit order

↓

Kitchen harus refresh halaman

↓

Order baru terlihat
```

Masalah:

- Order dapat terlambat diproses.
- Kitchen harus sering melihat layar.
- Risiko order terlewat.

---

Setelah realtime:

```text
Kasir submit order

↓

Laravel Event

↓

Pusher

↓

Vue KDS menerima event

↓

Queue update otomatis
```

---

# 2. Realtime Architecture

Arsitektur:

```text
Flutter POS

      |
      |
      v

Laravel API

      |
      |
      v

Laravel Event

      |
      |
      v

Pusher Channel

      |
      |
      v

Vue KDS

      |
      |
      v

Update UI
```

---

# 3. Technology Stack

## Backend

Menggunakan:

```text
Laravel Broadcasting

Pusher

Laravel Events
```

---

## Frontend

Menggunakan:

```text
Laravel Echo

Pusher JS

Pinia Store
```

---

# 4. Event yang Dibutuhkan

Event realtime pertama:

## Order Created

Ketika kasir submit order.

Flow:

```text
Order Submitted

↓

Broadcast OrderCreated

↓

Kitchen Queue Update
```

---

## Order Updated

Ketika status berubah.

Contoh:

```text
Preparing

↓

Ready
```

---

## Item Updated

Ketika item berubah.

Contoh:

```text
Ikan Etong

Waiting

↓

Preparing
```

---

# 5. Backend Preparation

Pastikan Laravel memiliki:

```text
app/Events/
```

Tambah:

```text
OrderCreated.php

OrderStatusUpdated.php

OrderItemStatusUpdated.php
```

---

# 6. Broadcasting Channel

Gunakan private channel.

Contoh:

```text
private-kitchen.{outlet_id}
```

Contoh:

```text
private-kitchen.1
```

---

Kenapa menggunakan outlet?

Karena desain database sudah mendukung:

```text
restaurant

↓

outlet

↓

kitchen
```

Outlet A tidak boleh menerima order Outlet B.

---

# 7. Authorization Channel

Backend harus melakukan pengecekan:

```text
User Login

↓

Check Outlet

↓

Check Permission

↓

Allow Channel
```

---

Contoh:

Kitchen Outlet 1:

```text
private-kitchen.1
```

Bisa akses:

```text
private-kitchen.1
```

Tidak bisa:

```text
private-kitchen.2
```

---

# 8. Frontend Structure Update

Tambah:

```text
resources/js/kitchen/

├── realtime/

│   ├── echo.js
│   ├── channels.js
│   └── listeners.js


├── services/

│   └── realtime.service.js
```

---

# 9. Pusher Configuration

Environment:

```env
VITE_PUSHER_APP_KEY=

VITE_PUSHER_CLUSTER=

VITE_PUSHER_HOST=

VITE_PUSHER_PORT=
```

---

# 10. Echo Setup

Buat konfigurasi:

```javascript
echo.js
```

Fungsi:

- Connect Pusher.
- Authenticate private channel.
- Listen event.

---

Contoh:

```javascript
Echo.private(
    `kitchen.${outletId}`
)
.listen(
    'OrderCreated',
    callback
)
```

---

# 11. Kitchen Store Integration

Update:

```text
stores/kitchen.store.js
```

Tambah:

```javascript
subscribeRealtime()

unsubscribeRealtime()
```

---

Flow:

```text
Dashboard Open

↓

Subscribe Channel

↓

Listen Event

↓

Update Store

↓

Update UI
```

---

# 12. Order Created Handler

Ketika event diterima:

```text
OrderCreated

↓

Tambah order baru

↓

Queue update
```

---

Contoh:

Sebelum:

```text
Queue

ORD-001
```

Event masuk:

```text
ORD-002
```

Sesudah:

```text
Queue

ORD-001

ORD-002
```

---

# 13. Order Status Handler

Ketika status berubah:

Contoh:

```text
ORD-001

Waiting

↓

Preparing
```

Event:

```text
OrderStatusUpdated
```

Frontend:

```text
Update card status
```

---

# 14. Item Status Handler

Contoh:

```text
Ikan Etong

Preparing

↓

Ready
```

UI otomatis berubah:

```text
Ready
```

---

# 15. Notification Component

Buat:

```text
components/

└── NotificationToast.vue
```

---

Contoh:

Ketika order masuk:

```text
🔔 Order Baru

ORD-002

Meja 5
```

---

# 16. Sound Notification

Opsional.

Tambahkan:

```text
New Order Sound
```

Flow:

```text
Event masuk

↓

Play sound

↓

Show notification
```

---

Catatan:

Jangan dibuat terlalu kompleks.

KDS cukup membutuhkan notifikasi sederhana.

---

# 17. Connection Status

Tambahkan indikator koneksi.

Contoh:

```text
● Connected
```

atau

```text
● Disconnected
```

---

Komponen:

```text
ConnectionStatus.vue
```

---

# 18. Reconnection Handling

Jika koneksi putus:

```text
Pusher Disconnect

↓

Retry Connection

↓

Reconnect

↓

Sync Data
```

---

Setelah reconnect:

Lakukan:

```text
GET kitchen/orders
```

untuk memastikan data terbaru.

---

# 19. Testing Scenario

## Scenario 1 — New Order

Flow:

```text
Kasir submit order

↓

Event broadcast

↓

KDS menerima

↓

Order muncul
```

Expected:

✅ Order muncul tanpa refresh.

---

## Scenario 2 — Kitchen Update Status

Flow:

```text
Kitchen A

Prepare Item

↓

Event broadcast

↓

Kitchen B melihat perubahan
```

Expected:

✅ Semua layar sinkron.

---

## Scenario 3 — Disconnect

Flow:

```text
Internet putus

↓

Reconnect

↓

Sync data
```

Expected:

✅ Data kembali konsisten.

---

# 20. Task Checklist

## Backend

- [x] Create Broadcasting Event
- [x] Setup Pusher
- [x] Setup Private Channel
- [x] Setup Channel Authorization
- [x] Broadcast Order Created
- [x] Broadcast Status Update


## Vue

### Realtime

- [x] Install Pusher JS
- [x] Setup Laravel Echo
- [x] Create Channel Listener
- [x] Handle Event


### Store

- [x] Subscribe Channel
- [x] Update Queue Automatically
- [x] Update Item Status


### UI

- [x] Notification Toast
- [x] Connection Status
- [x] New Order Indicator


### Testing

- [x] Test new order
- [x] Test status update
- [x] Test reconnect

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Order baru muncul otomatis di KDS

✅ Status order berubah realtime

✅ Status item berubah realtime

✅ Tidak membutuhkan refresh manual

✅ Private channel berjalan

✅ Outlet isolation berjalan

✅ Reconnect dapat menangani koneksi putus

---

# Output Sprint

Hasil akhir Sprint 06:

```text
Kitchen Display System


Kasir

↓

Submit Order


↓

Laravel Event


↓

Pusher


↓

KDS


↓

Realtime Update

```

Pada tahap ini KDS sudah menjadi sistem operasional realtime.

---

# Sprint Berikutnya

## Web Kitchen Display System Sprint 07 — Kitchen Notification & UX Improvement

Fitur yang akan dibuat:

- Notification center
- Sound alert
- Visual priority indicator
- Order timer
- Improved kitchen workflow UX
- Better screen layout untuk monitor dapur

Sprint berikutnya fokus meningkatkan pengalaman penggunaan KDS di kondisi operasional nyata.