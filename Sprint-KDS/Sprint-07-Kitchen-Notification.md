# Web Kitchen Display System Sprint 07

# Kitchen Notification & UX Improvement

## Status

Completed

---

# Tujuan Sprint

Menyempurnakan pengalaman penggunaan Kitchen Display System agar lebih nyaman digunakan pada kondisi operasional restoran yang sibuk.

Pada akhir sprint ini, KDS mampu:

- Memberikan notifikasi visual dan suara untuk order baru.
- Menampilkan prioritas order berdasarkan lama waktu menunggu.
- Menampilkan timer pada setiap order.
- Memberikan indikator status koneksi realtime.
- Mempermudah kitchen staff mengidentifikasi order yang harus diprioritaskan.

Sprint ini tidak menambah business logic baru, tetapi meningkatkan usability dan efisiensi kerja dapur.

---

# 1. Business Context

Pada jam sibuk restoran, puluhan order dapat masuk dalam waktu bersamaan.

Masalah yang sering terjadi:

- Order baru tidak terlihat.
- Order lama tertumpuk.
- Kitchen sulit menentukan prioritas.
- Tidak mengetahui apakah koneksi realtime masih aktif.

Sprint ini bertujuan mengurangi risiko tersebut.

---

# 2. UX Goals

Target utama:

- Informasi penting terlihat dalam < 2 detik.
- Order prioritas langsung menarik perhatian.
- Kitchen tidak perlu melakukan refresh.
- Tidak ada order yang terlewat.

---

# 3. Notification Flow

Flow:

```text id="a9h1kp"
Kasir Submit Order

↓

Laravel Event

↓

Pusher

↓

Notification

↓

Kitchen Staff Melihat Order
```

---

# 4. Notification Toast

Buat komponen:

```text id="fd4rxj"
components/

NotificationToast.vue
```

---

Contoh:

```text id="yrfxm9"
🔔 Order Baru

ORD-003

Meja 7

4 Item
```

Toast muncul selama:

```text id="n0k9lv"
5 detik
```

Lalu menghilang otomatis.

---

# 5. Sound Notification

Ketika order baru masuk:

```text id="i7m2ez"
Play Notification Sound
```

Syarat:

- Tidak berulang untuk event yang sama.
- Volume tidak terlalu keras.
- Tidak mengganggu proses memasak.

---

# 6. Priority Indicator

Order diberi indikator berdasarkan lama menunggu.

Contoh:

| Waktu      | Prioritas        |
| ---------- | ---------------- |
| 0–5 menit  | Normal           |
| 5–10 menit | Perhatian        |
| >10 menit  | Prioritas Tinggi |

---

Tampilan:

```text id="e4zb1w"
🟢 Normal

🟡 Perhatian

🔴 Prioritas Tinggi
```

---

# 7. Order Timer

Setiap Order Card memiliki timer.

Contoh:

```text id="mr8zpw"
ORD-001

08:34
```

Timer dihitung dari:

```text id="0mnjfr"
sent_to_kitchen_at
```

Bukan dari:

```text id="vztqvq"
created_at
```

Karena yang dihitung adalah waktu sejak dapur menerima order.

---

# 8. Timer Update

Timer berjalan otomatis.

Contoh:

```text id="yeo0rk"
00:35

↓

00:36

↓

00:37
```

Tidak memerlukan request API.

Menggunakan:

```text id="v8a6d1"
setInterval()
```

---

# 9. Queue Highlight

Order yang menjadi prioritas tinggi mendapatkan tampilan berbeda.

Contoh:

```text id="0xkl5s"
━━━━━━━━━━━━━━

🔴 ORD-005

Meja 8

15 Menit

━━━━━━━━━━━━━━
```

Tujuannya agar mudah terlihat dari kejauhan.

---

# 10. Kitchen Summary Improvement

Tambahkan informasi:

```text id="4yyxqz"
Waiting

Preparing

Ready

Served

Average Waiting Time
```

Contoh:

```text id="h0ef2l"
Waiting

6


Preparing

3


Ready

2


Average

07:15
```

---

# 11. Connection Status

Tambahkan indikator realtime.

Komponen:

```text id="4vr5nv"
ConnectionStatus.vue
```

Status:

```text id="z5r9lv"
🟢 Connected
```

atau

```text id="w4q3gz"
🔴 Disconnected
```

Jika reconnect:

```text id="4f8xqv"
🟡 Reconnecting...
```

---

# 12. Last Sync Information

Tambahkan informasi sinkronisasi.

Contoh:

```text id="iwl8ot"
Last Update

10:35:24
```

Berguna saat koneksi tidak stabil.

---

# 13. Empty Queue Improvement

Jika tidak ada order:

```text id="oaxm0f"
🍽️

Tidak ada pesanan.

Dapur siap menerima order.
```

---

# 14. Error Banner

Jika realtime gagal:

```text id="hhkq0y"
Koneksi realtime terputus.

Mencoba menghubungkan kembali...
```

Tetap tampil sampai koneksi berhasil.

---

# 15. Auto Synchronization

Jika koneksi kembali:

```text id="hjvuxk"
Reconnect

↓

Fetch Kitchen Orders

↓

Update Queue

↓

Resume Listening
```

Tujuan:

Menghindari kehilangan event selama koneksi terputus.

---

# 16. Accessibility

Pastikan:

- Ukuran font mudah dibaca dari jarak ±2 meter.
- Tombol memiliki area klik yang besar.
- Status tidak hanya dibedakan dengan warna, tetapi juga ikon dan teks.

Contoh:

```text id="s3rhuy"
🔴 Priority

🟡 Attention

🟢 Normal
```

---

# 17. Responsive Layout

Optimasi untuk:

- Monitor dapur 24"
- TV 32"
- Tablet landscape

Gunakan layout yang memanfaatkan ruang horizontal agar beberapa order dapat terlihat sekaligus.

---

# 18. Performance Optimization

Pastikan:

- Timer tidak menyebabkan render seluruh halaman.
- Hanya Order Card yang berubah melakukan update.
- Event realtime tidak menghasilkan request API berulang jika data event sudah cukup.

---

# 19. Testing Scenario

## Scenario 1

Order baru masuk.

Expected:

- Toast muncul.
- Suara diputar.
- Queue bertambah otomatis.

---

## Scenario 2

Order mencapai 10 menit.

Expected:

- Status berubah menjadi Prioritas Tinggi.
- Card mendapat highlight.

---

## Scenario 3

Internet terputus.

Expected:

- Banner koneksi muncul.
- Reconnect otomatis berjalan.

---

## Scenario 4

Realtime kembali normal.

Expected:

- Queue tersinkronisasi.
- Timer tetap berjalan.

---

# 20. Task Checklist

## Notification

- [x] Notification Toast
- [x] Notification Sound
- [x] Duplicate Event Protection

---

## Queue

- [x] Priority Indicator
- [x] Queue Highlight
- [x] Order Timer

---

## Dashboard

- [x] Average Waiting Time
- [x] Last Sync Time
- [x] Connection Status

---

## UX

- [x] Empty State Improvement
- [x] Error Banner
- [x] Responsive Layout

---

## Performance

- [x] Optimize Timer Rendering
- [x] Optimize Realtime Update
- [x] Auto Reconnect

---

## Testing

- [x] Test Notification
- [x] Test Timer
- [x] Test Priority
- [x] Test Reconnect
- [x] Test Responsive Layout

---

# Definition of Done

Sprint dianggap selesai jika:

✅ Order baru menampilkan notifikasi visual dan suara

✅ Timer berjalan otomatis

✅ Prioritas order berubah berdasarkan waktu tunggu

✅ Status koneksi realtime terlihat

✅ Queue tetap sinkron setelah reconnect

✅ Dashboard lebih mudah dipantau saat jam sibuk

---

# Output Sprint

Hasil akhir Sprint 07:

```text id="sx0jwp"
Kitchen Display System

↓

Realtime Dashboard

↓

Notification

↓

Priority Queue

↓

Order Timer

↓

Connection Status

↓

Improved Kitchen Experience
```

Pada tahap ini KDS telah siap digunakan pada operasional restoran sehari-hari dengan pengalaman pengguna yang lebih baik dan minim risiko kehilangan order.

---

# Tahap Selanjutnya

Dengan selesainya Sprint 07, **Web Kitchen Display System MVP dinyatakan selesai**.

Tahap pengembangan berikutnya (di luar MVP) dapat berupa:

- Kitchen Performance Dashboard
- Kitchen Analytics
- Historical Kitchen Report
- Kitchen Shift Management
- Kitchen Productivity Metrics
- Multi Kitchen Station Monitoring

Fitur-fitur tersebut lebih berfokus pada analitik dan manajemen operasional daripada proses memasak harian.
