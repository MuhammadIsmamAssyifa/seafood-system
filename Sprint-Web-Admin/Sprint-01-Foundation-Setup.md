# Web Admin / Owner Dashboard Sprint 01

# Foundation Setup

## Status

Completed

---

# Tujuan Sprint

Membangun fondasi Web Admin / Owner Dashboard agar seluruh fitur berikutnya dapat dikembangkan secara konsisten, scalable, dan mudah dipelihara.

Sprint ini **tidak membuat fitur bisnis**, tetapi menyiapkan seluruh infrastruktur frontend.

Pada akhir sprint ini aplikasi sudah memiliki:

- Vue 3
- Vite
- Pinia
- Vue Router
- Axios
- Theme
- Layout
- Sidebar
- Topbar
- API Client
- Global Components
- Error Handling
- Authentication Preparation

---

# 1. Scope Sprint

Yang dibuat:

- Vue 3 Setup
- Vite Configuration
- Pinia
- Vue Router
- Axios Client
- Folder Structure
- Theme
- Layout
- Sidebar
- Topbar
- Loading Component
- Modal Component
- Notification Component
- Environment Configuration

Belum termasuk:

- Login
- Dashboard
- Reporting
- Inventory
- Employee
- Authorization

---

# 2. Technology Stack

## Backend

Laravel 12

---

## Frontend

Vue 3

---

## Build Tool

Vite

---

## State Management

Pinia

Digunakan untuk:

- Authentication
- User
- Notification
- Global State

---

## Routing

Vue Router

Digunakan untuk:

- Navigation
- Route Guard
- Lazy Loading

---

## HTTP Client

Axios

Digunakan untuk:

- REST API
- Interceptor
- Token Injection
- Error Handling

---

## Styling

Tailwind CSS

---

## Icons

Heroicons

---

## Date

Day.js

---

## Chart

Belum digunakan.

Akan dipasang pada Sprint Reporting.

---

# 3. Project Structure

```text
resources/

├── js/

│   ├── admin/

│   │
│   ├── app.js
│   │
│   ├── router/
│   │   ├── index.js
│   │   └── routes.js
│   │
│   ├── stores/
│   │   ├── auth.store.js
│   │   ├── user.store.js
│   │   └── notification.store.js
│   │
│   ├── services/
│   │   ├── api.js
│   │   ├── auth.service.js
│   │   └── storage.service.js
│   │
│   ├── layouts/
│   │   ├── DefaultLayout.vue
│   │   └── AuthLayout.vue
│   │
│   ├── components/
│   │   ├── common/
│   │   ├── layout/
│   │   ├── form/
│   │   └── feedback/
│   │
│   ├── pages/
│   │
│   ├── composables/
│   │
│   ├── utils/
│   │
│   └── constants/
│
└── views/

    └── admin.blade.php
```

---

# 4. Blade Entry Point

Gunakan satu Blade sebagai host Vue.

```text
resources/views/admin.blade.php
```

Fungsi:

- Load Vue
- Load Vite
- Mount SPA

Tidak ada logic bisnis di Blade.

---

# 5. Vue Entry Point

Buat:

```text
resources/js/admin/app.js
```

Bertugas:

- Create Vue App
- Register Pinia
- Register Router
- Mount App

---

# 6. Environment Configuration

Gunakan:

```env
VITE_APP_NAME=

VITE_API_URL=

VITE_PUSHER_APP_KEY=

VITE_PUSHER_CLUSTER=
```

Contoh:

```env
VITE_API_URL=http://127.0.0.1:8000/api/v1
```

---

# 7. API Client

Buat:

```text
services/api.js
```

Fitur:

- Axios Instance
- Base URL
- Timeout
- Request Interceptor
- Response Interceptor

---

## Request

Tambahkan otomatis:

```http
Authorization: Bearer TOKEN
```

---

## Response

Handle:

- 200
- 201
- 401
- 403
- 404
- 422
- 500

---

# 8. Local Storage

Buat:

```text
storage.service.js
```

Digunakan untuk:

- Access Token
- User Session
- Remember Me

---

# 9. Routing

Gunakan Vue Router.

Struktur:

```text
/

↓

login

↓

dashboard

↓

report

↓

inventory

↓

employee

↓

setting
```

Belum semua halaman dibuat.

Hanya routing dasar.

---

# 10. Route Guard

Flow:

```text
Open App

↓

Token?

↓

Ya

↓

Dashboard

↓

Tidak

↓

Login
```

Semua halaman admin harus melewati autentikasi.

---

# 11. Layout

Buat dua layout.

## Default Layout

Untuk seluruh halaman admin.

Berisi:

- Sidebar
- Topbar
- Content
- Notification Area

---

## Auth Layout

Digunakan untuk:

- Login
- Forgot Password (jika ada)

---

# 12. Sidebar Preparation

Menu sementara:

```text
Dashboard

Reporting

Orders

Inventory

Employees

Settings
```

Belum memiliki fitur.

---

# 13. Topbar Preparation

Berisi:

- Nama User
- Outlet Aktif
- Profile
- Logout

---

# 14. Theme

Buat:

```text
theme/

colors.js

typography.js
```

Warna utama:

- Primary
- Secondary
- Success
- Warning
- Danger
- Background
- Surface

---

# 15. Global Components

Buat komponen reusable.

## Button

```text
BaseButton.vue
```

---

## Input

```text
BaseInput.vue
```

---

## Card

```text
BaseCard.vue
```

---

## Modal

```text
BaseModal.vue
```

---

## Loading

```text
BaseLoading.vue
```

---

## Empty State

```text
BaseEmpty.vue
```

---

## Confirm Dialog

```text
BaseConfirm.vue
```

---

## Toast

```text
BaseToast.vue
```

---

# 16. Notification Store

Buat:

```text
notification.store.js
```

Digunakan untuk:

- Success
- Error
- Warning
- Info

---

# 17. Error Handling

Buat handler global.

Contoh:

```text
401

↓

Redirect Login
```

---

```text
422

↓

Validation Message
```

---

```text
500

↓

Global Error Dialog
```

---

# 18. Loading State

Seluruh request API menggunakan loading global.

Flow:

```text
Request

↓

Loading

↓

Success

↓

Hide Loading
```

---

# 19. Folder Convention

Gunakan struktur per fitur.

Contoh:

```text
pages/

dashboard/

inventory/

employee/

report/

setting/
```

Setiap fitur nantinya memiliki:

- components
- pages
- services (jika diperlukan)

---

# 20. Development Convention

Gunakan standar berikut:

- Composition API
- `<script setup>`
- Pinia untuk global state
- Axios untuk seluruh request
- Tidak menggunakan jQuery
- Tidak menggunakan inline JavaScript
- Semua komponen reusable ditempatkan di `components/`

---

# 21. Task Checklist

## Setup

- [x] Install Vue 3
- [x] Install Vite
- [x] Install Pinia
- [x] Install Vue Router
- [x] Install Axios
- [x] Install Day.js
- [x] Install Heroicons

---

## Structure

- [x] Create Folder Structure
- [x] Create Layout
- [x] Create Components
- [x] Create Services

---

## API

- [x] Axios Instance
- [x] Interceptor
- [x] Error Handler

---

## Routing

- [x] Vue Router
- [x] Route Guard
- [x] Layout Routing

---

## UI

- [x] Sidebar
- [x] Topbar
- [x] Loading
- [x] Modal
- [x] Toast

---

## Testing

- [x] Vue berhasil di-mount
- [x] Routing berjalan
- [x] Axios dapat request API
- [x] Layout tampil dengan benar

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Vue 3 berhasil berjalan di dalam project Laravel

✅ Vite berhasil melakukan build

✅ Pinia aktif

✅ Vue Router berjalan

✅ Axios dapat melakukan request API

✅ Layout Admin tampil

✅ Sidebar dan Topbar tersedia

✅ Global Components siap digunakan

✅ Error Handling sudah tersedia

---

# Output Sprint

Hasil akhir Sprint 01:

```text
Web Admin Dashboard

↓

Laravel

↓

Vue 3 SPA

↓

Pinia

↓

Vue Router

↓

Axios

↓

Tailwind CSS

↓

Reusable Components

↓

Ready for Feature Development
```

Sprint ini menghasilkan fondasi frontend yang stabil dan konsisten sehingga seluruh sprint berikutnya dapat fokus pada implementasi fitur bisnis tanpa perlu mengubah arsitektur aplikasi.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 02 — Authentication

Fitur yang akan dibuat:

- Login Owner/Admin
- Session Management
- Route Protection
- Logout
- User Profile
- Authentication Flow
- Permission Preparation

Setelah Sprint 02 selesai, Web Admin sudah dapat digunakan untuk autentikasi dan siap memasuki pengembangan Dashboard Overview.
