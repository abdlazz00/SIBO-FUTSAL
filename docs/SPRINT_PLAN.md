# Sprint Plan — VITKA FUTSAL Booking System
## Dokumen Perencanaan Sprint (MVP)

| | |
|---|---|
| **Versi** | 1.0 |
| **Tanggal** | 13 Juni 2026 |
| **Total Sprint** | 5 Sprint |
| **Durasi per Sprint** | 1 Minggu |
| **Target** | Demo ke Client VITKA FUTSAL |

---

## Ringkasan Sprint

| Sprint | Nama | Fokus Utama | Estimasi |
|--------|------|-------------|----------|
| Sprint 1 | Foundation & Auth | Setup project, autentikasi, routing, layout dasar | Minggu 1 |
| Sprint 2 | Courts & Landing Page | Manajemen lapangan, landing page publik | Minggu 1-2 |
| Sprint 3 | Booking System | Kalender slot, booking flow, booking management admin | Minggu 2 |
| Sprint 4 | Payment & Reports | Konfirmasi bayar, expense, laporan keuangan, export | Minggu 3 |
| Sprint 5 | Dashboard, Notifikasi & Polish | Dashboard analytics, real-time notif, profile, audit | Minggu 3-4 |

---

## SPRINT 1 — Foundation & Auth

**Tujuan**: Project siap jalan, semua role bisa login dan diarahkan ke halaman yang benar.

### Backend Tasks

#### S1-BE-01: Setup Project
- [ ] Install Laravel 12 dengan Breeze (Inertia + Vue stack)
- [ ] Konfigurasi koneksi PostgreSQL di `.env`
- [ ] Install & konfigurasi PrimeVue + dependencies frontend
- [ ] Install Laravel Reverb (`php artisan install:broadcasting`)
- [ ] Setup struktur folder Service Repository Pattern:
  - `app/Services/`
  - `app/Repositories/`
  - `app/Repositories/Contracts/`
- [ ] Buat `RepositoryServiceProvider` untuk binding Interface ke Implementation

#### S1-BE-02: Database & Migration
- [ ] Migration: `users` (tambah field: `phone`, `role`, `photo`, `is_active`)
- [ ] Migration: `courts`
- [ ] Migration: `court_photos`
- [ ] Migration: `court_price_overrides`
- [ ] Migration: `court_audit_logs`
- [ ] Migration: `bookings`
- [ ] Migration: `payments`
- [ ] Migration: `expenses`
- [ ] Migration: `notifications`
- [ ] Migration: `testimonials`
- [ ] Buat Seeder: user Owner, Admin, beberapa Customer contoh

#### S1-BE-03: Autentikasi & Role
- [ ] Modifikasi User model: tambah enum role `owner | admin | customer`
- [ ] Buat `RoleMiddleware` → redirect sesuai role setelah login
- [ ] Daftarkan middleware di `bootstrap/app.php`
- [ ] Buat route groups dengan middleware:
  - `prefix('admin') → middleware(['auth', 'role:admin,owner'])`
  - `prefix('owner') → middleware(['auth', 'role:owner'])`
  - `prefix('customer') → middleware(['auth', 'role:customer'])`
  - `prefix('/') → public routes`
- [ ] Kustomisasi redirect setelah login (berdasarkan role)
- [ ] Kustomisasi halaman login Breeze (branding VITKA FUTSAL)

#### S1-BE-04: Repository & Service Layer Auth
- [ ] Buat `UserRepositoryInterface` + `UserRepository`
- [ ] Buat `ProfileService` (update profile, ganti password)

### Frontend Tasks

#### S1-FE-01: Setup Layout
- [ ] Setup struktur folder Feature-Based:
  - `resources/js/Features/`
  - `resources/js/Layouts/`
  - `resources/js/Components/`
- [ ] Buat `AdminLayout.vue` (sidebar + topbar + notification bell placeholder)
- [ ] Buat `OwnerLayout.vue` (extend AdminLayout dengan menu tambahan Owner)
- [ ] Buat `CustomerLayout.vue` (navbar sederhana)
- [ ] Buat `PublicLayout.vue` (navbar publik + footer placeholder)
- [ ] Setup PrimeVue theme dan custom CSS variables (color palette VITKA FUTSAL)

#### S1-FE-02: Halaman Auth
- [ ] Kustomisasi `Login.vue` (branding + styling PrimeVue)
- [ ] Kustomisasi `Register.vue` (untuk customer self-register)
- [ ] Halaman redirect setelah login per role (stub halaman Dashboard)

### Deliverable Sprint 1
- [ ] Semua role bisa register, login, logout
- [ ] Setelah login, redirect ke dashboard masing-masing role (halaman stub)
- [ ] Database lengkap dengan semua tabel dan seeder
- [ ] Layout dasar (sidebar, navbar) tampil dengan benar per role

---

## SPRINT 2 — Courts & Landing Page

**Tujuan**: Admin/Owner bisa kelola lapangan; Landing page publik sudah bisa diakses dan menampilkan daftar lapangan.

### Backend Tasks

#### S2-BE-01: Courts Repository & Service
- [ ] Buat `CourtRepositoryInterface` + `CourtRepository`:
  - `getAll(filters)`, `findById(id)`, `create(data)`, `update(id, data)`, `delete(id)`
  - `getActiveForPublic()` → untuk landing page
- [ ] Buat `CourtService`:
  - `listCourts()`, `createCourt(data)`, `updateCourt(id, data)`
  - `toggleStatus(id, status)`
  - `addPriceOverride(id, date, price)`
  - `getAuditLogs(id)`
  - `generateSlots(court, date)` → hitung slot dari open_time/close_time/slot_duration
- [ ] Buat `CourtAuditLogObserver` → otomatis catat perubahan Model Court ke `court_audit_logs`
- [ ] Konfigurasi Observer di `AppServiceProvider`

#### S2-BE-02: Court Controllers & Requests
- [ ] `Admin\CourtController` (CRUD + toggle status + price override + audit log)
- [ ] `Public\LandingController` → return data lapangan aktif + testimoni
- [ ] Form Requests: `StoreCourtRequest`, `UpdateCourtRequest`, `StorePriceOverrideRequest`
- [ ] Konfigurasi upload foto lapangan (Laravel Storage, disk `public`)
- [ ] Endpoint generate slots: `GET /api/courts/{id}/slots?date=YYYY-MM-DD`

#### S2-BE-03: Testimoni
- [ ] Migration seeder testimoni contoh (3-5 data)
- [ ] `TestimonialRepositoryInterface` + `TestimonialRepository`
- [ ] `Admin\TestimonialController` (CRUD sederhana)

#### S2-BE-04: Routes
```
// Public
GET  /                          → Landing
GET  /courts/{id}/slots         → API get slot tersedia

// Admin: Courts
GET  /admin/courts              → index
GET  /admin/courts/create       → create form
POST /admin/courts              → store
GET  /admin/courts/{id}/edit    → edit form
PUT  /admin/courts/{id}         → update
DELETE /admin/courts/{id}       → destroy
PATCH /admin/courts/{id}/status → toggle status
GET  /admin/courts/{id}/audit   → audit log
POST /admin/courts/{id}/price-override → store override

// Admin: Testimonials
GET|POST|PUT|DELETE /admin/testimonials/{...}
```

### Frontend Tasks

#### S2-FE-01: Feature Courts
- [ ] `Features/courts/composables/useCourt.js` (CRUD actions, useForm Inertia)
- [ ] `Features/courts/components/CourtCard.vue` → tampilan card lapangan
- [ ] `Features/courts/components/CourtForm.vue` → form tambah/edit lapangan (PrimeVue InputText, Dropdown, FileUpload)
- [ ] `Features/courts/components/CourtStatusToggle.vue` → toggle Aktif/Nonaktif/Maintenance
- [ ] `Features/courts/components/CourtPhotoGallery.vue` → preview multi-foto + drag reorder
- [ ] `Features/courts/components/PriceOverrideForm.vue` → set harga khusus per tanggal
- [ ] `Features/courts/components/AuditLogTable.vue` → tabel riwayat perubahan

#### S2-FE-02: Pages Courts (Admin)
- [ ] `Pages/Admin/Courts/Index.vue` → daftar lapangan + filter status
- [ ] `Pages/Admin/Courts/Create.vue` → form tambah lapangan
- [ ] `Pages/Admin/Courts/Edit.vue` → form edit + audit log tab

#### S2-FE-03: Landing Page
- [ ] `Pages/Public/Landing.vue`
- [ ] `Features/courts/components/CourtCardPublic.vue` → versi public dengan tombol Book Now
- [ ] Section: Hero (banner, tagline, 2 CTA button)
- [ ] Section: Daftar Lapangan (grid/card dengan CourtCardPublic)
- [ ] Section: Testimoni (carousel/grid rating)
- [ ] Section: Footer (kontak, alamat, sosmed, Google Maps embed)
- [ ] Responsif (mobile-first)

### Deliverable Sprint 2
- [ ] Admin/Owner bisa CRUD lapangan, upload foto, toggle status, set price override, lihat audit log
- [ ] Landing page tampil lengkap: Hero, Card Lapangan (data real dari DB), Testimoni, Footer
- [ ] Slot lapangan bisa di-generate dan di-query via endpoint

---

## SPRINT 3 — Booking System

**Tujuan**: Customer (login & guest) bisa booking via kalender/grid; Admin bisa kelola semua booking.

### Backend Tasks

#### S3-BE-01: Booking Repository & Service
- [ ] Buat `BookingRepositoryInterface` + `BookingRepository`:
  - `getAll(filters)`, `findById(id)`, `findByNumber(booking_number)`
  - `create(data)`, `update(id, data)`
  - `getByUserId(user_id, filters)` → customer history
  - `checkConflict(court_id, date, start, end, exclude_id?)` → cek overlap
- [ ] Buat `BookingService`:
  - `createBooking(data)` → **dengan DB Transaction + SELECT FOR UPDATE locking**
  - `cancelBooking(id, reason, cancelled_by)` → validasi status
  - `rescheduleBooking(id, new_date, new_start, new_end)` → **hanya Admin, dengan locking**
  - `addManualBooking(data)` → Admin buat booking manual
  - `generateBookingNumber()` → VF-XXXXXX unik
  - `getAvailableSlots(court_id, date)` → slot tersedia (exclude yang sudah di-booking)

#### S3-BE-02: Booking Controllers & Requests
- [ ] `Public\GuestBookingController`:
  - `store()` → buat booking (guest/login)
  - `check()` → cek status via nomor booking
- [ ] `Customer\BookingController`:
  - `index()` → history booking customer
  - `cancel(id)` → cancel oleh customer sendiri
- [ ] `Admin\BookingController`:
  - `index()` → semua booking + filter
  - `store()` → booking manual
  - `reschedule(id)` → ganti slot
  - `cancel(id)` → cancel + alasan
  - `export()` → PDF/Excel
- [ ] Form Requests: `StoreBookingRequest`, `RescheduleBookingRequest`, `CancelBookingRequest`
- [ ] Buat custom Exception: `SlotNotAvailableException`

#### S3-BE-03: Routes
```
// Public
GET  /booking              → Halaman booking publik
POST /booking              → Store booking (guest/login)
GET  /booking/check        → Form cek booking (guest)
POST /booking/check        → Cek booking by nomor booking

// Customer (auth)
GET  /customer/bookings    → History booking
DELETE /customer/bookings/{id} → Cancel booking sendiri

// Admin
GET    /admin/bookings            → Daftar semua booking
POST   /admin/bookings            → Booking manual
PATCH  /admin/bookings/{id}/reschedule → Reschedule
PATCH  /admin/bookings/{id}/cancel     → Cancel
GET    /admin/bookings/export          → Export PDF/Excel
```

### Frontend Tasks

#### S3-FE-01: Feature Booking
- [ ] `Features/booking/composables/useBooking.js`
  - fetchSlots(court_id, date), submitBooking(data), cancelBooking(id), rescheduleBooking(id, data)
- [ ] `Features/booking/components/BookingCalendar.vue`:
  - DatePicker (PrimeVue Calendar) untuk pilih tanggal
  - Disable tanggal sebelum hari ini
- [ ] `Features/booking/components/SlotGrid.vue`:
  - Grid visual slot per lapangan
  - Warna: Tersedia (hijau), Terisi (merah), Dipilih (biru)
  - Klik slot untuk memilih
- [ ] `Features/booking/components/BookingForm.vue`:
  - Input: nama, HP, email (opsional)
  - Toggle: Login / Guest
  - Summary: lapangan, tanggal, slot, total harga
  - Submit button
- [ ] `Features/booking/components/BookingStatusBadge.vue`:
  - Badge warna per status: Confirmed (biru), Completed (hijau), Cancelled (merah)
- [ ] `Features/booking/components/BookingCard.vue` → card untuk history customer

#### S3-FE-02: Pages Booking (Public & Customer)
- [ ] `Pages/Public/Booking.vue` → Step-by-step booking (pilih lapangan → kalender → slot → form → konfirmasi)
- [ ] `Pages/Public/BookingTrack.vue` → Form + tampilan hasil cek booking (guest)
- [ ] `Pages/Customer/Bookings/Index.vue` → History booking dengan status badge

#### S3-FE-03: Pages Booking Management (Admin)
- [ ] `Pages/Admin/Bookings/Index.vue`:
  - DataTable PrimeVue dengan filter, search, pagination
  - Kolom: No. Booking, Customer, Lapangan, Tanggal, Slot, Status, Aksi
  - Tombol: Detail, Reschedule, Cancel
- [ ] `Pages/Admin/Bookings/Create.vue` → Form booking manual (reuse BookingForm)
- [ ] Modal Reschedule: pilih tanggal + slot baru (reuse SlotGrid)
- [ ] Modal Cancel: input alasan wajib (min 10 karakter)
- [ ] Modal Detail: info lengkap booking + riwayat status

### Deliverable Sprint 3
- [ ] Customer (login & guest) bisa booking via kalender/grid visual
- [ ] Guest booking tampilkan nomor booking, bisa cek status via `/booking/check`
- [ ] Customer login bisa lihat history booking
- [ ] Admin bisa lihat semua booking, tambah manual, reschedule, cancel
- [ ] Tidak ada double booking (diuji dengan request bersamaan)

---

## SPRINT 4 — Payment & Reports

**Tujuan**: Admin bisa konfirmasi pembayaran dan catat expense; Owner bisa lihat laporan keuangan lengkap; semua data bisa di-export.

### Backend Tasks

#### S4-BE-01: Payment Repository & Service
- [ ] Buat `PaymentRepositoryInterface` + `PaymentRepository`:
  - `getAll(filters)`, `findByBookingId(booking_id)`
  - `confirmPayment(booking_id, method, confirmed_by)` → update payment + booking status ke Completed
  - `processRefund(payment_id, amount, reason)` → update refund_amount, refund_reason
- [ ] Buat `PaymentService`:
  - `confirmPayment(booking_id, method, admin_id)` → DB Transaction
  - `processRefund(payment_id, amount, reason)` → validasi amount <= original
  - `getTransactionSummary(filters)` → aggregate data untuk tabel
- [ ] Buat `ExpenseRepository` + `ExpenseService` (CRUD expense)

#### S4-BE-02: Report Service
- [ ] Buat `ReportService` (untuk Owner Dashboard):
  - `getRevenueData(period)` → total revenue per periode
  - `getExpenseData(period)` → total expense per periode
  - `getProfitLoss(period)` → revenue - expense
  - `getRevenueByMethod(period)` → breakdown per metode bayar
  - `getRevenueByCount(period)` → breakdown per lapangan
  - `getExpenseByCategory(period)` → breakdown expense per kategori

#### S4-BE-03: Export Service
- [ ] Install `maatwebsite/excel` + `barryvdh/laravel-dompdf`
- [ ] Buat Excel exports:
  - `BookingExport` → daftar booking
  - `PaymentExport` → laporan transaksi
  - `FinancialReportExport` → laporan keuangan Owner (revenue, expense, profit)
- [ ] Buat PDF views (Blade):
  - `reports/booking-receipt.blade.php` → struk booking
  - `reports/financial-report.blade.php` → laporan keuangan
- [ ] `ExportService` → wrapper untuk generate dan stream file

#### S4-BE-04: Controllers & Routes
- [ ] `Admin\PaymentController`:
  - `index()` → daftar transaksi + filter
  - `confirm(booking_id)` → konfirmasi pembayaran
  - `refund(payment_id)` → proses refund
  - `export()` → export PDF/Excel
- [ ] `Admin\ExpenseController` (CRUD expense)
- [ ] `Owner\ReportController`:
  - `index()` → laporan keuangan Owner
  - `export()` → export PDF/Excel
- [ ] Routes:
```
// Admin: Payment
GET  /admin/payments                         → index
POST /admin/payments/{booking_id}/confirm    → confirm
POST /admin/payments/{payment_id}/refund     → refund
GET  /admin/payments/export                  → export

// Admin: Expense
GET|POST|PUT|DELETE /admin/expenses/{...}

// Owner: Reports
GET /owner/reports        → halaman laporan keuangan
GET /owner/reports/export → export PDF/Excel
```

### Frontend Tasks

#### S4-FE-01: Feature Payment
- [ ] `Features/payment/composables/usePayment.js`
- [ ] `Features/payment/components/PaymentConfirmModal.vue`:
  - Select metode bayar (Cash/Transfer/QRIS) dengan ikon
  - Tampilkan amount dari booking (auto-fill)
  - Button konfirmasi
- [ ] `Features/payment/components/RefundModal.vue`:
  - Input jumlah refund (max = amount original)
  - Input alasan refund
- [ ] `Features/payment/components/ExpenseForm.vue`:
  - Input: kategori (Dropdown), deskripsi, jumlah, tanggal
- [ ] `Features/payment/components/TransactionTable.vue`:
  - DataTable: No. Booking, Customer, Amount, Metode, Status, Tanggal, Aksi

#### S4-FE-02: Pages Payment (Admin)
- [ ] `Pages/Admin/Payments/Index.vue`:
  - Tabel transaksi + filter (tanggal range, metode, status)
  - Summary card: Total Hari Ini, Total Bulan Ini, per Metode
  - Export button (PDF/Excel)
- [ ] `Pages/Admin/Expenses/Index.vue`:
  - Tabel expense + form tambah (modal)
  - Edit & delete expense

#### S4-FE-03: Pages Reports (Owner)
- [ ] `Pages/Owner/Reports/Index.vue`:
  - Filter periode (bulan/tahun)
  - Summary: Total Revenue, Total Expense, Net Profit (dengan warna positif/negatif)
  - Revenue Breakdown: per metode bayar (donut chart), per lapangan (bar chart)
  - Expense Breakdown: per kategori (donut chart)
  - Profit/Loss trend per bulan (line chart)
  - Tabel detail transaksi
  - Export PDF & Excel

### Deliverable Sprint 4
- [ ] Admin bisa konfirmasi pembayaran (pilih metode) dan proses refund
- [ ] Admin bisa input dan kelola expense
- [ ] Owner bisa lihat laporan keuangan lengkap (revenue, expense, profit)
- [ ] Semua data bisa di-export ke PDF dan Excel

---

## SPRINT 5 — Dashboard, Notifikasi & Polish

**Tujuan**: Dashboard analytics lengkap, notifikasi real-time aktif, profile management selesai, staff management tersedia, polish UI/UX.

### Backend Tasks

#### S5-BE-01: Dashboard Data Service
- [ ] Buat `DashboardService`:
  - `getAdminStats()` → total booking hari ini, pendapatan hari ini, lapangan aktif, booking confirmed (belum bayar)
  - `getRevenueChart(period, range)` → data revenue untuk chart (harian/mingguan/bulanan)
  - `getBookingTrend(period)` → trend jumlah booking
  - `getOccupancyRate()` → per lapangan: total slot tersedia vs terisi (%)
  - `getPeakHoursHeatmap()` → matrix [jam][hari] = jumlah booking
  - `getPaymentMethodBreakdown(period)` → breakdown Cash/Transfer/QRIS
  - `getTopCourts(limit)` → lapangan tersibuk
  - `getRecentBookings(limit)` → N booking terbaru

#### S5-BE-02: Notifikasi Real-time (Reverb)
- [ ] Buat Laravel Events:
  - `BookingCreated(booking)` → implements `ShouldBroadcast`
  - `BookingRescheduled(booking)` → implements `ShouldBroadcast`
  - `BookingCancelled(booking)` → implements `ShouldBroadcast`
  - `PaymentConfirmed(booking, payment)` → implements `ShouldBroadcast`
- [ ] Buat Listeners untuk setiap event → simpan ke tabel `notifications`
- [ ] Buat `NotificationRepository` + `NotificationService`:
  - `getUnread(user_id)`, `markAsRead(id)`, `markAllAsRead(user_id)`
- [ ] Konfigurasi Reverb channels (`channels.php`):
  - Private channel per user: `App.Models.User.{id}`
- [ ] `NotificationController`:
  - `GET /notifications` → list notifikasi
  - `PATCH /notifications/{id}/read` → mark as read
  - `PATCH /notifications/read-all` → mark all as read
- [ ] Dispatch event di BookingService dan PaymentService

#### S5-BE-03: Profile Management
- [ ] `ProfileService` (sudah dibuat di Sprint 1, lengkapi):
  - `updateProfile(user_id, data)` → nama, HP, email, foto
  - `changePassword(user_id, old_pass, new_pass)`
  - `uploadAvatar(user_id, file)` → resize & store
- [ ] `ProfileController` untuk semua role:
  - `GET /profile` → tampilkan profil
  - `PUT /profile` → update profil
  - `PUT /profile/password` → ganti password

#### S5-BE-04: Staff Management (Owner)
- [ ] `StaffController` (Owner):
  - `GET /owner/staff` → daftar Admin (role=admin), read-only

#### S5-BE-05: Dashboard Controllers
- [ ] `Admin\DashboardController`:
  - `index()` → pass semua data dashboard ke Inertia
- [ ] `Owner\DashboardController`:
  - `index()` → data admin + data keuangan Owner

### Frontend Tasks

#### S5-FE-01: Feature Dashboard
- [ ] `Features/dashboard/composables/useDashboard.js` → fetch semua data dashboard
- [ ] `Features/dashboard/components/StatsCard.vue` → card metric (icon, value, label, trend)
- [ ] `Features/dashboard/components/RevenueChart.vue` → Line/Bar chart (PrimeVue Chart / ApexCharts)
- [ ] `Features/dashboard/components/BookingTrendChart.vue`
- [ ] `Features/dashboard/components/OccupancyRate.vue` → Progress bar per lapangan
- [ ] `Features/dashboard/components/PeakHoursHeatmap.vue` → Grid jam x hari dengan color intensity
- [ ] `Features/dashboard/components/PaymentMethodChart.vue` → Donut chart
- [ ] `Features/dashboard/components/TopCourtsTable.vue` → Ranking lapangan tersibuk
- [ ] `Features/dashboard/components/RecentBookingsTable.vue` → 10 booking terbaru

#### S5-FE-02: Pages Dashboard
- [ ] `Pages/Admin/Dashboard/Index.vue`:
  - Stats Cards (4 metric utama)
  - Revenue Chart + Booking Trend (toggle periode)
  - Occupancy Rate per lapangan
  - Peak Hours Heatmap
  - Payment Method Donut Chart
  - Recent Bookings Table + Top Courts
- [ ] `Pages/Owner/Dashboard/Index.vue`:
  - Semua widget Admin +
  - Profit/Loss Summary (Revenue - Expense)
  - Revenue Breakdown per lapangan & metode
  - Quick access ke halaman Reports

#### S5-FE-03: Feature Notifikasi
- [ ] `Features/notifications/composables/useNotification.js`:
  - Connect ke Reverb channel: `window.Echo.private('App.Models.User.${userId}')`
  - Listen event notifikasi → update state reaktif
  - `fetchNotifications()`, `markRead(id)`, `markAllRead()`
- [ ] `Features/notifications/components/NotificationBell.vue`:
  - Bell icon di topbar dengan badge unread count
  - Animasi ring saat notifikasi baru masuk
- [ ] `Features/notifications/components/NotificationDropdown.vue`:
  - List notifikasi (terbaru di atas)
  - Item: icon type, title, message, waktu relatif (5 menit yang lalu)
  - Klik item → mark as read + navigate ke halaman terkait
  - Tombol "Tandai semua dibaca"
  - Empty state jika tidak ada notifikasi

#### S5-FE-04: Feature Profile
- [ ] `Features/profile/composables/useProfile.js`
- [ ] `Features/profile/components/ProfileForm.vue`:
  - Upload & preview foto profil (PrimeVue FileUpload)
  - Edit nama, HP, email
- [ ] `Features/profile/components/PasswordChangeForm.vue`:
  - Input: password lama, password baru, konfirmasi password baru
  - Validasi real-time
- [ ] `Pages/[Role]/Profile/Index.vue` → gabung kedua komponen + history booking (untuk customer)

#### S5-FE-05: Staff Management
- [ ] `Pages/Owner/Staff/Index.vue`:
  - Tabel daftar Admin: nama, email, HP, bergabung, status badge
  - Filter by status (Aktif/Nonaktif)
  - Read-only (tidak ada tombol tambah/edit/hapus)

#### S5-FE-06: Polish UI/UX
- [ ] Review semua halaman di mobile (360px) dan tablet
- [ ] Tambah loading state (PrimeVue Skeleton / Spinner) pada operasi async
- [ ] Tambah empty state pada semua tabel/list kosong
- [ ] Tambah konfirmasi dialog (PrimeVue ConfirmDialog) untuk aksi destruktif
- [ ] Toast notification (PrimeVue Toast) untuk feedback operasi berhasil/gagal
- [ ] Pastikan semua form memiliki error message yang jelas
- [ ] Review konsistensi warna, spacing, tipografi seluruh aplikasi
- [ ] Test responsivitas semua halaman kritis

### Deliverable Sprint 5
- [ ] Dashboard Admin tampil dengan semua widget analytics
- [ ] Dashboard Owner dengan laporan keuangan tambahan
- [ ] Notifikasi real-time berfungsi via Reverb (booking baru, konfirmasi bayar, dll.)
- [ ] Semua user bisa edit profil dan ganti password
- [ ] Owner bisa lihat daftar staff (read-only)
- [ ] Semua halaman responsif dan polish

---

## Acceptance Criteria Keseluruhan (Demo Ready)

### Fungsional
- [ ] Guest dan customer login bisa booking lapangan tanpa double booking
- [ ] Guest bisa cek status booking via nomor booking
- [ ] Admin bisa: kelola booking, konfirmasi bayar, input expense, kelola lapangan
- [ ] Owner bisa: lihat laporan keuangan, lihat staff, akses semua fitur admin
- [ ] Notifikasi real-time berfungsi (booking baru muncul di bell tanpa refresh)
- [ ] Export PDF & Excel berjalan
- [ ] Audit log lapangan tercatat

### Non-Fungsional
- [ ] Landing page load < 2 detik
- [ ] Tidak ada double booking meskipun 2 request bersamaan
- [ ] Semua halaman responsif di mobile & tablet
- [ ] Tidak ada error console di browser pada happy path

---

## Risiko & Mitigasi

| Risiko | Dampak | Mitigasi |
|--------|--------|----------|
| Complexity DB locking menyebabkan deadlock | Tinggi | Implementasi timeout lock (nowait atau lock_timeout), unit test concurrent booking |
| Reverb tidak stabil di lokal | Sedang | Fallback ke polling jika Reverb error; siapkan setup guide |
| Export PDF lambat untuk data besar | Rendah | Queue untuk export besar; limit export ke periode wajar (maks 3 bulan) |
| PrimeVue heatmap tidak tersedia built-in | Sedang | Gunakan custom grid CSS atau ApexCharts treemap sebagai alternatif |
| Deadline demo terlalu mepet | Tinggi | Prioritaskan Sprint 1-3 (booking flow inti) untuk demo minimal |

---

## Catatan untuk Tim

> **Prioritas Demo**: Jika waktu sangat terbatas, Sprint 1, 2, dan 3 adalah yang **wajib selesai** untuk demo. Sprint 4 dan 5 bisa ditunjukkan sebagai in-progress.

> **Branching Strategy**: Gunakan `main` untuk production-ready code. Feature branch per sprint (`sprint/1-foundation`, `sprint/2-courts`, dst.)

> **Daily Standup** (jika tim): apa yang dikerjakan kemarin, hari ini, dan blocker.

---

*Dokumen ini akan diperbarui di setiap akhir sprint berdasarkan progress aktual.*
