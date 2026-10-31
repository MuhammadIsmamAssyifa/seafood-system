# Web Admin / Owner Dashboard Sprint 11

# Audit Log

## Status

Completed

---

# Tujuan Sprint

Membangun modul **Audit Log** untuk mencatat seluruh aktivitas penting yang dilakukan pengguna di dalam sistem.

Sprint ini bertujuan memberikan:

- Transparansi aktivitas user
- Tracking perubahan data
- Monitoring keamanan
- Histori operasional restoran

---

# 1. Business Context

Dalam sistem restoran, banyak aktivitas penting yang mengubah data bisnis.

Contoh:

```text id="g2j5qs"
Kasir membuat order

↓

Kitchen menerima order

↓

Owner mengubah harga menu

↓

Admin menyesuaikan stok
```

Jika terjadi masalah:

```text id="q8w1px"
"Kenapa stok berkurang?"

"Siapa yang mengubah harga?"

"Siapa yang membatalkan order?"
```

Owner membutuhkan jawaban.

---

Tanpa Audit Log:

```text id="5m8kq3"
Data berubah

↓

Tidak diketahui siapa pelakunya
```

---

Dengan Audit Log:

```text id="7d4npy"
User

↓

Action

↓

Data

↓

Timestamp

↓

History
```

---

# 2. Hubungan Dengan Modul Sebelumnya

Audit Log menggunakan:

```text id="1j7m4z"
Employee Management

+

Authorization

+

Business Modules
```

---

Flow:

```text id="9v3xqa"
User melakukan aksi

↓

Permission Check

↓

Action berhasil

↓

Audit Record dibuat

↓

Owner dapat melihat history
```

---

# 3. Scope Sprint

## Yang dibuat:

- Audit Log Database
- Audit Service
- Activity Tracking
- Audit Log Viewer
- Filter Activity
- Detail Perubahan Data

---

## Tidak termasuk:

### Real-time Security Monitoring

Belum diperlukan untuk MVP.

---

### Notification jika ada aktivitas mencurigakan

Dapat ditambahkan kemudian.

---

# 4. Audit Log Architecture

Flow:

```text id="p8x4mw"
Controller

↓

Service

↓

Business Action

↓

Audit Service

↓

audit_logs table
```

---

Audit dibuat setelah transaksi berhasil.

Contoh:

```text id="4s7qdc"
Update Menu Price

↓

Save Menu

↓

Create Audit Log
```

---

# 5. Database Requirement

Buat tabel:

```text id="v3n8zk"
audit_logs
```

---

Structure:

```text id="5q1xpf"
audit_logs

id

user_id

outlet_id

action

module

model_type

model_id

old_values

new_values

ip_address

user_agent

created_at
```

---

# 6. Field Explanation

## user_id

Siapa yang melakukan.

Contoh:

```text id="4x9n2m"
User:
Budi
```

---

## action

Jenis aktivitas.

Contoh:

```text id="7m3qvp"
create

update

delete

login

logout
```

---

## module

Bagian sistem.

Contoh:

```text id="8w2zfk"
Order

Inventory

Employee

Menu
```

---

## old_values

Data sebelum perubahan.

Contoh:

```json id="2x9cqa"
{
    "price": 50000
}
```

---

## new_values

Data setelah perubahan.

Contoh:

```json id="6q8vmp"
{
    "price": 55000
}
```

---

# 7. Audit Service

Buat:

```text id="6v4qpa"
app/Services/AuditService.php
```

---

Method:

```php id="3m7qkf"
log()

getHistory()
```

---

Contoh:

```php
$auditService->log(
    user:$user,
    action:'update',
    model:$menu,
    old:$oldData,
    new:$newData
);
```

---

# 8. Automatic Model Tracking

Gunakan Laravel Model Observer.

Contoh:

```text id="7z1r5q"
MenuObserver

OrderObserver

InventoryObserver
```

---

Flow:

```text id="0k8pms"
Model Updated

↓

Observer Trigger

↓

Audit Created
```

---

# 9. Modul Yang Diaudit

Prioritas MVP:

## Order

```text id="1q8mws"
Create Order

Update Order

Cancel Order

Payment
```

---

## Inventory

```text id="4v7zpk"
Stock Adjustment

Waste

Batch Update
```

---

## Employee

```text id="9c2xmv"
Create User

Update User

Change Role
```

---

## Menu

```text id="8k5nqm"
Create Menu

Update Price

Deactivate Menu
```

---

# 10. Frontend Structure

Buat:

```text id="3f7kpd"
resources/js/admin/pages/audit/
```

---

Struktur:

```text id="9x4mcz"
audit/

├── AuditPage.vue

├── AuditDetailPage.vue

├── components/

│   ├── AuditTable.vue
│   ├── AuditFilter.vue
│   ├── AuditDetailModal.vue
│   └── ActivityBadge.vue

└── audit.service.js
```

---

# 11. Audit Service Frontend

Buat:

```text id="5r9kqm"
services/audit.service.js
```

---

Method:

```javascript id="2k8mvn"
getLogs();

getDetail();
```

---

# 12. Audit Store

Buat:

```text id="8m4qxs"
stores/audit.store.js
```

---

State:

```text id="1x7vqp"
logs

selectedLog

filters

loading

error
```

---

Action:

```text id="4w9kzm"
fetchLogs()

fetchDetail()
```

---

# 13. Audit Log Page

Halaman:

```text id="9p3xqa"
AuditPage.vue
```

---

Tampilan:

```text id="7h2mwc"
+--------------------------------+

| Audit History                  |

+--------------------------------+

| User | Action | Module | Time |

+--------------------------------+
```

---

# 14. Audit Table

Component:

```text id="3z8kqp"
AuditTable.vue
```

---

Column:

| Field       | Description |
| ----------- | ----------- |
| User        | Pelaku      |
| Action      | Aktivitas   |
| Module      | Modul       |
| Description | Detail      |
| Time        | Waktu       |

---

Contoh:

```text id="5y8nvd"
Budi

UPDATE

Menu

Mengubah harga Cumi

10:30
```

---

# 15. Audit Detail

Component:

```text id="8m2qfx"
AuditDetailModal.vue
```

---

Menampilkan:

```text id="2q7zmx"
User

Action

Before

After

Timestamp

IP Address
```

---

Contoh:

Before:

```json
{
    "price": 50000
}
```

After:

```json
{
    "price": 60000
}
```

---

# 16. Filter Audit

Filter:

## User

```text id="4k7pzm"
Budi

Andi
```

---

## Module

```text id="8x3mqv"
Order

Inventory

Employee
```

---

## Action

```text id="6n9qws"
Create

Update

Delete
```

---

## Date

```text id="7p4mzc"
Today

This Week

This Month
```

---

# 17. API Requirement

## Get Audit Logs

Endpoint:

```http id="9q5xmw"
GET /audit-logs
```

---

Response:

```json id="3v8kqa"
{
    "data": [
        {
            "user": "Owner",
            "action": "update",
            "module": "menu",
            "created_at": "2026-10-31"
        }
    ]
}
```

---

# 18. Security

Audit Log hanya boleh diakses:

```text id="5z7nqm"
Owner

Admin tertentu
```

---

Tidak boleh:

```text id="9w4xpk"
Kasir

Kitchen
```

---

Karena audit berisi:

- User activity
- Business operation
- Internal information

---

# 19. Data Retention

Untuk MVP:

Simpan semua data.

Tidak perlu:

```text id="7m8qpx"
Auto delete
```

---

Jika sistem berkembang:

Tambahkan:

- Archive
- Export
- Retention policy

---

# 20. Testing Scenario

## Scenario 1

User update menu.

Expected:

```text id="8k3mwp"
Audit record dibuat.
```

---

## Scenario 2

Stock adjustment.

Expected:

```text id="2x7qmv"
Old dan new value tersimpan.
```

---

## Scenario 3

User membuka audit page.

Expected:

```text id="5p9zqa"
History tampil.
```

---

## Scenario 4

Kasir membuka audit.

Expected:

```text id="7n4mxp"
403 Forbidden.
```

---

# 21. Task Checklist

## Backend

- [x] Audit Migration
- [x] Audit Model
- [x] Audit Service
- [x] Observer Setup
- [x] Audit API

---

## Frontend

- [x] Audit Page
- [x] Audit Table
- [x] Audit Detail
- [x] Filter Component

---

## Store

- [x] Audit Store

---

## Security

- [x] Permission Audit View
- [x] API Protection

---

## Testing

- [x] Create Log
- [x] Update Log
- [x] Delete Log
- [x] Permission Check

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Aktivitas penting tercatat

✅ User pelaku dapat diketahui

✅ Perubahan data dapat dibandingkan

✅ Owner dapat melihat histori

✅ Audit memiliki permission

---

# Output Sprint

Hasil akhir Sprint 11:

```text id="1q9mzw"
User Action

↓

Audit Trail

↓

Business Transparency

↓

System Accountability
```

Setelah Sprint 11 selesai, sistem memiliki kemampuan tracking perubahan sehingga lebih aman untuk operasional restoran.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 12 — System Settings

Fitur:

- Restaurant Profile
- Outlet Configuration
- Application Settings
- Printer Settings
- General Preferences

```

```
