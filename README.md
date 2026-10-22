# 🛡️ SecureAcademy — Secure E-Learning Web Application
> **AOL Group Project — COMP6695001 Secure Programming**  
> **School of Computer Science (SOCS) — Cyber Security Program, BINUS University**  
> *Target Evaluasi: White-Box Code Review & Peer Penetration Testing (Sesi 10)*

---

## 📌 1. Gambaran Umum Proyek
**SecureAcademy** adalah platform pembelajaran daring (*e-learning / Udemy clone*) yang dirancang khusus dengan metodologi **Secure Software Engineering**. Seluruh fitur dibangun dengan prinsip *Defense in Depth* agar tahan terhadap audit *white-box code review* dan simulasi serangan siber dari kelompok penilai pada Sesi 10 perkuliahan.

### Celah Kritis yang Dimatikan Sejak Desain Awal:
1. **Remote Code Execution (RCE) via File Upload:** Mencegah upload webshell/script backdoor oleh pengajar melalui inspeksi Magic Bytes (`finfo`), pengacakan nama file (UUID), dan penyimpanan terisolasi di luar direktori web (`storage/app/private/`).
2. **Race Condition & Double-Spending:** Mencegah eksploitasi transaksi paralel saat checkout course menggunakan **Pessimistic Database Locking (`lockForUpdate`)** di dalam transaksi atomik (`DB::transaction`).
3. **Price & Parameter Tampering:** Mencegah manipulasi harga course via Burp Suite/Inspect Element dengan selalu mengambil harga valid dari sisi database server.
4. **Insecure Direct Object References (IDOR):** Mencegah siswa mengakses/streaming materi video atau melihat invoice milik siswa lain tanpa hak akses via **Laravel Policies**.
5. **Mass Assignment & Privilege Escalation:** Mencegah eskalasi peran menjadi `admin` saat registrasi/update profil melalui `$fillable` model yang ketat dan middleware RBAC 3-level.
6. **OWASP Top 10 Lainnya:** Proteksi otomatis terhadap SQLi (Eloquent ORM), XSS (Blade auto-escaping), CSRF token di setiap request state-changing, Session Fixation, dan Content Security Policy (CSP).

---

## 👥 2. Pembagian Tugas 4 Anggota Tim (Work Breakdown Structure)

Setiap anggota memiliki tanggung jawab fungsional, modul pertahanan keamanan khusus, dan bagian penulisan laporan akademik.

```
┌────────────────────────────────────────────────────────────────────────┐
│                        STRUKTUR TIM 4 ORANG                            │
├────────────────────┬────────────────────┬──────────────────────────────┤
│ ANGGOTA            │ MODUL FUNGSIONAL   │ FOKUS KEAMANAN & LAPORAN     │
├────────────────────┼────────────────────┼──────────────────────────────┤
│ Anggota 1          │ Auth & Admin Core  │ RBAC, Rate Limiting, Audit   │
│ Anggota 2          │ Instructor Engine  │ Secure Upload & Anti-RCE     │
│ Anggota 3          │ Catalog & Checkout │ Anti-Race Condition, IDOR    │
│ Anggota 4          │ Classroom & QA     │ Security Headers, Auto Tests │
└────────────────────┴────────────────────┴──────────────────────────────┘
```

---

### 👨‍💻 Anggota 1: Security Lead, Auth & Admin Engine
* **Modul Fungsional:**
  - Setup fondasi repositori, Git workflow, dan standardisasi environment `.env`.
  - Halaman Otentikasi: Register, Login, Logout, Lupa Password.
  - Dashboard Admin: Mengelola data user (aktivasi/blokir), moderasi/approval status course (draft, published, rejected).
* **Tanggung Jawab Keamanan (Security Focus):**
  - Mengonfigurasi hashing password standar industri (`Argon2id` / `Bcrypt` dengan cost factor aman).
  - Mengimplementasikan regenerasi session ID saat login (`session()->regenerate()`) untuk mematikan **Session Fixation**.
  - Mengimplementasikan Rate Limiting pada endpoint login (`throttle:6,1`) untuk mitigasi **Brute-Force & Credential Stuffing**.
  - Mengonfigurasi middleware `RoleMiddleware` dan proteksi model `User` terhadap **Mass Assignment** (kolom `role` dan `balance` dilarang ada di `$fillable`).
  - Membangun tabel dan logging otomatis `AuditLog` untuk mencatat login mencurigakan atau akses ilegal.
* **Tanggung Jawab Laporan AOL:**
  - Menulis Bab Arsitektur Sistem, Threat Modeling, Sistem Autentikasi, dan Kontrol Akses Berbasis Peran (RBAC).

---

### 👨‍💻 Anggota 2: Instructor Module & Secure File Upload Engine
* **Modul Fungsional:**
  - Dashboard Pengajar (Instructor): Manajemen kursus (Buat Course, Edit Metadata, Hapus Course).
  - Curriculum Builder: Menambah bab/section dan materi pelajaran (Lesson).
  - Form Upload Materi: Upload video pembelajaran (.mp4), slide/modul (.pdf), dan thumbnail (.jpg, .png).
* **Tanggung Jawab Keamanan (Security Focus):**
  - Mengimplementasikan `App\Services\FileUploadService`:
    - Validasi ukuran file (max 50MB video, 10MB dokumen) anti-DoS.
    - **Pemeriksaan Magic Bytes via `finfo`**: Memverifikasi signature biner asli file, bukan hanya membaca ekstensi `getClientOriginalExtension()`.
    - **Pemeriksaan Signature Script**: Menolak file yang menyisipkan string eksekusi `<?php`, `<?=`, atau `<script`.
    - **UUID Obfuscation**: Mengubah nama file asli menjadi UUID v4 acak (`Str::uuid()`) untuk mencegah serangan *Path Traversal* dan *File Guessing*.
    - **Storage Isolation**: Menyimpan seluruh materi di folder privat `storage/app/private/courses/` (tidak pernah di-symlink ke `public/`).
  - Membangun **Secure Streaming Controller**:
    - Route `GET /courses/{course}/stream/{lesson}` yang memeriksa otorisasi via `CoursePolicy@viewContent`.
    - Melakukan chunked streaming response dengan header `Content-Type` yang tepat tanpa membocorkan path fisik server.
* **Tanggung Jawab Laporan AOL:**
  - Menulis Bab Analisis Keamanan File Upload, Mitigasi RCE, Pencegahan MIME-Spoofing, dan Mekanisme Private Streaming.

---

### 👨‍💻 Anggota 3: Student Catalog, Checkout & Secure Transaction Engine
* **Modul Fungsional:**
  - Katalog Pembelajaran Publik & Mahasiswa: Eksplorasi course, filter kategori, pencarian course, dan halaman detail kursus.
  - Sistem Saldo / Virtual Wallet: Tampilan saldo dompet dan antarmuka simulasi Top-up.
  - Alur Checkout & Pembelian Course: Keranjang/tombol beli langsung, verifikasi pembelian, dan pembuatan bukti bayar / invoice.
  - Riwayat Transaksi: Daftar transaksi keluar-masuk saldo pengguna.
* **Tanggung Jawab Keamanan (Security Focus):**
  - Mengimplementasikan `App\Services\PaymentService`:
    - **Pencegahan Race Condition (Double-Spending / TOCTOU):** Menggunakan `DB::transaction()` dengan klausa **Pessimistic Locking (`User::lockForUpdate()`)** agar saldo terkunci saat transaksi berlangsung.
    - **Pencegahan Price Tampering:** Harga course WAJIB diambil langsung dari basis data server, sama sekali tidak boleh menerima nilai harga dari form/body HTTP request client.
    - **Pengecekan Saldo Negatif:** Validasi matematis bahwa saldo akhir tidak boleh bernilai negatif ($balance \ge price$).
    - **Anti-Duplicate Enrollment:** Proteksi unique constraint pada database dan validasi ganda agar siswa tidak membayar course yang sama dua kali.
  - Mengimplementasikan `TransactionPolicy` untuk mematikan **IDOR pada Invoice/Kwitansi**: Siswa hanya dapat melihat invoice miliknya sendiri.
* **Tanggung Jawab Laporan AOL:**
  - Menulis Bab Integritas Finansial, Analisis Serangan Concurrency (Race Condition), dan Mitigasi Price Tampering / IDOR.

---

### 👨‍💻 Anggota 4: Student Classroom, DevSecOps, QA Security Testing & Report Coordinator
* **Modul Fungsional:**
  - Ruang Belajar Mahasiswa (Classroom Player): Antarmuka pemutar video materi, navigasi daftar materi, dan penanda materi selesai.
  - PDF In-browser Viewer yang aman untuk materi modul bacaan.
* **Tanggung Jawab Keamanan (Security Focus):**
  - Mengonfigurasi `SecurityHeaders` Middleware:
    - `X-Content-Type-Options: nosniff` (mencegah MIME-sniffing).
    - `X-Frame-Options: DENY` (mencegah Clickjacking).
    - `Content-Security-Policy (CSP)` (mencegah eksekusi skrip jahat / XSS).
    - `Referrer-Policy: strict-origin-when-cross-origin`.
  - Mengembangkan Automated Security Test Suite (`tests/Feature/SecurityArchitectureTest.php`):
    - Unit & Feature test untuk simulasi serangan Upload Webshell.
    - Feature test untuk Race Condition / Concurrency testing pada transaksi wallet.
    - Feature test untuk Broken Access Control (student mencoba buka route admin).
    - Feature test untuk verifikasi perlindungan Mass Assignment.
* **Tanggung Jawab Laporan AOL:**
  - Bertindak sebagai koordinator kompilasi **Application Report** (dokumen lengkap fitur dan mitigasi keamanan aplikasi).
  - Menyiapkan metodologi pengujian, panduan pentest, dan template **Code Review Report** untuk mengaudit aplikasi kelompok lawan di Sesi 10.

---

## 📜 3. Aturan Emas Coding Tim (Security Do's & Don'ts)

Semua anggota tim **WAJIB** mematuhi panduan ini saat menulis kode agar tidak meninggalkan celah saat di-review kelompok lawan:

| Komponen | ❌ DILARANG KERAS (Vulnerable) | ✅ WAJIB DILAKUKAN (Secure) |
| :--- | :--- | :--- |
| **Model User** | Menambahkan `role` atau `balance` ke dalam `$fillable` array. | Biarkan `$fillable` hanya untuk atribut profil dasar (`name`, `email`, `password`). Ubah role/balance hanya via service/controller khusus. |
| **File Upload** | Menyimpan materi ke `public/` atau `storage/app/public/`. | Gunakan `storage/app/private/courses/` dan proses lewat `FileUploadService`. |
| **Nama File** | Menyimpan nama file sesuai input pengguna (`$file->getClientOriginalName()`). | Ganti nama file menjadi random UUID (`Str::uuid() . '.' . $ext`). |
| **Harga Course** | Mengambil harga dari request (`$request->price`) saat checkout. | Selalu query harga valid dari database (`$course->price`). |
| **Transaksi Saldo**| Melakukan update saldo biasa (`$user->balance -= $price; $user->save()`). | Bungkus dalam `DB::transaction()` dan pasang `lockForUpdate()`. |
| **Query Database** | Menggunakan konkatenasi string mentah pada query (`DB::raw("SELECT * WHERE ...")`). | Gunakan Eloquent ORM atau Query Builder berparameter (`where('id', $id)`). |
| **Tampilan Blade** | Menggunakan tag unescaped `{!! $data !!}` untuk input pengguna. | Selalu gunakan tag auto-escaping `{{ $data }}`. |
| **Akses File/Materi**| Mengembalikan link file statis langsung ke browser pengguna. | Gunakan controller streaming yang dilindungi `CoursePolicy@viewContent`. |

---

## 🚀 4. Panduan Menjalankan Project di Komputer Lokal

### Prasyarat Sistem:
- PHP >= 8.2 (Pastikan ekstensi `pdo_mysql`, `fileinfo`, `zip`, `openssl`, `mbstring` aktif di `php.ini`)
- Composer (v2.x)
- MySQL / MariaDB (via XAMPP / Laragon)
- Node.js & npm (v20+)

### Langkah Instalasi:

1. **Clone Repositori:**
   ```bash
   git clone <URL_REPOSITORY_GITHUB>
   cd Aol
   ```

2. **Salin File Konfigurasi Environment:**
   ```bash
   copy .env.example .env
   ```

3. **Install Dependensi PHP & Generate Application Key:**
   ```bash
   composer install
   php artisan key:generate
   ```

4. **Konfigurasi Database MySQL (di file `.env`):**
   Nyalakan MySQL di XAMPP Control Panel, lalu sesuaikan `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=secure_academy
   DB_USERNAME=root
   DB_PASSWORD=
   ```
   *(Buat database baru bernama `secure_academy` di phpMyAdmin jika belum ada)*

5. **Jalankan Migrasi Database dan Seeder Akun Uji Coba:**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Development Server:**
   ```bash
   php artisan serve
   ```
   Aplikasi dapat diakses di browser: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## 🔑 5. Akun Uji Coba Bawaan (Default Seeded Accounts)

Setelah menjalankan `php artisan migrate --seed`, akun berikut siap digunakan untuk testing:

| Role | Email | Password | Saldo Awal | Fungsi / Hak Akses |
| :--- | :--- | :--- | :--- | :--- |
| **Admin** | `admin@secureacademy.test` | `PasswordAdmin123!` | Rp 0 | Kelola user, approval course, cek audit log |
| **Instructor** | `instructor@secureacademy.test` | `PasswordInstruktur123!` | Rp 0 | Buat course, upload video materi pelajaran |
| **Student 1** | `student1@secureacademy.test` | `PasswordStudent123!` | Rp 500.000 | Beli course, stream materi berbayar |
| **Student 2** | `student2@secureacademy.test` | `PasswordStudent123!` | Rp 100.000 | Uji coba saldo tidak cukup / uji coba IDOR |

---

## 🧪 6. Menjalankan Automated Security Tests

Untuk memverifikasi bahwa pertahanan keamanan aktif dan bekerja:
```bash
php artisan test --filter=SecurityArchitectureTest
```

Test suite ini memverifikasi:
- ✅ **Mass Assignment Defense:** Percobaan menyuntikkan `role=admin` dan saldo via payload request otomatis digagalkan.
- ✅ **Webshell & Magic Bytes Inspection:** Percobaan upload file berbahaya (skrip PHP bertopeng video) otomatis ditolak dan menghasilkan exception.
- ✅ **Balance Protection:** Pembelian dengan saldo tidak mencukupi otomatis digagalkan dan di-rollback.

---

## 🌿 7. Strategi Branching Git

Untuk mencegah konflik kode antar 4 anggota, gunakan alur kerja Git berikut:

- `main` : Hanya berisi kode yang sudah lulus pengujian dan siap dipresentasikan di Sesi 10.
- `develop` : Tempat penggabungan fitur-fitur dari anggota tim.
- `feature/auth-admin` : Branch kerja Anggota 1.
- `feature/instructor-upload` : Branch kerja Anggota 2.
- `feature/student-checkout` : Branch kerja Anggota 3.
- `feature/classroom-tests` : Branch kerja Anggota 4.

### Alur Kerja Harian:
1. Sebelum mulai coding: `git pull origin develop`
2. Pindah ke branch fitur masing-masing: `git checkout feature/<nama-fitur>`
3. Commit secara teratur dengan pesan yang jelas.
4. Buat Pull Request (PR) ke branch `develop` dan lakukan peer-review sebelum di-merge.

---

## 📋 8. Rencana Code Review Kelompok Lawan (Sesi 10)

Pada Sesi 10, repositori kita akan dibekukan (*frozen*) dan ditukar dengan kelompok lain untuk **White-box Code Assessment**.  
Gunakan daftar uji berikut baik untuk mengaudit diri sendiri maupun saat mengaudit kode kelompok lawan:

1. **Audit File Upload:**
   - Cari fungsi `move_uploaded_file`, `storeAs`, atau `getClientOriginalName`.
   - Cek apakah file disimpan di folder `public/`.
   - Cek apakah ekstensi diverifikasi hanya dengan regex string atau menggunakan magic bytes (`mime_content_type` / `finfo`).
2. **Audit Broken Access Control / IDOR:**
   - Cek endpoint download/streaming: apakah ada pengecekan kepemilikan/enrollment atau hanya berdasarkan ID di URL (`/lesson/{id}/download`).
   - Cek controller update profil: apakah parameter role dapat diubah sembarangan.
3. **Audit Concurrency & Race Condition:**
   - Cari logika update saldo: apakah menggunakan `lockForUpdate()` di dalam transaksi atomik, atau hanya `update(['balance' => $newBalance])` biasa.
4. **Audit SQL Injection & XSS:**
   - Cari query mentah `DB::raw()` tanpa binding `?`.
   - Cari tag Blade unescaped `{!! ... !!}` yang merender input pengguna tanpa sanitasi.
