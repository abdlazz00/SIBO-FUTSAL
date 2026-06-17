# VITKA FUTSAL - Booking System v2.0

Sistem penyewaan lapangan futsal modern, responsif, dan real-time untuk **Vitka Futsal**. Aplikasi ini dikembangkan menggunakan arsitektur monolitik modern dengan performa tinggi dan desain visual **Brutalist / The Verge Light Edition (Hazard White)** yang berkarakter kuat (bingkai garis tegas, flat shadows, tipografi monospace, dan warna aksen neon).

---

## 🚀 Tech Stack & Arsitektur

*   **Backend & Framework Utama**: Laravel 12 (PHP 8.2+)
*   **Frontend Integration**: Inertia.js (Menghubungkan Laravel & Vue secara seamless tanpa REST API overhead)
*   **Frontend Framework**: Vue 3 (Composition API) dengan TypeScript (`lang="ts"`)
*   **Styling**: Tailwind CSS v4 (Desain Brutalist Kustom dengan variabel HSL terkurasi)
*   **Database**: PostgreSQL
*   **Real-time Communication**: Laravel Reverb (Websockets bawaan Laravel) & Laravel Echo
*   **Icons**: Lucide Vue Next

---

## ⚡ Fitur Utama & Alur Kerja Sistem

### 1. Booking Online (Pelanggan / Tamu)
*   **Booking Step-by-Step**: Alur intuitif mulai dari pemilihan tipe lapangan (Indoor/Outdoor) $\rightarrow$ kalender harian $\rightarrow$ grid slot waktu interaktif $\rightarrow$ formulir kontak pemesan.
*   **Anti Double-Booking (Concurrency Protection)**: Menggunakan mekanisme *database row-level locking* (`lockForUpdate`) saat pengecekan ketersediaan slot di [BookingService](file:///E:/Study%20Area/JOKI/vitka-futsal/app/Services/BookingService.php) untuk mencegah konflik jika dua user melakukan pemesanan di slot yang sama dalam waktu bersamaan.
*   **Success Receipt & Cetak Tiket**: Menampilkan popup modal animasi sukses pada halaman tanda terima ([BookingSuccess.vue](file:///E:/Study%20Area/JOKI/vitka-futsal/resources/js/Pages/Public/BookingSuccess.vue)) yang memiliki layout struk brutalist serta fitur cetak lokal (`window.print()`).
*   **Lacak Status (Guest)**: Tamu non-login bisa mengecek status booking mereka menggunakan kode booking `VF-XXXXXX` melalui halaman Lacak Booking.

### 2. Dashboard Analytics & Real-Time Feed
*   **Admin Dashboard**: Menampilkan metrik harian (pendapatan, okupansi lapangan, booking masuk), visual grafik jam-jam sibuk (*peak hours*), peringkat lapangan terlaris, dan daftar aktivitas booking terbaru.
*   **Owner Dashboard**: Menampilkan analisis keuntungan bersih periodik, omzet kotor, total pengeluaran operasional bulan ini, serta utilitas lapangan aktif.
*   **Websocket Notification Bell**: Terdapat ikon bel di topbar admin/owner yang menerima notifikasi real-time instan menggunakan Laravel Reverb ketika ada booking masuk atau ketika pembayaran telah lunas dikonfirmasi.

### 3. Modul Pembayaran & Pengeluaran (Admin)
*   **Konfirmasi Pembayaran**: Verifikasi transaksi sewa di lokasi dengan opsi metode bayar Cash, Transfer, dan QRIS. Konfirmasi ini otomatis memperbarui status sewa menjadi `completed` (Lunas).
*   **Pencatatan Pengeluaran (Expense)**: Mencatat pengeluaran operasional (listrik, gaji staf, maintenance lapangan) untuk kalkulasi laba bersih.
*   **Refund**: Memproses pembatalan berbayar dengan mengisi alasan pembatalan tertulis (min 10 karakter) dan nominal uang yang dikembalikan.

### 4. Laporan Keuangan (Owner)
*   **Laba-Rugi (Profit & Loss)**: Penghitungan otomatis dari total pemasukan dikurangi pengeluaran operasional.
*   **Visual Chart (No Heavy Libraries)**: Grafik performa bulanan digambar menggunakan CSS/SVG murni berkinerja tinggi untuk menjaga kecepatan loading halaman.
*   **Ekspor Data**: Mendukung ekspor daftar transaksi, pembayaran, dan laporan keuangan ke format CSV.

---

## 🔑 Kredensial & Hak Akses (Development)

Terdapat tiga peran (*roles*) utama di dalam sistem ini:

| Peran (Role) | Email Login | Password | Hak Akses Utama |
| :--- | :--- | :--- | :--- |
| **Owner** (Pemilik) | `owner@vitkafutsal.com` | `password` | Financial Reports, Staff List, Profit/Loss, Dashboard |
| **Admin** (Staff) | `admin@vitkafutsal.com` | `password` | Input Manual, Reschedule, Konfirmasi Bayar, Catat Expense |
| **Customer** (Member) | *Daftar baru via Register* | *Kustom* | Histori Booking, Detail Tiket, Pengajuan Batal Mandiri |

---

## 🛠️ Panduan Instalasi Lokal

### 1. Persiapan Environment
Pastikan Anda sudah menginstal **PHP 8.2+**, **Composer**, **Node.js 18+**, dan **PostgreSQL** di komputer Anda.

### 2. Kloning & Install Dependensi
```bash
# Salin file konfigurasi .env
cp .env.example .env

# Pasang dependensi PHP
composer install

# Pasang dependensi JavaScript
npm install
```

### 3. Konfigurasi Database (.env)
Sesuaikan pengaturan database PostgreSQL di file `.env` Anda:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=vitka_futsal
DB_USERNAME=laravel
DB_PASSWORD=laravel
```

### 4. Konfigurasi Websocket (Laravel Reverb)
Pastikan port Reverb dikonfigurasi dengan benar di file `.env`:
```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=849204
REVERB_APP_KEY=vitkafutsalkey123
REVERB_APP_SECRET=vitkafutsalsecret123
REVERB_HOST="127.0.0.1"
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 5. Inisialisasi Database & Seeding
Jalankan migrasi database beserta data historis (akun demo, data sewa lapangan 6 bulan terakhir, pengeluaran, dan review):
```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

### 6. Menjalankan Server
Jalankan tiga terminal berikut untuk mengoperasikan aplikasi secara penuh di lokal:

*   **Terminal 1 (Aplikasi Web)**:
    ```bash
    php artisan serve
    ```
*   **Terminal 2 (WebSocket Server)**:
    ```bash
    php artisan reverb:start
    ```
*   **Terminal 3 (Vite Assets Server)**:
    ```bash
    npm run dev
    # Atau compile untuk produksi: npm run build
    ```

Aplikasi dapat diakses melalui browser di alamat [http://127.0.0.1:8000](http://127.0.0.1:8000).
