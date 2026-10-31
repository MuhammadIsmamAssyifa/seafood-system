# Web Admin / Owner Dashboard Sprint 09

# Employee Management

## Status

Completed

---

# Tujuan Sprint

Membangun modul **Employee Management** untuk mengelola data karyawan yang menggunakan sistem restoran.

Sprint ini menjadi dasar sebelum membangun:

- Authorization & Permission
- Audit Log
- User Activity Tracking

Karena sistem membutuhkan identitas pengguna yang jelas:

```text
Siapa yang login?

Siapa yang melakukan perubahan?

Siapa yang melakukan transaksi?
```

---

# 1. Business Context

Pada restoran terdapat beberapa jenis pengguna:

Contoh:

```text
Owner

Admin

Kasir

Kitchen
```

Setiap pengguna memiliki tanggung jawab berbeda.

Tanpa Employee Management:

```text
User

↓

Tidak jelas siapa orangnya
```

Dengan Employee Management:

```text
Employee

↓

User Account

↓

Activity History
```

---

# 2. Hubungan Dengan Backend Existing

Menggunakan struktur backend yang sudah ada:

```text
users

+

user_outlets

+

roles
```

---

Flow:

```text
Employee

↓

User Account

↓

Outlet Assignment

↓

Role Assignment
```

---

# 3. Scope Sprint

## Yang dibuat:

- Employee List
- Employee Detail
- Create Employee
- Edit Employee
- Activate / Deactivate Employee
- Outlet Assignment
- Basic Employee Information

---

## Tidak termasuk:

### Permission Management

Masuk Sprint 10.

---

### Payroll / Salary

Tidak masuk MVP.

---

### Attendance

Tidak masuk MVP.

---

### Shift Management

Ditunda karena kompleksitas.

---

# 4. Architecture

Flow:

```text
Owner

↓

Employee Management Page

↓

Laravel Employee API

↓

User Data

↓

Display Employee
```

---

# 5. Page Structure

Buat:

```text
resources/js/admin/pages/employees/
```

Struktur:

```text
employees/

├── EmployeePage.vue

├── EmployeeDetailPage.vue

├── components/

│   ├── EmployeeTable.vue
│   ├── EmployeeFormModal.vue
│   ├── EmployeeStatusBadge.vue
│   ├── EmployeeRoleBadge.vue
│   └── OutletAssignment.vue

└── employee.service.js
```

---

# 6. Employee Service

Buat:

```text
services/employee.service.js
```

Method:

```javascript
getEmployees();

getEmployeeDetail();

createEmployee();

updateEmployee();

updateStatus();

assignOutlet();
```

---

# 7. Employee Store

Buat:

```text
stores/employee.store.js
```

State:

```text
employees

selectedEmployee

roles

outlets

loading

error
```

---

Action:

```text
fetchEmployees()

fetchDetail()

saveEmployee()

changeStatus()

assignOutlet()
```

---

# 8. Employee List

Halaman utama:

```text
EmployeePage.vue
```

Menampilkan:

| Field    | Description    |
| -------- | -------------- |
| Name     | Nama karyawan  |
| Username | Account        |
| Role     | Jabatan        |
| Outlet   | Lokasi kerja   |
| Status   | Aktif/nonaktif |
| Action   | Detail/Edit    |

---

Contoh:

```text
Budi

kasir01

Kasir

Outlet Utama

Active
```

---

# 9. Employee Detail

Route:

```text
/employees/:id
```

---

Menampilkan:

```text
Employee Profile

↓

Account Information

↓

Outlet Assignment

↓

Role
```

---

Data:

```text
Nama

Username

Email

Role

Outlet

Status

Created Date
```

---

# 10. Create Employee

Component:

```text
EmployeeFormModal.vue
```

---

Form:

```text
Name

Username

Email

Password

Role

Outlet

Status
```

---

Contoh:

```text
Name:

Andi


Username:

andi.kitchen


Role:

Kitchen


Outlet:

Outlet Utama
```

---

# 11. Employee Status

Status awal:

```text
active

inactive
```

---

Tujuan:

Ketika karyawan berhenti:

Jangan hapus user.

Tetapi:

```text
active

↓

inactive
```

---

Alasan:

Data transaksi lama tetap memiliki referensi user.

---

# 12. Outlet Assignment

Component:

```text
OutletAssignment.vue
```

---

Mengelola:

```text
User

↓

Outlet
```

---

Contoh:

```text
Andi

Outlet Utama
```

---

Menggunakan:

```text
user_outlets
```

---

# 13. Employee Role Display

Component:

```text
EmployeeRoleBadge.vue
```

---

Menampilkan role:

```text
Owner

Admin

Kasir

Kitchen
```

---

Catatan:

Assignment permission belum dilakukan.

Hanya menampilkan role yang tersedia.

---

# 14. API Requirement

## Employee List

Endpoint:

```http
GET /employees
```

Response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Andi",
            "username": "andi",
            "status": "active",
            "roles": ["kitchen"]
        }
    ]
}
```

---

# 15. Create Employee API

Endpoint:

```http
POST /employees
```

Request:

```json
{
    "name": "Andi",
    "username": "andi",
    "email": "andi@mail.com",
    "password": "password",
    "role": "kitchen",
    "outlet_id": 1
}
```

---

Backend:

```text
Validate

↓

Create User

↓

Assign Role

↓

Assign Outlet
```

---

# 16. Update Employee

Endpoint:

```http
PUT /employees/{id}
```

Update:

```text
Name

Email

Role

Outlet
```

---

Password tidak wajib berubah.

---

# 17. Search & Filter

Employee dapat difilter:

## Search

```text
Nama

Username
```

---

## Role

```text
Owner

Admin

Kasir

Kitchen
```

---

## Status

```text
Active

Inactive
```

---

# 18. Permission Preparation

Walaupun permission belum dibuat, struktur harus siap.

Contoh:

```text
Employee Management

↓

require permission:

employee.view

employee.create

employee.update
```

---

Implementasi middleware dilakukan Sprint 10.

---

# 19. Security

Data employee termasuk data sensitif.

Frontend:

Tidak boleh menampilkan:

- Password
- Token

---

Backend:

Password wajib:

```text
hashed
```

Menggunakan:

```php
Hash::make()
```

---

# 20. Error Handling

## Username sudah digunakan

Response:

```text
Username sudah digunakan.
```

---

## Email sudah digunakan

```text
Email sudah digunakan.
```

---

## Outlet tidak ditemukan

```text
Outlet tidak tersedia.
```

---

## Role tidak valid

```text
Role tidak tersedia.
```

---

# 21. Performance Consideration

Jumlah employee kecil, tetapi tetap gunakan:

```text
Pagination

Search

Filter
```

---

Jangan:

```text
Load seluruh user tanpa batas
```

---

# 22. Testing Scenario

## Scenario 1

Create employee.

Expected:

```text
User berhasil dibuat.
```

---

## Scenario 2

Assign outlet.

Expected:

```text
User memiliki outlet.
```

---

## Scenario 3

Deactivate employee.

Expected:

```text
Status menjadi inactive.
```

---

## Scenario 4

Employee login.

Expected:

```text
Account masih dapat dikenali sistem.
```

---

## Scenario 5

Delete employee.

Expected:

```text
Tidak dilakukan.

Gunakan inactive.
```

---

# 23. Task Checklist

## Backend

- [x] Employee API
- [x] Employee Service
- [x] User Management
- [x] Outlet Assignment

---

## Frontend

- [x] Employee Page
- [x] Employee Table
- [x] Employee Form
- [x] Employee Detail

---

## Store

- [x] Employee Store

---

## UX

- [x] Loading
- [x] Empty State
- [x] Error State

---

## Testing

- [x] Create Employee
- [x] Update Employee
- [x] Status Change
- [x] Outlet Assignment

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat melihat daftar employee

✅ Owner dapat membuat employee

✅ Owner dapat mengubah employee

✅ Employee dapat memiliki outlet

✅ Employee dapat memiliki role dasar

✅ Status employee dapat dikelola

---

# Output Sprint

Hasil akhir Sprint 09:

```text
Owner

↓

Employee Management

↓

User Identity

↓

Ready For Authorization
```

Setelah Sprint 09 selesai, sistem memiliki fondasi pengguna yang jelas sehingga Sprint 10 dapat membangun Authorization & Permission dengan benar.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 10 — Authorization and Permission

Fitur:

- Role Management
- Permission Management
- User Role Assignment
- Access Control
- Menu Visibility
- Route Protection

```

```
