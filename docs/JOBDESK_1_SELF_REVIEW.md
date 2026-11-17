# Self Review White-Box: Jobdesk 1 (Auth & Admin Engine)

> **Mata Kuliah:** COMP6695001 Secure Programming (AOL SOCS BINUS University)
> **Aplikasi:** FortiLearn
> **Cakupan:** branch `feature/auth-admin`, dibandingkan dengan commit dasar `5b41a75`
> **Tujuan:** mengaudit modul sendiri dengan cara yang sama seperti kelompok penilai pada Sesi 10, lalu menutup celah yang ditemukan sebelum kode dibekukan.

---

## 1. Metode

1. **Review diff otomatis.** Seluruh perubahan `5b41a75..feature/auth-admin` ditelusuri dari input pengguna sampai operasi sensitif: form login, register, lupa dan reset kata sandi, filter dan aksi admin, serta isi `audit_logs`. Hanya temuan dengan keyakinan minimal 8 dari 10 yang dilaporkan.
2. **Penelusuran alur bisnis secara manual.** Fitur moderasi course diuji dari sudut pandang instruktur yang berniat jahat: apa yang masih bisa diubah setelah admin menyetujui course?
3. **Bukti.** Setiap temuan yang diperbaiki punya test otomatis yang gagal sebelum perbaikan dan lulus sesudahnya.

## 2. Hasil review diff

Tidak ada temuan dengan keyakinan 8 atau lebih pada kode yang ditambahkan branch ini. Area yang diperiksa:

| Area | Hasil pemeriksaan |
|---|---|
| SQL injection | Pencarian pengguna memakai parameter terikat dan `ESCAPE '!'`; filter peran, status, status course, dan jenis event dibatasi `Rule::in`. |
| XSS | Tidak ada `{!! !!}` baru. Judul course, alasan penolakan, payload audit, dan email di URL reset dicetak dengan `{{ }}`. |
| Kontrol akses admin | Semua route `/admin/*` melewati `auth` dan `role:admin`, lalu `UserPolicy` dan `CoursePolicy::moderate` di controller. Admin tidak bisa mengubah status dirinya sendiri atau admin lain. |
| Sesi | ID sesi diganti saat login. Remember token dirotasi dan sesi database dihapus saat akun dinonaktifkan atau kata sandi direset. `EnsureUserIsActive` memutus sesi yang tersisa. |
| Reset kata sandi | Tautan dibangun dari `APP_URL` (bukan header `Host`), token tidak pernah masuk audit log, pesan sama untuk email terdaftar maupun tidak, token berlaku 30 menit. |
| Mass assignment | Kolom `rejection_reason`, `reviewed_by`, `reviewed_at`, `approved_at` tidak ada di `Course::$fillable`. |
| CSRF | `CsrfProtectionTest` mengaktifkan ulang pemeriksaan CSRF (Laravel mematikannya saat test) dan membuktikan semua route auth dan admin yang mengubah data menolak request tanpa token. |

## 3. Temuan yang diperbaiki

### SR-01: Moderasi course bisa dilewati setelah course terbit

| | |
|---|---|
| **Severity** | Medium |
| **Kategori** | Broken Access Control (business logic), OWASP A01 |
| **Lokasi awal** | `app/Http/Controllers/Instructor/CourseController.php` `update()`, `LessonController.php` `store()`, `SectionController.php` `store()` dan `update()` |
| **Status** | Diperbaiki |

**Masalah.** Setelah admin menyetujui course, instruktur masih bisa mengganti judul, deskripsi, harga, thumbnail, bab, dan menambah materi baru. Status tetap `published`, sehingga semua perubahan langsung tampil di katalog tanpa pernah dilihat admin. Persetujuan admin hanya melindungi versi pertama course.

**Skenario serangan.** Instruktur mengajukan course yang wajar sampai disetujui. Setelah terbit, ia mengganti deskripsi, menaikkan harga, dan mengunggah PDF baru berisi tautan phishing. Pembeli baru dan siswa lama langsung melihat konten yang tidak pernah direview.

**Perbaikan.**

1. `Course::flagForReReview()` dipanggil sebelum setiap perubahan konten oleh instruktur. Course yang `published` kembali ke `pending_approval`, keluar dari katalog dan checkout, dan event `course.resubmitted_after_edit` dicatat.
2. Kolom baru `courses.approved_at` menyimpan waktu persetujuan terakhir. Admin mengisinya saat menerbitkan course.
3. `Course::isAccessibleBy()` dipakai di halaman course, daftar "Kursus Saya", streaming materi, dan thumbnail. Publik hanya melihat course `published`. Siswa yang sudah membeli tetap bisa membuka course selama menunggu review ulang (atau setelah perubahan ditolak), karena mereka sudah membayar dan course pernah disetujui.
4. `Course::lessonIsReleasedTo()` menyembunyikan materi yang dibuat setelah `approved_at` dari siswa, baik di daftar materi maupun di endpoint streaming (respons 404). Materi itu baru terbuka setelah admin menyetujui ulang. Instruktur pemilik dan admin tetap bisa melihatnya untuk review.
5. Menghapus materi atau bab tidak memicu review ulang karena tidak menambahkan konten baru.
6. Antarmuka: halaman edit instruktur memberi peringatan sebelum course terbit diubah, dan halaman review admin menandai materi yang "Baru sejak terakhir disetujui".

**Bukti.** `tests/Feature/CourseReModerationSecurityTest.php` (13 test), antara lain:

- mengubah metadata, menambah materi, atau menambah bab pada course terbit mengembalikannya ke antrean review;
- pembeli baru tidak bisa checkout course yang menunggu review ulang;
- siswa lama tetap bisa membuka materi yang disetujui, tetapi mendapat 404 untuk materi baru;
- setelah admin menyetujui ulang, materi baru terbuka; jika perubahan ditolak, materi baru tetap tersembunyi;
- course berstatus draft tidak terpengaruh.

## 4. Risiko sisa yang diterima

| ID | Risiko | Alasan diterima / mitigasi |
|---|---|---|
| RR-01 | Lockout login per pasangan (email, IP) tidak menahan brute force terdistribusi dari banyak IP ke satu akun. | Lockout per email saja memungkinkan penyerang mengunci akun korban dengan sengaja (account lockout DoS). Batas 20 percobaan per menit per IP dan aturan kata sandi kuat tetap berlaku. |
| RR-02 | Enumerasi akun lewat perbedaan waktu respons: login dengan email tak terdaftar sedikit lebih cepat (tanpa hash), dan lupa kata sandi untuk email terdaftar sedikit lebih lambat (email benar-benar dikirim). | Isi respons sudah identik. Perbaikan penuh butuh hash palsu dan antrean email (queue); dicatat untuk pengembangan berikutnya. |
| RR-03 | CSP mengizinkan `script-src 'unsafe-inline'` (milik modul Security Headers, Jobdesk 4). | Semua output Blade di modul ini di-escape, jadi tidak ada XSS yang diketahui. Tanpa `unsafe-inline`, CSP akan menjadi lapisan kedua jika XSS muncul. Direkomendasikan ke Jobdesk 4: pindahkan skrip inline ke file atau pakai nonce. |
| RR-04 | `status` masih ada di `Course::$fillable`. | Tidak bisa dieksploitasi saat ini karena `CourseRequest` tidak menerima `status`. Mudah menjadi celah jika ada controller baru yang memakai `fill($request->all())`. Direkomendasikan ke Jobdesk 2 untuk menghapusnya dari `$fillable`. |
| RR-05 | Selama menunggu review ulang, siswa lama melihat judul dan deskripsi versi baru. | Hanya teks metadata, tidak ada file baru yang terbuka. Konten berisiko (materi baru) tetap tersembunyi sampai disetujui. |
