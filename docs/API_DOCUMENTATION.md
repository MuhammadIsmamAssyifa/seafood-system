# API Documentation Reference

Dokumentasi lengkap RESTful API Seafood Restaurant Management System (RMS).

- **Base URL**: `http://localhost/api/v1` (atau domain hosting)
- **Format Payload**: `application/json`
- **Autentikasi**: Bearer Token (Laravel Sanctum) pada header: `Authorization: Bearer <TOKEN>`

---

## 1. Format Amplop Standar (Response Envelope)

Seluruh endpoint API mengembalikan format JSON seragam dibungkus dalam Resource (`data`, `meta`, `links`):

### Sukses (200 / 201)
```json
{
  "data": { ... }
}
```

### Error Validasi (422 Unprocessable Content)
```json
{
  "message": "Meja sedang digunakan."
}
```

### Error Larangan Akses (403 Forbidden)
```json
{
  "message": "Anda tidak memiliki hak akses untuk tindakan ini.",
  "error": "forbidden"
}
```

### Error Batas Laju (429 Too Many Requests)
```json
{
  "message": "Too Many Attempts."
}
```

---

## 2. Authentication Endpoints

### 2.1 Login Web / Owner / Admin
- **Method**: `POST`
- **Endpoint**: `/login`
- **Throttle**: Maksimal 5x percobaan per menit
- **Request Body**:
```json
{
  "email": "owner@seafood.com",
  "password": "password123",
  "device_name": "Web Dashboard Chrome"
}
```
- **Response (200 OK)**:
```json
{
  "token": "1|sanctum_token_string_here",
  "user": {
    "id": 1,
    "name": "Owner Resto",
    "email": "owner@seafood.com",
    "username": "owner",
    "status": "active",
    "roles": ["owner"]
  }
}
```

### 2.2 Login Staff PIN (Mobile POS)
- **Method**: `POST`
- **Endpoint**: `/staff-login`
- **Request Body**:
```json
{
  "username": "kasir1",
  "pin": "1234",
  "device_name": "POS-Tablet-Kasir-01"
}
```

### 2.3 Logout (Revoke Current Device)
- **Method**: `POST`
- **Endpoint**: `/logout`
- **Auth**: Wajib
- **Response (200 OK)**:
```json
{
  "message": "Logged out successfully"
}
```

### 2.4 Logout All Devices
- **Method**: `POST`
- **Endpoint**: `/logout-all`
- **Auth**: Wajib
- **Response (200 OK)**:
```json
{
  "message": "All device tokens revoked successfully"
}
```

---

## 3. Catalog Module

### 3.1 Daftar Menu Katalog
- **Method**: `GET`
- **Endpoint**: `/catalog/menus`
- **Permission**: `menu.view`
- **Cache**: 5 menit (`catalog_menus_outlet_{id}`)
- **Response (200 OK)**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "Gurame Bakar",
      "type": "seafood",
      "variants": [
        {
          "id": 1,
          "name": "Gurame Bakar Porsi",
          "price": 65000.0,
          "sales_unit": "portion",
          "cooking_method": { "id": 1, "name": "Bakar" },
          "kitchen_station": { "id": 1, "code": "GRILL", "name": "Grill Station" }
        }
      ]
    }
  ]
}
```

### 3.2 Daftar Kitchen Stations
- **Method**: `GET`
- **Endpoint**: `/catalog/stations`
- **Permission**: `menu.view|kitchen.view`

---

## 4. Orders Module

### 4.1 Daftar Pesanan (Paginated)
- **Method**: `GET`
- **Endpoint**: `/orders?page=1&per_page=15&status=active`
- **Permission**: `order.view`

### 4.2 Buat Pesanan Baru (Draft)
- **Method**: `POST`
- **Endpoint**: `/orders`
- **Permission**: `order.create`
- **Request Body**:
```json
{
  "order_table_id": 1,
  "customer_name": "Pak Haji Sutrisno",
  "guest_count": 4
}
```

### 4.3 Tambah Item ke Pesanan
- **Method**: `POST`
- **Endpoint**: `/orders/{order_id}/items`
- **Permission**: `order.create|order.update`
- **Request Body**:
```json
{
  "menu_variant_id": 1,
  "quantity": 2
}
```

### 4.4 Kirim Batch ke Dapur
- **Method**: `POST`
- **Endpoint**: `/order-batches/{batch_id}/submit`
- **Permission**: `order.create|order.update`
- **Realtime Trigger**: Mengirim event Pusher `kds.batch.submitted` ke channel KDS.

---

## 5. Dynamic Price & Weight Adjustment

### 5.1 Ajukan Perubahan Bobot / Harga Seafood
- **Method**: `POST`
- **Endpoint**: `/order-items/{item_id}/changes`
- **Permission**: `order.change`
- **Request Body**:
```json
{
  "type": "quantity_change",
  "new_quantity": 1.25,
  "new_price": 125000,
  "reason": "Bobot ikan kakap hasil timbangan pasar seberat 1.25 kg"
}
```

### 5.2 Konfirmasi Persetujuan Pelanggan
- **Method**: `PATCH`
- **Endpoint**: `/order-item-changes/{change_id}/approve`
- **Permission**: `order.change|order.update`
- **Realtime Trigger**: Pusher `kds.item.changed`.

### 5.3 Pelanggan Menolak Perubahan
- **Method**: `PATCH`
- **Endpoint**: `/order-item-changes/{change_id}/reject`
- **Permission**: `order.change|order.update`

---

## 6. Kitchen Display System (KDS)

### 6.1 Antrean Masakan Aktif
- **Method**: `GET`
- **Endpoint**: `/kitchen/orders`
- **Permission**: `kitchen.view`

### 6.2 Dapur Menerima Batch
- **Method**: `PATCH`
- **Endpoint**: `/kitchen/batches/{batch_id}/accept`
- **Permission**: `kitchen.update`

### 6.3 Dapur Memasak Item (Potong Stok FIFO & Catat COGS)
- **Method**: `PATCH`
- **Endpoint**: `/kitchen/items/{item_id}/prepare`
- **Permission**: `kitchen.update`

### 6.4 Makanan Matang (Siap Saji)
- **Method**: `PATCH`
- **Endpoint**: `/kitchen/items/{item_id}/ready`
- **Permission**: `kitchen.update`

### 6.5 Makanan Disajikan ke Meja
- **Method**: `PATCH`
- **Endpoint**: `/kitchen/items/{item_id}/serve`
- **Permission**: `kitchen.update`

---

## 7. Payments Module

### 7.1 Ringkasan Pembayaran Pesanan
- **Method**: `GET`
- **Endpoint**: `/orders/{order_id}/payments/summary`
- **Permission**: `order.view|payment.view`

### 7.2 Eksekusi Pembayaran Kasir (Support Split Payment)
- **Method**: `POST`
- **Endpoint**: `/orders/{order_id}/payments`
- **Permission**: `payment.create`
- **Request Body**:
```json
{
  "method": "cash",
  "amount": 200000,
  "received_amount": 200000
}
```

---

## 8. Reporting & Analytics Suite

| Endpoint | Method | Permission | Deskripsi |
|---|---|---|---|
| `/dashboard/summary` | `GET` | `dashboard.view` | Ringkasan omzet hari ini, HPP, laba kotor, okupansi meja, low-stock alert, 5 order terbaru |
| `/reports/sales` | `GET` | `report.view` | Laporan omzet kotor, diskon, omzet bersih, AOV, tren harian (`start_date`, `end_date`) |
| `/reports/payments` | `GET` | `report.view` | Rekapitulasi uang masuk per metode bayar (Cash/Transfer) dan log transaksi kasir |
| `/reports/menu-sales` | `GET` | `report.view` | Peringkat menu terlaris (Best Seller), omzet, HPP, margin %, revenue share |
| `/reports/inventory` | `GET` | `report.view` | Valuasi rupiah seluruh aset stok aktif di gudang dapur |
| `/reports/stock-movements` | `GET` | `report.view` | Audit trail mutasi keluar-masuk stok (In, Sale, Waste, Adjustment) |
| `/reports/profit/summary` | `GET` | `report.view` | Rekapitulasi laba riil outlet berbasis modal beli bahan baku FIFO |
| `/reports/orders/{id}/profit` | `GET` | `report.view` | Rincian laba kotor dan margin per item pada order tertentu |
| `/audit-logs` | `GET` | `report.view\|user.manage` | Jejak audit riwayat aktivitas pengguna (filter: action, user, date) |

