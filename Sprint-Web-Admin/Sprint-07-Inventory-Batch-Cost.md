# Web Admin / Owner Dashboard Sprint 07

# Inventory Batch Cost

## Status

Completed

---

# Tujuan Sprint

Membangun modul **Inventory Batch Cost** untuk memberikan informasi biaya bahan baku berdasarkan histori pembelian dan pergerakan stok.

Sprint ini melanjutkan Sprint 06 — Inventory Management.

Jika Sprint 06 menjawab:

> "Berapa stok yang tersedia?"

Maka Sprint 07 menjawab:

> "Berapa nilai dan biaya sebenarnya dari stok tersebut?"

---

# 1. Business Context

Pada restoran seafood, harga bahan baku tidak selalu tetap.

Contoh:

Hari Senin:

```text
Ikan Etong

10 kg

Rp 80.000/kg
```

Hari Selasa:

```text
Ikan Etong

15 kg

Rp 95.000/kg
```

Jika Owner hanya melihat stok:

```text
25 kg
```

maka informasi bisnis belum lengkap.

Owner perlu mengetahui:

- Berapa modal stok saat ini?
- Berapa harga rata-rata bahan?
- Apakah biaya bahan naik?
- Berapa estimasi food cost?

---

# 2. Hubungan Dengan Backend

Modul ini menggunakan struktur yang sudah dibuat:

```text
inventory_batches

↓

stock_movements

↓

ingredients
```

---

Contoh data:

## Inventory Batch

```text
Batch 1

Ingredient:
Ikan Etong

Quantity:
10 kg

Cost:
80.000/kg
```

---

```text
Batch 2

Ingredient:
Ikan Etong

Quantity:
15 kg

Cost:
95.000/kg
```

---

System dapat menghitung:

```text
Total Stock Cost

Average Cost

FIFO Cost
```

---

# 3. Scope Sprint

## Yang dibuat:

- Inventory Batch List
- Ingredient Cost Detail
- Batch History
- Average Cost Calculation Display
- Stock Valuation
- Cost Trend
- Cost Summary

---

## Tidak termasuk:

- Purchasing

Belum dibuat karena MVP belum membutuhkan supplier management.

- Automatic purchase order

- Supplier comparison

---

# 4. Architecture

Flow:

```text
Owner

↓

Inventory Cost Page

↓

Select Ingredient

↓

Request Cost Data

↓

Laravel Cost Calculation

↓

Display Cost Analysis
```

---

# 5. Page Structure

Buat:

```text
resources/js/admin/pages/inventory-cost/
```

Struktur:

```text
inventory-cost/

├── InventoryCostPage.vue

├── InventoryCostDetailPage.vue

├── components/

│   ├── CostSummaryCard.vue
│   ├── BatchTable.vue
│   ├── CostHistoryChart.vue
│   ├── StockValuation.vue
│   └── CostDetail.vue

└── inventory-cost.service.js
```

---

# 6. Inventory Cost Service

Buat:

```text
services/inventory-cost.service.js
```

Method:

```javascript
getCostSummary();

getIngredientCost();

getBatchHistory();
```

---

# 7. API Integration

## Cost Summary

Endpoint:

```http
GET /inventory/cost
```

---

Response:

```json
{
    "total_stock_value": 25000000,
    "ingredient_count": 35,
    "average_cost_change": 5
}
```

---

# 8. Ingredient Cost Detail API

Endpoint:

```http
GET /inventory/cost/{ingredient}
```

---

Response:

```json
{
    "ingredient": {
        "name": "Ikan Etong",
        "unit": "kg"
    },

    "stock": 25,

    "average_cost": 89000,

    "batches": []
}
```

---

# 9. Inventory Cost Store

Buat:

```text
stores/inventory-cost.store.js
```

State:

```text
summary

selectedIngredient

batches

loading

error
```

---

Action:

```text
fetchCostSummary()

fetchIngredientCost()
```

---

# 10. Cost Summary Dashboard

Komponen:

```text
CostSummaryCard.vue
```

Menampilkan:

---

## Total Inventory Value

Contoh:

```text
Rp 25.000.000
```

---

## Total Ingredient

```text
35 bahan
```

---

## Average Cost Change

```text
+5%
```

---

# 11. Batch Table

Komponen:

```text
BatchTable.vue
```

Menampilkan:

| Field      | Description   |
| ---------- | ------------- |
| Batch Date | tanggal masuk |
| Quantity   | jumlah masuk  |
| Remaining  | sisa stok     |
| Cost/unit  | harga satuan  |
| Value      | nilai batch   |

---

Contoh:

```text
Ikan Etong

10 kg

Sisa 7 kg

80.000/kg
```

---

# 12. Stock Valuation

Komponen:

```text
StockValuation.vue
```

Menampilkan:

Formula:

```text
Remaining Quantity

×

Cost Per Unit

=

Stock Value
```

---

Contoh:

```text
7 kg

×

80.000

=

560.000
```

---

# 13. Average Cost

Menampilkan harga rata-rata.

Formula:

```text
(Total Nilai Batch)

/

(Total Quantity)
```

---

Contoh:

Batch:

```text
10 kg x 80.000

15 kg x 95.000
```

---

Calculation:

```text
(800.000 + 1.425.000)

/

25 kg

=

89.000/kg
```

---

# 14. Cost History

Komponen:

```text
CostHistoryChart.vue
```

Menampilkan:

Perubahan harga bahan.

Contoh:

```text
Oktober

80k

85k

90k

95k
```

---

Tujuan:

Owner mengetahui:

- Harga bahan naik
- Margin menu berpotensi turun

---

# 15. FIFO Cost Awareness

Backend inventory menggunakan FIFO.

Frontend hanya menampilkan hasil.

Contoh:

Stock:

```text
Batch lama

5 kg

80.000
```

-

```text
Batch baru

10 kg

95.000
```

Ketika digunakan:

FIFO:

```text
Ambil batch lama terlebih dahulu.
```

---

# 16. Connection Dengan Reporting

Data ini nantinya digunakan untuk:

```text
Sales Report

↓

Revenue

-

Ingredient Cost

↓

Profit
```

---

Namun perhitungan profit detail belum dilakukan pada sprint ini.

---

# 17. Decimal Handling

Karena seafood menggunakan berat:

Support:

```text
0.500 kg

1.250 kg

10.750 kg
```

Frontend wajib menggunakan decimal.

Tidak boleh:

```text
integer
```

---

# 18. Loading State

Flow:

```text
Open Cost Page

↓

Loading

↓

Load Batch

↓

Display Cost
```

---

# 19. Error Handling

## Ingredient tidak ditemukan

```text
Data bahan tidak tersedia.
```

---

## Cost belum tersedia

```text
Belum ada data biaya.
```

---

## API Error

```text
Gagal mengambil data biaya inventory.
```

---

# 20. Performance Consideration

Inventory batch dapat bertambah besar.

Jangan:

```text
Ambil seluruh batch history
```

---

Gunakan:

```text
Pagination

Date Filter

Ingredient Filter
```

---

# 21. Security

Data cost termasuk data bisnis sensitif.

Akses:

Allowed:

- Owner
- Admin tertentu

Tidak boleh:

- Kasir
- Kitchen

---

Backend tetap menjadi sumber validasi permission.

---

# 22. Testing Scenario

## Scenario 1

Ingredient memiliki beberapa batch.

Expected:

```text
Semua batch tampil.
```

---

## Scenario 2

Hitung average cost.

Expected:

```text
Nilai sesuai backend.
```

---

## Scenario 3

Stock berubah setelah penggunaan.

Expected:

```text
Remaining batch berubah.
```

---

## Scenario 4

Tidak ada cost.

Expected:

```text
Empty state tampil.
```

---

# 23. Task Checklist

## Page

- [x] Inventory Cost Page
- [x] Detail Page

---

## API

- [x] Cost Service
- [x] Cost Endpoint

---

## Store

- [x] Inventory Cost Store

---

## Components

- [x] Cost Summary
- [x] Batch Table
- [x] Stock Valuation
- [x] Cost History

---

## UX

- [x] Loading
- [x] Empty State
- [x] Error State

---

## Testing

- [x] Batch Display
- [x] Cost Calculation
- [x] Stock Value
- [x] Filter

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat melihat nilai stok berdasarkan cost

✅ Batch inventory dapat dilihat

✅ Average cost tampil

✅ Cost history tampil

✅ Data menggunakan hasil kalkulasi backend

✅ Decimal quantity berjalan

✅ Permission dasar diterapkan

---

# Output Sprint

Hasil akhir Sprint 07:

```text
Owner

↓

Inventory Batch Cost

↓

Stock Value

↓

Ingredient Cost Analysis

↓

Business Cost Awareness
```

Setelah sprint ini selesai, Owner tidak hanya mengetahui jumlah stok, tetapi juga mengetahui nilai modal yang tersimpan dalam inventory.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 08 — Waste Management

Fitur yang akan dibuat:

- Waste Recording
- Waste History
- Waste Reason
- Ingredient Loss Tracking
- Waste Cost Analysis

Sprint berikutnya fokus mengontrol kehilangan bahan baku restoran.
