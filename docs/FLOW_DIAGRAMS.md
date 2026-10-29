# Business Process Flow Diagrams

Dokumen ini memetakan alur proses bisnis operasional restoran seafood dari kedatangan pelanggan, pemesanan multi-batch, penanganan dapur KDS, timbangan dinamis ikan hidup, hingga kalkulasi HPP dan pembayaran kasir.

---

## 1. Siklus Hidup Pesanan (Order Lifecycle & Multi-Batch)

Pelanggan dapat melakukan pemesanan awal, kemudian menambah pesanan makanan/minuman kapan saja (Batch 2, 3, dst.) tanpa membatalkan atau mengganggu pesanan yang sudah dimasak di dapur.

```mermaid
flowchart TD
    A["Tamu Datang & Pilih Meja"] --> B["Kasir/Waiter Buat Order Draft (POST /api/v1/orders)"]
    B --> C["Meja Terkunci (TableStatus: Occupied)"]
    C --> D["Tambah Item ke Batch 1 (Status: Waiting)"]
    D --> E["Kirim Batch 1 ke Dapur (POST /order-batches/{id}/submit)"]
    E --> F["Broadcast Realtime ke KDS (kds.batch.submitted)"]
    F --> G["Dapur Memproses Batch 1"]
    
    G --> H{"Pelanggan Mau Tambah Menu?"}
    H -- "Ya" --> I["Sistem Buat Otomatis Batch 2 (Status: Waiting)"]
    I --> J["Tambah Item Tambahan ke Batch 2"]
    J --> K["Kirim Batch 2 ke Dapur"]
    K --> F
    
    H -- "Tidak" --> L["Semua Item Disajikan (Served)"]
    L --> M["Order Siap Dibayar"]
    M --> N["Kasir Proses Pembayaran (Cash/Transfer/Split)"]
    N --> O["Order Selesai (Completed) & Meja Kembali Available"]
```

---

## 2. Kitchen Display System (KDS Workflow)

Alur kerja staf dapur berbasis stasiun memasak (Grill, Fry, Wok, Drink, Steam):

```mermaid
stateDiagram-v2
    [*] --> Waiting: Batch Dikirim dari POS
    Waiting --> Cooking: Dapur Klik Terima Batch (acceptBatch)
    
    state "Proses Per Item Masakan" as ItemProcess {
        WaitingItem: Item Status Waiting
        PreparingItem: Dapur Memasak Item (prepareItem)
        ReadyItem: Item Matang di Meja Saji (readyItem)
        ServedItem: Item Diantar ke Meja Tamu (serveItem)
        
        WaitingItem --> PreparingItem: Potong Stok FIFO & Catat COGS
        PreparingItem --> ReadyItem: Notifikasi Waiter
        ReadyItem --> ServedItem: Selesai Disajikan
    }
    
    Cooking --> ItemProcess
    ItemProcess --> Ready: Semua Item Matang
    ItemProcess --> Completed: Semua Item Disajikan
    Completed --> [*]
```

---

## 3. Alur Bobot Seafood Hidup Dinamis (Dynamic Weight & Price Change)

Pada restoran seafood, ikan/kepiting/udang hidup ditimbang riil saat diambil dari kolam/pasar. Jika berat riil berbeda dari estimasi awal menu, perubahan bobot dan harga harus dikonfirmasi oleh pelanggan:

```mermaid
flowchart TD
    A["Customer Pesan: Ikan Kakap Bakar (Estimasi 0.7 kg @ Rp 70.000)"] --> B["Waiter Ambil Ikan Hidup & Timbang Riil"]
    B --> C{"Berat Riil Berbeda?"}
    C -- "Sama (0.7 kg)" --> D["Lanjut Masak di Dapur"]
    C -- "Berbeda (Contoh: 0.95 kg @ Rp 95.000)" --> E["Waiter Input Perubahan (POST /order-items/{id}/changes)"]
    
    E --> F["Status Perubahan: pending_customer"]
    F --> G["Kasir/Waiter Konfirmasi ke Pelanggan di Meja"]
    
    G --> H{"Keputusan Pelanggan"}
    H -- "Setuju (Approve)" --> I["PATCH /order-item-changes/{id}/approve"]
    I --> J["Update quantity riil & subtotal pesanan"]
    J --> K["Recalculate Grand Total & Broadcast KDS Update"]
    K --> D
    
    H -- "Tolak (Reject)" --> L["PATCH /order-item-changes/{id}/reject"]
    L --> M{"Opsi Alternatif"}
    M -- "Ganti Ikan Lain" --> N["POST /order-items/{id}/replace"]
    M -- "Batalkan Item" --> O["POST /order-items/{id}/changes (type: cancel)"]
```

---

## 4. Alur Manajemen Stok FIFO & Kalkulasi HPP (COGS)

Setiap bahan baku yang masuk dicatat dalam *inventory batch* terpisah dengan tanggal terima dan harga modal beli masing-masing. Saat item diproses di dapur, sistem memotong stok menggunakan prinsip First-In First-Out:

```mermaid
flowchart LR
    subgraph Gudang Bahan Baku (Inventory Batches)
        B1["Batch 1 (Oldest): 5 kg @ Rp 20.000"]
        B2["Batch 2 (Newer): 5 kg @ Rp 30.000"]
    end

    subgraph Dapur Masak
        Req["Dapur Memasak Gurame 7 kg"]
    end

    Req --> Deduct1["Ambil Habis Batch 1: 5 kg x Rp 20.000 = Rp 100.000"]
    Deduct1 --> Deduct2["Ambil Sisa 2 kg dari Batch 2: 2 kg x Rp 30.000 = Rp 60.000"]
    
    Deduct2 --> Result["Total HPP (COGS): Rp 160.000"]
    Result --> ProfitCalc["Gross Profit = Penjualan (Rp 350.000) - HPP (Rp 160.000) = Rp 190.000"]
    ProfitCalc --> Rec["Tercatat Otomatis di stock_movements & order_items"]
```

