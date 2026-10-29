
# Roadmap Setelah Backend MVP Selesai

Status saat ini:

✅ Backend MVP selesai

Seluruh modul utama telah tersedia:

- Authentication
- Authorization
- Catalog
- Order
- Order Batch
- Kitchen
- Inventory
- Inventory FIFO
- Inventory Adjustment
- Recipe (Opsional)
- Payment
- Split Payment
- Reporting
- Dashboard
- Audit Log

Artinya backend sudah memasuki tahap **Production Preparation**.

---

# PHASE 1 — Code Quality

Status: ✅ Selesai

Tujuan:
Meningkatkan kualitas source code, kebersihan arsitektur, dan standarisasi style & static analysis.

Checklist:
- [x] ✅ **Unit Test**: Test kalkulasi bisnis murni (OrderCalculationServiceTest, ProfitCalculationServiceTest)
- [x] ✅ **Feature Test**: Test flow lengkap lifecycle order, dynamic pricing, kitchen KDS, reporting & analytics
- [x] ✅ **API Test**: Test otorisasi RBAC, API Envelope Resources, sanitasi input Form Request, exception response 422/403
- [x] ✅ **PHPStan (Larastan)**: Lulus static analysis level 5 pada seluruh 86 file tanpa error (0 errors)
- [x] ✅ **Laravel Pint**: Seluruh file PHP terformat rapi sesuai standar styling PSR-12 / Laravel
- [x] ✅ **Dead Code Cleanup**: Penghapusan direktori sisa pp/Modules/, pembersihan namespace tidak terpakai, dan pembersihan enum usang
- [x] ✅ **Duplicate Code Cleanup**: Konsolidasi logic recalculation, standarisasi envelope resource, dan reuse trait/service
- [x] ✅ **Refactor Naming**: Konsistensi penamaan controller RESTful, API resources, enum backing values, dan service methods
- [x] ✅ **Folder Structure Review**: Pembersihan PSR-4 autoload composer, struktur folder modular standar Laravel (Enums, Http/Controllers/Api, Http/Requests, Http/Resources, Models, Services)

Target:
Backend bersih, type-safe, konsisten, dan mudah di-maintain.

---

# PHASE 2 — Performance

Status: ✅ Selesai

Optimasi performa database, framework caching, dan efisiensi endpoint API.

Checklist:

Database:
- [x] ✅ **Query Optimization**: SQL Direct Aggregation (SUM, COUNT, COALESCE) pada ReportingService & Dashboard, menggantikan kalkulasi manual di memori PHP
- [x] ✅ **N+1 Query Audit**: Penerapan Model::preventLazyLoading dan Model::preventSilentlyDiscardingAttributes di AppServiceProvider
- [x] ✅ **Composite Index**: Migrasi 2026_10_29_000003_add_performance_composite_indexes untuk tabel orders, order_batches, inventory_batches (FIFO lookup), stock_movements, audit_logs, dan menu_variants
- [x] ✅ **Explain Analyze**: Analisis eksekusi query PostgreSQL membuktikan kecepatan sub-milidetik (0.028 ms untuk order aggregation, 0.127 ms untuk FIFO batch traversal)

Laravel:
- [x] ✅ **Cache**: Cache master data menu katalog & kitchen stations (catalog_menus_outlet_*) dengan TTL 5 menit
- [x] ✅ **Config Cache**: Kompatibilitas 100% php artisan config:cache tanpa error konfigurasi
- [x] ✅ **Route Cache**: Kompatibilitas 100% php artisan route:cache
- [x] ✅ **Queue & Event Ready**: Struktur database dan framework siap pemrosesan asynchronous

API:
- [x] ✅ **Pagination**: Penambahan endpoint GET /api/v1/orders berpaginasi untuk mobile POS, melengkapi paginasi audit-logs & stock movements
- [x] ✅ **Lazy Loading & Eager Loading Review**: Audit eager loading lengkap pada Kitchen Display (order.orderTable, items), Catalog (ariants.cookingMethod, mainIngredient, kitchenStation), dan Order Resources

Target:
Sistem memiliki latensi rendah dan throughput tinggi bahkan saat volume transaksi restoran memuncak.

---

# PHASE 3 — Security

Status: ✅ Selesai

Penguatan keamanan otentikasi, otorisasi RBAC, pembatasan laju request (throttling), dan isolasi transaksi database.

Checklist:

Authentication:
- [x] ✅ **Token Expiration**: Konfigurasi masa aktif token Sanctum 24 jam (1440 menit)
- [x] ✅ **Revoke Token**: Endpoint POST /api/v1/logout (mencabut access token aktif) dan POST /api/v1/logout-all (mencabut seluruh sesi perangkat)
- [x] ✅ **Device Login**: Dukungan parameter device_name pada login web dan staff PIN

Authorization:
- [x] ✅ **Permission Audit**: Seluruh endpoint API terlindungi middleware permission Spatie yang presisi
- [x] ✅ **Owner Protection**: Hak istimewa user management & konfigurasi outlet terlindungi
- [x] ✅ **Kitchen Protection**: Peran kitchen terisolasi penuh pada KDS dan diblokir (403) dari pembuatan pesanan, pembayaran, serta laporan keuangan

API:
- [x] ✅ **Rate Limiter & Throttling**: Rate limiting 5 request/menit untuk login (mencegah brute force) dan 60 request/menit untuk API umum
- [x] ✅ **Validation Review**: Sanitasi ketat seluruh payload Form Request
- [x] ✅ **Error Handling**: Respons error seragam (422 Domain Exception, 403 Forbidden, 429 Throttle) tanpa membocorkan trace database

Database:
- [x] ✅ **Transaction Review**: Seluruh operasi mutasi data dibungkus dalam DB::transaction
- [x] ✅ **Locking Review**: Penerapan pesimistic locking lockForUpdate() pada meja, order status, dan inventory batch FIFO

Target:
Sistem terproteksi dari ancaman brute-force, kebocoran sesi, manipulasi data konkuren, dan akses tidak sah.

---

# PHASE 4 — Documentation

Dokumentasi teknis.

Buat:

ERD

Flow Diagram

Sequence Diagram

API Documentation

Database Documentation

Business Rules

Deployment Guide

Target:

Developer baru dapat memahami sistem.

---

# PHASE 5 — Frontend Development

## Flutter POS

- Login
- Dashboard
- Table
- Order
- Payment
- Split Payment

---

## Web Kitchen Display

- Waiting
- Accepted
- Preparing
- Ready
- Served

---

## Web Dashboard

- Dashboard
- Report
- Inventory
- User
- Menu

Target:

Seluruh frontend selesai.

---

# PHASE 6 — Integration Testing

Testing end-to-end.

Checklist:

Order

Kitchen

Payment

Inventory

Report

Audit Log

Target:

Tidak ada bug antar modul.

---

# PHASE 7 — User Acceptance Test (UAT)

Testing langsung di restoran.

Skenario:

- Customer makan biasa
- Split Payment
- Tambah pesanan
- Stok habis
- Ganti menu
- Perubahan harga
- Inventory Adjustment
- Closing

Target:

Flow bisnis tervalidasi.

---

# PHASE 8 — Production

Checklist:

Server

Database

Backup

Monitoring

Log

SSL

Domain

Deployment

Target:

Go Live.

---

# PHASE 9 — Future Roadmap

Setelah sistem berjalan stabil.

- Purchasing
- Supplier
- Goods Receiving
- Waste Management lanjutan
- Shift Kasir
- Multi Outlet
- SaaS
- Mobile Owner
- QR Ordering
- Membership
- Loyalty
- Promo
- Voucher