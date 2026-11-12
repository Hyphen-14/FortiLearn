@extends('layouts.app')

@section('title', 'Panduan & Pusat Bantuan')

@section('content')
<div class="max-w-4xl mx-auto space-y-7">
    <!-- Hero Banner -->
    <section class="rounded-2xl bg-[#090D16] text-white p-6 sm:p-8 border border-slate-800">
        <span class="text-xs font-mono font-bold uppercase tracking-widest text-emerald-400 block mb-1">
            PUSAT LAYANAN PELAJAR
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
            Panduan Penggunaan &amp; Tanya Jawab (FAQ)
        </h1>
        <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed max-w-2xl">
            Pelajari alur lengkap cara memilih kursus, mengisi ulang saldo Dompet Belajar, hingga mengunduh kwitansi resmi di FortiLearn.
        </p>
    </section>

    <!-- 3-Step Visual Workflow Cards -->
    <section aria-label="Alur Belajar 3 Langkah" class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-2">
            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-mono font-extrabold text-xs flex items-center justify-center">
                01
            </span>
            <h2 class="text-sm font-extrabold text-slate-900">Pilih Bidang &amp; Kursus</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Telusuri kelas lintas bidang di menu <strong>Eksplorasi Kelas</strong> atau <strong>Kategori Bidang</strong>, lalu lihat silabus modulnya.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-2">
            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-mono font-extrabold text-xs flex items-center justify-center">
                02
            </span>
            <h2 class="text-sm font-extrabold text-slate-900">Isi Saldo Dompet Belajar</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Gunakan menu <strong>Dompet Belajar</strong> untuk simulasi pengisian saldo instan mulai dari Rp 10.000 tanpa biaya tambahan.
            </p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-2">
            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 font-mono font-extrabold text-xs flex items-center justify-center">
                03
            </span>
            <h2 class="text-sm font-extrabold text-slate-900">Beli &amp; Akses Seumur Hidup</h2>
            <p class="text-xs text-slate-600 leading-relaxed">
                Konfirmasi pembelian kursus dengan 1 klik. Materi langsung terbuka di menu <strong>Kursus Saya</strong> beserta kwitansi resmi.
            </p>
        </div>
    </section>

    <!-- Interactive FAQ Accordions -->
    <section aria-labelledby="faq-heading" class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-2xs space-y-4">
        <h2 id="faq-heading" class="text-lg font-extrabold text-slate-900 mb-2">
            Pertanyaan yang Sering Diajukan (FAQ)
        </h2>

        <details name="faq-page" class="group rounded-xl border border-slate-200 bg-slate-50/60 p-4" open>
            <summary class="font-bold text-slate-900 text-sm flex items-center justify-between">
                <span>Bagaimana cara mencoba aplikasi menggunakan Akun Demo?</span>
                <span class="text-emerald-600 font-mono font-bold">+</span>
            </summary>
            <p class="mt-2.5 text-xs text-slate-600 leading-relaxed">
                Buka halaman <a href="{{ route('login') }}" class="text-emerald-700 font-bold underline">Masuk Akun</a>, lalu klik tombol <strong>Alice Maharani (Saldo Rp 500.000)</strong> atau <strong>Budi Hartono (Saldo Rp 100.000)</strong> untuk mengisi email dan kata sandi secara otomatis.
            </p>
        </details>

        <details name="faq-page" class="group rounded-xl border border-slate-200 bg-slate-50/60 p-4">
            <summary class="font-bold text-slate-900 text-sm flex items-center justify-between">
                <span>Apakah kursus yang sudah dibeli memiliki batas waktu akses?</span>
                <span class="text-emerald-600 font-mono font-bold">+</span>
            </summary>
            <p class="mt-2.5 text-xs text-slate-600 leading-relaxed">
                Tidak ada batas waktu. Sekali Anda menyelesaikan pembelian kelas, seluruh modul video dan panduan PDF dapat diakses seumur hidup melalui menu <strong>Kursus Saya</strong>.
            </p>
        </details>

        <details name="faq-page" class="group rounded-xl border border-slate-200 bg-slate-50/60 p-4">
            <summary class="font-bold text-slate-900 text-sm flex items-center justify-between">
                <span>Bagaimana jika saldo Dompet Belajar saya kurang saat ingin membeli kelas?</span>
                <span class="text-emerald-600 font-mono font-bold">+</span>
            </summary>
            <p class="mt-2.5 text-xs text-slate-600 leading-relaxed">
                Halaman konfirmasi pembelian akan memberi tahu selisih kekurangan saldo Anda dan menyediakan tombol langsung menuju menu <strong>Dompet Belajar</strong> agar Anda dapat melakukan pengisian ulang terlebih dahulu.
            </p>
        </details>

        <details name="faq-page" class="group rounded-xl border border-slate-200 bg-slate-50/60 p-4">
            <summary class="font-bold text-slate-900 text-sm flex items-center justify-between">
                <span>Di mana saya bisa melihat dan mencetak bukti kwitansi pembayaran?</span>
                <span class="text-emerald-600 font-mono font-bold">+</span>
            </summary>
            <p class="mt-2.5 text-xs text-slate-600 leading-relaxed">
                Setiap transaksi top-up maupun pembelian kursus tercatat otomatis di menu <strong>Riwayat &amp; Kwitansi</strong>. Klik tombol <strong>Kwitansi</strong> pada baris transaksi untuk membuka dan mencetak bukti pembayaran resmi Anda.
            </p>
        </details>
    </section>
</div>
@endsection

