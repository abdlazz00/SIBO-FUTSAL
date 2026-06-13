# Product Requirements Document (PRD)
## VITKA FUTSAL — Sistem Booking Online

| | |
|---|---|
| **Versi** | 1.0 |
| **Tanggal** | 13 Juni 2026 |
| **Status** | Draft |
| **Dibuat oleh** | Tim Pengembang |
| **Client** | VITKA FUTSAL |

---

## Daftar Isi
1. [Latar Belakang & Tujuan](#1-latar-belakang--tujuan)
2. [Ruang Lingkup](#2-ruang-lingkup)
3. [Pengguna & Role](#3-pengguna--role)
4. [Arsitektur Sistem](#4-arsitektur-sistem)
5. [Alur Bisnis Utama](#5-alur-bisnis-utama)
6. [Spesifikasi Fitur](#6-spesifikasi-fitur)
7. [Model Data](#7-model-data)
8. [Business Rules & Constraint](#8-business-rules--constraint)
9. [Keamanan & Otorisasi](#9-keamanan--otorisasi)
10. [Non-Functional Requirements](#10-non-functional-requirements)
11. [Keputusan Desain](#11-keputusan-desain)

---

## 1. Latar Belakang & Tujuan

### Latar Belakang
VITKA FUTSAL adalah fasilitas olahraga futsal dengan 1 lokasi yang memiliki beberapa lapangan. Saat ini proses pemesanan lapangan masih dilakukan secara manual (via telepon / chat langsung), yang menyebabkan:
- Risiko double booking
- Tidak ada rekap pendapatan yang terstruktur
- Kesulitan tracking histori booking
- Pengalaman customer yang kurang profesional

### Tujuan Proyek
Membangun sistem booking online yang memungkinkan:
- Customer memesan lapangan secara mandiri, kapan saja, tanpa perlu menghubungi admin
- Admin mengelola semua operasional booking & pembayaran dengan efisien
- Owner mendapatkan visibilitas penuh atas kinerja bisnis melalui laporan keuangan & analitik

### Success Metrics
- Nol insiden double booking
- Waktu booking customer < 3 menit
- Admin dapat mengkonfirmasi pembayaran dalam 1 klik
- Owner dapat mengakses laporan keuangan kapan saja

---

## 2. Ruang Lingkup

### Dalam Lingkup (In-Scope MVP)
- Landing page publik
- Sistem booking untuk customer (login & guest)
- Manajemen booking untuk admin
- Manajemen pembayaran untuk admin
- Manajemen lapangan untuk admin & owner
- Dashboard analitik untuk admin & owner
- Manajemen profil untuk semua user
- Staff management untuk owner
- Notifikasi in-app (real-time via WebSocket)

### Di Luar Lingkup (Out-of-Scope)
- Integrasi payment gateway otomatis (Midtrans, Xendit, dll.)
- Aplikasi mobile (Android/iOS)
- Multi-lokasi / multi-tenant
- Sistem loyalitas / poin reward
- Integrasi WhatsApp / email otomatis
- Sistem inventaris peralatan

---

## 3. Pengguna & Role

### Hierarki Role
```
Owner  >=  Admin  >=  Customer (Registered)  >=  Customer (Guest)
```

### Matriks Akses

| Fitur | Guest | Customer | Admin | Owner |
|-------|:-----:|:--------:|:-----:|:-----:|
| Lihat landing page | ✅ | ✅ | ✅ | ✅ |
| Booking lapangan | ✅ | ✅ | ✅ | ✅ |
| Cek status booking (via no. booking) | ✅ | — | — | — |
| Lihat history booking pribadi | ❌ | ✅ | — | — |
| Profile management | ❌ | ✅ | ✅ | ✅ |
| Booking management | ❌ | ❌ | ✅ | ✅ |
| Payment management | ❌ | ❌ | ✅ | ✅ |
| Courts management | ❌ | ❌ | ✅ | ✅ |
| Dashboard analytics | ❌ | ❌ | ✅ | ✅ |
| Laporan keuangan detail | ❌ | ❌ | ❌ | ✅ |
| Staff management | ❌ | ❌ | ❌ | ✅ |

---

## 4. Arsitektur Sistem

### Tech Stack

| Layer | Teknologi | Keterangan |
|-------|-----------|------------|
| **Backend** | Laravel 12 | PHP 8.3+, Monolitik |
| **Frontend** | Vue.js 3 via Inertia.js | SPA-like experience |
| **Database** | PostgreSQL | Permintaan client |
| **UI Library** | PrimeVue | Komponen siap pakai |
| **Real-time** | Laravel Reverb | WebSocket untuk notifikasi in-app |
| **Export** | Laravel Excel + DomPDF | PDF & Excel |
| **Auth** | Laravel Breeze (Inertia stack) | Session-based |

### Pola Arsitektur Backend — Service Repository Pattern

```
HTTP Request
    ↓
[Controller]          → Terima request, validasi input, return response
    ↓
[Service]             → Business logic, orchestration antar repository
    ↓
[Repository]          → Abstraksi query database (Eloquent)
    ↓
[Model / Database]    → Eloquent Model + PostgreSQL
```

**Struktur Direktori Backend:**
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Admin/
│   │   │   ├── BookingController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── CourtController.php
│   │   │   └── DashboardController.php
│   │   ├── Owner/
│   │   │   ├── DashboardController.php
│   │   │   ├── ReportController.php
│   │   │   └── StaffController.php
│   │   ├── Customer/
│   │   │   ├── BookingController.php
│   │   │   └── ProfileController.php
│   │   └── Public/
│   │       ├── LandingController.php
│   │       └── GuestBookingController.php
│   ├── Requests/          → Form Request Validation
│   └── Middleware/        → RoleMiddleware, dll.
├── Services/
│   ├── BookingService.php
│   ├── PaymentService.php
│   ├── CourtService.php
│   ├── ReportService.php
│   └── NotificationService.php
├── Repositories/
│   ├── Contracts/         → Interface setiap repository
│   │   ├── BookingRepositoryInterface.php
│   │   ├── CourtRepositoryInterface.php
│   │   └── ...
│   ├── BookingRepository.php
│   ├── CourtRepository.php
│   ├── PaymentRepository.php
│   └── UserRepository.php
├── Models/
└── Events/ & Listeners/   → Laravel Events untuk notifikasi
```

### Pola Arsitektur Frontend — Feature-Based Component Architecture

```
resources/
└── js/
    ├── app.js
    ├── Layouts/
    │   ├── PublicLayout.vue
    │   ├── AdminLayout.vue
    │   ├── OwnerLayout.vue
    │   └── CustomerLayout.vue
    ├── Pages/
    │   ├── Public/
    │   │   ├── Landing.vue
    │   │   └── BookingTrack.vue
    │   ├── Auth/
    │   │   ├── Login.vue
    │   │   └── Register.vue
    │   ├── Admin/
    │   │   ├── Dashboard/
    │   │   ├── Bookings/
    │   │   ├── Payments/
    │   │   └── Courts/
    │   ├── Owner/
    │   │   ├── Dashboard/
    │   │   ├── Reports/
    │   │   └── Staff/
    │   └── Customer/
    │       ├── Profile/
    │       └── Bookings/
    ├── Features/
    │   ├── booking/
    │   │   ├── components/
    │   │   │   ├── BookingCalendar.vue
    │   │   │   ├── SlotGrid.vue
    │   │   │   ├── BookingForm.vue
    │   │   │   └── BookingStatusBadge.vue
    │   │   ├── composables/
    │   │   │   └── useBooking.js
    │   │   └── index.js
    │   ├── payment/
    │   │   ├── components/
    │   │   │   ├── PaymentConfirmModal.vue
    │   │   │   ├── ExpenseForm.vue
    │   │   │   └── TransactionTable.vue
    │   │   └── composables/
    │   │       └── usePayment.js
    │   ├── courts/
    │   │   ├── components/
    │   │   │   ├── CourtCard.vue
    │   │   │   ├── CourtForm.vue
    │   │   │   └── CourtStatusToggle.vue
    │   │   └── composables/
    │   │       └── useCourt.js
    │   ├── dashboard/
    │   │   ├── components/
    │   │   │   ├── RevenueChart.vue
    │   │   │   ├── OccupancyRate.vue
    │   │   │   ├── PeakHoursHeatmap.vue
    │   │   │   └── StatsCard.vue
    │   │   └── composables/
    │   │       └── useDashboard.js
    │   ├── notifications/
    │   │   ├── components/
    │   │   │   ├── NotificationBell.vue
    │   │   │   └── NotificationDropdown.vue
    │   │   └── composables/
    │   │       └── useNotification.js
    │   └── profile/
    │       ├── components/
    │       │   ├── ProfileForm.vue
    │       │   └── PasswordChangeForm.vue
    │       └── composables/
    │           └── useProfile.js
    └── Components/
        ├── AppButton.vue
        ├── AppModal.vue
        ├── DataTable.vue
        └── StatusBadge.vue
```

---

## 5. Alur Bisnis Utama

### 5.1 Alur Booking Customer (Registered)
```
Landing Page
    → Klik "Book Now" di card lapangan
    → Pilih tanggal di kalender
    → Pilih slot waktu dari grid visual (slot hijau = tersedia)
    → Isi form: nama, HP, email (opsional)
    → Submit → sistem akuisisi lock DB → cek konflik
    → Jika tersedia: status Confirmed, simpan ke DB
    → Notifikasi in-app dikirim ke customer & admin
    → Customer datang ke lokasi, bayar sesuai metode yang dipilih
    → Admin konfirmasi pembayaran (pilih metode: Cash/Transfer/QRIS)
    → Status berubah menjadi Completed
```

### 5.2 Alur Booking Guest
```
(sama dengan customer registered sampai Submit)
    → Setelah booking Confirmed, tampilkan Nomor Booking unik
    → Guest dapat mengecek status via halaman publik "Cek Booking"
       dengan memasukkan Nomor Booking
```

### 5.3 Alur Booking Manual oleh Admin
```
Admin → Booking Management → "Tambah Booking Manual"
    → Isi form: nama customer, HP, pilih lapangan, tanggal, slot
    → Submit → sistem cek konflik dengan locking
    → Status langsung Confirmed
```

### 5.4 Alur Reschedule (Hanya Admin)
```
Admin → Booking Management → Pilih booking → "Reschedule"
    → Pilih tanggal & slot baru
    → Sistem cek ketersediaan slot baru
    → Konfirmasi → data booking diperbarui → notifikasi ke customer
```

### 5.5 Alur Konfirmasi Pembayaran
```
Admin → Payment Management → Temukan transaksi → "Konfirmasi Pembayaran"
    → Pilih metode pembayaran: Cash / Transfer / QRIS
    → Submit → booking status → Completed
    → Data tercatat di tabel payments dengan metode yang dipilih
    → Notifikasi in-app dikirim ke customer
```

### Status Booking

```
        ┌──────────────────────────────────────────┐
        │                                          ▼
[Confirmed] ──── Admin Konfirmasi Bayar ──→ [Completed]
        │
        └──────── Admin Cancel (+ alasan) ──→ [Cancelled]
```

---

## 6. Spesifikasi Fitur

### F-01: Landing Page (Publik)

**Hero Section**
- Banner visual VITKA FUTSAL (gambar/video background)
- Tagline & sub-tagline
- CTA button: "Pesan Sekarang" (scroll ke card lapangan atau langsung ke booking)
- CTA sekunder: "Cek Booking Saya" (untuk guest tracking)

**Card Lapangan**
- Foto lapangan (carousel jika lebih dari 1 foto)
- Nama lapangan
- Tipe: Indoor / Outdoor
- Harga per slot
- Status: Tersedia / Penuh Hari Ini
- Tombol: **Book Now**

**Testimoni**
- Nama customer, foto avatar, rating bintang, teks review
- Data testimoni dikelola manual oleh admin (CRUD sederhana)

**Footer**
- Nama & logo VITKA FUTSAL
- Alamat lengkap + embed Google Maps
- Nomor telepon & jam operasional
- Link media sosial

---

### F-02: Sistem Booking Customer

**Halaman Booking**
- Step 1: Pilih lapangan (jika belum dipilih dari landing)
- Step 2: Pilih tanggal (Date Picker)
- Step 3: Grid slot waktu — tampil visual per slot:
  - Tersedia (bisa diklik)
  - Sudah terisi (disabled)
  - Sedang dipilih
- Step 4: Form data customer (nama, HP, email opsional)
- Step 5: Review & konfirmasi

**Guest Booking Tracking**
- Halaman publik /booking/check
- Input: Nomor Booking (format: VF-XXXXXX)
- Output: Detail booking (lapangan, tanggal, slot, status)

---

### F-03: Booking Management (Admin)

| Komponen | Detail |
|----------|--------|
| **Tabel Booking** | Kolom: No. Booking, Customer, Lapangan, Tanggal, Slot, Status, Aksi |
| **Filter** | By tanggal (range), lapangan, status |
| **Search** | By nama customer, nomor booking, nomor HP |
| **Tambah Booking Manual** | Form inline / modal |
| **Detail Booking** | Modal/halaman detail dengan history status |
| **Reschedule** | Hanya Admin. Pilih slot baru dari grid |
| **Cancel** | Konfirmasi modal + input alasan wajib diisi |
| **Export** | PDF (struk/rekap) & Excel |

---

### F-04: Payment Management (Admin)

| Komponen | Detail |
|----------|--------|
| **Tabel Transaksi** | No. Booking, Customer, Lapangan, Amount, Metode, Status, Tanggal Konfirmasi |
| **Konfirmasi Pembayaran** | Modal: pilih metode (Cash/Transfer/QRIS) + amount otomatis dari booking |
| **Refund** | Input alasan refund + jumlah refund (bisa parsial) |
| **Input Expense** | Form: kategori, deskripsi, jumlah, tanggal |
| **Filter** | By tanggal (range), metode pembayaran, status |
| **Export** | PDF & Excel laporan keuangan per periode |

---

### F-05: Courts Management (Admin & Owner)

| Komponen | Detail |
|----------|--------|
| **Daftar Lapangan** | Card/tabel dengan thumbnail foto |
| **Tambah/Edit Lapangan** | Form: nama, tipe (indoor/outdoor), harga flat, durasi slot (menit), jam buka-tutup, foto |
| **Upload Foto** | Multiple foto, drag & drop, preview |
| **Toggle Status** | Aktif / Nonaktif / Maintenance |
| **Price Override** | Atur harga khusus untuk tanggal tertentu |
| **Audit Log** | Tabel riwayat perubahan: siapa, kapan, field apa, nilai lama → baru |

---

### F-06: Dashboard Admin

**Stats Cards (Real-time)**
- Total Booking Hari Ini
- Pendapatan Hari Ini
- Lapangan Aktif
- Booking Confirmed (belum dibayar)

**Charts & Visualisasi**
- Revenue Chart: Line/Bar chart harian/mingguan/bulanan (toggle)
- Booking Trend: Jumlah booking per periode
- Occupancy Rate: Per lapangan, persentase slot terisi vs tersedia
- Peak Hours Heatmap: Grid jam x hari (warna intensity = jumlah booking)
- Payment Method Breakdown: Donut chart (Cash/Transfer/QRIS)

**Tabel**
- 10 Booking Terbaru (dengan status real-time)
- Lapangan Tersibuk (ranking)

---

### F-07: Dashboard Owner

Semua konten Dashboard Admin ditambah:

**Laporan Keuangan**
- Total Revenue vs Total Expense = Net Profit
- Revenue Breakdown: per metode pembayaran, per lapangan
- Expense Breakdown: per kategori
- Profit/Loss trend per bulan
- Export laporan ke PDF & Excel

---

### F-08: Profile Management (Semua User)

| Field | Customer | Admin | Owner |
|-------|:--------:|:-----:|:-----:|
| Nama lengkap | ✅ | ✅ | ✅ |
| Foto profil | ✅ | ✅ | ✅ |
| Nomor HP | ✅ | ✅ | ✅ |
| Email | ✅ | ✅ | ✅ |
| Ganti password | ✅ | ✅ | ✅ |
| History booking | ✅ | ❌ | ❌ |

**Validasi Password:**
- Minimal 8 karakter
- Wajib isi password lama untuk ganti password
- Konfirmasi password baru harus cocok

---

### F-09: Staff Management (Owner)

- Tabel daftar semua Admin yang memiliki akses sistem
- Kolom: Nama, Email, No. HP, Tanggal Bergabung, Status (Aktif/Nonaktif)
- Filter: by status
- Read-only di MVP: Owner hanya dapat melihat, bukan mengelola Admin

---

### F-10: Notifikasi In-App (Real-time via Reverb)

**Trigger Notifikasi:**

| Event | Penerima |
|-------|----------|
| Booking baru berhasil | Admin + Customer (jika login) |
| Booking direschedule oleh Admin | Customer (jika login) |
| Booking dibatalkan | Customer (jika login) |
| Pembayaran dikonfirmasi | Customer (jika login) |

**UI Notifikasi:**
- Bell icon di navbar dengan badge jumlah unread
- Dropdown list notifikasi (terbaru di atas)
- Klik notifikasi → mark as read + redirect ke halaman terkait
- Tombol "Tandai semua dibaca"

---

## 7. Model Data

### Skema Database (PostgreSQL)

```sql
-- Users (semua role dalam 1 tabel)
users
  id              BIGSERIAL PRIMARY KEY
  name            VARCHAR(255) NOT NULL
  email           VARCHAR(255) UNIQUE NOT NULL
  phone           VARCHAR(20)
  password        VARCHAR(255) NOT NULL
  role            VARCHAR(20) DEFAULT 'customer'   -- 'owner', 'admin', 'customer'
  photo           VARCHAR(255)
  is_active       BOOLEAN DEFAULT true
  remember_token  VARCHAR(100)
  created_at      TIMESTAMP
  updated_at      TIMESTAMP

-- Lapangan
courts
  id              BIGSERIAL PRIMARY KEY
  name            VARCHAR(255) NOT NULL
  type            VARCHAR(20) NOT NULL              -- 'indoor', 'outdoor'
  price           DECIMAL(12,2) NOT NULL
  slot_duration   INTEGER NOT NULL                  -- durasi dalam menit
  open_time       TIME NOT NULL
  close_time      TIME NOT NULL
  status          VARCHAR(20) DEFAULT 'active'      -- 'active', 'inactive', 'maintenance'
  created_at      TIMESTAMP
  updated_at      TIMESTAMP

-- Foto Lapangan
court_photos
  id              BIGSERIAL PRIMARY KEY
  court_id        BIGINT REFERENCES courts(id) ON DELETE CASCADE
  path            VARCHAR(255) NOT NULL
  sort_order      INTEGER DEFAULT 0
  created_at      TIMESTAMP

-- Override Harga per Tanggal
court_price_overrides
  id              BIGSERIAL PRIMARY KEY
  court_id        BIGINT REFERENCES courts(id) ON DELETE CASCADE
  date            DATE NOT NULL
  price           DECIMAL(12,2) NOT NULL
  note            TEXT
  created_by      BIGINT REFERENCES users(id)
  created_at      TIMESTAMP
  UNIQUE(court_id, date)

-- Audit Log Perubahan Lapangan
court_audit_logs
  id              BIGSERIAL PRIMARY KEY
  court_id        BIGINT REFERENCES courts(id) ON DELETE CASCADE
  user_id         BIGINT REFERENCES users(id)
  action          VARCHAR(50) NOT NULL              -- 'create', 'update', 'delete'
  field_name      VARCHAR(100)
  old_value       TEXT
  new_value       TEXT
  created_at      TIMESTAMP

-- Booking
bookings
  id              BIGSERIAL PRIMARY KEY
  booking_number  VARCHAR(20) UNIQUE NOT NULL       -- format: VF-XXXXXX
  court_id        BIGINT REFERENCES courts(id)
  user_id         BIGINT REFERENCES users(id)       -- NULL jika guest
  customer_name   VARCHAR(255) NOT NULL
  customer_phone  VARCHAR(20) NOT NULL
  customer_email  VARCHAR(255)
  date            DATE NOT NULL
  start_time      TIME NOT NULL
  end_time        TIME NOT NULL
  total_price     DECIMAL(12,2) NOT NULL
  status          VARCHAR(20) DEFAULT 'confirmed'   -- 'confirmed', 'completed', 'cancelled'
  cancel_reason   TEXT
  cancelled_by    BIGINT REFERENCES users(id)
  is_manual       BOOLEAN DEFAULT false
  created_by      BIGINT REFERENCES users(id)
  created_at      TIMESTAMP
  updated_at      TIMESTAMP
  -- Index
  INDEX idx_bookings_court_date (court_id, date)
  INDEX idx_bookings_number (booking_number)

-- Pembayaran
payments
  id              BIGSERIAL PRIMARY KEY
  booking_id      BIGINT UNIQUE REFERENCES bookings(id)
  payment_method  VARCHAR(20)                       -- 'cash', 'transfer', 'qris'
  amount          DECIMAL(12,2) NOT NULL
  refund_amount   DECIMAL(12,2) DEFAULT 0
  refund_reason   TEXT
  confirmed_by    BIGINT REFERENCES users(id)
  confirmed_at    TIMESTAMP
  created_at      TIMESTAMP
  updated_at      TIMESTAMP

-- Pengeluaran
expenses
  id              BIGSERIAL PRIMARY KEY
  category        VARCHAR(100) NOT NULL
  description     TEXT
  amount          DECIMAL(12,2) NOT NULL
  expense_date    DATE NOT NULL
  recorded_by     BIGINT REFERENCES users(id)
  created_at      TIMESTAMP
  updated_at      TIMESTAMP

-- Notifikasi
notifications
  id              BIGSERIAL PRIMARY KEY
  user_id         BIGINT REFERENCES users(id) ON DELETE CASCADE
  title           VARCHAR(255) NOT NULL
  message         TEXT NOT NULL
  type            VARCHAR(50)                       -- 'booking', 'payment', 'system'
  reference_id    BIGINT
  reference_type  VARCHAR(50)
  is_read         BOOLEAN DEFAULT false
  read_at         TIMESTAMP
  created_at      TIMESTAMP

-- Testimoni
testimonials
  id              BIGSERIAL PRIMARY KEY
  customer_name   VARCHAR(255) NOT NULL
  avatar          VARCHAR(255)
  rating          INTEGER NOT NULL                  -- 1-5
  content         TEXT NOT NULL
  is_active       BOOLEAN DEFAULT true
  sort_order      INTEGER DEFAULT 0
  created_at      TIMESTAMP
  updated_at      TIMESTAMP
```

---

## 8. Business Rules & Constraint

### BR-01: Pencegahan Double Booking
- Sistem menggunakan **PostgreSQL Advisory Lock + SELECT FOR UPDATE** (pessimistic locking)
- Pengecekan konflik: court_id + date + overlap(start_time, end_time)
- Jika slot sudah terisi → error dikembalikan ke user
- Timeout lock: 5 detik

```php
// Contoh implementasi di BookingService
DB::transaction(function () use ($data) {
    Court::where('id', $data['court_id'])
         ->lockForUpdate()
         ->firstOrFail();

    $conflict = Booking::where('court_id', $data['court_id'])
        ->where('date', $data['date'])
        ->where('status', '!=', 'cancelled')
        ->where(function ($q) use ($data) {
            $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
              ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
              ->orWhere(function ($q) use ($data) {
                  $q->where('start_time', '<=', $data['start_time'])
                    ->where('end_time', '>=', $data['end_time']);
              });
        })->exists();

    if ($conflict) {
        throw new SlotNotAvailableException('Slot sudah terisi.');
    }

    return Booking::create($data);
});
```

### BR-02: Nomor Booking
- Format: VF- + 6 karakter alfanumerik acak (uppercase)
- Contoh: VF-A3K9PL
- Harus unik di database
- Digunakan untuk guest tracking

### BR-03: Pembatalan Booking
- Alasan pembatalan wajib diisi (minimal 10 karakter)
- Admin dapat membatalkan booking berstatus confirmed
- Customer (login) hanya dapat membatalkan booking miliknya dengan status confirmed

### BR-04: Reschedule
- Hanya Admin yang dapat melakukan reschedule
- Sistem wajib cek ketersediaan slot baru (dengan locking)
- Reschedule hanya boleh untuk booking berstatus confirmed

### BR-05: Slot Waktu
- Slot dihasilkan otomatis dari open_time, close_time, dan slot_duration setiap lapangan
- Contoh: Lapangan A buka 07:00, tutup 22:00, durasi 60 menit → 15 slot
- Slot yang melewati close_time tidak ditampilkan

### BR-06: Harga
- Harga default: flat rate dari field price di tabel courts
- Jika ada court_price_overrides untuk tanggal yang dipilih → gunakan harga override
- Harga yang digunakan saat booking disimpan ke total_price di tabel bookings (immutable setelah booking)

---

## 9. Keamanan & Otorisasi

### Autentikasi
- Session-based authentication via Laravel Breeze (Inertia stack)
- Password di-hash menggunakan Bcrypt
- Guest booking tidak memerlukan autentikasi

### Otorisasi
- Middleware per route group: role:owner, role:admin, role:customer
- Laravel Gates & Policies untuk aksi spesifik
- Semua endpoint admin/owner wajib terautentikasi

### Proteksi Data
- CSRF protection aktif (default Laravel)
- XSS protection via Vue.js rendering
- Input validation via Laravel Form Requests
- Mass assignment protection via $fillable di setiap Model

---

## 10. Non-Functional Requirements

| Kategori | Requirement |
|----------|-------------|
| **Performa** | Halaman public load < 2 detik pada koneksi normal |
| **Ketersediaan** | Uptime 99% (production) |
| **Responsivitas** | Tampilan responsif: Mobile (360px+), Tablet, Desktop |
| **Real-time** | Notifikasi terkirim dalam < 2 detik setelah event terjadi (via Reverb) |
| **Skalabilitas** | Arsitektur siap di-refactor ke API-based jika dibutuhkan |
| **Browser Support** | Chrome, Firefox, Edge (2 versi terakhir), Safari |
| **Keamanan** | Tidak ada SQL Injection, XSS, CSRF vulnerability |
| **Export** | File PDF & Excel bisa di-download dalam < 5 detik untuk data 1 bulan |

---

## 11. Keputusan Desain

| No. | Keputusan | Alasan |
|-----|-----------|--------|
| D-01 | Booking langsung **Confirmed** tanpa perlu approval Admin | Mengurangi friction customer, operasional lebih simpel |
| D-02 | Pembayaran dilakukan **di luar sistem** (manual) | Menghindari kompleksitas payment gateway di MVP |
| D-03 | Admin yang pilih **metode pembayaran** saat konfirmasi | Membantu kategorisasi pendapatan untuk laporan Owner |
| D-04 | **Guest Booking** tersedia | Meningkatkan konversi booking tanpa hambatan registrasi |
| D-05 | Guest tracking via **Nomor Booking** | Solusi simpel tanpa memerlukan akun |
| D-06 | **Hanya Admin** yang bisa reschedule | Menjaga kontrol operasional, menghindari abuse |
| D-07 | **Pessimistic Locking** untuk booking | Menjamin konsistensi data, mencegah race condition |
| D-08 | Notifikasi **real-time via Reverb** | UX lebih baik, Reverb mudah di-setup untuk environment lokal |
| D-09 | **Staff Management read-only** di MVP | Scope MVP terbatas |
| D-10 | **Service Repository Pattern** di BE | Separation of concerns, mudah di-test, mudah di-maintain |
| D-11 | **Feature-based architecture** di FE | Skalabilitas fitur, co-location komponen + logic per fitur |

---

*Dokumen ini adalah living document yang akan diperbarui seiring perkembangan proyek.*
