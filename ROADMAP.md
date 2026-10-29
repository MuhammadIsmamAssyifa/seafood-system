# Seafood Restaurant Management System

## Backend Development Roadmap & Progress

**Versi:** 1.0  
**Status:** ? Backend MVP Selesai (API Freeze) - Siap Pengembangan Frontend

---

# 1. Tujuan Sistem

Membangun Restaurant Management System (RMS) yang terdiri dari:

- Flutter Mobile POS
- Web Kitchen Display System (KDS)
- Web Dashboard Owner/Admin

Backend menggunakan:

- Laravel 12
- PostgreSQL
- Sanctum
- Spatie Permission

Arsitektur saat ini ditujukan untuk **1 restoran**, namun seluruh desain sudah dipersiapkan agar dapat berkembang menjadi **multi outlet** dan **SaaS** di masa depan.

---

# 2. Progress Backend Saat Ini

## ? Authentication

Selesai

- Login Owner/Admin
- Login Staff
- Sanctum Authentication

---

## ? Catalog Module

Selesai

Meliputi:

- Menu
- Menu Variant
- Ingredient
- Cooking Method
- Kitchen Station

---

## ? Order Module

Selesai

Meliputi:

- Create Order
- Table Management
- Customer Name
- Guest Count
- Add Item
- Update Item
- Delete Item
- Snapshot Menu
- Snapshot Price

---

## ? Order Batch Module

Selesai

Flow:

Order

?

Batch 1

?

Submit Kitchen

?

Customer tambah pesanan

?

Batch 2

?

Submit Kitchen

Batch memungkinkan customer menambah pesanan setelah pesanan sebelumnya sudah dikirim ke dapur.

---

## ? Kitchen Module

Selesai

Workflow:

Waiting

?

Accepted

?

Preparing

?

Ready

?

Served

Kitchen bekerja berdasarkan Batch.

---

## ? Order Status Synchronization

Selesai

Status Order akan berubah otomatis berdasarkan status Order Item.

Contoh:

Preparing

?

Order Preparing

Ready

?

Order Ready

Served

?

Order Completed

---

## ? Payment Module

Selesai

Mendukung:

- Cash
- Transfer
- Split Payment

---

## ? Inventory Module

Selesai

Meliputi:

Inventory Batch

FIFO

Stock Movement

Stock Deduction

Inventory Adjustment

---

## ? Order Item Change

Selesai

Mendukung:

- Quantity Change
- Cancel Item
- Replace Item

Dengan mekanisme approval customer.

---

## ? Dynamic Price Adjustment

Selesai

Contoh:

Customer pesan Etong 0.7kg

?

Stok habis

?

Owner membeli dari pasar

?

Harga jual berubah

?

Customer approve

?

Order diperbarui

---

## ? Recipe Module

Selesai (Opsional)

Recipe dapat diaktifkan jika restoran ingin menggunakan inventory otomatis.

Jika tidak menggunakan recipe maka inventory tetap dapat berjalan secara manual.

---

# 3. Flow Sistem Saat Ini

Customer Datang

?

Kasir Membuat Order

?

Tambah Menu

?

Submit Batch

?

Kitchen Accept

?

Kitchen Prepare

?

Inventory Deduct (FIFO)

?

Kitchen Ready

?

Kitchen Serve

?

Payment

?

Order Completed

---

# 4. Audit Backend

Sebelum frontend dimulai, backend akan distabilkan.

## Tahap Audit

### Database

Audit:

- Foreign Key
- Index
- Decimal
- Nullable
- Cascade
- Timestamp

Status:

? Selesai

---

### Enum

Audit:

- Semua enum
- Semua cast
- Semua validation

Status:

? Selesai

---

### Model

Audit:

- Fillable
- Cast
- Relationship
- Helper Method

Status:

? Selesai

---

### Relationship

Audit:

- belongsTo
- hasMany
- hasOne
- eager loading

Status:

? Selesai

---

### API Resource

Audit:

Seluruh endpoint harus menggunakan Resource yang konsisten.

Status:

? Selesai

---

### Request Validation

Audit seluruh Form Request.

Status:

? Selesai

---

### Service Layer

Audit:

- Transaction
- Exception
- Dependency
- Responsibility

Status:

? Selesai

---

### Controller

Target:

Controller hanya menerima Request dan memanggil Service.

Status:

? Selesai

---

### Route

Audit:

- RESTful
- Middleware
- Permission

Status:

? Selesai

---

### API Contract

Target:

Setelah audit selesai:

- Endpoint tidak berubah
- Nama field tidak berubah
- Response tidak berubah

Backend dianggap stabil.

---

# 5. Roadmap Setelah Audit

## Phase 1

### Reporting & Dashboard

Status: ? Selesai

Suite analitik dan laporan lengkap untuk owner dan manajemen restoran:

1. **Dashboard Summary (GET /api/v1/dashboard/summary)**:
    - Omzet penjualan hari ini, HPP, laba kotor, dan margin keuntungan (%)
    - Metrik order: total pesanan hari ini, pesanan selesai, pesanan aktif yang sedang diproses di dapur/meja, dan jumlah tamu
    - Okupansi meja: total meja, meja terisi (occupied), meja kosong, persentase keterisian meja (%)
    - Peringatan stok menipis (low stock alert) secara otomatis jika sisa stok bahan baku <= 5 kg/unit
    - Daftar 5 pesanan terbaru (recent orders) dengan nomor pesanan, meja, status, dan nominal

2. **Sales Report (GET /api/v1/reports/sales)**:
    - Filter rentang tanggal (start_date, end_date)
    - Ringkasan omzet kotor (gross sales), total diskon, omzet bersih (net sales), total pesanan, total tamu
    - Rata-rata nilai transaksi (AOV - Average Order Value) dan rata-rata belanja per tamu
    - Tren harian (daily trend breakdown) omzet dan volume order

3. **Profit & COGS Report (GET /api/v1/reports/profit/summary & /reports/orders/{order}/profit)**:
    - Analisis laba riil berbasis FIFO modal bahan baku
    - Laba kotor per order maupun ringkasan seluruh outlet

4. **Payment Report (GET /api/v1/reports/payments)**:
    - Total uang terkumpul dan total frekuensi transaksi
    - Rincian per metode pembayaran (Cash, Transfer, dll) beserta nominal dan persentase kontribusi
    - Riwayat transaksi pembayaran lengkap dengan nama kasir yang menerima pembayaran

5. **Menu Sales Report (GET /api/v1/reports/menu-sales)**:
    - Peringkat menu terlaris (Best Seller) berdasarkan omzet dan kuantitas porsi/kg terjual
    - Analisis kontribusi penjualan menu terhadap total omzet (% revenue share)
    - Laba kotor dan margin keuntungan per menu varian

6. **Inventory & Stock Report (GET /api/v1/reports/inventory & /reports/stock-movements)**:
    - Laporan stok terkini seluruh bahan baku dengan total valuasi rupiah aset inventori
    - Riwayat mutasi stok lengkap (masuk, penjualan dapur, waste, adjustment) dengan jejak batch FIFO dan pencatat

Seluruh response endpoint telah distandarisasi menggunakan API Resource dan diproteksi middleware Spatie Permission (dashboard.view &
eport.view).

Target tercapai: Owner dan manajemen dapat melihat seluruh kondisi operasional, keuangan, stok, dan performa restoran secara komprehensif.

---

## Phase 2

### Inventory Batch Cost

Status: ? Selesai

Menghitung:

- **COGS (Cost of Goods Sold)**: Perhitungan HPP otomatis saat item pesanan diproses di dapur (Preparing)
- **FIFO Cost**: Deduct stok bahan baku secara FIFO dari inventory_batches, mencatat cost_per_unit dan otal_cost di stock_movements
- **Actual Profit & Margin**:
    - Per item: gross_profit = subtotal - cogs
    - Per order: gross_profit = subtotal - total_cogs - discount
    - Profit margin percentage: (gross_profit / revenue) \* 100
- **Endpoints & Reporting**:
    - GET /api/v1/reports/orders/{order}/profit (laporan profit per order & rincian item)
    - GET /api/v1/reports/profit/summary (laporan agregat profit outlet per periode tanggal & breakdown menu)

Target tercapai: Laporan profit restoran seafood kini akurat secara riil berdasarkan harga modal beli bahan baku.

---

## Phase 3

### Authorization

Status: ? Selesai

Implementasi penuh Role & Permission berbasis Spatie Laravel Permission:

Role:

- **Owner**: Full access ke seluruh sistem dan seluruh permissions
- **Admin**: Manajemen menu, user, inventori, laporan, dan operasional pesanan
- **Kasir / Cashier**: Order, payment, menu view, order item change
- **Kitchen / Kitchen Staff / Head**: Kitchen display system (accept, prepare, ready, serve), menu view, inventory view

Permission yang aktif & diproteksi middleware:

- User: user.view, user.manage
- Menu: menu.view, menu.manage
- Order: order.view, order.create, order.update, order.cancel, order.change
- Kitchen: kitchen.view, kitchen.update
- Payment: payment.view, payment.create
- Inventory: inventory.view, inventory.manage, inventory.adjust
- Dashboard & Report: dashboard.view,
  eport.view

Respons unauthorized ditangani otomatis via global exception handler di bootstrap/app.php (403 Forbidden).

---

## Phase 4

### Audit Log

Status: ✅ Selesai

Mencatat rekam jejak aktivitas operasional & sistem secara otomatis (auditing trail):

- **Login**: Pencatatan user login (web / POS / staff PIN) dengan IP Address & User Agent
- **Create Order**: Pembuatan order baru beserta detail meja, jumlah tamu, dan nama pelanggan
- **Cancel Order**: Pembatalan order beserta histori alasan pembatalan
- **Payment**: Penerimaan pembayaran kasir, metode pembayaran, nominal, dan order terkait
- **Inventory Adjustment**: Penambahan stok / mutasi bahan baku (FIFO batch, unit, kuantitas, rupiah modal)
- **Price & Dynamic Weight Change**: Perubahan harga dan timbangan riil seafood hidup
- **Delete Menu**: Penghapusan katalog menu oleh admin/owner
- **User Management**: Perubahan hak akses, peran, dan data pengguna

Endpoint:

- GET /api/v1/audit-logs: Pagination riwayat audit log dengan filter ction, user_id, start_date, end_date, dan outlet_id.
- Standarisasi envelope AuditLogResource dan otorisasi middleware permission:report.view|user.manage.

Target tercapai: Owner dan manajemen memiliki visibilitas dan akuntabilitas penuh atas seluruh transaksi dan aktivitas operasional restoran.

---

# 6. Setelah Backend Stabil

Mulai pengembangan Frontend.

## Flutter POS

Meliputi:

- Login
- POS
- Table
- Order
- Payment

---

## Web Kitchen Display

Meliputi:

- Waiting
- Accepted
- Preparing
- Ready
- Served

---

## Web Dashboard

Meliputi:

Dashboard

Report

Inventory

Master Data

User Management

---

# 7. Fitur Ditunda

Belum menjadi prioritas saat ini.

- Purchasing
- Supplier
- Purchase Order
- Goods Receiving
- Shift Kasir
- Waste Management (lanjutan)
- Loyalty
- Membership
- Voucher
- Promo
- QR Menu
- Multi Outlet
- SaaS

---

# 8. Target MVP

Backend dianggap selesai apabila memenuhi kondisi berikut:

- [x] ✅ Authentication stabil
- [x] ✅ Catalog stabil
- [x] ✅ Order stabil
- [x] ✅ Kitchen stabil
- [x] ✅ Inventory stabil & FIFO COGS
- [x] ✅ Payment stabil
- [x] ✅ Reporting & Dashboard Analytics selesai (Phase 1)
- [x] ✅ Inventory Batch Cost & FIFO COGS selesai (Phase 2)
- [x] ✅ Authorization Spatie RBAC selesai (Phase 3)
- [x] ✅ Audit Log selesai (Phase 4)
- [x] ✅ API Contract telah dibekukan (API Freeze)

Status Saat Ini: **Seluruh target backend telah 100% terpenuhi, teruji (426 assertions passing), dan siap untuk integrasi Frontend (Flutter Mobile POS, Web KDS, dan Web Dashboard Owner).**
