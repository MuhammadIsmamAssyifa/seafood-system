# User Manual - Web Admin & Owner Dashboard

## 1. Login & Navigasi

### Masuk ke Sistem
1. Akses halaman admin di browser: `http://localhost/admin` (atau domain production Anda).
2. Masukkan **Username / Email** dan **Password**.
3. Klik tombol **Masuk ke Dashboard**.
4. Sistem akan memverifikasi peran Anda (`owner`, `admin`, `cashier`, `kitchen`).

### Navigasi & Shortcut
- **Sidebar**: Akses seluruh modul di sisi kiri (Dashboard, Laporan Penjualan, Riwayat Pesanan, Stok & Inventaris, Resep Menu, Manajemen Karyawan, Cabang/Outlet, Pengaturan Sistem, dan Audit Log).
- **Pencarian Global (`Ctrl + K`)**: Tekan tombol `Ctrl + K` kapan saja di halaman admin untuk membuka jendela pencarian cepat. Ketik minimal 2 huruf untuk mencari No. Order, Nama Pelanggan, Menu, Bahan, Karyawan, Resep, atau Cabang.
- **Peralihan KDS**: Klik tombol **Kitchen KDS** di pojok kanan atas untuk membuka antarmuka dapur pada layar terpisah.

---

## 2. Fitur Utama Owner & Admin

### A. Dashboard Overview
- Menampilkan omset hari ini, estimasi keuntungan kotor (*gross profit*), jumlah nota pesanan, dan rata-rata nilai transaksi.
- Dilengkapi ringkasan status pesanan aktif, grafik tren harian, menu terlaris (*best seller*), dan peringatan stok menipis (*low stock alert*).

### B. Stok Bahan & Valuasi Biaya (FIFO)
- **Katalog Bahan**: Menampilkan stok sisa bahan baku secara real-time.
- **Stok Masuk (Restock)**: Klik tombol Tambah Stok, isi jumlah dan harga beli per unit. Sistem mencatat batch FIFO baru.
- **Waste Management**: Catat bahan rusak atau kedaluwarsa beserta alasan (rusak, kedaluwarsa, tumpah) agar HPP dan penyusutan tercatat akurat.

### C. Resep & Analisis Food Cost
- Hubungkan varian menu dengan bahan-bahan baku yang digunakan.
- Sistem otomatis menghitung estimasi modal bahan berdasarkan harga beli batch aktif.
- Menampilkan status Food Cost % (Hijau < 30%, Kuning 30-40%, Merah > 40%) dan margin kotor menu.

### D. Multi-Cabang & Karyawan
- Tambah cabang baru (Outlet) dengan nama dan kode unik.
- Atur penempatan karyawan ke cabang tertentu.
- Atur peran karyawan (`admin`, `cashier`, `kitchen`) dan izin akses (*permissions*).

### E. Pengaturan Sistem & Audit Log
- Atur profil restoran, nama struk, teks footer, dan metode pembayaran yang aktif.
- Pantau seluruh riwayat aktivitas sensitif di **Audit Log** (login, penyesuaian kas, pembatalan pesanan, perubahan harga).

