# Web Admin / Owner Dashboard Sprint 08

# Waste Management

## Status

Completed

---

# Tujuan Sprint

Membangun modul **Waste Management** untuk membantu Owner mencatat, memonitor, dan menganalisis kehilangan bahan baku restoran.

Sprint ini bertujuan memberikan visibilitas terhadap:

- Bahan yang terbuang
- Penyebab waste
- Nilai kerugian
- Trend kehilangan bahan

---

# 1. Business Context

Pada bisnis restoran seafood, kehilangan bahan baku adalah salah satu sumber kebocoran uang terbesar.

Contoh:

```text
Ikan Etong masuk:

10 kg

Terjual:

8 kg

Sisa:

2 kg
```

Kemungkinan:

```text
- Rusak
- Salah penyimpanan
- Terbuang saat proses masak
- Salah handling
- Expired
```

Tanpa pencatatan, Owner hanya melihat:

```text
Stok berkurang
```

Tetapi tidak mengetahui:

```text
Kenapa stok berkurang?
Berapa nilai kerugiannya?
Siapa yang melakukan?
```

---

# 2. Hubungan Dengan Modul Sebelumnya

Waste Management menggunakan data:

```text
Inventory Management

+

Inventory Batch Cost

+

Stock Movement
```

Flow:

```text
Waste Record

↓

Stock Adjustment OUT

↓

Stock Movement

↓

Inventory Berkurang

↓

Waste Cost Tercatat
```

---

# 3. Scope Sprint

## Yang dibuat:

- Waste Dashboard
- Waste Recording
- Waste History
- Waste Reason Management
- Waste Cost Calculation
- Waste Detail

---

## Tidak termasuk:

- Approval workflow

(alasan: MVP belum membutuhkan multi approval)

- Photo evidence

(dapat ditambahkan kemudian)

- Automatic waste prediction

---

# 4. Database Requirement

Menggunakan tabel baru:

```text
waste_records
```

---

Struktur:

```text
waste_records

id

outlet_id

ingredient_id

quantity

unit_cost

total_cost

reason

note

created_by

created_at
```

---

Relasi:

```text
Ingredient

↓

WasteRecord

↓

StockMovement
```

---

# 5. Architecture

Flow:

```text
Owner

↓

Waste Management Page

↓

Submit Waste

↓

Laravel Waste Service

↓

Inventory Adjustment

↓

Stock Movement Created
```

---

# 6. Page Structure

Buat:

```text
resources/js/admin/pages/waste/
```

---

Struktur:

```text
waste/

├── WastePage.vue

├── WasteDetailPage.vue

├── components/

│   ├── WasteSummaryCard.vue
│   ├── WasteTable.vue
│   ├── WasteFormModal.vue
│   ├── WasteReasonBadge.vue
│   └── WasteCostCard.vue

└── waste.service.js
```

---

# 7. Waste Service

Buat:

```text
services/waste.service.js
```

Method:

```javascript
getWasteSummary();

getWasteRecords();

createWaste();

getWasteDetail();
```

---

# 8. Waste Dashboard

Halaman utama:

```text
WastePage.vue
```

Layout:

```text
+--------------------------------+

| Total Waste Cost               |

| Total Waste Quantity           |

| Most Waste Ingredient          |

+--------------------------------+

| Waste History                  |

+--------------------------------+
```

---

# 9. Waste Summary Card

Component:

```text
WasteSummaryCard.vue
```

Menampilkan:

## Total Waste Cost

Contoh:

```text
Rp 2.500.000
```

---

## Total Waste Quantity

Contoh:

```text
35 kg
```

---

## Highest Waste Ingredient

Contoh:

```text
Ikan Etong
```

---

# 10. Waste List

Component:

```text
WasteTable.vue
```

Menampilkan:

| Field      | Description    |
| ---------- | -------------- |
| Date       | tanggal waste  |
| Ingredient | bahan          |
| Quantity   | jumlah         |
| Reason     | alasan         |
| Cost       | nilai kerugian |
| User       | pencatat       |

---

Contoh:

```text
26 Oktober 2026

Ikan Etong

2 kg

Rusak

Rp160.000
```

---

# 11. Waste Recording

Owner dapat mencatat waste.

Component:

```text
WasteFormModal.vue
```

---

Form:

```text
Ingredient

Quantity

Reason

Note
```

---

Contoh:

```text
Ingredient:

Ikan Etong


Quantity:

2 kg


Reason:

Rusak
```

---

# 12. Waste Reason

Gunakan enum:

```text
WasteReason
```

---

Nilai awal:

```text
expired

damaged

cooking_loss

wrong_preparation

other
```

---

Tujuan:

Agar laporan waste dapat dianalisis.

---

# 13. Create Waste API

Endpoint:

```http
POST /waste
```

---

Request:

```json
{
    "ingredient_id": 1,
    "quantity": 2,
    "reason": "damaged",
    "note": "Ikan rusak sebelum digunakan"
}
```

---

Backend melakukan:

```text
Validate Stock

↓

Create Waste Record

↓

Deduct Inventory

↓

Create Stock Movement
```

---

# 14. Waste Cost Calculation

Menggunakan:

```text
Inventory Batch Cost
```

---

Formula:

```text
Waste Quantity

×

Current Cost

=

Waste Cost
```

---

Contoh:

```text
2 kg

×

80.000

=

160.000
```

---

# 15. Waste Detail

Route:

```text
/waste/:id
```

---

Menampilkan:

```text
Ingredient

Quantity

Cost

Reason

Note

Created By

Time
```

---

# 16. Waste Cost Analysis

Component:

```text
WasteCostCard.vue
```

---

Menampilkan:

```text
Total Waste

Rp 2.500.000


Waste Percentage

3%
```

---

Dapat digunakan Owner untuk mengetahui:

```text
Apakah operasional dapur efisien?
```

---

# 17. Waste Filter

Filter:

## Date

```text
Today

This Week

This Month

Custom
```

---

## Ingredient

```text
Ikan

Udang

Cumi
```

---

## Reason

```text
Expired

Damaged

Other
```

---

# 18. Integration Dengan Reporting

Data waste akan digunakan pada:

```text
Reporting Dashboard
```

Untuk menghitung:

```text
Revenue

-

Ingredient Cost

-

Waste Cost

=

Actual Profit
```

---

# 19. Security

Waste merupakan data sensitif.

Permission:

Allowed:

```text
Owner

Admin
```

Tidak boleh:

```text
Kasir

Kitchen
```

---

Backend wajib melakukan:

```text
Permission Check
```

---

# 20. Error Handling

## Stock tidak cukup

Response:

```text
Stok bahan tidak mencukupi.
```

---

## Quantity invalid

```text
Jumlah waste tidak valid.
```

---

## Ingredient tidak ditemukan

```text
Bahan tidak tersedia.
```

---

# 21. Performance Consideration

Waste record dapat bertambah besar.

Gunakan:

```text
Pagination

Date Filter

Ingredient Filter
```

---

Jangan:

```text
Load semua waste history
```

---

# 22. Testing Scenario

## Scenario 1

Create waste.

Expected:

```text
Waste record tersimpan.
```

---

## Scenario 2

Create waste dengan stok kosong.

Expected:

```text
Error stok tidak cukup.
```

---

## Scenario 3

Waste berhasil dibuat.

Expected:

```text
Inventory berkurang.

Stock movement tercatat.
```

---

## Scenario 4

Open waste history.

Expected:

```text
Data tampil dengan pagination.
```

---

## Scenario 5

Filter waste berdasarkan alasan.

Expected:

```text
Data sesuai filter.
```

---

# 23. Task Checklist

## Database

- [x] Create waste_records migration
- [x] Waste Model
- [x] Waste Relationship

---

## Backend Integration

- [x] Waste API
- [x] Inventory Deduction
- [x] Stock Movement Integration

---

## Frontend

- [x] Waste Dashboard
- [x] Waste Table
- [x] Waste Form
- [x] Waste Detail

---

## State

- [x] Waste Store

---

## UX

- [x] Loading State
- [x] Empty State
- [x] Error State

---

## Testing

- [x] Create Waste
- [x] Stock Deduction
- [x] Cost Calculation
- [x] Filtering

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat mencatat bahan terbuang

✅ Stok otomatis berkurang

✅ Waste memiliki histori

✅ Nilai kerugian dapat dihitung

✅ Penyebab waste dapat dianalisis

✅ Permission diterapkan

---

# Output Sprint

Hasil akhir Sprint 08:

```text
Owner

↓

Waste Management

↓

Loss Tracking

↓

Inventory Control

↓

Operational Efficiency
```

Setelah sprint ini selesai, Owner dapat mengetahui bukan hanya berapa stok yang hilang, tetapi juga alasan dan nilai uang yang hilang.

---
