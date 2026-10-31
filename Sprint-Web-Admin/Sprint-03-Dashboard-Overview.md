# Web Admin / Owner Dashboard Sprint 03

# Dashboard Overview

## Status

Completed

---

# Tujuan Sprint

Membangun halaman utama Web Admin / Owner Dashboard yang memberikan gambaran cepat mengenai kondisi operasional restoran.

Dashboard menjadi halaman pertama yang dilihat Owner setelah login.

Pada akhir sprint ini Owner dapat melihat:

- Penjualan hari ini
- Jumlah transaksi
- Total pendapatan
- Estimasi profit
- Menu terlaris
- Order terbaru
- Statistik operasional restoran

---

# 1. Business Context

Owner tidak membutuhkan semua data setiap saat.

Informasi yang paling penting ketika membuka sistem:

```text
Bagaimana penjualan hari ini?

Apakah restoran ramai?

Menu apa yang paling laku?

Apakah ada masalah operasional?
```

Dashboard bertujuan menjawab pertanyaan tersebut tanpa membuka banyak halaman.

---

# 2. Scope Sprint

## Yang dibuat:

- Dashboard Layout
- Dashboard API
- Summary Card
- Sales Overview
- Order Summary
- Best Selling Menu
- Recent Orders
- Quick Statistics
- Empty State
- Loading State

---

## Tidak termasuk:

- Advanced Reporting
- Export Report
- Filter kompleks
- Inventory Dashboard Detail
- Employee Performance

Fitur tersebut dibuat pada sprint berikutnya.

---

# 3. Dashboard Architecture

Flow:

```text
User Login

↓

Dashboard Page

↓

Request Dashboard Data

↓

Laravel API

↓

Aggregate Data

↓

Display Widget
```

---

# 4. Dashboard Page

Buat:

```text
resources/js/admin/pages/dashboard/DashboardPage.vue
```

Tanggung jawab:

- Mengatur layout dashboard
- Memanggil dashboard store/service
- Menampilkan widget

---

# 5. Dashboard Folder Structure

```text
pages/

└── dashboard/

    ├── DashboardPage.vue

    ├── components/

    │   ├── SummaryCard.vue
    │   ├── SalesChart.vue
    │   ├── OrderSummary.vue
    │   ├── BestMenuCard.vue
    │   └── RecentOrders.vue

    └── dashboard.service.js
```

---

# 6. Dashboard API Integration

Buat service:

```text
services/dashboard.service.js
```

Endpoint:

```http
GET /dashboard/overview
```

---

Response contoh:

```json
{
    "sales_today": 3500000,
    "orders_today": 45,
    "profit_today": 1200000,
    "best_menu": [],
    "recent_orders": []
}
```

---

# 7. Dashboard Store

Buat:

```text
stores/dashboard.store.js
```

State:

```text
summary

bestMenus

recentOrders

loading

error
```

---

Action:

```text
fetchDashboard()
```

---

# 8. Summary Card

Komponen:

```text
SummaryCard.vue
```

Menampilkan:

## Total Sales

Contoh:

```text
Rp 3.500.000
```

---

## Total Orders

Contoh:

```text
45 Order
```

---

## Profit

Contoh:

```text
Rp 1.200.000
```

---

## Average Order

Contoh:

```text
Rp 77.000
```

---

# 9. Dashboard Layout

Struktur:

```text
+--------------------------------+

| Summary Cards                  |

+--------------------------------+

| Sales Overview | Best Menu     |

+--------------------------------+

| Recent Orders                  |

+--------------------------------+
```

---

# 10. Sales Overview

Menampilkan trend sederhana.

Contoh:

```text
Penjualan Hari Ini

09:00  ███

12:00  ███████

15:00  █████

18:00  ████████
```

---

Pada sprint ini dapat menggunakan data sederhana.

Chart lengkap dibuat pada Reporting Sprint.

---

# 11. Best Selling Menu

Komponen:

```text
BestMenuCard.vue
```

Menampilkan:

- Nama menu
- Jumlah terjual
- Revenue

Contoh:

```text
1. Ikan Bakar

25 porsi

Rp 1.750.000
```

---

# 12. Recent Orders

Komponen:

```text
RecentOrders.vue
```

Menampilkan:

- Nomor order
- Customer
- Total
- Status
- Waktu

Contoh:

```text
ORD-001

Budi

Rp 250.000

Completed
```

---

# 13. Quick Statistics

Tambahkan informasi:

- Order aktif
- Order selesai
- Order dibatalkan

Contoh:

```text
Active Order

5

Completed

38

Cancelled

2
```

---

# 14. Loading State

Saat dashboard mengambil data:

```text
Request

↓

Skeleton Loading

↓

Data Loaded
```

Gunakan:

```text
BaseLoading.vue
```

atau skeleton component.

---

# 15. Error State

Jika API gagal:

Tampilkan:

```text
Tidak dapat mengambil data dashboard.

Coba lagi.
```

---

# 16. Empty State

Jika restoran belum memiliki transaksi:

```text
Belum ada transaksi hari ini.
```

---

# 17. Currency Formatter

Buat helper:

```text
utils/formatter.js
```

Fungsi:

```javascript
formatCurrency();
```

Contoh:

Input:

```text
3500000
```

Output:

```text
Rp 3.500.000
```

---

# 18. Date Formatter

Gunakan:

```text
Day.js
```

Untuk:

- Waktu order
- Last update
- Hari transaksi

---

# 19. Responsive Design

Target:

- Desktop
- Laptop
- Tablet

Dashboard utama diprioritaskan untuk:

```text
Owner Office / Laptop
```

---

# 20. Performance

Perhatikan:

- Jangan request data berulang.
- Gunakan satu endpoint aggregation.
- Gunakan caching sementara di store.

---

# 21. Testing Scenario

## Scenario 1

Owner login.

Expected:

```text
Dashboard tampil.
```

---

## Scenario 2

Ada transaksi baru.

Expected:

```text
Jumlah order berubah.
```

---

## Scenario 3

Tidak ada transaksi.

Expected:

```text
Empty state muncul.
```

---

## Scenario 4

API error.

Expected:

```text
Error message tampil.
```

---

# 22. Task Checklist

## Page

- [x] Dashboard Page
- [x] Dashboard Layout

---

## API

- [x] Dashboard Service
- [x] Dashboard Endpoint Integration

---

## Store

- [x] Dashboard Store
- [x] State Management

---

## Components

- [x] Summary Card
- [x] Best Menu
- [x] Recent Orders
- [x] Statistics Card

---

## Utility

- [x] Currency Formatter
- [x] Date Formatter

---

## UX

- [x] Loading State
- [x] Error State
- [x] Empty State

---

## Testing

- [x] Dashboard Load
- [x] API Success
- [x] API Failed
- [x] Empty Data

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat melihat dashboard setelah login

✅ Summary penjualan tampil

✅ Total order tampil

✅ Profit tampil

✅ Menu terlaris tampil

✅ Recent order tampil

✅ Loading dan error handling tersedia

✅ Dashboard responsive

---

# Output Sprint

Hasil akhir Sprint 03:

```text
Login

↓

Dashboard

↓

Operational Summary

↓

Sales Overview

↓

Recent Orders

↓

Owner Monitoring Ready
```

Pada tahap ini Web Admin sudah memiliki halaman utama yang dapat digunakan Owner untuk memantau kondisi restoran secara cepat.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 04 — Sales Reporting

Fitur yang akan dibuat:

- Daily Sales Report
- Monthly Sales Report
- Revenue Analysis
- Profit Analysis
- Menu Performance
- Sales Filter
- Export Preparation

Sprint berikutnya mulai masuk ke area analitik bisnis.
