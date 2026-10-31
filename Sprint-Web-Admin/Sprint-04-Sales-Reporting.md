# Web Admin / Owner Dashboard Sprint 04

# Sales Reporting

## Status

Completed

---

# Tujuan Sprint

Membangun modul laporan penjualan yang memungkinkan Owner melakukan analisis performa bisnis berdasarkan data transaksi restoran.

Berbeda dengan Dashboard Overview yang hanya menampilkan ringkasan cepat, modul Reporting menyediakan data yang lebih detail untuk membantu pengambilan keputusan.

Pada akhir sprint ini Owner dapat melihat:

- Total penjualan berdasarkan periode
- Jumlah transaksi
- Revenue trend
- Profit estimation
- Performa menu
- Perbandingan periode penjualan

---

# 1. Business Context

Owner membutuhkan laporan untuk menjawab pertanyaan bisnis:

```text
Berapa omzet restoran minggu ini?

Apakah penjualan meningkat atau menurun?

Menu apa yang paling menghasilkan?

Jam berapa restoran paling ramai?

Berapa estimasi keuntungan?
```

Dashboard menjawab:

> "Bagaimana kondisi sekarang?"

Reporting menjawab:

> "Kenapa kondisi tersebut terjadi?"

---

# 2. Scope Sprint

## Yang dibuat:

- Sales Report Page
- Date Filter
- Sales Summary
- Revenue Chart
- Order Statistic
- Menu Performance
- Sales Comparison
- Profit Overview

---

## Tidak termasuk:

- Export PDF/Excel
- Inventory Cost Detail
- Waste Calculation
- Employee Performance

Fitur tersebut dibuat pada sprint berikutnya.

---

# 3. Reporting Architecture

Flow:

```text
Owner

↓

Sales Report Page

↓

Select Filter

↓

Request Report API

↓

Laravel Aggregation Query

↓

Return Report Data

↓

Render Chart & Table
```

---

# 4. Page Structure

Buat:

```text
resources/js/admin/pages/report/SalesReportPage.vue
```

Tanggung jawab:

- Filter periode
- Load report data
- Menampilkan komponen laporan

---

# 5. Folder Structure

```text
pages/

└── report/

    ├── SalesReportPage.vue

    ├── components/

    │   ├── SalesSummaryCard.vue
    │   ├── RevenueChart.vue
    │   ├── OrderStatistic.vue
    │   ├── MenuPerformanceTable.vue
    │   └── SalesComparison.vue

    └── report.service.js
```

---

# 6. Sales Report API

Buat service:

```text
services/report.service.js
```

---

Endpoint:

```http
GET /reports/sales
```

---

Request:

```http
/reports/sales?start_date=2026-10-01&end_date=2026-10-31
```

---

Response:

```json
{
    "period": {
        "start": "2026-10-01",
        "end": "2026-10-31"
    },
    "summary": {
        "total_sales": 25000000,
        "total_orders": 320,
        "average_order": 78000
    },
    "chart": [],
    "menus": []
}
```

---

# 7. Report Store

Buat:

```text
stores/report.store.js
```

State:

```text
salesReport

selectedPeriod

loading

error
```

---

Action:

```text
fetchSalesReport()

setPeriod()
```

---

# 8. Date Filter

Komponen:

```text
DateFilter.vue
```

Pilihan:

```text
Hari Ini

Kemarin

Minggu Ini

Bulan Ini

Custom Range
```

---

Contoh:

```text
01 Oktober 2026

-

31 Oktober 2026
```

---

# 9. Sales Summary

Komponen:

```text
SalesSummaryCard.vue
```

Menampilkan:

## Total Revenue

Contoh:

```text
Rp 25.000.000
```

---

## Total Order

Contoh:

```text
320 transaksi
```

---

## Average Order Value

Contoh:

```text
Rp 78.000
```

---

## Growth

Contoh:

```text
+12% dibanding periode sebelumnya
```

---

# 10. Revenue Chart

Komponen:

```text
RevenueChart.vue
```

Menampilkan grafik:

- Harian
- Mingguan
- Bulanan

Contoh:

```text
Sen
 ███

Sel
 █████

Rab
 ███████
```

---

Library:

```text
ApexCharts
```

---

# 11. Order Statistic

Komponen:

```text
OrderStatistic.vue
```

Menampilkan:

- Total order
- Completed
- Cancelled
- Average order value

---

Contoh:

```text
Total Order

320


Completed

310


Cancelled

10
```

---

# 12. Menu Performance

Komponen:

```text
MenuPerformanceTable.vue
```

Menampilkan:

| Menu            | Qty | Revenue      |
| --------------- | --- | ------------ |
| Ikan Bakar      | 120 | Rp 8.000.000 |
| Cumi Asam Manis | 90  | Rp 6.500.000 |

---

Data digunakan untuk:

- Menentukan menu favorit
- Evaluasi harga
- Promosi

---

# 13. Profit Overview

Komponen:

```text
ProfitOverview.vue
```

Menampilkan:

```text
Revenue

-

Estimated Cost

=

Estimated Profit
```

---

Catatan:

Pada tahap ini profit masih menggunakan data yang tersedia.

Inventory Batch Cost yang lebih akurat baru dibuat pada:

```text
Sprint 07 — Inventory Batch Cost
```

---

# 14. Sales Comparison

Komponen:

```text
SalesComparison.vue
```

Membandingkan:

Contoh:

```text
Oktober 2026

vs

September 2026
```

Menampilkan:

- Revenue difference
- Order difference
- Growth percentage

---

# 15. Currency Formatter

Gunakan helper sebelumnya:

```text
utils/formatter.js
```

Format:

```text
25000000

↓

Rp 25.000.000
```

---

# 16. Loading State

Saat report dipanggil:

```text
Filter berubah

↓

Loading

↓

Update Chart

↓

Update Table
```

---

# 17. Error Handling

## API Error

Tampilkan:

```text
Gagal mengambil laporan penjualan.
```

---

## Invalid Date

Tampilkan:

```text
Rentang tanggal tidak valid.
```

---

# 18. Performance Consideration

Reporting berpotensi menjadi query berat.

Aturan:

- Gunakan endpoint aggregation.
- Jangan mengambil semua order.
- Backend melakukan perhitungan.
- Frontend hanya menampilkan.

---

Contoh buruk:

```text
Ambil semua order

↓

Hitung di Vue
```

---

Contoh benar:

```text
Laravel Query

↓

Return Summary

↓

Vue Render
```

---

# 19. Testing Scenario

## Scenario 1

Pilih:

```text
Hari Ini
```

Expected:

- Data hari ini muncul.

---

## Scenario 2

Pilih:

```text
Bulan Ini
```

Expected:

- Grafik berubah.

---

## Scenario 3

Tidak ada transaksi.

Expected:

- Empty state tampil.

---

## Scenario 4

API gagal.

Expected:

- Error message tampil.

---

# 20. Task Checklist

## Page

- [x] Sales Report Page

---

## API

- [x] Report Service
- [x] Sales Report Endpoint

---

## Store

- [x] Report Store
- [x] Filter State

---

## Components

- [x] Summary Card
- [x] Revenue Chart
- [x] Order Statistic
- [x] Menu Performance
- [x] Profit Overview

---

## Filter

- [x] Date Filter
- [x] Custom Range

---

## UX

- [x] Loading State
- [x] Error State
- [x] Empty State

---

## Testing

- [x] Daily Report
- [x] Monthly Report
- [x] Custom Range
- [x] Empty Data

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat membuka halaman Sales Reporting

✅ Filter periode bekerja

✅ Revenue report tampil

✅ Order statistic tampil

✅ Menu performance tampil

✅ Chart berhasil menampilkan data

✅ Error handling tersedia

✅ Reporting tidak menghitung data di frontend

---

# Output Sprint

Hasil akhir Sprint 04:

```text
Owner

↓

Sales Reporting

↓

Revenue Analysis

↓

Menu Performance

↓

Business Insight
```

Pada tahap ini Owner sudah memiliki kemampuan untuk menganalisis performa penjualan restoran berdasarkan histori transaksi.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 05 — Order Management

Fitur yang akan dibuat:

- Order List
- Order Detail
- Order History
- Payment Detail
- Order Status Monitoring
- Search & Filter Order

Sprint berikutnya fokus pada pengelolaan transaksi operasional restoran.
