# Web Admin / Owner Dashboard Sprint 06

# Inventory Management

## Status

Completed

---

# Tujuan Sprint

Membangun modul Inventory Management pada Web Admin / Owner Dashboard agar Owner dapat memonitor kondisi stok bahan baku restoran secara real-time.

Sprint ini berfokus pada:

- Melihat stok bahan baku
- Melihat histori perubahan stok
- Melakukan penyesuaian stok
- Monitoring bahan yang hampir habis

Inventory Management menjadi dasar sebelum masuk ke:

- Inventory Batch Cost
- Waste Management
- Recipe Management

---

# 1. Business Context

Pada restoran seafood, stok bahan baku memiliki karakteristik khusus:

- Bahan memiliki satuan berat (kg/gram)
- Harga bahan dapat berubah setiap pembelian
- Stok berkurang berdasarkan order yang masuk dapur
- Ada kemungkinan stok rusak atau hilang

Owner membutuhkan informasi:

```text id="bqk91p"
Berapa stok ikan saat ini?

Bahan apa yang hampir habis?

Berapa banyak stok masuk?

Berapa banyak stok keluar?

Apakah ada selisih stok?
```

---

# 2. Scope Sprint

## Yang dibuat:

- Inventory Dashboard
- Ingredient List
- Current Stock Monitoring
- Stock Movement History
- Stock Adjustment
- Low Stock Indicator
- Inventory Detail

---

## Tidak termasuk:

- Purchasing

(alasan: belum menjadi bagian MVP)

- Supplier Management
- Recipe Deduction
- Waste Recording

(Waste dibuat pada Sprint 08)

- Batch Cost Calculation

(Dibuat pada Sprint 07)

---

# 3. Inventory Architecture

Flow:

```text id="xj7qgk"
Owner

↓

Inventory Page

↓

Request Inventory API

↓

Laravel Inventory Service

↓

Inventory Data

↓

Display Stock
```

---

# 4. Page Structure

Buat:

```text id="j85s6m"
resources/js/admin/pages/inventory/
```

Struktur:

```text id="4s8v6k"
inventory/

├── InventoryPage.vue

├── InventoryDetailPage.vue

├── components/

│   ├── InventoryTable.vue
│   ├── StockCard.vue
│   ├── StockMovementTable.vue
│   ├── StockAdjustmentModal.vue
│   └── LowStockAlert.vue

└── inventory.service.js
```

---

# 5. Inventory Service

Buat:

```text id="1j40wh"
services/inventory.service.js
```

Method:

```javascript
getIngredients();

getIngredientDetail();

getStockMovements();

adjustStock();
```

---

# 6. Inventory API

## Ingredient Stock List

Endpoint:

```http
GET /inventory
```

---

Response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Ikan Etong",
            "unit": "kg",
            "stock": 15.5,
            "status": "available"
        }
    ]
}
```

---

# 7. Inventory Store

Buat:

```text
stores/inventory.store.js
```

State:

```text
ingredients

selectedIngredient

movements

loading

error
```

---

Action:

```text
fetchInventory()

fetchDetail()

adjustStock()
```

---

# 8. Inventory Dashboard

Halaman utama:

```text
InventoryPage.vue
```

Layout:

```text
+--------------------------------+

| Total Ingredient               |

| Low Stock                      |

| Out Of Stock                   |

+--------------------------------+

| Inventory Table                |

+--------------------------------+
```

---

# 9. Stock Summary Card

Komponen:

```text
StockCard.vue
```

Menampilkan:

## Total Ingredient

Contoh:

```text
35 bahan
```

---

## Low Stock

Contoh:

```text
5 bahan
```

---

## Empty Stock

Contoh:

```text
2 bahan
```

---

# 10. Ingredient Table

Komponen:

```text
InventoryTable.vue
```

Menampilkan:

| Field         | Description  |
| ------------- | ------------ |
| Ingredient    | Nama bahan   |
| Unit          | kg/pcs/liter |
| Current Stock | Jumlah stok  |
| Status        | Kondisi stok |
| Action        | Detail       |

---

Contoh:

```text
Ikan Etong

12.5 kg

Available
```

---

# 11. Stock Status

Status frontend:

```text
Available

Low Stock

Out Of Stock
```

---

Contoh aturan:

```text
Stock > minimum

=

Available


Stock <= minimum

=

Low Stock


Stock = 0

=

Out Of Stock
```

---

# 12. Ingredient Detail

Route:

```text
/inventory/:id
```

Menampilkan:

- Detail bahan
- Current stock
- Stock movement
- Recent activity

---

# 13. Stock Movement History

Komponen:

```text
StockMovementTable.vue
```

Menampilkan:

| Type | Quantity | Date   |
| ---- | -------- | ------ |
| IN   | +10 kg   | 20 Oct |
| OUT  | -2 kg    | 21 Oct |

---

Source data:

```text
stock_movements
```

---

# 14. Stock Adjustment

Owner dapat melakukan koreksi stok.

Contoh:

Stok sistem:

```text
10 kg
```

Stok fisik:

```text
8 kg
```

Adjustment:

```text
-2 kg
```

---

Komponen:

```text
StockAdjustmentModal.vue
```

---

Form:

```text
Ingredient

Quantity

Type

Note
```

---

Type:

```text
IN

OUT
```

---

# 15. Adjustment API

Endpoint:

```http
POST /inventory/adjustments
```

---

Request:

```json
{
    "ingredient_id": 1,
    "quantity": 2,
    "type": "out",
    "note": "Stock opname"
}
```

---

# 16. Low Stock Monitoring

Komponen:

```text
LowStockAlert.vue
```

Menampilkan:

```text
Bahan hampir habis:

- Ikan Etong
- Udang
- Cumi
```

---

# 17. Decimal Handling

Karena restoran seafood menggunakan berat:

Contoh:

```text
Ikan Etong

2.5 kg
```

Frontend harus mendukung:

```text
decimal(10,3)
```

Tidak boleh menggunakan integer.

---

# 18. Currency Handling

Pada sprint ini:

Harga belum menjadi fokus.

Cost detail dibuat pada:

```text
Sprint 07 — Inventory Batch Cost
```

---

# 19. Error Handling

## Stock tidak cukup

Response:

```text
Stok tidak mencukupi.
```

---

## Invalid Quantity

```text
Jumlah stok tidak valid.
```

---

## API Error

```text
Gagal mengambil data inventory.
```

---

# 20. Loading State

Flow:

```text
Open Inventory

↓

Loading Table

↓

Display Stock
```

---

# 21. Performance Consideration

Inventory dapat bertambah besar.

Aturan:

Jangan:

```text
Ambil semua stock movement
```

---

Gunakan:

```text
Pagination

Filter

Date Range
```

---

# 22. Security

Inventory Adjustment hanya boleh dilakukan oleh:

- Owner
- Admin dengan permission inventory.update

Frontend hanya menyembunyikan menu.

Backend tetap melakukan validasi permission.

---

# 23. Testing Scenario

## Scenario 1

Open inventory.

Expected:

```text
Daftar bahan tampil.
```

---

## Scenario 2

Klik detail ingredient.

Expected:

```text
Stock history tampil.
```

---

## Scenario 3

Tambah adjustment stok.

Expected:

```text
Stock berubah.

Movement tercatat.
```

---

## Scenario 4

Stock tidak cukup.

Expected:

```text
Error muncul.
```

---

## Scenario 5

Bahan habis.

Expected:

```text
Out Of Stock badge muncul.
```

---

# 24. Task Checklist

## Inventory Page

- [x] Inventory Dashboard
- [x] Stock Summary
- [x] Ingredient Table

---

## Detail

- [x] Ingredient Detail
- [x] Movement History

---

## Adjustment

- [x] Adjustment Modal
- [x] Validation
- [x] Submit API

---

## Store

- [x] Inventory Store

---

## API

- [x] Inventory Service
- [x] Adjustment API

---

## UX

- [x] Loading
- [x] Empty State
- [x] Error State

---

## Testing

- [x] View Stock
- [x] Stock Detail
- [x] Adjustment
- [x] Low Stock

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat melihat stok bahan baku

✅ Owner dapat melihat histori stok

✅ Owner dapat melakukan stock adjustment

✅ Perubahan stok tercatat

✅ Low stock dapat diketahui

✅ Decimal quantity berjalan

✅ Permission dasar diterapkan

---

# Output Sprint

Hasil akhir Sprint 06:

```text
Owner

↓

Inventory Management

↓

Stock Monitoring

↓

Stock Adjustment

↓

Inventory Control
```

Setelah sprint ini selesai, Owner sudah memiliki kontrol dasar terhadap stok bahan baku restoran.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 07 — Inventory Batch Cost

Fitur yang akan dibuat:

- Inventory Batch List
- FIFO Cost Tracking
- Average Cost
- Ingredient Cost Analysis
- Stock Valuation
- Cost History

Sprint berikutnya akan menghubungkan inventory dengan perhitungan biaya bahan baku.
