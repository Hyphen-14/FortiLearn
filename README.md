# FortiLearn — Modern Multi-Category E-Learning Platform

**FortiLearn** adalah platform pembelajaran daring (*e-learning marketplace & learning management system*) berbasis web yang dirancang untuk mempertemukan pengajar profesional dengan pembelajar di berbagai bidang industri—mulai dari pengembangan web (*Frontend & Backend*), *UI/UX Design*, *Data & AI*, *Bisnis Digital*, hingga *Cyber Security*.

Aplikasi ini dibangun menggunakan arsitektur **Laravel** dengan antarmuka **3-Zone Dashboard** yang modern, responsif, dan terintegrasi dengan ruang belajar interaktif, sistem dompet internal, serta kontrol akses berbasis peran (*Role-Based Access Control*).

---

## ✨ Fitur yang Tersedia

### 1. Katalog & Eksplorasi Pembelajaran
- **Multi-Kategori & Pencarian Instan**: Filter kursus berdasarkan kategori bidang (*Frontend*, *Backend*, *UI/UX Design*, *Data & AI*, *Bisnis*, *Cyber Security*), pengurutan kursus, dan pencarian kata kunci.
- **Halaman Detail & Pratinjau Silabus**: Informasi lengkap mengenai instruktur, daftar bab, modul video/PDF, serta ulasan dan rating dari siswa terdaftar.
- **Wishlist Interaktif**: Simpan kursus pilihan untuk dipelajari atau dibeli di lain waktu.

### 2. Ruang Belajar Interaktif (*Classroom Player*)
- **Pemutar Video HD & Streaming Terproteksi**: Mendukung pemutaran modul video dengan kontrol kecepatan (`1.0x` – `2.0x`), *timeline scrubbing*, indikator bagian materi, mode layar penuh, serta dukungan *HTTP Range* untuk pencarian durasi video secara mulus.
- **Pembaca Dokumen PDF Terintegrasi**: Akses materi bacaan PDF secara langsung di browser maupun pengunduhan berkas bagi peserta terdaftar.

### 3. Kredit Kursus Gratis Pertama & Dompet Belajar
- **Bonus Anggota Baru**: Setiap pengguna (`student`) yang baru mendaftar secara otomatis mendapatkan **1x Kredit Kursus Gratis** untuk mengklaim 1 kursus pilihan pertamanya tanpa biaya.
- **Dompet Belajar & Checkout**: Pengisian saldo dompet internal, konfirmasi pembelian kelas, pemrosesan transaksi atomik, serta penerbitan kwitansi digital (*invoice*) resmi pada menu Riwayat Transaksi.

### 4. Studio Instruktur (*Instructor Workspace*)
- **Portal Login & Registrasi Khusus Instruktur**: Jalur autentikasi dan pendaftaran tersendiri bagi pengajar.
- **Manajemen Kursus & Curriculum Builder**: Pembuatan kursus baru, penyusunan bab (*sections*), pengunggahan materi video MP4 dan modul PDF ke penyimpanan privat, serta pengajuan moderasi publikasi.
- **Analitik Penjualan & Ulasan**: Pemantauan jumlah siswa terdaftar, total pendapatan kursus, dan umpan balik ulasan siswa.

### 5. Panel Administrasi (*Admin Console*)
- **Manajemen Pengguna**: Pencarian akun, aktivasi, dan penonaktifan akun pengguna secara aman.
- **Moderasi Kursus**: Peninjauan materi kursus yang diajukan instruktur sebelum disetujui untuk tayang (*published*) atau ditolak beserta catatan alasan.
- **Audit Log Aktivitas**: Pencatatan dan pemfilteran peristiwa penting sistem, seperti aktivitas autentikasi, moderasi, dan otorisasi akses.

---

## 👥 Pengguna, Peran, dan Hak Akses (*Roles & Access Control*)

FortiLearn membagi hak akses sistem ke dalam tiga peran utama yang dipisahkan secara ketat pada level rute, *middleware*, dan *policy*:

| Peran | Deskripsi | Hak Akses & Ruang Kerja |
| :--- | :--- | :--- |
| **Admin** (`admin`) | Pengelola utama dan moderator platform. | • Mengakses **Admin Console** (`/admin/dashboard`)<br>• Mengelola status aktif/nonaktif akun pengguna (`/admin/users`)<br>• Meninjau, menyetujui, atau menolak pengajuan kursus instruktur (`/admin/courses`)<br>• Memantau **Audit Log** aktivitas sistem (`/admin/audit-logs`) |
| **Instructor** (`instructor`) | Pengajar dan pembuat konten pembelajaran. | • Mengakses **Studio Instruktur** (`/instructor/dashboard`)<br>• Membuat, mengubah, dan menghapus kursus miliknya sendiri<br>• Mengelola bab (*sections*) dan mengunggah materi video/PDF (`/instructor/courses`)<br>• Mengajukan kursus untuk moderasi Admin<br>• Melihat laporan penjualan dan ulasan pada kursusnya (`/instructor/students`, `/instructor/reviews`) |
| **Users / Students** (`student`) | Pembelajar dan peserta kursus. | • Menjelajahi katalog, kategori, dan halaman bantuan<br>• Mendapatkan **1x Kredit Kursus Gratis** saat pertama kali mendaftar<br>• Mengelola **Dompet Belajar**, melakukan top-up, dan membeli kursus (`/wallet`, `/checkout`)<br>• Melihat riwayat transaksi & kwitansi miliknya (`/transactions`)<br>• Memutar materi video/PDF kelas yang dimiliki dan memberikan ulasan kursus |

---

## 📁 Struktur Folder

```text
FortiLearn/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Kontroler dashboard, manajemen user, moderasi kursus, & audit log
│   │   │   ├── Auth/           # Otentikasi siswa/instruktur, registrasi, & reset password
│   │   │   ├── Instructor/     # Studio instruktur, CRUD kursus, bab, materi, penjualan, & ulasan
│   │   │   └── Student/        # Katalog, dompet, checkout, riwayat transaksi, & ulasan siswa
│   │   ├── Middleware/         # RoleMiddleware, EnsureUserIsActive, & SecurityHeaders
│   │   └── Requests/           # Form Request validation (Course, Lesson, TopUp)
│   ├── Models/                 # Eloquent Models (User, Course, Section, Lesson, Enrollment, WalletTransaction, Review, AuditLog)
│   ├── Policies/               # Otorisasi kepemilikan data (CoursePolicy, TransactionPolicy, UserPolicy)
│   ├── Providers/              # Registrasi policy & aturan default password
│   ├── Services/               # Layanan inti (PaymentService, FileUploadService, DummyMediaGenerator)
│   └── Support/                # Utilitas pencatatan Audit Log
├── bootstrap/                  # Inisialisasi framework & konfigurasi middleware global
├── config/                     # Konfigurasi aplikasi, autentikasi, filesystem, & sesi
├── database/
│   ├── migrations/             # Skema tabel users, courses, sections, lessons, enrollments, transactions, reviews, audit_logs
│   └── seeders/                # Seeder katalog kursus awal & media pembelajaran
├── public/                     # Entry point publik (index.php) dan aset statis publik
├── resources/
│   ├── css/ & js/              # Aset sumber frontend (Vite)
│   └── views/
│       ├── admin/              # Antarmuka panel administrasi
│       ├── auth/               # Halaman login, login instruktur, registrasi, & lupa/reset password
│       ├── catalog/            # Katalog utama, kategori, kursus saya, wishlist, detail kelas, & bantuan
│       ├── checkout/           # Halaman konfirmasi pembelian & klaim kursus gratis
│       ├── instructor/         # Antarmuka studio instruktur & curriculum builder
│       ├── layouts/            # Layout utama 3-zona aplikasi & layout studio instruktur
│       ├── transactions/       # Riwayat transaksi & halaman kwitansi digital
│       └── wallet/             # Halaman Dompet Belajar & form top-up
├── routes/
│   └── web.php                 # Definisi seluruh rute aplikasi & pembatasan peran
├── storage/
│   └── app/private/courses/    # Direktori privat terisolasi untuk berkas video, PDF, & thumbnail kursus
└── tests/
    └── Feature/                # Suite pengujian otomatis fitur & keamanan aplikasi
```

---

## 🚀 Panduan Menjalankan Aplikasi

### Prasyarat Sistem
- **PHP** >= 8.2 (ekstensi `pdo_mysql`, `fileinfo`, `openssl`, `mbstring`, `zip` aktif)
- **Composer** v2.x
- **MySQL / MariaDB**
- **Node.js & npm** v20+

### Langkah Instalasi
1. **Clone Repositori**
   ```bash
   git clone https://github.com/Hyphen-14/FortiLearn.git
   cd FortiLearn
   ```

2. **Konfigurasi Environment**
   ```bash
   cp .env.example .env
   composer install
   php artisan key:generate
   ```

3. **Konfigurasi Database di `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fortilearn
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Migrasi & Seeding Data Awal**
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan Server Aplikasi**
   ```bash
   php artisan serve
   ```
   Akses aplikasi melalui browser di `http://127.0.0.1:8000`.

---

## 🧪 Menjalankan Pengujian Otomatis

```bash
php artisan test
```
