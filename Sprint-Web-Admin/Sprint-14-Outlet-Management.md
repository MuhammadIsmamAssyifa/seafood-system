# Web Admin / Owner Dashboard Sprint 14

# Outlet Management

## Status

Completed

---

# Tujuan Sprint

Membangun modul **Outlet Management** untuk mengelola data cabang restoran dan mempersiapkan sistem agar siap berkembang menjadi **multi-outlet restaurant management system (RMS)**.

Sprint ini memastikan seluruh data operasional dapat terisolasi berdasarkan outlet:

- Menu
- Order
- Inventory
- Employee
- Reporting
- Configuration

---

# 1. Business Context

Saat ini sistem dimulai dari:

```text
1 Restaurant

1 Outlet
```

Namun desain database sudah diarahkan agar dapat berkembang:

```text
Restaurant

↓

Outlet A

Outlet B

Outlet C
```

---

Contoh perkembangan bisnis:

Awal:

```text
Seafood Bahari

Outlet Utama
```

Kemudian:

```text
Seafood Bahari

├── Outlet Jakarta

├── Outlet Bandung

└── Outlet Bekasi
```

---

Tanpa Outlet Management:

```text
Semua data bercampur

↓

Sulit mengetahui:

- Penjualan outlet mana?
- Stok outlet mana?
- Karyawan outlet mana?
```

---

Dengan Outlet Management:

```text
Restaurant

↓

Outlet

↓

Operational Data
```

---

# 2. Hubungan Dengan Modul Existing

Outlet Management menggunakan:

```text
Restaurant

+

User Outlet

+

Authorization

+

Inventory

+

Reporting
```

---

Flow:

```text
Owner

↓

Create Outlet

↓

Assign Employee

↓

Outlet memiliki:

- Menu
- Stock
- Order
- Report
```

---

# 3. Scope Sprint

## Yang dibuat:

- Outlet List
- Create Outlet
- Update Outlet
- Outlet Detail
- Outlet Status
- Employee Outlet Assignment
- Outlet Configuration
- Outlet Filter

---

## Tidak termasuk:

### Outlet Transfer

Contoh:

```text
Transfer stock

Outlet A → Outlet B
```

Ditunda.

---

### Central Inventory

Belum dibuat.

Karena setiap outlet masih memiliki inventory sendiri.

---

### Franchise Management

Tidak masuk MVP.

---

# 4. Outlet Architecture

Struktur:

```text
Restaurant

    |

    +---- Outlet 1

    |       |

    |       +---- Users

    |       +---- Orders

    |       +---- Inventory


    +---- Outlet 2

            |

            +---- Users

            +---- Orders
```

---

# 5. Database Existing

Menggunakan tabel:

```text
outlets
```

---

Structure:

```text
outlets

id

restaurant_id

name

code

address

phone

status

created_at

updated_at
```

---

# 6. Field Explanation

## restaurant_id

Outlet berada di restoran mana.

Contoh:

```text
Seafood Bahari
```

---

## name

Nama outlet.

Contoh:

```text
Seafood Bahari Jakarta
```

---

## code

Kode unik outlet.

Contoh:

```text
JKT01
```

---

## address

Alamat outlet.

---

## phone

Nomor outlet.

---

## status

Status operasional:

```text
active

inactive
```

---

# 7. Backend Structure

Buat:

```text
app/

├── Models/

│   └── Outlet.php


├── Services/

│   └── OutletService.php


├── Http/

│   ├── Controllers/

│   │   └── OutletController.php

│   │

│   ├── Requests/

│   │   └── OutletRequest.php

│   │

│   └── Resources/

│       └── OutletResource.php
```

---

# 8. Outlet Model

Relationship:

```php
restaurant()

users()

orders()

menus()

inventoryBatches()
```

---

Contoh:

```php
public function users()
{
    return $this->belongsToMany(User::class);
}
```

---

# 9. Outlet Service

Buat:

```text
app/Services/OutletService.php
```

---

Method:

```php
create()

update()

delete()

assignUser()

getDetail()
```

---

Contoh:

```php
createOutlet()
```

Flow:

```text
Validate

↓

Create Outlet

↓

Create Configuration

↓

Audit Log
```

---

# 10. Outlet CRUD API

## Get Outlet List

```http
GET /outlets
```

---

Response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Seafood Jakarta",
            "status": "active"
        }
    ]
}
```

---

## Create Outlet

```http
POST /outlets
```

Request:

```json
{
    "name": "Seafood Bandung",
    "code": "BDG01",
    "address": "Jl. Asia Afrika"
}
```

---

## Update Outlet

```http
PUT /outlets/{id}
```

---

## Detail Outlet

```http
GET /outlets/{id}
```

---

# 11. Frontend Structure

Buat:

```text
resources/js/admin/pages/outlets/
```

---

Struktur:

```text
outlets/

├── OutletPage.vue

├── OutletDetailPage.vue

├── components/

│   ├── OutletTable.vue
│   ├── OutletFormModal.vue
│   ├── OutletStatusBadge.vue
│   └── OutletEmployeeList.vue

└── outlet.service.js
```

---

# 12. Outlet Store

Buat:

```text
stores/outlet.store.js
```

---

State:

```text
outlets

selectedOutlet

loading

error
```

---

Action:

```text
fetchOutlets()

fetchDetail()

createOutlet()

updateOutlet()
```

---

# 13. Outlet List Page

Route:

```text
/outlets
```

---

Tampilan:

```
+--------------------------------+

| Outlet Management              |

+--------------------------------+

| Name | Code | Status | Action |

+--------------------------------+
```

---

Column:

| Field   | Description |
| ------- | ----------- |
| Name    | Nama outlet |
| Code    | Kode outlet |
| Address | Lokasi      |
| Status  | Aktif       |
| Action  | Detail/Edit |

---

# 14. Outlet Detail Page

Route:

```text
/outlets/{id}
```

---

Menampilkan:

```text
Outlet Information

↓

Employee

↓

Configuration

↓

Summary
```

---

Contoh:

```
Outlet Jakarta

Employee:
15 Staff

Orders Today:
120

Status:
Active
```

---

# 15. Employee Outlet Assignment

Integrasi:

```text
Employee Management

+

Outlet Management
```

---

Flow:

```text
Employee

↓

Assign Outlet

↓

User Outlet Record
```

---

Contoh:

```
Budi

Role:
Kasir

Outlet:
Jakarta
```

---

# 16. Outlet Switch Context

Untuk persiapan multi outlet.

User dapat memiliki:

```text
Outlet A

Outlet B
```

---

Contoh:

Owner:

```text
All Outlet
```

---

Kasir:

```text
Outlet Jakarta
```

---

Kitchen:

```text
Outlet Bandung
```

---

# 17. Outlet Filter

Tambahkan global filter:

```text
Outlet Selector
```

---

Digunakan pada:

- Dashboard
- Reporting
- Inventory
- Employee
- Audit Log

---

Contoh:

```
Current Outlet:

[ Jakarta ▼ ]
```

---

# 18. Permission

Permission:

```text
outlet.view

outlet.create

outlet.update

outlet.delete
```

---

Access:

## Owner

Full:

```text
CRUD
```

---

## Admin

```text
view

update
```

---

## Kasir

Tidak boleh.

---

## Kitchen

Tidak boleh.

---

# 19. Audit Integration

Aktivitas yang dicatat:

## Create Outlet

```text
Action:

CREATE

Module:

Outlet
```

---

## Update Outlet

Contoh:

```text
Status:

active

↓

inactive
```

---

## Assign Employee

```text
User:

Owner

Action:

ASSIGN_OUTLET
```

---

# 20. Validation

## Name

Required:

```text
true
```

---

## Code

Rules:

```text
required

unique per restaurant
```

---

## Status

Allowed:

```text
active

inactive
```

---

# 21. Testing Scenario

## Scenario 1

Owner membuat outlet baru.

Expected:

```
Outlet berhasil dibuat.
```

---

## Scenario 2

Assign employee ke outlet.

Expected:

```
User dapat mengakses outlet tersebut.
```

---

## Scenario 3

User membuka outlet lain.

Expected:

```
Ditolak jika tidak memiliki akses.
```

---

## Scenario 4

Owner melihat dashboard outlet tertentu.

Expected:

```
Data terfilter berdasarkan outlet.
```

---

## Scenario 5

Update outlet status.

Expected:

```
Audit Log tercatat.
```

---

# 22. Task Checklist

## Backend

- [x] Outlet Model
- [x] Outlet Service
- [x] Outlet Controller
- [x] Outlet Request
- [x] Outlet Resource
- [x] Outlet API

---

## Frontend

- [x] Outlet Page
- [x] Outlet Table
- [x] Outlet Form
- [x] Outlet Detail
- [x] Employee Assignment

---

## Security

- [x] Outlet Permission
- [x] Outlet Access Guard

---

## Integration

- [x] Audit Log
- [x] Employee Management
- [x] User Outlet

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat membuat outlet

✅ Outlet dapat dikelola

✅ Employee dapat ditempatkan ke outlet

✅ Akses outlet dapat dibatasi

✅ Data dapat difilter berdasarkan outlet

✅ Aktivitas tercatat di Audit Log

---

# Output Sprint

Hasil akhir Sprint 14:

```
Restaurant

↓

Multiple Outlet Ready

↓

Outlet Operational Control
```

Setelah Sprint 14 selesai, sistem sudah memiliki fondasi multi-outlet dan siap berkembang menjadi SaaS Restaurant Management System.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 15 — Final Integration & Optimization

Fitur:

- Global Search
- Dashboard Refinement
- Performance Optimization
- Security Review
- Production Preparation

```

```
