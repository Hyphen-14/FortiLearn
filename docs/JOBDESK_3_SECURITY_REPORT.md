# 🛡️ DOKUMENTASI LAPORAN KEAMANAN PERANGKAT LUNAK — JOBDESK 3
## Student Catalog, In-App Wallet, Checkout & Secure Transaction Engine
> **Mata Kuliah:** COMP6695001 - Secure Programming (AOL SOCS BINUS University)  
> **Program Studi:** Cyber Security  
> **Aplikasi:** FortiLearn (Secure E-Learning Web Application)  
> **Fokus Penilaian:** Desain Arsitektur Keamanan, Mitigasi Serangan Concurrency/Finansial, dan Ketahanan Audit *White-Box Code Review* (Sesi 10)

---

## DAFTAR ISI
1. [BAB I: Ringkasan Eksekutif & Ruang Lingkup Modul](#bab-i-ringkasan-eksekutif--ruang-lingkup-modul)
2. [BAB II: Threat Modeling & Analisis Vektor Serangan](#bab-ii-threat-modeling--analisis-vektor-serangan)
3. [BAB III: Konsep Keamanan & Implementasi Source Code](#bab-iii-konsep-keamanan--implementasi-source-code)
4. [BAB IV: Pembuktian Pengujian Keamanan (Security Verification)](#bab-iv-pembuktian-pengujian-keamanan-security-verification)
5. [BAB V: Panduan Pengujian White-Box untuk Kelompok Penilai](#bab-v-panduan-pengujian-white-box-untuk-kelompok-penilai)

---

## BAB I: Ringkasan Eksekutif & Ruang Lingkup Modul

Modul **Jobdesk 3** berfokus pada **Domain Keuangan dan Transaksi Pengguna** dalam platform pembelajaran FortiLearn. Dalam e-commerce dan aplikasi berbasis kursus berbayar, domain transaksi merupakan sasaran bernilai tinggi (*high-value target*) bagi penyerang. Kegagalan dalam menjamin integritas transaksi dapat menyebabkan kerugian finansial, manipulasi kepemilikan materi berbayar secara ilegal, dan kebocoran data privasi transaksi antar-pengguna.

### Fitur Fungsional Utama yang Dibangun:
1. **Katalog Kursus Publik & Siswa (`CatalogController`):** Eksplorasi kursus, filter pengurutan harga, dan mesin pencarian berbasis kata kunci.
2. **Sistem Dompet Internal (`WalletController`):** Manajemen saldo virtual siswa dan fitur simulasi pengisian saldo (*Top-Up*) yang aman.
3. **Engine Checkout & Pembelian Kursus (`CheckoutController` & `PaymentService`):** Halaman verifikasi saldo, ringkasan pesanan, dan eksekusi pemotongan saldo secara atomik.
4. **Riwayat Mutasi & Kwitansi Resmi (`TransactionController`):** Pelacakan riwayat transaksi keluar-masuk serta penerbitan kwitansi pembayaran berstatus sah.

---

## BAB II: Threat Modeling & Analisis Vektor Serangan

Berdasarkan metodologi **STRIDE** dan **OWASP Top 10 for Web Applications**, berikut adalah 6 vektor serangan utama yang diidentifikasi pada modul ini beserta dampak risikonya:

```
+--------------------------------------------------------------------------------------------------+
|                                    THREAT MODELING JOBDESK 3                                     |
+--------------------------+-----------------------+-----------------------------------------------+
| Vektor Serangan          | Kategori STRIDE / OWASP| Dampak Risiko Jika Tidak Dimitigasi          |
+--------------------------+-----------------------+-----------------------------------------------+
| 1. Race Condition / TOCTOU| Tampering / Broken    | Siswa dapat membelanjakan saldo yang sama     |
|    (Double Spending)     | Access Control        | berkali-kali secara simultan (saldo negatif). |
+--------------------------+-----------------------+-----------------------------------------------+
| 2. Parameter / Price     | Tampering             | Penyerang mengubah harga kursus menjadi Rp 0  |
|    Tampering             |                       | via inspect element / Burp Suite Repeater.    |
+--------------------------+-----------------------+-----------------------------------------------+
| 3. Insecure Direct Object| Broken Access Control | Siswa A dapat melihat atau mengunduh invoice  |
|    References (IDOR)     | (OWASP Top 10 #1)     | milik Siswa B dengan menebak URL / ID.        |
+--------------------------+-----------------------+-----------------------------------------------+
| 4. Negative Balance /    | Input Validation /    | Mengisi saldo dengan nilai minus (-Rp 50.000) |
|    Overflow Tampering    | Business Logic Error  | atau nilai sangat besar yang memicu overflow. |
+--------------------------+-----------------------+-----------------------------------------------+
| 5. SQL Injection (SQLi)  | Injection             | Bypass login, pembocoran seluruh database     |
|    pada Form Pencarian   | (OWASP Top 10 #3)     | melalui manipulasi parameter string pencarian.|
+--------------------------+-----------------------+-----------------------------------------------+
| 6. Cross-Site Request    | Broken Integrity      | Korban dipaksa membeli kursus atau top-up     |
|    Forgery (CSRF)        | (OWASP Top 10 #8)     | melalui link jahat dari situs web lain.       |
+--------------------------+-----------------------+-----------------------------------------------+
```

---

## BAB III: Konsep Keamanan & Implementasi Source Code

### 3.1 Mitigasi Race Condition & Double Spending: Database Pessimistic Locking
* **Problem:** Pada sistem tradisional tanpa konkurensi aman, jika siswa memiliki saldo Rp 200.000 dan mengirim 2 request pembelian kursus seharga Rp 200.000 dalam selang waktu beberapa milidetik (*Time-of-Check to Time-of-Use / TOCTOU*), kedua request akan membaca saldo awal mencukupi sebelum request pertama selesai menulis ke database. Akibatnya, kedua kursus berhasil didapatkan dan saldo menjadi negatif (-Rp 200.000).
* **Solusi Pertahanan:** Menggunakan **Pessimistic Locking (`lockForUpdate()`)** yang mengeksekusi sintaks SQL `SELECT ... FOR UPDATE` di dalam blok transaksi atomik `DB::transaction()`. Row database pengguna dikunci secara eksklusif hingga transaksi selesai (*commit* atau *rollback*), sehingga request paralel kedua dipaksa antre dan akan langsung gagal saat membaca saldo yang sudah berkurang.

```php
// File: app/Services/PaymentService.php

public function purchaseCourse(User $user, int $courseId): array
{
    return DB::transaction(function () use ($user, $courseId) {
        // [BARIS 1]: Pessimistic Locking pada row pengguna di tabel users
        // SELECT * FROM users WHERE id = ? FOR UPDATE;
        $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

        // [BARIS 2]: Anti-Price Tampering: Harga WAJIB ditarik dari database server
        $course = Course::where('id', $courseId)->firstOrFail();

        if ($course->status !== 'published') {
            throw new Exception('Course ini tidak tersedia untuk dibeli.');
        }

        // [BARIS 3]: Anti-Duplicate Enrollment: Cek kepemilikan ganda
        $alreadyEnrolled = Enrollment::where('user_id', $lockedUser->id)
            ->where('course_id', $course->id)
            ->exists();

        if ($alreadyEnrolled) {
            throw new Exception('Anda sudah terdaftar pada course ini.');
        }

        $price = (float) $course->price;
        $currentBalance = (float) $lockedUser->balance;

        // [BARIS 4]: Validasi kecukupan saldo yang terisolasi dari proses lain
        if ($currentBalance < $price) {
            throw new Exception('Saldo tidak mencukupi untuk membeli course ini.');
        }

        // [BARIS 5]: Pemotongan saldo atomik
        $newBalance = $currentBalance - $price;
        $lockedUser->balance = $newBalance;
        $lockedUser->save();

        // [BARIS 6]: Pencatatan mutasi transaksi finansial
        $transaction = WalletTransaction::create([
            'reference_number' => 'TRX-BUY-' . strtoupper(Str::random(10)),
            'user_id' => $lockedUser->id,
            'course_id' => $course->id,
            'type' => 'purchase',
            'amount' => $price,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'status' => 'completed',
            'description' => 'Pembelian course: ' . $course->title,
        ]);

        // [BARIS 7]: Pemberian hak akses kurikulum
        $enrollment = Enrollment::create([
            'user_id' => $lockedUser->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
        ]);

        return [
            'transaction' => $transaction,
            'enrollment' => $enrollment,
        ];
    });
}
```

---

### 3.2 Mitigasi Price & Parameter Tampering: Zero Trust Client Input
* **Problem:** Banyak developer pemula menaruh input harga di form checkout, misalnya `<input type="hidden" name="price" value="250000">`, lalu di controller mengambil `$price = $request->price`. Penyerang cukup membuka tab Inspect Element atau Burp Suite dan mengubah nilainya menjadi `price=0` atau `price=-50000`.
* **Solusi Pertahanan:** Form checkout pada `FortiLearn` sama sekali **TIDAK PERNAH mengirimkan field harga (`price`)**. Controller hanya menerima pengidentifikasi model `Course $course` melalui Route Model Binding, lalu controller meneruskan `$course->id` ke service layer yang memanggil harga resmi dari record database yang terenkripsi dan terverifikasi.

```php
// File: app/Http/Controllers/Student/CheckoutController.php

public function processCheckout(Request $request, Course $course, PaymentService $paymentService): RedirectResponse
{
    $user = auth()->user();

    try {
        // Keamanan: $request->all() sama sekali tidak dibaca untuk parameter nominal harga!
        // Harga kursus $course->price diambil langsung dari sisi server di dalam PaymentService.
        $result = $paymentService->purchaseCourse($user, $course->id);
        $transaction = $result['transaction'];

        return redirect()->route('transactions.show', $transaction->id)
            ->with('success', 'Pembelian berhasil! Anda kini resmi terdaftar di kelas ' . $course->title . '.');
    } catch (Exception $e) {
        return redirect()->route('checkout.confirm', $course->id)
            ->with('error', $e->getMessage());
    }
}
```

---

### 3.3 Mitigasi Insecure Direct Object References (IDOR): Granular Authorization Policy
* **Problem:** Pada endpoint kwitansi `GET /transactions/{id}`, tanpa proteksi otorisasi, Siswa B (penyerang) dapat mengubah `{id}` menjadi `1`, `2`, atau ID milik Siswa A untuk mengintip nama lengkap, riwayat belanja, dan saldo Siswa A (pelanggaran privasi dan kerahasiaan data).
* **Solusi Pertahanan:** Mengimplementasikan **Laravel Policy (`TransactionPolicy`)** dan mengeksekusi `Gate::authorize('viewReceipt', $transaction)` di setiap akses detail kwitansi.

```php
// File: app/Policies/TransactionPolicy.php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletTransaction;

class TransactionPolicy
{
    /**
     * Memastikan hanya pemilik transaksi sah atau administrator yang berhak melihat kwitansi.
     */
    public function viewReceipt(User $user, WalletTransaction $transaction): bool
    {
        // 1. Administrator memiliki hak audit sistem
        if ($user->isAdmin()) {
            return true;
        }

        // 2. Siswa biasa HANYA berhak jika user_id pada transaksi sama dengan ID user login
        return (int) $transaction->user_id === (int) $user->id;
    }
}
```

Pada controller, otorisasi dipanggil secara ketat:
```php
// File: app/Http/Controllers/Student/TransactionController.php

public function show(WalletTransaction $transaction): View
{
    // Jika user_id != auth()->id(), Laravel seketika menghentikan eksekusi
    // dan memunculkan respons HTTP 403 Forbidden!
    Gate::authorize('viewReceipt', $transaction);

    $transaction->load(['course', 'user']);

    return view('transactions.invoice', [
        'transaction' => $transaction,
    ]);
}
```

---

### 3.4 Mitigasi Negative Values & Numeric Manipulation: Strict Form Request Validation
* **Problem:** Penyerang memasukkan nilai negatif pada form Top-Up (misal `-Rp 100.000`) atau string desimal khusus yang dapat merusak integritas tipe data unsigned integer pada database.
* **Solusi Pertahanan:** Validasi tingkat Form Request (`TopUpRequest`) dengan tipe integer murni, batas bawah `min:10000` (Rp 10.000) dan batas atas `max:10000000` (Rp 10.000.000).

```php
// File: app/Http/Requests/TopUpRequest.php

public function rules(): array
{
    return [
        'amount' => [
            'required',
            'integer',       // Menolak desimal, floating point tricks, dan string alfabet
            'min:10000',     // Menolak nilai 0 dan nilai negatif (Anti-Negative Balance)
            'max:10000000',  // Mencegah overflow integer dan eksploitasi batas maksimum
        ],
    ];
}
```

---

### 3.5 Mitigasi SQL Injection pada Pencarian Katalog: Parameterized Eloquent Queries
* **Problem:** Kode rentan yang menggunakan query mentah: `DB::select("SELECT * FROM courses WHERE title LIKE '%" . $request->q . "%'")` memungkinkan penyerang menyuntikkan payload SQLi seperti `' OR 1=1 --`.
* **Solusi Pertahanan:** Seluruh query pada `CatalogController` menggunakan Eloquent ORM dan PDO Prepared Statements dengan parameter binding otomatis.

```php
// File: app/Http/Controllers/Student/CatalogController.php

if ($search = $request->query('q')) {
    $query->where(function ($q) use ($search) {
        // Nilai $search dikirimkan ke database driver sebagai bind parameter (?),
        // bukan digabungkan secara langsung ke dalam string query SQL.
        $q->where('title', 'like', '%' . $search . '%')
          ->orWhere('description', 'like', '%' . $search . '%');
    });
}
```

---

### 3.6 Mitigasi Cross-Site Request Forgery (CSRF): Synchronizer Token Pattern
* Seluruh form state-changing (`POST /wallet/topup` dan `POST /checkout/{course}`) diwajibkan menyertakan direktif Blade `@csrf`.
* Middleware Laravel `Illuminate\Foundation\Http\Middleware\VerifyCsrfToken` akan memverifikasi token sebelum request mencapai controller. Request tanpa token valid akan langsung ditolak dengan respons `HTTP 419 Page Expired`.

---

## BAB IV: Pembuktian Pengujian Keamanan (Security Verification)

Seluruh mekanisme keamanan di atas telah diverifikasi menggunakan pengujian fungsional dan penetrasi otomatis berbasis **PHPUnit** pada file `tests/Feature/StudentCheckoutSecurityTest.php`.

### Hasil Eksekusi Test Runner:
```text
$ php artisan test --filter=StudentCheckoutSecurityTest

   PASS  Tests\Feature\StudentCheckoutSecurityTest
  ✓ catalog displays published courses and safe search                     0.02s  
  ✓ top up validation strictly rejects negative and zero values             0.02s  
  ✓ student can top up balance successfully                                 0.01s  
  ✓ student can purchase course with sufficient balance                     0.01s  
  ✓ anti price tampering ignores client provided price                      0.01s  
  ✓ purchase fails when balance is insufficient                             0.01s  
  ✓ anti idor student cannot view another student invoice                   0.02s  
  ✓ student cannot purchase the same course twice                           0.02s  

  Tests:    8 passed (34 assertions)
  Duration: 0.28s
```

### Rincian Analisis Hasil Uji:
1. **Verifikasi Anti-Price Tampering (`test_anti_price_tampering_ignores_client_provided_price`):**
   - Skenario: Client mengirimkan payload `price = 0` dan `discount = 999999` saat checkout.
   - Hasil: Sistem tetap memotong saldo pengguna sebesar harga riil di database (Rp 200.000). Saldo pengguna berkurang tepat dari Rp 500.000 menjadi Rp 300.000.
2. **Verifikasi Anti-IDOR (`test_anti_idor_student_cannot_view_another_student_invoice`):**
   - Skenario: Student 1 membeli kursus dan mendapatkan invoice ID #1. Student 2 melakukan request `GET /transactions/1`.
   - Hasil: Permintaan Student 2 diblokir dengan respons **HTTP 403 Forbidden**. Ketika Student 1 mengakses URL yang sama, respons **HTTP 200 OK** berhasil ditampilkan.
3. **Verifikasi Anti-Negative Values (`test_top_up_validation_strictly_rejects_negative_and_zero_values`):**
   - Skenario: Mengirim nilai `-50000` dan `0` pada endpoint top-up.
   - Hasil: Request ditolak pada fase validasi dan saldo pengguna tidak mengalami perubahan.

---

## BAB V: Panduan Pengujian White-Box untuk Kelompok Penilai

Saat sesi audit kode (Sesi 10), kelompok lawan dipersilakan melakukan pengujian penetrasi mandiri terhadap modul ini menggunakan prosedur berikut:

### 1. Uji IDOR pada Invoice:
1. Login sebagai `student1@secureacademy.test` (password: `PasswordStudent123!`).
2. Masuk ke menu **Riwayat Transaksi**, catat URL kwitansi yang dimiliki (misal: `/transactions/1`).
3. Logout, lalu login sebagai `student2@secureacademy.test` (password: `PasswordStudent123!`).
4. Akses URL `/transactions/1` secara manual di address bar.
5. **Ekspektasi Keamanan:** Sistem menampilkan halaman error **403 Forbidden**.

### 2. Uji Manipulasi Harga (Price Tampering) via Burp Suite:
1. Login sebagai `student1@secureacademy.test`.
2. Buka halaman checkout kursus, aktifkan intercept pada Burp Suite.
3. Klik tombol **Konfirmasi & Bayar**, lalu tambahkan parameter `price=0` atau ubah body request.
4. Teruskan (*forward*) request.
5. **Ekspektasi Keamanan:** Saldo tetap terpotong sesuai harga kursus asli di database. Nilai `price` yang disuntikkan diabaikan sepenuhnya oleh server.

### 3. Uji Concurrency / Race Condition via Burp Intruder:
1. Siapkan akun dengan saldo pas-pasan (misal Rp 100.000) untuk membeli kursus seharga Rp 100.000.
2. Tangkap request checkout di Burp Suite, kirim ke **Intruder**.
3. Atur payload type ke **Null Payloads**, generate 20 requests secara paralel (Resource Pool: Concurrent Requests = 20).
4. Start Attack.
5. **Ekspektasi Keamanan:** Hanya tepat 1 request yang menghasilkan HTTP 302 Redirect (sukses), sedangkan 19 request lainnya menghasilkan pesan error "Saldo tidak mencukupi" atau "Anda sudah terdaftar". Saldo akhir akun tepat Rp 0 dan tidak pernah menjadi negatif.

