# Database Documentation & Dictionary

Dokumentasi detail arsitektur basis data PostgreSQL `seafood_pos`.

---

## 1. Daftar Enums (Backed Enums)

Seluruh status dan unit diimplementasikan menggunakan PHP Backed Enum dan divalidasi ketat pada lapisan aplikasi dan database:

| Enum Name | File Path | Nilai Backing Value | Deskripsi |
|---|---|---|---|
| `OrderStatus` | `app/Enums/OrderStatus.php` | `draft`, `submitted`, `preparing`, `ready`, `completed`, `cancelled` | Status siklus hidup pesanan meja |
| `OrderBatchStatus` | `app/Enums/OrderBatchStatus.php` | `waiting`, `sent`, `cooking`, `ready`, `completed` | Status pengiriman batch ke dapur KDS |
| `OrderItemStatus` | `app/Enums/OrderItemStatus.php` | `waiting`, `checking`, `accepted`, `preparing`, `ready`, `served`, `cancelled` | Status proses per item masakan di stasiun |
| `OrderItemChangeStatus` | `app/Enums/OrderItemChangeStatus.php` | `pending_customer`, `approved`, `rejected` | Persetujuan perubahan timbangan/harga oleh pelanggan |
| `OrderItemChangeType` | `app/Enums/OrderItemChangeType.php` | `quantity_change`, `cancel`, `replace` | Jenis perubahan item masakan |
| `PaymentMethod` | `app/Enums/PaymentMethod.php` | `cash`, `transfer`, `qris`, `debit`, `credit` | Metode transaksi pembayaran |
| `PaymentStatus` | `app/Enums/PaymentStatus.php` | `pending`, `paid`, `failed`, `refunded` | Status penyelesaian pembayaran |
| `SalesUnit` | `app/Enums/SalesUnit.php` | `portion`, `pcs`, `kg`, `gram` | Satuan unit penjualan menu di kasir POS |
| `IngredientUnit` | `app/Enums/IngredientUnit.php` | `kg`, `gram`, `liter`, `ml`, `pcs`, `pack` | Satuan unit bahan baku di gudang dapur |
| `StockMovementType` | `app/Enums/StockMovementType.php` | `in`, `sale`, `waste`, `adjustment` | Tipe mutasi inventori |
| `TableStatus` | `app/Enums/TableStatus.php` | `available`, `occupied`, `reserved` | Ketersediaan meja makan restoran |
| `ActiveStatus` | `app/Enums/ActiveStatus.php` | `active`, `inactive` | Status aktif entitas katalog dan pengguna |
| `MenuType` | `app/Enums/MenuType.php` | `seafood`, `food`, `drink`, `snack` | Klasifikasi kategori menu |

---

## 2. Struktur Tabel Utama

### 2.1 `orders`
Menyimpan header data pesanan pelanggan per meja.
- `id`: BIGSERIAL (Primary Key)
- `outlet_id`: BIGINT (Foreign Key ke `outlets.id`)
- `order_table_id`: BIGINT (Foreign Key ke `order_tables.id`)
- `order_number`: VARCHAR(50) UNIQUE (Format: `ORD-YmdHis-XXXX`)
- `customer_name`: VARCHAR(100)
- `guest_count`: INTEGER
- `status`: VARCHAR(30) (Default: `draft`)
- `subtotal`: NUMERIC(14, 2)
- `discount`: NUMERIC(14, 2) (Default: 0)
- `grand_total`: NUMERIC(14, 2)
- `total_cogs`: NUMERIC(14, 2) (Total modal HPP FIFO bahan baku)
- `gross_profit`: NUMERIC(14, 2) (Laba kotor: `grand_total - total_cogs`)
- `created_by`: BIGINT (Foreign Key ke `users.id`)
- `timestampsTz`

### 2.2 `order_batches`
Mendukung penambahan pesanan bertahap tanpa mengganggu pesanan yang sudah dimasak.
- `id`: BIGSERIAL (Primary Key)
- `order_id`: BIGINT (Foreign Key ke `orders.id`)
- `batch_number`: INTEGER
- `status`: VARCHAR(30) (Default: `waiting`)
- `sent_to_kitchen_at`: TIMESTAMPTZ (Nullable)
- `timestampsTz`

### 2.3 `order_items`
Rincian item makanan/minuman dengan penanganan angka desimal untuk seafood timbangan.
- `id`: BIGSERIAL (Primary Key)
- `order_batch_id`: BIGINT (Foreign Key ke `order_batches.id`)
- `menu_variant_id`: BIGINT (Foreign Key ke `menu_variants.id`)
- `menu_name_snapshot`: VARCHAR(150) (Menyimpan nama menu saat dipesan)
- `variant_name_snapshot`: VARCHAR(150)
- `price_snapshot`: NUMERIC(12, 2) (Menyimpan harga per unit saat dipesan)
- `sales_unit`: VARCHAR(20) (`portion`, `pcs`, `kg`, `gram`)
- `quantity`: NUMERIC(10, 3) (Mendukung berat desimal seperti 0.750 kg)
- `inventory_quantity`: NUMERIC(12, 3) (Kuantitas bahan baku yang akan dipotong: `quantity * conversion_rate`)
- `subtotal`: NUMERIC(14, 2)
- `cogs`: NUMERIC(14, 2) (HPP riil saat item dimasak)
- `gross_profit`: NUMERIC(14, 2)
- `status`: VARCHAR(30) (Default: `waiting`)
- `timestampsTz`

### 2.4 `inventory_batches`
Fondasi algoritma FIFO. Setiap stok yang masuk dicatat tanggal dan harga belinya.
- `id`: BIGSERIAL (Primary Key)
- `outlet_id`: BIGINT (Foreign Key ke `outlets.id`)
- `ingredient_id`: BIGINT (Foreign Key ke `ingredients.id`)
- `quantity`: NUMERIC(12, 3) (Kuantitas masuk awal)
- `remaining_quantity`: NUMERIC(12, 3) (Sisa kuantitas yang belum terpotong)
- `cost_per_unit`: NUMERIC(12, 2) (Harga modal beli per kg/unit)
- `received_at`: TIMESTAMPTZ (Kunci sorting antrean FIFO)
- `timestampsTz`

### 2.5 `stock_movements`
Audit trail mutasi keluar-masuk stok bahan baku dapur.
- `id`: BIGSERIAL (Primary Key)
- `outlet_id`: BIGINT
- `ingredient_id`: BIGINT
- `inventory_batch_id`: BIGINT (Nullable, link ke batch asal)
- `type`: VARCHAR(30) (`in`, `sale`, `waste`, `adjustment`)
- `quantity`: NUMERIC(12, 3)
- `cost_per_unit`: NUMERIC(12, 2)
- `total_cost`: NUMERIC(14, 2)
- `reference_type`: VARCHAR(100) (Polymorphic: `App\Models\OrderItem`, etc.)
- `reference_id`: BIGINT
- `timestampsTz`

---

## 3. Strategi Composite Indexes

Tabel PostgreSQL telah dioptimalkan dengan indeks gabungan (*composite index*) untuk menjamin eksekusi sub-milidetik:

| Nama Indeks | Tabel | Kolom | Tujuan Optimasi |
|---|---|---|---|
| `orders_outlet_status_created_idx` | `orders` | `(outlet_id, status, created_at)` | Filter cepat dashboard, omzet harian & laporan penjualan |
| `order_batches_status_created_idx` | `order_batches` | `(status, created_at)` | Query antrean Kitchen Display System (KDS) |
| `inv_batches_fifo_lookup_idx` | `inventory_batches` | `(ingredient_id, remaining_quantity, received_at)` | Traversal algoritma pemotongan stok FIFO tercepat |
| `stock_movements_outlet_type_created_idx` | `stock_movements` | `(outlet_id, type, created_at)` | Laporan mutasi stok dan kartu stok bahan baku |
| `audit_logs_outlet_action_created_idx` | `audit_logs` | `(outlet_id, action, created_at)` | Filter jejak audit log berdasarkan aksi dan tanggal |
| `menu_variants_menu_status_idx` | `menu_variants` | `(menu_id, status)` | Pemuatan varian aktif pada katalog menu kasir |

