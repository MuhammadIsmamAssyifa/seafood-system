# Web Admin / Owner Dashboard Sprint 02

# Authentication

## Status

Completed

---

# Tujuan Sprint

Membangun sistem autentikasi untuk Web Admin / Owner Dashboard agar hanya pengguna yang memiliki hak akses yang dapat menggunakan aplikasi.

Sprint ini mencakup proses login, logout, session management, route protection, dan persiapan integrasi Authorization pada sprint berikutnya.

Pada akhir sprint ini, Owner dan Admin sudah dapat masuk ke sistem menggunakan akun yang telah dibuat pada backend Laravel.

---

# 1. Scope Sprint

Yang dibuat:

- Login Page
- Authentication Flow
- Session Management
- Remember Login
- Route Guard
- Logout
- Profile Dropdown
- Unauthorized Page
- Authentication Store

Belum termasuk:

- Dashboard
- Reporting
- Inventory
- Employee Management
- Permission Management

---

# 2. Business Context

Owner membutuhkan akses penuh terhadap Dashboard.

Admin memiliki akses sesuai Role & Permission.

Seluruh halaman Admin Dashboard wajib melalui proses autentikasi.

---

# 3. Authentication Flow

```text
Open Website

↓

Login

↓

Submit Credential

↓

Laravel API

↓

Success

↓

Store Token

↓

Get User Profile

↓

Dashboard
```

---

# 4. Login Screen

Buat halaman:

```text
pages/auth/LoginPage.vue
```

Field:

- Username / Email
- Password
- Remember Me
- Login Button

---

# 5. Login Validation

Validasi frontend:

Username:

- Required

Password:

- Required

Jika kosong:

```text
Username wajib diisi.

Password wajib diisi.
```

---

# 6. Login API

Gunakan endpoint backend:

```http
POST /auth/login
```

Body:

```json
{
    "username": "owner",
    "password": "password"
}
```

---

Response:

```json
{
    "token": "...",
    "user": {
        ...
    }
}
```

---

# 7. Authentication Store

Buat:

```text
stores/auth.store.js
```

State:

```text
token

user

isAuthenticated

loading
```

Action:

```text
login()

logout()

fetchUser()

clearSession()
```

---

# 8. Session Storage

Simpan:

- Access Token
- User Data
- Login Status

Menggunakan:

```text
storage.service.js
```

---

# 9. Axios Authentication

Request otomatis menambahkan:

```http
Authorization: Bearer TOKEN
```

Jika token tidak ada:

Tidak mengirim Authorization Header.

---

# 10. User Profile

Setelah login berhasil:

Request:

```http
GET /me
```

Digunakan untuk mengambil:

- Nama
- Username
- Role
- Restaurant
- Outlet

Disimpan ke Pinia.

---

# 11. Route Guard

Flow:

```text
Open Route

↓

Sudah Login?

↓

Ya

↓

Lanjut

↓

Tidak

↓

Redirect Login
```

---

# 12. Guest Guard

Jika user sudah login:

```text
/login

↓

Redirect Dashboard
```

Tidak boleh kembali ke halaman login.

---

# 13. Unauthorized Page

Buat halaman:

```text
pages/errors/403.vue
```

Ditampilkan ketika:

```text
HTTP 403
```

Isi halaman:

```text
Anda tidak memiliki hak akses.
```

---

# 14. Session Expired

Jika API mengembalikan:

```text
401 Unauthorized
```

Flow:

```text
Clear Session

↓

Redirect Login

↓

Tampilkan

"Sesi Anda telah berakhir."
```

---

# 15. Logout

Flow:

```text
Klik Logout

↓

POST /logout

↓

Clear Session

↓

Redirect Login
```

---

# 16. Topbar User Menu

Tambahkan dropdown:

```text
Nama User

↓

Profile

Logout
```

Profile detail akan dibuat pada sprint berikutnya.

---

# 17. Loading State

Saat login:

```text
Klik Login

↓

Loading

↓

Disable Button

↓

Success / Error
```

Mencegah double submit.

---

# 18. Error Handling

401

```text
Username atau Password salah.
```

---

422

```text
Tampilkan pesan validasi.
```

---

500

```text
Terjadi kesalahan pada server.
```

---

Network Error

```text
Tidak dapat terhubung ke server.
```

---

# 19. Folder Structure

```text
pages/

└── auth/

    ├── LoginPage.vue

    └── UnauthorizedPage.vue
```

---

Store:

```text
stores/

auth.store.js
```

---

Service:

```text
services/

auth.service.js
```

---

# 20. Authentication Workflow

```text
Login

↓

Receive Token

↓

Save Token

↓

Fetch User

↓

Store User

↓

Redirect Dashboard
```

---

# 21. Security Rules

Frontend tidak menentukan hak akses.

Frontend hanya:

- Menyembunyikan menu
- Mengarahkan halaman

Seluruh validasi permission tetap dilakukan oleh backend Laravel menggunakan Spatie Permission.

---

# 22. UI Components

Gunakan:

```text
BaseInput

BaseButton

BaseCard

BaseLoading

BaseToast
```

Tidak membuat komponen baru.

---

# 23. Testing Scenario

## Scenario 1

Credential benar.

Expected:

- Login berhasil
- Dashboard tampil

---

## Scenario 2

Password salah.

Expected:

- Login gagal
- Pesan error tampil

---

## Scenario 3

Token expired.

Expected:

- Logout otomatis
- Redirect Login

---

## Scenario 4

Refresh Browser.

Expected:

- Session tetap aktif
- Tidak perlu login ulang

---

## Scenario 5

Akses Dashboard tanpa login.

Expected:

- Redirect Login

---

## Scenario 6

Sudah login membuka halaman Login.

Expected:

- Redirect Dashboard

---

# 24. Task Checklist

## Authentication

- [x] Login Page
- [x] Login Form
- [x] Remember Me
- [x] Logout

---

## Store

- [x] Auth Store
- [x] User State
- [x] Token State

---

## API

- [x] Login API
- [x] Logout API
- [x] Get Profile API

---

## Routing

- [x] Auth Guard
- [x] Guest Guard
- [x] Redirect

---

## Session

- [x] Save Token
- [x] Load Session
- [x] Clear Session

---

## UI

- [x] Loading Login
- [x] Error Message
- [x] Unauthorized Page

---

## Testing

- [x] Login Success
- [x] Login Failed
- [x] Session Restore
- [x] Token Expired
- [x] Logout

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Login berhasil menggunakan akun backend

✅ Token tersimpan dengan aman

✅ User Profile berhasil dimuat

✅ Session tetap aktif setelah browser di-refresh

✅ Logout menghapus seluruh session

✅ Route Guard berjalan

✅ Guest Guard berjalan

✅ Unauthorized Page tersedia

---

# Output Sprint

Hasil akhir Sprint 02:

```text
User

↓

Login

↓

Laravel API

↓

Token

↓

Pinia Store

↓

Protected Route

↓

Dashboard Ready
```

Web Admin kini telah memiliki sistem autentikasi yang siap digunakan dan menjadi fondasi untuk seluruh fitur berikutnya.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 03 — Dashboard Overview

Fitur yang akan dibangun:

- Dashboard Home
- Summary Card
- Sales Today
- Total Orders
- Revenue
- Profit
- Best Selling Menu
- Recent Orders
- Quick Statistics
- Dashboard Layout Integration

Setelah Sprint 03 selesai, Owner akan memiliki halaman utama yang menampilkan ringkasan kondisi restoran secara real-time.
