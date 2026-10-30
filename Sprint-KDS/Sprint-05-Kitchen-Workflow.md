# Web Kitchen Display System Sprint 05

# Kitchen Workflow

## Status

Completed

---

# Tujuan Sprint

Menghubungkan tampilan Kitchen Display System dengan workflow operasional dapur yang sudah tersedia di Laravel backend.

Pada akhir sprint ini, staff kitchen dapat melakukan:

- Menerima order dari queue.
- Mengubah status batch menjadi diterima dapur.
- Mengubah item menjadi sedang dimasak.
- Mengubah item menjadi siap disajikan.
- Mengubah item menjadi selesai disajikan.
- Melihat perubahan status secara langsung pada KDS.

Sprint ini merupakan sprint pertama yang membuat **aksi perubahan data** dari frontend ke backend.

---

# 1. Business Context

Workflow dapur yang sudah kita desain:

```text
Kasir

↓

Submit Order Batch

↓

Kitchen Queue

↓

Accept

↓

Preparing

↓

Ready

↓

Served
```

KDS bertugas sebagai alat kontrol operasional dapur.

---

# 2. Existing Backend Support

Backend Laravel sudah memiliki endpoint:

```http
PATCH /api/v1/kitchen/batches/{batch}/accept

PATCH /api/v1/kitchen/items/{item}/prepare

PATCH /api/v1/kitchen/items/{item}/ready

PATCH /api/v1/kitchen/items/{item}/serve
```

Frontend hanya mengonsumsi API tersebut.

Tidak ada business logic di Vue.

---

# 3. Workflow Detail

## Step 1 — Accept Batch

Ketika order masuk:

```text
Waiting

↓

Kitchen Accept

↓

Preparing Queue
```

Digunakan oleh kepala dapur untuk mengambil pekerjaan.

---

## Step 2 — Preparing

Ketika koki mulai memasak:

```text
Waiting

↓

Preparing
```

Contoh:

```text
Ikan Etong Bakar

Status:

Preparing
```

---

## Step 3 — Ready

Ketika masakan selesai:

```text
Preparing

↓

Ready
```

Artinya:

- Makanan selesai.
- Siap diambil waiter/kasir.

---

## Step 4 — Served

Ketika makanan sudah diberikan:

```text
Ready

↓

Served
```

Status akhir item.

---

# 4. Frontend Structure

Tambah struktur:

```text
resources/js/kitchen/

├── pages/

│   ├── Dashboard.vue
│   └── OrderDetail.vue


├── components/

│   └── workflow/

│       ├── StatusButton.vue
│       ├── WorkflowAction.vue
│       └── StatusBadge.vue


├── services/

│   └── kitchen-action.service.js
```

---

# 5. Kitchen Action Service

File:

```text
kitchen/services/kitchen-action.service.js
```

Tanggung jawab:

- Memanggil endpoint workflow.
- Mengirim ID item/batch.
- Menangani response.

---

Method:

```text
acceptBatch()

prepareItem()

readyItem()

serveItem()
```

---

# 6. Kitchen Store Update

Update:

```text
stores/kitchen.store.js
```

Tambahkan:

```javascript
actions: acceptBatch();

prepareItem();

readyItem();

serveItem();

updateOrderStatus();
```

---

Flow:

```text
Button Click

↓

Store Action

↓

Service

↓

Laravel API

↓

Update State

↓

Refresh UI
```

---

# 7. Accept Batch UI

Pada Order Card:

Sebelum:

```text
ORD-001

Waiting

[Detail]
```

Sesudah:

```text
ORD-001

Waiting


[Accept Order]
```

---

Ketika klik:

```text
Accept Order

↓

API Request

↓

Success

↓

Status berubah
```

---

# 8. Preparing UI

Pada item:

```text
Ikan Etong Bakar

2.5 kg

Waiting


[Mulai Masak]
```

Setelah klik:

```text
Ikan Etong Bakar

2.5 kg

Preparing
```

---

# 9. Ready UI

Saat memasak selesai:

```text
Ikan Etong Bakar

Preparing


[Selesai Masak]
```

Menjadi:

```text
Ikan Etong Bakar

Ready
```

---

# 10. Served UI

Untuk konfirmasi makanan sudah keluar:

```text
Ikan Etong Bakar

Ready


[Serve]
```

Menjadi:

```text
Ikan Etong Bakar

Served
```

---

# 11. Status Badge Component

Buat komponen:

```text
StatusBadge.vue
```

Mapping:

| Backend   | Tampilan   |
| --------- | ---------- |
| waiting   | Menunggu   |
| preparing | Diproses   |
| ready     | Siap       |
| served    | Selesai    |
| cancelled | Dibatalkan |

---

# 12. Confirmation Dialog

Untuk aksi penting:

- Accept
- Ready
- Serve

Gunakan konfirmasi.

Contoh:

```text
Apakah makanan sudah selesai dimasak?

[Cancel]

[Confirm]
```

---

# 13. Error Handling

Kasus:

## Item sudah berubah status

Response:

```text
Item sudah diproses.
```

UI:

```text
Status terbaru sudah berubah.
```

---

## Unauthorized

```text
Session habis.

Silakan login kembali.
```

---

## Validation Error

```text
Data tidak valid.
```

---

# 14. Loading State

Ketika action berjalan:

Contoh:

```text
[ Processing... ]
```

Button harus disabled.

Mencegah:

- Double click.
- Duplicate request.

---

# 15. Optimistic Update

Tidak menggunakan optimistic update dulu.

Alasan:

Workflow dapur berhubungan dengan:

- Stock deduction.
- Order status.
- Audit history.

Lebih aman:

```text
Click

↓

API Success

↓

Update UI
```

---

# 16. Order Status Synchronization

Setelah item berubah:

Frontend melakukan:

```text
Action Success

↓

Refresh Order Detail

↓

Refresh Queue
```

Agar status tetap sesuai backend.

---

# 17. Permission Handling

Frontend menyembunyikan tombol berdasarkan role.

Contoh:

Kitchen:

```text
Accept

Prepare

Ready
```

Waiter:

```text
Serve
```

Tetapi validasi tetap dilakukan Laravel.

---

# 18. Testing Scenario

## Scenario 1

Order masuk:

```text
Waiting
```

Action:

Accept

Expected:

```text
Preparing Queue
```

---

## Scenario 2

Item dimasak:

Action:

Prepare

Expected:

```text
Preparing
```

---

## Scenario 3

Masakan selesai:

Action:

Ready

Expected:

```text
Ready
```

---

## Scenario 4

Makanan diberikan:

Action:

Serve

Expected:

```text
Served
```

---

# 19. Task Checklist

## Service

- [x] Create kitchen action service
- [x] Connect accept endpoint
- [x] Connect prepare endpoint
- [x] Connect ready endpoint
- [x] Connect serve endpoint

## Store

- [x] Add workflow actions
- [x] Refresh state after mutation

## UI

- [x] Accept Button
- [x] Prepare Button
- [x] Ready Button
- [x] Serve Button
- [x] Status Badge
- [x] Confirmation Dialog

## UX

- [x] Loading state
- [x] Error handling
- [x] Disable duplicate action

## Testing

- [x] Test batch accept
- [x] Test item prepare
- [x] Test item ready
- [x] Test item serve

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Kitchen dapat menerima order

✅ Kitchen dapat memulai memasak

✅ Kitchen dapat menandai selesai

✅ Kitchen dapat menandai served

✅ Status frontend selalu sesuai backend

✅ Error handling tersedia

✅ Tidak ada perubahan status ilegal

---

# Output Sprint

Hasil akhir Sprint 05:

```text
Kitchen Display System


Dashboard

↓

Queue

↓

Order Detail


Workflow:


Waiting

↓

Accepted

↓

Preparing

↓

Ready

↓

Served
```

Pada tahap ini KDS sudah dapat digunakan untuk operasional dasar dapur.

---

# Sprint Berikutnya

## Web Kitchen Display System Sprint 06 — Kitchen Realtime Notification

Fitur yang akan dibuat:

- Integrasi Pusher
- Order masuk realtime
- Status update realtime
- Kitchen notification
- Menghilangkan kebutuhan refresh manual

Sprint ini akan membuat KDS berubah dari aplikasi monitoring menjadi sistem operasional realtime.
