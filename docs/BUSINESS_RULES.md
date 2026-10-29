# Business Rules & Logic

Dokumen ini mendefinisikan seluruh aturan bisnis (*business rules*) yang diatur dalam backend sistem restoran seafood.

---

## 1. Satuan Penjualan & Validasi Kuantitas

1. **Satuan Bulat (`portion` dan `pcs`)**:
   - Menu dengan satuan porsi atau pieces (contoh: Nasi Putih, Es Teh Manis, Kerupuk) **wajib berupa bilangan bulat positif** ($1, 2, 3$).
   - Input desimal seperti $1.5$ pada satuan porsi akan otomatis ditolak dengan pesan error validasi HTTP 422: `"Quantity untuk portion atau pcs harus bilangan bulat."`.

2. **Satuan Timbangan (`kg` dan `gram`)**:
   - Menu seafood segar (Kepiting Bakau, Ikan Kerapu, Udang Galah) menerima bilangan desimal hingga 3 angka di belakang koma (contoh: $0.750\text{ kg}$ atau $1.250\text{ kg}$).

---

## 2. Snapshot Menu & Harga (Historical Integrity)

Untuk mencegah perubahan laporan keuangan masa lalu ketika admin mengubah harga master menu:
- Saat item dimasukkan ke pesanan, sistem mengambil salinan tetap (*snapshot*):
  - `menu_name_snapshot`: Nama menu saat dipesan.
  - `variant_name_snapshot`: Nama varian saat dipesan.
  - `price_snapshot`: Harga jual per unit saat pesanan dibuat.
- Perubahan harga di katalog menu master di masa depan **tidak akan mengubah** data pesanan yang sudah tercatat.

---

## 3. Pesanan Bertahap (Multi-Batch Ordering)

- Satu order meja (`orders`) dapat memiliki banyak `order_batches`.
- Item baru yang ditambahkan oleh pelanggan yang sedang makan di restoran akan otomatis dialokasikan ke batch berstatus `waiting`.
- Batch yang telah berstatus `sent` (sudah dikirim ke dapur) **terkunci dari pengeditan langsung**. Item tambahan harus melalui batch baru.

---

## 4. Penimbangan Ulang Seafood Hidup (Dynamic Price Adjustment)

- Ikan hidup dari kolam atau pasar seringkali tidak persis dengan berat yang diinginkan tamu (misal pesan 0.7 kg, tetapi ikan yang tertangkap seberat 0.85 kg).
- Pelayan menginput timbangan riil ke sistem melalui `OrderItemChange`.
- Status perubahan menjadi `pending_customer`.
- Item **tidak boleh dimasak di dapur** sebelum pelanggan menyetujui (`approved`).
- Jika pelanggan menolak (`rejected`), pesanan dapat dibatalkan atau diganti menu lain via endpoint replace.

---

## 5. Algoritma FIFO Inventory & Kalkulasi COGS

1. **Prioritas Pemotongan Stok**:
   - Bahan baku dipotong berdasarkan tanggal masuk tertua (`received_at` terlama) yang masih memiliki `remaining_quantity > 0`.
   - Menggunakan pesimistik lock (`lockForUpdate`) untuk mencegah *race condition* saat dua pesanan diproses bersamaan.

2. **Waktu Perhitungan HPP**:
   - HPP (Cost of Goods Sold / COGS) dihitung secara instan saat koki mengklik "Mulai Memasak" (`prepareItem`) pada KDS.
   - Jika menu memiliki resep (`Recipe`), stok dipotong berdasarkan rincian bahan di resep.
   - Jika tidak memiliki resep, stok dipotong berdasarkan `main_ingredient` dan `conversion_rate` menu varian.

3. **Formula Laba Kotor**:
   $$\text{Gross Profit Item} = \text{Subtotal Item} - \text{COGS Item}$$
   $$\text{Gross Profit Order} = \text{Subtotal Order} - \text{Total COGS Order} - \text{Diskon}$$
   $$\text{Margin Keuntungan (\%)} = \left(\frac{\text{Gross Profit}}{\text{Grand Total}}\right) \times 100$$

---

## 6. Matrix Otorisasi RBAC (Role-Based Access Control)

| Hak Akses / Permission | Owner | Admin | Kasir / POS | Kitchen (KDS) |
|---|:---:|:---:|:---:|:---:|
| `dashboard.view` | ✅ | ✅ | ❌ | ❌ |
| `report.view` (Laporan Keuangan & HPP) | ✅ | ✅ | ❌ | ❌ |
| `user.manage` (Tambah/Edit User) | ✅ | ❌ | ❌ | ❌ |
| `menu.view` | ✅ | ✅ | ✅ | ✅ |
| `menu.manage` | ✅ | ✅ | ❌ | ❌ |
| `order.view` | ✅ | ✅ | ✅ | ❌ |
| `order.create` / `order.update` | ✅ | ✅ | ✅ | ❌ |
| `order.change` (Dynamic Seafood Weight) | ✅ | ✅ | ✅ | ❌ |
| `kitchen.view` (Layar Antrean KDS) | ✅ | ✅ | ❌ | ✅ |
| `kitchen.update` (Masak / Siap / Saji) | ✅ | ✅ | ❌ | ✅ |
| `payment.view` / `payment.create` | ✅ | ✅ | ✅ | ❌ |
| `inventory.view` | ✅ | ✅ | ❌ | ✅ |
| `inventory.manage` / `inventory.adjust` | ✅ | ✅ | ❌ | ❌ |

