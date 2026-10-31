# Web Admin / Owner Dashboard Sprint 10

# Authorization and Permission

## Status

Completed

---

# Tujuan Sprint

Membangun sistem **Authorization & Permission** untuk mengatur hak akses setiap pengguna dalam aplikasi Web Admin / Owner Dashboard.

Sprint ini melanjutkan:

```text id="5xj8sa"
Sprint 09

Employee Management

↓

Sprint 10

Authorization & Permission
```

Tujuan utama:

- Membatasi akses berdasarkan role
- Mengatur permission secara fleksibel
- Melindungi halaman frontend
- Melindungi API backend
- Menyiapkan sistem keamanan untuk pengembangan berikutnya

---

# 1. Business Context

Tidak semua pengguna boleh mengakses seluruh fitur.

Contoh:

## Owner

Boleh:

```text id="1o4qmp"
Dashboard

Reporting

Inventory

Employee

Settings
```

---

## Kasir

Boleh:

```text id="7k3i4f"
Order

Payment
```

Tidak boleh:

```text id="k8z3po"
Inventory Cost

Employee Management
```

---

## Kitchen

Boleh:

```text id="8m4c7d"
Kitchen Display
```

Tidak boleh:

```text id="x7h4v2"
Sales Report

Employee
```

---

Tanpa authorization:

```text id="l8j4hq"
User Login

↓

Semua Menu Terlihat

↓

Semua API Bisa Dipanggil
```

Ini adalah security risk.

---

# 2. Hubungan Dengan Backend Existing

Menggunakan:

```text id="4d1f7a"
Spatie Laravel Permission
```

---

Struktur:

```text id="6p4q1e"
users

↓

roles

↓

permissions

↓

model_has_roles

↓

role_has_permissions
```

---

Flow:

```text id="3u7x5a"
Employee

↓

Assign Role

↓

Role memiliki Permission

↓

User mendapat akses
```

---

# 3. Scope Sprint

## Yang dibuat:

- Role Management
- Permission Management
- Role Permission Mapping
- User Role Assignment
- Route Protection
- Menu Visibility Control
- Frontend Permission Guard

---

## Tidak termasuk:

- Approval workflow

- Custom permission builder kompleks

- Multi-level hierarchy permission

---

# 4. Authorization Architecture

Flow:

```text id="w9p4kc"
User Login

↓

Laravel Sanctum Token

↓

Load User Role

↓

Load Permission

↓

Frontend Store

↓

Control Access
```

---

# 5. Permission Design

Gunakan format:

```text id="5jz9vq"
resource.action
```

---

Contoh:

## Employee

```text id="4g8pkl"
employee.view

employee.create

employee.update
```

---

## Inventory

```text id="m2h8bd"
inventory.view

inventory.adjust
```

---

## Reporting

```text id="u6c3ad"
report.view
```

---

# 6. Default Role

Role awal:

```text id="9x0vkc"
Owner

Admin

Kasir

Kitchen
```

---

## Owner

Full access:

```text id="3a1m7z"
*
```

---

## Admin

Operational management:

```text id="k5n2qp"
employee.*

inventory.*

report.*
```

---

## Kasir

POS:

```text id="9e7x1b"
order.create

order.update

payment.create
```

---

## Kitchen

Kitchen:

```text id="4b6f9m"
kitchen.view

kitchen.update
```

---

# 7. Backend Implementation

## Permission Seeder

Buat:

```text id="w7v4xp"
PermissionSeeder.php
```

---

Contoh:

```php
Permission::create([
    'name' => 'inventory.view'
]);
```

---

## Role Seeder

Buat:

```text id="s3n8kc"
RoleSeeder.php
```

---

Contoh:

```php
$owner = Role::create([
    'name'=>'owner'
]);

$owner->givePermissionTo(
    Permission::all()
);
```

---

# 8. API Requirement

## Get Current User Permission

Endpoint:

```http id="8z3w4r"
GET /auth/me
```

---

Response:

```json id="7r9q2m"
{
    "user": {
        "id": 1,
        "name": "Owner"
    },

    "roles": ["owner"],

    "permissions": ["inventory.view", "employee.view"]
}
```

---

# 9. Frontend Permission Store

Buat:

```text id="3y8p4n"
stores/auth.store.js
```

---

Tambahkan:

```text id="5k9z2x"
roles

permissions
```

---

Method:

```javascript id="8w4m2b"
hasPermission();

hasRole();
```

---

Contoh:

```javascript
hasPermission("inventory.view");
```

---

Return:

```text id="m7v2qa"
true

false
```

---

# 10. Route Guard

Integrasi dengan router.

Contoh:

```javascript
{
 path:'/inventory',
 component:InventoryPage,
 meta:{
    permission:'inventory.view'
 }
}
```

---

Flow:

```text id="f2n8ka"
Open Page

↓

Check Permission

↓

Allowed

↓

Show Page
```

---

Jika tidak punya akses:

```text id="6j3p9x"
403 Forbidden Page
```

---

# 11. Menu Visibility

Sidebar harus dinamis.

Contoh:

Sebelum:

```text id="k7p1wd"
Dashboard

Inventory

Employee

Settings
```

---

Kasir:

```text id="1a9f6c"
Dashboard

Order

Payment
```

---

Implementasi:

```javascript
if (hasPermission("inventory.view")) {
    showMenu();
}
```

---

# 12. Role Management Page

Buat:

```text id="n5b8qy"
resources/js/admin/pages/roles/
```

---

Struktur:

```text id="3v6m1s"
roles/

├── RolePage.vue

├── components/

│   ├── RoleTable.vue
│   ├── PermissionMatrix.vue
│   └── RoleFormModal.vue

└── role.service.js
```

---

# 13. Permission Matrix

Component:

```text id="p6x2vb"
PermissionMatrix.vue
```

---

Tampilan:

| Permission     | Owner | Admin | Kasir |
| -------------- | ----- | ----- | ----- |
| inventory.view | ✓     | ✓     | -     |
| employee.view  | ✓     | ✓     | -     |
| order.create   | ✓     | ✓     | ✓     |

---

Tujuan:

Owner dapat memahami akses setiap role.

---

# 14. User Role Assignment

Dari Employee Detail:

```text id="7m4x9c"
Employee

↓

Role

↓

Permission
```

---

Contoh:

```text id="k2y8qs"
Andi

Role:

Kitchen
```

---

Permission otomatis:

```text id="n8r4vb"
Kitchen Access
```

---

# 15. API Security

Frontend bukan security utama.

Backend wajib:

```text id="z5x8qm"
Middleware Permission
```

---

Contoh:

```php
Route::get(
'/inventory',
InventoryController::class
)
->middleware(
'permission:inventory.view'
);
```

---

# 16. Error Handling

## Tidak memiliki akses

Response:

```json id="9v2kqa"
{
    "message": "You do not have permission."
}
```

HTTP:

```text id="5r8wcm"
403
```

---

# 17. Audit Preparation

Setiap permission check harus siap untuk dicatat.

Contoh:

```text id="x2m7qp"
User

Action

Resource

Result
```

Akan digunakan Sprint 11:

```text id="c9n3vf"
Audit Log
```

---

# 18. Testing Scenario

## Scenario 1

Owner login.

Expected:

```text id="r5m8dz"
Semua menu muncul.
```

---

## Scenario 2

Kasir membuka inventory.

Expected:

```text id="x8p4na"
403 Forbidden.
```

---

## Scenario 3

Admin membuka employee.

Expected:

```text id="w3q9kf"
Berhasil.
```

---

## Scenario 4

Role permission berubah.

Expected:

```text id="b7n2cx"
Akses berubah tanpa update code.
```

---

## Scenario 5

API dipanggil langsung tanpa permission.

Expected:

```text id="j4m8qp"
Request ditolak.
```

---

# 19. Task Checklist

## Backend

- [x] Permission Seeder
- [x] Role Seeder
- [x] Middleware Setup
- [x] Permission API

---

## Frontend

- [x] Permission Store
- [x] Route Guard
- [x] Dynamic Sidebar
- [x] Role Page

---

## Management

- [x] Role Assignment
- [x] Permission Matrix

---

## Testing

- [x] Role Access
- [x] API Protection
- [x] Menu Visibility
- [x] Forbidden Page

---

# Definition of Done

Sprint dianggap selesai jika:

✅ User memiliki role

✅ Role memiliki permission

✅ Permission dapat dikelola

✅ Route frontend terlindungi

✅ API backend terlindungi

✅ Menu tampil berdasarkan akses

---

# Output Sprint

Hasil akhir Sprint 10:

```text id="v6p2qa"
Employee

↓

Role

↓

Permission

↓

Secure Application
```

Setelah sprint ini selesai, aplikasi memiliki fondasi keamanan yang benar sebelum masuk ke Audit Log.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 11 — Audit Log

Fitur:

- Activity Tracking
- User Action History
- Data Change History
- Security Monitoring
- Audit Viewer

```

```
