# 🛡️ LAPORAN KEAMANAN PERANGKAT LUNAK — JOBDESK 2
## Instructor Module, Secure File Upload Engine & Private Streaming
> **Mata Kuliah:** COMP6695001 - Secure Programming (AOL SOCS BINUS University)
> **Aplikasi:** FortiLearn (Secure E-Learning Web Application)
> **Fokus:** Analisis Keamanan File Upload, Mitigasi RCE, Pencegahan MIME-Spoofing, Mekanisme Private Streaming

---

## DAFTAR ISI
1. [Ringkasan & Ruang Lingkup](#1-ringkasan--ruang-lingkup)
2. [Threat Modeling](#2-threat-modeling)
3. [Arsitektur Pertahanan File Upload (`FileUploadService`)](#3-arsitektur-pertahanan-file-upload)
4. [Mitigasi RCE & Pencegahan MIME-Spoofing](#4-mitigasi-rce--pencegahan-mime-spoofing)
5. [Private Streaming (`LessonStreamController`)](#5-private-streaming)
6. [Kontrol Akses Modul Instructor (RBAC + Policy)](#6-kontrol-akses-modul-instructor)
7. [Pembuktian Pengujian](#7-pembuktian-pengujian)
8. [Panduan Audit White-Box untuk Kelompok Penilai](#8-panduan-audit-white-box)
9. [Keterbatasan yang Diketahui](#9-keterbatasan-yang-diketahui)

---

## 1. Ringkasan & Ruang Lingkup

| Komponen | File | Fungsi |
|---|---|---|
| Dashboard & CRUD Course | `app/Http/Controllers/Instructor/CourseController.php` | Buat, edit, hapus, ajukan course |
| Curriculum Builder | `Instructor/SectionController.php`, `Instructor/LessonController.php`, tabel `sections` | Bab dan materi berurutan |
| Upload aman | `app/Services/FileUploadService.php` | Validasi, pemindaian, penyimpanan privat |
| Streaming privat | `app/Http/Controllers/LessonStreamController.php` | `GET /courses/{course}/stream/{lesson}` |
| Thumbnail | `app/Http/Controllers/CourseThumbnailController.php` | Menyajikan thumbnail dari storage privat |
| Validasi input | `app/Http/Requests/CourseRequest.php`, `LessonRequest.php` | Aturan input server-side |
| Audit | `app/Support/Audit.php` → tabel `audit_logs` | Pencatatan upload ditolak, akses ditolak |

Route modul instructor berada di `routes/web.php` dengan middleware `auth` + `role:instructor`, dan setiap record tambahan diperiksa `CoursePolicy`.

---

## 2. Threat Modeling

| # | Ancaman (STRIDE) | Vektor | Dampak | Mitigasi |
|---|---|---|---|---|
| T1 | Elevation / RCE | Upload `shell.php` dinamai `.mp4` atau `.pdf` | Eksekusi kode di server | Magic bytes `finfo`, pemindaian signature, penyimpanan di luar `public/`, nama UUID |
| T2 | Tampering / RCE (polyglot) | PDF valid dengan `<?php ... ?>` disisipkan jauh di dalam file | Lolos pemeriksaan header saja | Pemindaian **seluruh isi file** per chunk 1MB dengan overlap |
| T3 | Spoofing | `Content-Type` palsu / ekstensi palsu | Tipe berbahaya diterima | Ekstensi dan MIME dari client diabaikan; hanya hasil `finfo` yang dipercaya |
| T4 | Tampering (Path Traversal) | Nama file `../../public/x.php` | Menulis di luar folder tujuan | Nama file asli tidak pernah dipakai di filesystem (UUID v4); nama tampilan disanitasi |
| T5 | Info Disclosure | Menebak URL file, `/storage/...` | Mengunduh materi berbayar tanpa membayar | Disk `local` privat dengan `serve=false`; hanya lewat controller ber-Policy |
| T6 | Info Disclosure (IDOR/BOLA) | Mengganti `{lesson}`/`{course}` di URL | Akses materi atau course milik orang lain | `CoursePolicy@viewContent`, cek lesson ∈ course, `CoursePolicy@update` di setiap aksi instructor |
| T7 | DoS | Upload file sangat besar / berulang | Disk dan memori habis | Batas 50MB video, 10MB dokumen dan gambar; `throttle`; pembacaan chunk |
| T8 | Elevation | Instructor menyuntik `status=published` / `instructor_id` | Self-publish, mengambil alih course orang lain | Hanya field whitelist dari `validated()`; `status`, `instructor_id`, `slug` diisi server |
| T9 | Stored XSS | Upload SVG/HTML sebagai thumbnail | Script dieksekusi di origin aplikasi | SVG/HTML tidak ada di allow-list; `getimagesize()` wajib berhasil; `nosniff` |
| T10 | Repudiation | Percobaan serangan tanpa jejak | Tidak ada bukti forensik | `audit_logs`: `file.upload_rejected`, `access.stream_denied`, `course.delete_blocked` |
| T11 | Integritas finansial | Menghapus course yang sudah dibeli siswa | Siswa kehilangan akses setelah bayar | Course dengan enrollment tidak bisa dihapus |

---

## 3. Arsitektur Pertahanan File Upload

Urutan pemeriksaan di `FileUploadService::storeSecureLessonFile()` (urutan penting; file ditolak pada pemeriksaan pertama yang gagal):

```
Request ──► [Form Request: rule dasar, ukuran kasar]
        ──► [1] Ukuran nyata di disk (filesize) ≤ 50MB video / 10MB lainnya, dan > 0
        ──► [2] finfo(FILEINFO_MIME_TYPE) ∈ {video/mp4, application/pdf, image/jpeg, image/png}
        ──► [3] Tipe terdeteksi cocok dengan slot (video ≠ pdf ≠ image)
        ──► [4] Pemindaian seluruh file: "<?php" / "<?=" / "<script" (case-insensitive)
        ──► [5] Gambar: getimagesize() harus berhasil
        ──► [6] Nama = Str::uuid() + ekstensi hasil finfo
        ──► [7] Storage::disk('local') → storage/app/private/courses/
```

Cuplikan inti:

```php
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$realMime = finfo_file($finfo, $file->getRealPath());          // bukan getClientMimeType()

if ($this->containsForbiddenSignature($file->getRealPath())) { // seluruh file, bukan hanya 1KB awal
    throw new InvalidArgumentException('File mengandung signature kode berbahaya ...');
}

$secureFilename = Str::uuid()->toString() . '.' . $validatedExtension; // bukan getClientOriginalName()
Storage::disk('local')->putFileAs('courses', $file, $secureFilename);  // storage/app/private/courses
```

**Perbaikan dari versi awal:** (a) pemindaian signature sebelumnya hanya membaca 1024 byte pertama sehingga webshell yang disisipkan di akhir PDF lolos; (b) file disimpan ke `private/private/courses` tetapi dibaca dari `private/courses`, sehingga upload asli tidak akan pernah ditemukan saat di-stream; (c) slot `image` untuk thumbnail belum ada.

`FileUploadService::delete()` hanya menghapus path yang cocok dengan pola `private/courses/<uuid v4>.(mp4|pdf|jpg|png)`, sehingga nilai database yang dimanipulasi tidak dapat dipakai untuk menghapus file lain.

---

## 4. Mitigasi RCE & Pencegahan MIME-Spoofing

**Mengapa RCE dicegah berlapis (Defense in Depth):**

1. *Tidak bisa dieksekusi* — file tidak berada di `public/` dan tidak ada route statis yang menyajikannya (`serve => false` pada disk `local`). Server web tidak pernah memetakan URL ke folder ini.
2. *Tidak bisa ditebak* — nama berupa UUID v4 acak; ekstensi berasal dari isi file, bukan dari client.
3. *Tidak lolos masuk* — konten dengan `<?php`, `<?=` atau `<script` di mana pun ditolak, termasuk yang terpotong di batas chunk (diuji).
4. *Tidak bisa dibaca sembarang orang* — akses hanya melalui controller yang memanggil `CoursePolicy@viewContent`.
5. *Tidak di-sniff browser* — respons memakai `X-Content-Type-Options: nosniff` dan `Content-Type` ditetapkan server (`video/mp4` atau `application/pdf`).

**MIME-spoofing:** `getClientOriginalExtension()` dan `getClientMimeType()` sepenuhnya dikontrol penyerang. Layanan ini hanya mempercayai `finfo` (membaca signature biner, mis. `ftyp` untuk MP4 dan `%PDF` untuk PDF). Executable `MZ...` yang dinamai `.pdf`, teks biasa yang dinamai `.mp4`, SVG, dan HTML yang dinamai `.png` semuanya ditolak.

---

## 5. Private Streaming

`GET /courses/{course}/stream/{lesson}` (route lama `/courses/{slug}/lessons/{lesson}/stream` memakai controller yang sama):

| Langkah | Pemeriksaan | Hasil jika gagal |
|---|---|---|
| 1 | Login (`auth`) | 302 → `/login` |
| 2 | `lesson.course_id == course.id` | 404 (mencegah pertukaran ID lintas course) |
| 3 | Course belum `published` dan user bukan pemilik/admin | 404 (tidak membocorkan keberadaan) |
| 4 | `Gate::inspect('viewContent', $course)` (admin, instructor pemilik, atau siswa ter-enroll) | 403 + tercatat `access.stream_denied` |
| 5 | Path harus berpola UUID di `private/courses/` | 404 |
| 6 | Respons chunk 8KB, mendukung header `Range` (206/416) | — |

Header respons: `Content-Type` tetap, `Content-Disposition` dengan nama file yang disanitasi (tidak pernah memuat path fisik), `Accept-Ranges: bytes`, `Cache-Control: private, no-store`, `X-Content-Type-Options: nosniff`.

---

## 6. Kontrol Akses Modul Instructor

- **RBAC:** `role:instructor` pada seluruh `/instructor/*`; siswa mendapat 403, tamu diarahkan ke login, akun nonaktif di-logout oleh `RoleMiddleware`.
- **Object-level (anti-BOLA):** setiap aksi memanggil `Gate::authorize('update', $course)`. Dashboard hanya meng-query `where('instructor_id', auth()->id())`.
- **Mass assignment:** Controller hanya memakai `title, category, description, price` dari `validated()`. `instructor_id`, `status`, `slug`, `thumbnail`, `course_id`, `section_id` di-set eksplisit oleh server.
- **Workflow persetujuan:** instructor hanya bisa `draft/rejected → pending_approval`. `published` hanya dapat ditetapkan admin (modul Anggota 1).
- **Referensi silang:** `section_id` divalidasi `Rule::exists('sections','id')->where('course_id', $course->id)`.
- **Rate limit:** `throttle:20,1` pada pembuatan/ubah course, `throttle:30,1` pada upload materi.

---

## 7. Pembuktian Pengujian

`php artisan test --filter=InstructorModuleSecurityTest` (33 test):

| Kategori | Skenario yang dibuktikan |
|---|---|
| RBAC | Tamu → login, siswa → 403, dashboard hanya menampilkan course milik sendiri, instructor nonaktif ditolak |
| Mass assignment | `status=published`, `instructor_id`, `slug` yang disuntik diabaikan; kategori XSS dan harga negatif ditolak |
| IDOR/BOLA | Instructor B tidak bisa melihat, mengubah, menghapus, atau menambah bab dan materi pada course instructor A; `section_id` lintas course ditolak |
| Upload sah | MP4 dan PDF tersimpan di `private/courses/<uuid>`, tidak ada di `public/`, nama asli tidak muncul di path |
| Webshell | `<?php` bertopeng `.mp4`, polyglot PDF (payload setelah 5KB), signature terpotong di batas chunk 1MB: semua ditolak dan tercatat di `audit_logs` |
| MIME-spoofing | Teks sebagai `.mp4`, header `MZ` sebagai `.pdf`, PDF di slot video, MP4 di slot PDF, SVG dan HTML sebagai thumbnail: ditolak |
| Batas ukuran | PDF > 10MB dan file kosong ditolak |
| Path traversal | Nama `../../../public/shell<>.php.pdf` disanitasi; nilai DB `private/courses/../../../.env` → 404; `delete()` mengabaikan path non-UUID |
| Streaming | Tamu 302; siswa tidak ter-enroll 403 + audit; siswa ter-enroll 200 `application/pdf`; `Range` → 206/416; lesson course lain → 404; course draft → 404 untuk non-pemilik; URL langsung `/storage/...` → 404 |
| Integritas | Course dengan siswa tidak bisa dihapus; penghapusan course ikut menghapus file di disk |

Hasil uji end-to-end manual pada server MySQL (login instructor sungguhan):

| Percobaan | Hasil |
|---|---|
| Video MP4 valid, PDF valid | Diterima, tersimpan sebagai UUID |
| `shell.mp4` berisi `<?php`, polyglot PDF, EXE bernama `.pdf`, PDF di slot video | Semua ditolak, 4 entri `file.upload_rejected` di audit log |
| Instructor lain membuka `/instructor/courses/7/edit` | 403 |
| Pemilik streaming materi draft | 200 `video/mp4`; `Range: bytes=0-99` → 206 (100 byte) |
| Siswa tak ter-enroll / instructor lain / tamu | 404 / 404 / 302 |
| `/storage/courses/<uuid>.mp4` | 404 |

---

## 8. Panduan Audit White-Box

Checklist untuk kelompok penilai (dan audit-diri):

1. `grep -rn "getClientOriginalName\|move_uploaded_file\|storeAs\|public_path\|Storage::disk('public')" app` — nama klien hanya boleh muncul di `sanitizeDisplayName()` (hanya untuk tampilan).
2. `grep -rn "finfo_file" app` — harus ada di `FileUploadService`.
3. `config/filesystems.php`: disk `local` harus `serve => false` dan tidak ada symlink `public/storage`.
4. Cek setiap method di `app/Http/Controllers/Instructor/*` memanggil `Gate::authorize` sebelum mengubah data.
5. `grep -rn "\$request->all()\|->fill(\$request" app` — tidak boleh ada pada controller instructor.
6. Uji: unggah `shell.php` bertopeng `.mp4`, polyglot PDF, ubah `{lesson}` ke ID course lain, buka `/storage/courses/<uuid>`.

---

## 9. Keterbatasan yang Diketahui

- **Batas PHP:** XAMPP default `upload_max_filesize=40M` dan `post_max_size=40M`. Agar video sampai 50MB bisa diunggah, naikkan keduanya (mis. 64M) di `php.ini`. Jika file melebihi batas PHP, Laravel menolak dengan pesan upload gagal, bukan karena logika aplikasi.
- **Pemindaian signature berbasis string** bisa memberi false-positive pada video biner yang kebetulan memuat byte `<?=` atau `<script`; itu disengaja (fail-closed) sesuai spesifikasi tim.
- Validasi `finfo` memastikan **tipe** file, bukan bahwa MP4/PDF-nya sepenuhnya bebas dari eksploit parser pemutar; mitigasinya adalah `nosniff`, tanpa eksekusi di server, dan akses hanya untuk user ter-otorisasi. Pemindaian antivirus (ClamAV) dapat ditambahkan sebagai pengembangan lanjut.
- Tidak ada fitur *reorder* drag-and-drop; urutan bab dan materi mengikuti urutan pembuatan.
