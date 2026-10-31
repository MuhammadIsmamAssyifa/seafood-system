# Web Admin / Owner Dashboard Sprint 12

# System Settings

## Status

Completed

---

# Tujuan Sprint

Membangun modul **System Settings** sebagai pusat konfigurasi sistem yang digunakan Owner untuk mengatur perilaku aplikasi tanpa melakukan perubahan kode.

Sprint ini bertujuan menyediakan pengaturan:

- Informasi restoran
- Konfigurasi outlet
- Printer
- Pengaturan POS
- Pengaturan operasional
- Preferensi aplikasi

---

# 1. Business Context

Sistem restoran tidak selalu memiliki konfigurasi yang sama.

Contoh:

Restoran A:

```text
Nama:

Seafood Bahari


Printer:

Kitchen Printer


Payment:

Cash + Transfer
```

Restoran B:

```text
Nama:

Laut Nusantara


Printer:

Thermal Printer Kasir


Payment:

Cash Only
```

---

Tanpa System Settings:

```text
Developer

↓

Mengubah kode

↓

Deploy ulang
```

---

Dengan System Settings:

```text
Owner

↓

Update Configuration

↓

Sistem Berubah
```

---

# 2. Hubungan Dengan Modul Sebelumnya

System Settings menggunakan:

```text
Restaurant

+

Outlet

+

User Permission

+

POS Configuration
```

---

Flow:

```text
Owner

↓

System Settings

↓

Save Configuration

↓

Application Behavior Updated
```

---

# 3. Scope Sprint

## Yang dibuat:

- Restaurant Profile
- Outlet Configuration
- POS Configuration
- Printer Configuration
- Payment Configuration
- General Settings
- Settings API
- Settings Management UI

---

## Tidak termasuk:

### Advanced Configuration

Contoh:

- Workflow builder
- Custom formula
- Automation rule

---

### Multi Tenant Configuration

Akan mengikuti perkembangan SaaS.

---

# 4. Settings Architecture

Gunakan konsep:

```text
Key Value Configuration
```

---

Contoh:

```text
settings

key:

printer.cashier.enabled


value:

true
```

---

Flow:

```text
Application

↓

Read Settings

↓

Apply Configuration
```

---

# 5. Database Requirement

Buat tabel:

```text
settings
```

---

Structure:

```text
settings

id

restaurant_id

outlet_id nullable

key

value

type

created_at

updated_at
```

---

# 6. Field Explanation

## restaurant_id

Setting milik restoran.

Contoh:

```text
Tax Configuration
```

---

## outlet_id

Setting khusus outlet.

Contoh:

```text
Printer Kitchen
```

---

## key

Nama konfigurasi.

Contoh:

```text
pos.default_payment
```

---

## value

Nilai konfigurasi.

Contoh:

```text
cash
```

---

## type

Tipe data.

Contoh:

```text
string

boolean

integer

json
```

---

# 7. Settings Service

Buat:

```text
app/Services/SettingService.php
```

---

Method:

```php
get()

set()

getGroup()
```

---

Contoh:

```php
$settingService->get(
    'pos.default_payment'
);
```

---

Return:

```text
cash
```

---

# 8. Settings Group

Pisahkan konfigurasi berdasarkan kategori.

---

## Restaurant

```text
restaurant.name

restaurant.address

restaurant.phone
```

---

## POS

```text
pos.default_payment

pos.allow_discount

pos.require_customer_name
```

---

## Printer

```text
printer.cashier

printer.kitchen
```

---

## Inventory

```text
inventory.low_stock_threshold
```

---

# 9. Frontend Structure

Buat:

```text
resources/js/admin/pages/settings/
```

---

Struktur:

```text
settings/

├── SettingsPage.vue

├── components/

│   ├── RestaurantSetting.vue
│   ├── PosSetting.vue
│   ├── PrinterSetting.vue
│   ├── PaymentSetting.vue
│   └── SettingForm.vue

└── settings.service.js
```

---

# 10. Settings Service Frontend

Buat:

```text
services/settings.service.js
```

---

Method:

```javascript
getSettings();

updateSettings();
```

---

# 11. Settings Store

Buat:

```text
stores/settings.store.js
```

---

State:

```text
settings

loading

error
```

---

Action:

```text
fetchSettings()

saveSettings()
```

---

# 12. System Settings Page

Route:

```text
/settings
```

---

Layout:

```text
+--------------------------------+

| Restaurant                     |

+--------------------------------+

| POS Configuration              |

+--------------------------------+

| Printer                        |

+--------------------------------+

| Payment                        |

+--------------------------------+
```

---

# 13. Restaurant Profile

Component:

```text
RestaurantSetting.vue
```

---

Field:

```text
Restaurant Name

Address

Phone

Logo
```

---

Contoh:

```text
Seafood Bahari

Jl. Pantai No.10

081xxx
```

---

# 14. POS Configuration

Component:

```text
PosSetting.vue
```

---

Setting:

## Customer Name Required

```text
true / false
```

---

## Allow Discount

```text
true / false
```

---

## Default Payment

```text
cash

transfer
```

---

# 15. Printer Configuration

Component:

```text
PrinterSetting.vue
```

---

Setting:

## Cashier Printer

```text
enabled

printer_name
```

---

## Kitchen Printer

```text
enabled

printer_name
```

---

Contoh:

```text
Cashier:

EPSON TM-T82


Kitchen:

Kitchen Printer
```

---

# 16. Payment Configuration

Component:

```text
PaymentSetting.vue
```

---

Setting:

```text
cash_enabled

transfer_enabled
```

---

Contoh:

```text
Cash:

ON


Transfer:

ON
```

---

# 17. API Requirement

## Get Settings

Endpoint:

```http
GET /settings
```

---

Response:

```json
{
    "restaurant": {
        "name": "Seafood Bahari"
    },

    "pos": {
        "allow_discount": true
    },

    "printer": {
        "kitchen": true
    }
}
```

---

# 18. Update Settings

Endpoint:

```http
PUT /settings
```

---

Request:

```json
{
    "pos.allow_discount": true,

    "pos.default_payment": "cash"
}
```

---

Backend:

```text
Validate

↓

Save Settings

↓

Audit Log
```

---

# 19. Audit Integration

Perubahan settings harus masuk Audit Log.

Contoh:

```text
User:

Owner


Action:

UPDATE


Module:

Settings
```

---

Karena settings dapat mengubah perilaku sistem.

---

# 20. Security

System Settings hanya:

```text
Owner

Admin tertentu
```

---

Tidak boleh:

```text
Kasir

Kitchen
```

---

Permission:

```text
settings.view

settings.update
```

---

# 21. Validation

## Restaurant Name

Required:

```text
true
```

---

## Printer Name

Jika printer aktif:

```text
required
```

---

## Payment Method

Harus tersedia:

```text
cash

transfer
```

---

# 22. Error Handling

## Setting tidak ditemukan

Response:

```text
Configuration not found.
```

---

## Value tidak valid

Response:

```text
Invalid configuration value.
```

---

## Tidak memiliki akses

Response:

```text
Forbidden.
```

---

# 23. Testing Scenario

## Scenario 1

Owner mengubah nama restoran.

Expected:

```text
Nama berubah.
```

---

## Scenario 2

Owner mengaktifkan printer kitchen.

Expected:

```text
Printer configuration tersimpan.
```

---

## Scenario 3

Kasir membuka settings.

Expected:

```text
403 Forbidden.
```

---

## Scenario 4

Update settings.

Expected:

```text
Audit Log tercatat.
```

---

## Scenario 5

Application membaca setting.

Expected:

```text
Konfigurasi diterapkan.
```

---

# 24. Task Checklist

## Backend

- [x] Settings Migration
- [x] Settings Model
- [x] Settings Service
- [x] Settings API
- [x] Audit Integration

---

## Frontend

- [x] Settings Page
- [x] Restaurant Form
- [x] POS Form
- [x] Printer Form
- [x] Payment Form

---

## Store

- [x] Settings Store

---

## Security

- [x] Permission Settings View
- [x] Permission Settings Update

---

## Testing

- [x] Read Settings
- [x] Update Settings
- [x] Permission Check
- [x] Audit Check

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Owner dapat mengubah konfigurasi sistem

✅ Setting tersimpan berdasarkan restoran/outlet

✅ POS dapat membaca konfigurasi

✅ Perubahan tercatat di Audit Log

✅ Permission diterapkan

---

# Output Sprint

Hasil akhir Sprint 12:

```text
Owner

↓

System Settings

↓

Application Configuration

↓

Flexible Restaurant System
```

Setelah Sprint 12 selesai, sistem memiliki konfigurasi yang dapat dikelola tanpa perubahan kode.

---

# Sprint Berikutnya

## Web Admin / Owner Dashboard Sprint 13 — Recipe Management (Optional)

Fitur:

- Recipe Setup
- Ingredient Composition
- Menu Cost Calculation
- Food Cost Analysis
- Recipe Management

```

```
