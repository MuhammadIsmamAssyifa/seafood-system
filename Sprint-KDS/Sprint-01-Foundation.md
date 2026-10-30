# Web Kitchen Display System Sprint 01

# Vue Foundation Setup

## Status

Completed

---

# Tujuan Sprint

Membangun fondasi awal **Kitchen Display System (KDS)** menggunakan Vue 3 di dalam project Laravel yang sudah ada.

Sprint ini belum membuat fitur operasional dapur seperti:

- Kitchen Queue
- Accept Order
- Preparing
- Ready
- Served

Fokus sprint ini adalah menyiapkan seluruh kebutuhan teknis agar KDS dapat dikembangkan sebagai aplikasi web interaktif berbasis Vue SPA.

---

# 1. Arsitektur Aplikasi

Arsitektur final:

```text
                 Flutter POS
                     |
                     |
                     v

              Laravel API
                     |
        +------------+------------+
        |                         |
        v                         v

    Vue KDS                 Vue Admin

```

Laravel bertanggung jawab untuk:

- Business Logic
- Authentication
- Authorization
- Database
- REST API
- Pusher Event

Vue bertanggung jawab untuk:

- UI
- State Management
- User Interaction
- Realtime Rendering

---

# 2. Technology Stack

## Backend

Sudah tersedia:

```text
Laravel 12
PostgreSQL
Laravel Sanctum
Pusher
```

---

## Frontend

Menggunakan:

```text
Vue 3

Vite

Pinia

Vue Router

Axios

Tailwind CSS

Pusher JS
```

---

# 3. Project Structure

Karena menggunakan satu project Laravel, struktur frontend berada di:

```text
resources/js/

├── admin/
│
├── kitchen/
│
├── shared/
│
├── app.js
└── bootstrap.js
```

---

# 4. Kitchen Application Structure

Folder khusus KDS:

```text
resources/js/kitchen/

├── App.vue

├── router/
│   └── index.js

├── stores/
│   ├── auth.store.js
│   └── kitchen.store.js

├── services/
│   ├── api.js
│   └── pusher.js

├── components/
│   ├── AppLayout.vue
│   ├── Sidebar.vue
│   ├── Header.vue
│   ├── Loading.vue
│   ├── EmptyState.vue
│   └── ErrorState.vue

├── pages/
│   ├── Login.vue
│   └── Dashboard.vue

└── composables/
```

---

# 5. Shared Structure

Komponen yang dapat digunakan Admin dan Kitchen.

```text
resources/js/shared/

├── api/

│   └── axios.js


├── components/

│   ├── BaseButton.vue
│   ├── BaseModal.vue
│   ├── BaseToast.vue
│   └── BaseLoading.vue


├── utils/

│   ├── formatter.js
│   └── helper.js


└── services/

    └── pusher.js
```

---

# 6. Vue Installation

Install dependency:

```bash
npm install vue

npm install vue-router

npm install pinia

npm install axios

npm install laravel-echo

npm install pusher-js
```

---

# 7. Vite Configuration

Konfigurasi entry point.

Target:

```text
resources/js/kitchen/main.js

resources/js/admin/main.js
```

Aplikasi dapat memiliki dua entry Vue berbeda:

```text
/kitchen

↓

Kitchen Vue App


/admin

↓

Admin Vue App
```

---

# 8. Laravel Route Integration

Laravel hanya menyediakan entry point.

Contoh:

```php
Route::view('/kitchen/{any?}', 'kitchen')
    ->where('any', '.*');


Route::view('/admin/{any?}', 'admin')
    ->where('any', '.*');
```

Vue Router menangani navigasi.

---

# 9. Vue Router Setup

Route awal KDS:

```text
/kitchen/login

/kitchen/dashboard
```

Flow:

```text
Open Application

↓

Check Authentication

↓

Authenticated

↓

Dashboard


Tidak Login

↓

Login Page
```

---

# 10. Pinia Setup

State management awal.

## Auth Store

Menyimpan:

```text
user

token

role

authenticated status
```

---

## Kitchen Store

Persiapan data dapur:

```text
orders

queue

loading

error
```

Belum ada logic bisnis.

---

# 11. Axios Setup

Membuat HTTP client.

Fungsi:

- Base URL
- Token Injection
- Error Handling

Contoh:

```javascript
axios.get("/api/v1/kitchen/orders");
```

---

# 12. Authentication Preparation

KDS menggunakan akun staff.

Flow:

```text
Login

↓

Laravel Sanctum

↓

Token

↓

Vue Store

↓

Dashboard
```

Token disimpan menggunakan:

```text
localStorage
```

atau mekanisme Laravel session sesuai implementasi final.

---

# 13. Pusher Preparation

Konfigurasi realtime.

Belum membuat event.

Struktur:

```text
Pusher

↓

Laravel Event

↓

Vue Listener
```

Contoh future event:

```text
OrderCreated

OrderUpdated

OrderReady
```

---

# 14. Layout Dasar KDS

Buat layout utama.

```text
+--------------------------------+

 Header

----------------------------------

 Sidebar | Content

         |

         |

----------------------------------

 Footer


+--------------------------------+
```

---

# 15. Theme Setup

Menggunakan Tailwind.

Color dasar:

```text
Primary

Background

Surface

Success

Warning

Danger

Text
```

---

# 16. Global Components

Komponen wajib:

## Loading

```text
Loading data...
```

---

## Empty State

```text
Belum ada order.
```

---

## Error State

```text
Terjadi kesalahan.

[Coba Lagi]
```

---

## Toast

Jenis:

```text
Success

Error

Warning
```

---

# 17. Environment Configuration

Pisahkan environment:

```text
Development

Production
```

Contoh:

```env
VITE_API_URL=http://127.0.0.1:8000/api/v1

VITE_PUSHER_APP_KEY=
VITE_PUSHER_HOST=
VITE_PUSHER_PORT=
```

---

# 18. Coding Convention

## Vue

Gunakan:

- Composition API
- Single File Component
- Pinia Store

---

## Folder

Pisahkan:

- components
- pages
- stores
- services

Jangan membuat file Vue terlalu besar.

---

# 19. Task Checklist

## Setup

- [x] Install Vue
- [x] Setup Vite
- [x] Setup Tailwind
- [x] Setup Vue Router
- [x] Setup Pinia

---

## Structure

- [x] Create kitchen folder
- [x] Create shared folder
- [x] Create admin placeholder

---

## API

- [x] Setup Axios
- [x] API interceptor
- [x] Error handler

---

## Authentication

- [x] Auth Store
- [x] Token handling
- [x] Route guard

---

## Realtime

- [x] Install Pusher JS
- [x] Setup Echo/Pusher client

---

## UI

- [x] Layout
- [x] Sidebar
- [x] Header
- [x] Loading
- [x] Empty State
- [x] Error State

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Vue berhasil berjalan di dalam Laravel

✅ Route `/kitchen` membuka aplikasi Vue

✅ Struktur KDS tersedia

✅ Vue Router berjalan

✅ Pinia berjalan

✅ Axios siap komunikasi API

✅ Authentication flow sudah disiapkan

✅ Pusher client sudah terkonfigurasi

✅ Layout dasar KDS selesai

---

# Output Sprint

Hasil akhir Sprint 01:

```text
Laravel Project

resources/js/

├── kitchen

│   ├── Vue App

│   ├── Router

│   ├── Store

│   ├── API Client

│   └── Layout


├── shared

│   ├── Components

│   ├── Utils

│   └── Services


└── admin (future)
```

Aplikasi belum memiliki fitur dapur, tetapi fondasi frontend sudah siap untuk membangun:

- Kitchen Dashboard
- Kitchen Queue
- Order Processing
- Realtime Notification

---

# Sprint Berikutnya

## Web Kitchen Display System Sprint 02 — Kitchen Authentication

Fitur yang akan dibuat:

- Login Staff Kitchen
- Sanctum Authentication
- Role Verification
- Session Handling
- Logout
- Route Protection
- Kitchen Profile

Setelah sprint ini selesai, pengguna dapur sudah dapat masuk ke KDS menggunakan akun staff yang berasal dari sistem Laravel.
