# Sequence Diagrams

Dokumen ini mendokumentasikan interaksi pertukaran pesan antar aktor dan sistem: Mobile POS (Flutter), Backend API (Laravel), WebSocket Broker (Pusher Channels), Web Kitchen Display System (KDS), dan Database (PostgreSQL).

---

## 1. Login & Autentikasi Pengguna (Web & POS Staff PIN)

```mermaid
sequenceDiagram
    autonumber
    actor Kasir as Kasir / Waiter
    participant POS as Flutter Mobile POS
    participant API as Laravel Backend
    participant DB as PostgreSQL
    participant Audit as AuditLogService

    Kasir->>POS: Masukkan Username & 4-Digit PIN
    POS->>API: POST /api/v1/staff-login {username, pin, device_name}
    API->>DB: Query User berdasarkan username
    DB-->>API: Data User & pin_hash
    API->>API: Hash::check(pin, pin_hash)
    
    alt PIN Valid
        API->>DB: Create Sanctum PersonalAccessToken (device_name)
        API->>Audit: logLogin(user, 'POS Staff PIN')
        Audit->>DB: INSERT into audit_logs
        API-->>POS: HTTP 200 {token, user, roles, permissions}
        POS-->>Kasir: Tampilkan Menu POS & Meja
    else PIN Salah
        API-->>POS: HTTP 401 {"message": "Invalid credential"}
        POS-->>Kasir: Tampilkan Pesan Error
    end
```

---

## 2. Pembuatan Order & Broadcast Realtime ke Dapur (KDS)

```mermaid
sequenceDiagram
    autonumber
    actor Waiter as Waiter (POS)
    participant POS as Flutter Mobile POS
    participant API as Laravel Backend
    participant DB as PostgreSQL
    participant Pusher as Pusher Channels
    participant KDS as Web Kitchen Display

    Waiter->>POS: Pilih Meja T-05, Tamu: 4, Tambah Pesanan
    POS->>API: POST /api/v1/orders {order_table_id, customer_name, guest_count}
    API->>DB: Lock Meja (lockForUpdate) & Status -> 'occupied'
    API->>DB: INSERT order & Batch 1 ('waiting')
    API-->>POS: HTTP 201 {order_id, order_number, table}

    Waiter->>POS: Input Menu Makanan & Klik "Kirim ke Dapur"
    POS->>API: POST /api/v1/order-batches/{batch_id}/submit
    API->>DB: Update batch status -> 'sent'
    API->>DB: Update order status -> 'submitted'
    API->>Pusher: Trigger Event 'kds.batch.submitted' on channel 'pos-seafood-kds-*'
    Pusher-->>KDS: WebSocket Push {batch_id, table: "T-05", items: [...]}
    KDS->>KDS: Suara Notifikasi & Tiket Muncul di Kolom "Waiting"
    API-->>POS: HTTP 200 {success: true, status: "submitted"}
```

---

## 3. Eksekusi Masakan di Dapur & Pemotongan FIFO Bahan Baku

```mermaid
sequenceDiagram
    autonumber
    actor Chef as Koki Dapur
    participant KDS as Web Kitchen Display
    participant API as Laravel Backend
    participant DB as PostgreSQL
    participant Pusher as Pusher Channels
    participant POS as Flutter Mobile POS

    Chef->>KDS: Klik "Mulai Masak" pada Tiket
    KDS->>API: PATCH /api/v1/kitchen/batches/{id}/accept
    API->>DB: Batch status -> 'cooking'
    API->>Pusher: Trigger 'kds.batch.updated'
    Pusher-->>POS: Update Status Meja di POS menjadi "Sedang Dimasak"

    Chef->>KDS: Klik "Prepare" pada Ikan Gurame Bakar
    KDS->>API: PATCH /api/v1/kitchen/items/{id}/prepare
    API->>DB: Lock inventory_batches (FIFO: oldest received_at)
    API->>DB: Deduct remaining_quantity & calculate exact COGS
    API->>DB: INSERT stock_movements (type: 'sale', total_cost)
    API->>DB: Update order_item (cogs, gross_profit, status: 'preparing')
    API->>Pusher: Trigger 'kds.item.updated'
    API-->>KDS: HTTP 200 {item status: "preparing"}

    Chef->>KDS: Klik "Ready" saat Makanan Matang
    KDS->>API: PATCH /api/v1/kitchen/items/{id}/ready
    API->>DB: Item status -> 'ready'
    API->>Pusher: Trigger 'kds.item.updated'
    Pusher-->>POS: Notifikasi Waiter "Meja T-05 Siap Saji"
```

---

## 4. Proses Pembayaran Kasir & Pelepasan Meja

```mermaid
sequenceDiagram
    autonumber
    actor Kasir as Kasir
    participant POS as Flutter Mobile POS
    participant API as Laravel Backend
    participant DB as PostgreSQL
    participant Audit as AuditLogService

    Kasir->>POS: Buka Meja T-05 & Tampilkan Ringkasan Tagihan
    POS->>API: GET /api/v1/orders/{id}/payments/summary
    API->>DB: Calculate subtotal, discount, grand_total, total_paid
    DB-->>API: Summary data
    API-->>POS: HTTP 200 {grand_total: 350000, remaining_amount: 350000}

    Kasir->>POS: Input Pembayaran Cash Rp 400.000
    POS->>API: POST /api/v1/orders/{id}/payments {method: 'cash', amount: 350000, received_amount: 400000}
    API->>DB: INSERT into payments (change_amount: 50000)
    API->>DB: Check if order fully settled
    API->>DB: Update order status -> 'completed'
    API->>DB: Update order_table status -> 'available'
    API->>Audit: logPayment(payment)
    Audit->>DB: INSERT into audit_logs
    API-->>POS: HTTP 201 {payment_id, change: 50000, order_status: "completed"}
    POS-->>Kasir: Cetak Struk Pembayaran & Meja T-05 Kosong
```

