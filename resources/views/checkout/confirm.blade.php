@extends('layouts.app')

@section('title', 'Konfirmasi Pembelian Kursus')

@section('content')
<div class="max-w-3xl mx-auto my-6">
    <div class="mb-6">
        <a href="{{ route('catalog.show', $course->slug) }}" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center space-x-1">
            <span>&larr; Batalkan & Kembali ke Detail Kursus</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 md:p-8 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center space-x-2 text-xs font-bold text-indigo-600 uppercase tracking-wider mb-2">
                <span>Konfirmasi Checkout Terenkripsi</span>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Ringkasan Pembelian Kursus</h1>
            <p class="text-slate-500 text-sm mt-1">Pastikan saldo dompet internal mencukupi sebelum menyelesaikan transaksi.</p>
        </div>

        <div class="p-6 md:p-8 space-y-8">
            <!-- Course Summary Card -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200 gap-4">
                <div class="space-y-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Item yang Dibeli</span>
                    <h2 class="text-base font-bold text-slate-900">{{ $course->title }}</h2>
                    <p class="text-xs text-slate-500">Instruktur: {{ $course->instructor->name }} &bull; {{ $course->lessons->count() }} Modul Pelajaran</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold text-slate-400 uppercase">Harga Resmi</span>
                    <div class="text-xl font-extrabold text-slate-900">
                        Rp {{ number_format($coursePrice, 0, ',', '.') }}
                    </div>
                    <span class="text-[11px] text-emerald-600 font-semibold">&bull; Diverifikasi Server</span>
                </div>
            </div>

            <!-- Financial Balance Verification Box -->
            <div class="rounded-2xl border p-6 {{ $isBalanceSufficient ? 'border-emerald-200 bg-emerald-50/30' : 'border-rose-200 bg-rose-50/30' }}">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider mb-4 flex items-center justify-between">
                    <span>Pemeriksaan Saldo Dompet</span>
                    @if($isBalanceSufficient)
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Saldo Cukup</span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-xs font-bold">Saldo Kurang</span>
                    @endif
                </h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center text-slate-600">
                        <span>Saldo Dompet Anda Saat Ini:</span>
                        <span class="font-bold text-slate-900">Rp {{ number_format($userBalance, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center text-slate-600">
                        <span>Harga Kursus:</span>
                        <span class="font-bold text-slate-900">- Rp {{ number_format($coursePrice, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-200/80 flex justify-between items-center text-base font-extrabold">
                        <span>Sisa Saldo Setelah Pembelian:</span>
                        <span class="{{ $isBalanceSufficient ? 'text-emerald-700' : 'text-rose-600' }}">
                            @if($isBalanceSufficient)
                                Rp {{ number_format($remainingBalance, 0, ',', '.') }}
                            @else
                                Defisit Rp {{ number_format($coursePrice - $userBalance, 0, ',', '.') }}
                            @endif
                        </span>
                    </div>
                </div>

                @if(! $isBalanceSufficient)
                    <div class="mt-4 pt-4 border-t border-rose-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <span class="text-xs text-rose-700">
                            Silakan lakukan simulasi pengisian saldo (Top-Up) untuk melanjutkan pendaftaran.
                        </span>
                        <a href="{{ route('wallet.index') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-xs transition shrink-0">
                            + Isi Saldo Sekarang
                        </a>
                    </div>
                @endif
            </div>

            <!-- Transaction Execution Form -->
            @if($isBalanceSufficient)
                <form action="{{ route('checkout.process', $course->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Notice: NEVER include price in form payload to prevent client-side price tampering -->

                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-500 leading-relaxed">
                        <div class="font-bold text-slate-700 mb-1">Pemberitahuan Keamanan Sistem:</div>
                        Transaksi ini dilindungi dengan mekanisme database <strong>Pessimistic Locking (<code class="text-indigo-600">lockForUpdate</code>)</strong> di dalam transaksi atomik ACID. Saldo Anda akan dipotong secara terisolasi untuk menjamin tidak terjadi *double-spending* atau *race condition*.
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition text-base flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span>Konfirmasi & Bayar Rp {{ number_format($coursePrice, 0, ',', '.') }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
