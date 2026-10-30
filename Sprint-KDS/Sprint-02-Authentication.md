# Web Kitchen Display System Sprint 02

# Kitchen Authentication

## Status

Completed

---

# Tujuan Sprint

Membangun sistem autentikasi untuk pengguna Kitchen Display System (KDS).

Pada akhir sprint ini:

- Staff kitchen dapat login menggunakan akun yang sudah ada di sistem Laravel.
- Sistem dapat mengenali role pengguna.
- Halaman KDS hanya dapat diakses oleh user yang memiliki izin kitchen.
- Session pengguna dapat dipertahankan.
- User dapat logout dengan aman.

Sprint ini belum membuat fitur operasional dapur.

Belum termasuk:

- Kitchen Queue
- Accept Order
- Preparing
- Ready
- Served

---

# 1. Business Flow

Flow login kitchen:

```text
Staff Kitchen

↓

Buka KDS

↓

Login

↓

Laravel Authentication

↓

Validasi User

↓

Cek Role Permission

↓

Generate Session / Token

↓

Dashboard Kitchen
```

Jika gagal:

```text
Login

↓

Invalid Credential

↓

Error Message

↓

Tetap di Login Page
```

---

# 2. Authentication Architecture

Menggunakan sistem yang sudah ada:

```text
Laravel

├── User Model
├── Sanctum
├── Spatie Permission
└── Role Management
```

Frontend:

```text
Vue

├── Auth Store
├── Router Guard
└── Session Handler
```

---

# 3. Role Kitchen

Mengikuti backend yang sudah dibuat.

Contoh:

```text
Role:

- owner
- admin
- kasir
- kitchen
```

KDS hanya menerima:

```text
kitchen
```

atau permission:

```text
kitchen.access
```

---

# 4. API Endpoint

Menggunakan endpoint backend yang sudah tersedia / dibuat.

## Login

```http
POST /api/v1/login
```

Request:

```json
{
    "email": "kitchen@example.com",
    "password": "password"
}
```

Response:

```json
{
    "token": "xxxxxxxx",

    "user": {
        "id": 5,
        "name": "Chef Andi",
        "roles": ["kitchen"]
    }
}
```

---

## Current User

```http
GET /api/v1/me
```

Digunakan saat refresh halaman.

Response:

```json
{
    "id": 5,
    "name": "Chef Andi",
    "role": "kitchen"
}
```

---

## Logout

```http
POST /api/v1/logout
```

---

# 5. Frontend Structure

Tambah struktur:

```text
resources/js/kitchen/

├── pages/

│   ├── Login.vue
│   └── Dashboard.vue


├── stores/

│   └── auth.store.js


├── services/

│   └── auth.service.js


├── middleware/

│   └── auth.guard.js
```

---

# 6. Auth Store

Menggunakan Pinia.

File:

```text
auth.store.js
```

State:

```javascript
{
    user:null,

    token:null,

    authenticated:false,

    loading:false
}
```

---

## Method

```text
login()

logout()

getProfile()

checkAuth()
```

---

# 7. Login Page

UI:

```text
+-----------------------+

 Kitchen Display System


 Email

 [____________]


 Password

 [____________]


 [ Login ]

+-----------------------+
```

---

# 8. Login Validation

Validasi sebelum request.

## Email

Tidak boleh kosong.

---

## Password

Tidak boleh kosong.

---

Jika gagal:

```text
Email atau password salah.
```

---

# 9. Token Management

Setelah login berhasil:

```text
Token

↓

Auth Store

↓

Browser Storage
```

Token digunakan setiap request API.

Header:

```http
Authorization: Bearer TOKEN
```

---

# 10. Router Guard

Melindungi halaman KDS.

Flow:

```text
/kitchen/dashboard

↓

Check Auth

↓

Authenticated?

        Yes
         |
         ↓

     Dashboard


        No
         |
         ↓

       Login
```

---

# 11. Role Protection

Selain login, cek role.

Contoh:

```javascript
if (user.role !== "kitchen") {
    redirect("/unauthorized");
}
```

---

# 12. Session Restore

Saat browser refresh:

```text
Refresh Browser

↓

Load Token

↓

GET /me

↓

Restore User

↓

Dashboard
```

---

# 13. Logout Flow

Flow:

```text
Click Logout

↓

API Logout

↓

Clear Token

↓

Clear Store

↓

Login Page
```

---

# 14. Error Handling

Kasus:

## 401

```text
Session expired.

Silakan login kembali.
```

---

## 403

```text
Anda tidak memiliki akses.
```

---

## 422

```text
Input tidak valid.
```

---

# 15. Loading State

Saat login:

```text
Login

↓

Button Disabled

↓

Loading

↓

Response
```

Contoh:

```text
[ Loading... ]
```

---

# 16. Security

Yang wajib:

- Token tidak ditampilkan.
- Logout menghapus session.
- Route tidak bisa dibypass.
- Role tetap diverifikasi backend.

Frontend hanya sebagai UX.

Keamanan tetap di Laravel.

---

# 17. Task Checklist

## Backend

- [x] Pastikan endpoint login tersedia
- [x] Pastikan endpoint /me tersedia
- [x] Pastikan logout tersedia
- [x] Pastikan role kitchen tersedia

---

## Vue

### Authentication

- [x] Create Auth Store
- [x] Create Auth Service
- [x] Login Page
- [x] Logout Function
- [x] Session Restore

### Router

- [x] Route Guard
- [x] Protected Route
- [x] Unauthorized Handling

### UI

- [x] Login Form
- [x] Loading State
- [x] Error Message

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Staff kitchen dapat login

✅ Token tersimpan

✅ User dapat di-load ulang setelah refresh

✅ Dashboard hanya dapat dibuka user kitchen

✅ User non-kitchen ditolak

✅ Logout bekerja

✅ Error login tertangani

---

# Output Sprint

Hasil akhir Sprint 02:

```text
Kitchen Display System

Login

↓

Authentication

↓

Authorization

↓

Kitchen Dashboard Access
```

Staff dapur sudah memiliki akses masuk ke aplikasi KDS.

Namun halaman dashboard masih berupa placeholder.

---

# Sprint Berikutnya

## Web Kitchen Display System Sprint 03 — Kitchen Dashboard & Queue Foundation

Fitur yang akan dibuat:

- Dashboard Kitchen
- Menampilkan jumlah order:
    - Waiting
    - Preparing
    - Ready
    - Completed
- Kitchen Queue dasar
- Integrasi endpoint kitchen orders
- Tampilan order card

Sprint ini akan mulai masuk ke fitur utama operasional dapur.
