@extends('layouts.app')

@section('title', 'Konfirmasi Pembelian Kelas')

@section('content')
<div class="max-w-3xl mx-auto my-2 space-y-6">
    <nav aria-label="Breadcrumb">
        <a href="{{ route('catalog.show', $course->slug) }}"
           class="min-h-[40px] inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white border border-slate-200/90 hover:bg-slate-50 text-xs font-bold text-slate-700 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Kembali ke Halaman Kelas</span>
        </a>
    </nav>

    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <!-- Checkout Header -->
        <div class="p-6 md:p-8 bg-[#090D16] text-white border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-400 block mb-1">
                    Checkout Kelas Online
                </span>
                <h1 class="text-xl md:text-2xl font-extrabold text-white tracking-tight">Ringkasan Pembelian Kursus</h1>
                <p class="text-slate-400 text-xs mt-1">
                    Periksa rincian kelas pilihan Anda sebelum menyelesaikan pembayaran melalui Dompet Belajar.
                </p>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-xs font-semibold text-emerald-400 shrink-0">
                Akses Instan Seumur Hidup
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            <!-- Target Course Item Card -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-5 rounded-xl bg-slate-50 border border-slate-200 gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">{{ $course->category ?? 'Program Pilihan' }}</span>
                    <h2 class="text-base font-extrabold text-slate-900">{{ $course->title }}</h2>
                    <p class="text-xs text-slate-600">
                        Instruktur: <strong>{{ $course->instructor->name }}</strong> &bull; <span class="font-mono tabular-nums">{{ $course->lessons->count() }} Modul Lengkap</span>
                    </p>
                </div>
                <div class="sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-200 w-full sm:w-auto">
                    <span class="text-xs font-semibold text-slate-500 block">Harga Kelas</span>
                    <div class="text-xl font-mono font-extrabold text-slate-900 tabular-nums">
                        Rp {{ number_format($coursePrice, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            @if($hasFreeCourseCredit)
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs font-extrabold text-emerald-900">Kredit Kursus Gratis Pertama Tersedia</div>
                        <p class="text-xs text-emerald-800 mt-0.5">Sebagai anggota baru, Anda berhak mengklaim 1 kursus pilihan Anda secara gratis tanpa memotong saldo dompet.</p>
                    </div>
                    <span class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-bold shrink-0">1x Gratis</span>
                </div>
            @endif

            <!-- Financial Balance Summary Box -->
            <div class="rounded-xl border p-6 {{ $isBalanceSufficient ? 'border-slate-200 bg-white' : 'border-rose-200 bg-rose-50/40' }}">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Rincian Pembayaran Dompet Belajar
                    </h3>
                    @if($hasFreeCourseCredit)
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                            Gratis Member Baru
                        </span>
                    @elseif($isBalanceSufficient)
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                            Saldo Mencukupi
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">
                            Saldo Belum Cukup
                        </span>
                    @endif
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Saldo Dompet Anda Saat Ini:</span>
                        <span class="font-mono font-bold text-slate-900 tabular-nums">
                            Rp {{ number_format($userBalance, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center text-slate-600">
                        <span>Total Pembayaran Kelas:</span>
                        @if($hasFreeCourseCredit)
                            <span class="font-mono font-bold text-emerald-700 tabular-nums">
                                Rp 0 (Kredit Kursus Gratis)
                            </span>
                        @else
                            <span class="font-mono font-bold text-rose-600 tabular-nums">
                                - Rp {{ number_format($coursePrice, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-between items-center text-base font-extrabold">
                        <span class="text-slate-900">Sisa Saldo Setelah Pembelian:</span>
                        <span class="font-mono tabular-nums {{ $isBalanceSufficient ? 'text-emerald-700' : 'text-rose-600' }}">
                            @if($isBalanceSufficient)
                                Rp {{ number_format($remainingBalance, 0, ',', '.') }}
                            @else
                                Kurang Rp {{ number_format($coursePrice - $userBalance, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                </div>

                @if(! $isBalanceSufficient)
                    <div class="mt-5 pt-4 border-t border-rose-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <span class="text-xs text-rose-800 leading-relaxed">
                            Saldo Dompet Belajar Anda belum mencukupi untuk membeli kelas ini. Silakan isi ulang saldo Anda terlebih dahulu.
                        </span>
                        <a href="{{ route('wallet.index') }}"
                           class="min-h-[44px] inline-flex items-center justify-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl transition shrink-0">
                            Isi Saldo Sekarang
                        </a>
                    </div>
                @endif
            </div>

            <!-- Buyer Guarantee Disclosure (<details>) -->
            <details class="group rounded-xl border border-slate-200 bg-slate-50/70">
                <summary class="min-h-[48px] px-4 py-3 flex items-center justify-between text-xs font-bold text-slate-700 select-none rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                    <span class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>Garansi Pembelian &amp; Akses Materi FortiLearn</span>
                    </span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>
                <div class="px-4 pb-4 pt-2 border-t border-slate-200/80 text-xs text-slate-600 space-y-1.5 leading-relaxed">
                    <p>1. Akses ke seluruh modul video dan bahan bacaan PDF langsung terbuka otomatis begitu pembayaran selesai.</p>
                    <p>2. Kwitansi digital resmi diterbitkan secara otomatis dan dapat diunduh atau dicetak kapan saja melalui menu Riwayat Transaksi.</p>
                </div>
            </details>

            <!-- Transaction Execution Form -->
            @if($isBalanceSufficient)
                <form id="checkout-execute-form" action="{{ route('checkout.process', $course->id) }}" method="POST" class="space-y-3">
                    @csrf

                    <div class="flex flex-col sm:flex-row gap-3">
                        <button type="button"
                                data-open-dialog="confirm-payment-dialog"
                                class="min-h-[44px] sm:w-1/3 py-3 px-4 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                            Tinjau Ringkasan
                        </button>

                        <button type="submit"
                                class="min-h-[44px] sm:w-2/3 py-3.5 px-5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl transition text-sm inline-flex items-center justify-center gap-2 shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-mono tabular-nums">
                                @if($hasFreeCourseCredit)
                                    Klaim Kursus Gratis Pertama
                                @else
                                    Konfirmasi &amp; Bayar Rp {{ number_format($coursePrice, 0, ',', '.') }}
                                @endif
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Native <dialog closedby="any"> Final Confirmation Modal -->
                <dialog id="confirm-payment-dialog"
                        closedby="any"
                        aria-labelledby="confirm-payment-title"
                        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white text-slate-900 p-0 shadow-xl m-auto">
                    <div class="p-5 bg-[#090D16] text-white border-b border-slate-800 flex items-center justify-between">
                        <h4 id="confirm-payment-title" class="text-sm font-bold text-white">
                            Konfirmasi Pembayaran Kelas
                        </h4>
                        <button type="button"
                                data-close-dialog
                                aria-label="Tutup dialog konfirmasi"
                                class="min-h-[40px] min-w-[40px] inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4 text-xs text-slate-600">
                        <p class="leading-relaxed">
                            Apakah Anda yakin ingin menggunakan saldo sebesar <strong class="font-mono text-slate-900 tabular-nums">Rp {{ number_format($coursePrice, 0, ',', '.') }}</strong> untuk membeli kelas <strong>{{ $course->title }}</strong>?
                        </p>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 font-mono space-y-1.5 tabular-nums">
                            <div class="flex justify-between">
                                <span>Saldo Saat Ini:</span>
                                <span class="text-slate-900 font-semibold">Rp {{ number_format($userBalance, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Sisa Saldo Akhir:</span>
                                <span class="text-emerald-700 font-bold">Rp {{ number_format($remainingBalance, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button"
                                    data-close-dialog
                                    class="min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 font-semibold text-slate-700 transition">
                                Kembali
                            </button>
                            <button type="submit"
                                    form="checkout-execute-form"
                                    class="min-h-[44px] px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 font-bold text-white transition">
                                Bayar Sekarang
                            </button>
                        </div>
                    </div>
                </dialog>
            @endif
        </div>
    </div>
</div>
@endsection
