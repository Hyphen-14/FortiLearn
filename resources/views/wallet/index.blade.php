@extends('layouts.app')

@section('title', 'Dompet Belajar & Isi Saldo')

@section('content')
<div class="max-w-5xl mx-auto space-y-7">
    <!-- Section Header -->
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-700 block mb-1">
                Dompet Digital FortiLearn
            </span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Saldo &amp; Isi Ulang (Top-Up)</h1>
            <p class="text-slate-600 text-sm mt-1">
                Tambahkan saldo belajar dengan mudah untuk membeli berbagai kursus pilihan secara instan.
            </p>
        </div>
        <a href="{{ route('transactions.index') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2 rounded-xl bg-white border border-slate-200/90 hover:bg-slate-50 text-xs font-bold text-slate-700 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
            Lihat Riwayat Pembelian
        </a>
    </div>

    <!-- Balance Card & Interactive Top-Up Calculator Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <!-- Left: Deep Obsidian Wallet Card (5 Cols) -->
        <section aria-label="Status Saldo dan Proyeksi"
                 id="wallet-ledger-card"
                 data-current-balance="{{ (float) $user->balance }}"
                 class="lg:col-span-5 bg-[#090D16] text-white rounded-2xl border border-slate-800 p-6 flex flex-col justify-between shadow-sm">
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <span class="text-xs text-slate-400 block">Pemilik Akun</span>
                        <span class="text-sm font-bold text-white">{{ $user->name }}</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                        Aktif
                    </span>
                </div>

                <div>
                    <span class="text-xs text-slate-400 block mb-1">Saldo Tersedia Saat Ini</span>
                    <div class="text-3xl font-mono font-extrabold text-white tabular-nums tracking-tight">
                        Rp {{ number_format($user->balance, 0, ',', '.') }}
                    </div>
                </div>

                <!-- Live Balance Calculator Readout -->
                <div class="p-4 rounded-xl bg-slate-900/90 border border-slate-800 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400 font-medium">Nominal Isi Ulang:</span>
                        <span id="delta-amount" class="font-mono font-bold text-emerald-400 tabular-nums">
                            + Rp 100.000
                        </span>
                    </div>
                    <div class="pt-2.5 border-t border-slate-800 flex items-baseline justify-between">
                        <span class="text-xs font-semibold text-slate-300">Estimasi Saldo Akhir:</span>
                        <span id="projected-balance" class="text-lg font-mono font-extrabold text-emerald-400 tabular-nums">
                            Rp {{ number_format($user->balance + 100000, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-6 mt-6 border-t border-slate-800 text-xs text-slate-400 space-y-1">
                <div class="flex items-center gap-2 text-slate-200 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Saldo Instan Tanpa Biaya Admin</span>
                </div>
                <p class="text-[11px] text-slate-400 leading-relaxed">
                    Dana yang Anda tambahkan langsung aktif dan dapat digunakan untuk membuka seluruh kelas di katalog.
                </p>
            </div>
        </section>

        <!-- Right: Interactive Top-Up Form (7 Cols) -->
        <section aria-labelledby="topup-form-heading" class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 flex flex-col justify-between shadow-2xs">
            <div>
                <h2 id="topup-form-heading" class="text-lg font-extrabold text-slate-900 mb-1">Isi Ulang Saldo Belajar</h2>
                <p class="text-xs text-slate-500 mb-6">
                    Pilih nominal cepat di bawah ini, geser pengatur jumlah, atau ketik nominal yang Anda inginkan.
                </p>

                <form id="topup-form" action="{{ route('wallet.topup') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Preset Nominal Buttons -->
                    <div>
                        <span class="block text-xs font-bold text-slate-700 mb-2.5">
                            Pilih Nominal Instan
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            @foreach([50000, 100000, 250000, 500000, 1000000] as $preset)
                                <button type="button"
                                        data-preset-amount="{{ $preset }}"
                                        class="min-h-[44px] py-2 px-2.5 text-xs font-mono font-bold rounded-xl border border-slate-200 bg-white hover:border-emerald-600 hover:bg-emerald-50/50 text-slate-800 transition tabular-nums focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                                    +{{ number_format($preset / 1000, 0, ',', '.') }}rb
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Interactive Range Slider -->
                    <div>
                        <div class="flex justify-between items-center text-xs mb-2">
                            <label for="amount-slider" class="font-bold text-slate-700">
                                Atur Jumlah dengan Penggeser
                            </label>
                            <span class="font-mono text-slate-500 tabular-nums">Kelipatan Rp 10.000</span>
                        </div>
                        <input type="range"
                               id="amount-slider"
                               min="10000"
                               max="2000000"
                               step="10000"
                               value="{{ old('amount', 100000) }}"
                               class="w-full h-2 bg-slate-200 rounded-lg accent-emerald-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        <div class="flex justify-between text-[11px] font-mono text-slate-400 mt-1 tabular-nums">
                            <span>Rp 10.000</span>
                            <span>Rp 1.000.000</span>
                            <span>Rp 2.000.000+</span>
                        </div>
                    </div>

                    <!-- Exact Numeric Input -->
                    <div>
                        <label for="amount" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nominal Top-Up (Rupiah)
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500 font-mono font-bold text-sm">
                                Rp
                            </div>
                            <input type="number"
                                   name="amount"
                                   id="amount"
                                   inputmode="numeric"
                                   required
                                   min="10000"
                                   max="10000000"
                                   step="1000"
                                   value="{{ old('amount', 100000) }}"
                                   placeholder="100000"
                                   class="w-full min-h-[44px] pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 bg-white font-mono text-slate-900 tabular-nums focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm font-bold">
                        </div>
                        <span class="text-xs text-slate-500 mt-1.5 block tabular-nums">
                            Minimal pengisian Rp 10.000 hingga maksimal Rp 10.000.000 per transaksi.
                        </span>
                    </div>

                    <button type="submit"
                            class="w-full min-h-[44px] py-3 px-5 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl transition text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                        Tambahkan Saldo Sekarang
                    </button>
                </form>
            </div>
        </section>
    </div>

    <!-- Recent Wallet Activity -->
    <section aria-labelledby="recent-activity-heading" class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 id="recent-activity-heading" class="text-base font-extrabold text-slate-900">Aktivitas Dompet Terakhir</h2>
                <p class="text-xs text-slate-500">Menampilkan 5 transaksi terbaru pada akun Anda.</p>
            </div>
            <a href="{{ route('transactions.index') }}"
               class="min-h-[38px] inline-flex items-center px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-xs font-bold text-slate-800 transition">
                Lihat Semua Riwayat
            </a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($recentTransactions as $trx)
                <div class="p-4 sm:px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/80 transition">
                    <div class="flex items-start sm:items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 font-bold text-xs {{ $trx->type === 'topup' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                            {{ $trx->type === 'topup' ? '+' : '-' }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $trx->description }}</div>
                            <div class="text-xs text-slate-500 font-mono tabular-nums mt-0.5">
                                {{ $trx->created_at->format('d M Y, H:i') }} &bull; No. Ref: <span class="text-slate-700 font-semibold">{{ $trx->reference_number }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1.5">
                        <div class="text-sm font-mono font-extrabold tabular-nums {{ $trx->type === 'topup' ? 'text-emerald-700' : 'text-slate-900' }}">
                            {{ $trx->type === 'topup' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </div>
                        <a href="{{ route('transactions.show', $trx->id) }}"
                           class="text-xs font-bold text-emerald-700 hover:underline">
                            Lihat Kwitansi
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-slate-500">
                    Belum ada riwayat transaksi pada dompet Anda.
                </div>
            @endforelse
        </div>
    </section>
</div>

<script>
    (function () {
        'use strict';

        const ledgerCard = document.getElementById('wallet-ledger-card');
        const amountInput = document.getElementById('amount');
        const amountSlider = document.getElementById('amount-slider');
        const deltaEl = document.getElementById('delta-amount');
        const projectedEl = document.getElementById('projected-balance');
        const presetButtons = document.querySelectorAll('[data-preset-amount]');

        if (!ledgerCard || !amountInput || !deltaEl || !projectedEl) return;

        const baseBalance = parseFloat(ledgerCard.getAttribute('data-current-balance') || '0') || 0;
        const idrFormatter = new Intl.NumberFormat('id-ID');

        function updateProjection(rawVal) {
            const parsed = Math.max(0, Math.min(10000000, parseInt(rawVal, 10) || 0));
            const projected = baseBalance + parsed;

            deltaEl.textContent = '+ Rp ' + idrFormatter.format(parsed);
            projectedEl.textContent = 'Rp ' + idrFormatter.format(projected);

            presetButtons.forEach((btn) => {
                const btnVal = parseInt(btn.getAttribute('data-preset-amount'), 10);
                if (btnVal === parsed) {
                    btn.classList.add('border-emerald-600', 'bg-emerald-50', 'text-emerald-800');
                } else {
                    btn.classList.remove('border-emerald-600', 'bg-emerald-50', 'text-emerald-800');
                }
            });
        }

        presetButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const value = btn.getAttribute('data-preset-amount') || '100000';
                amountInput.value = value;
                if (amountSlider) {
                    amountSlider.value = Math.min(2000000, parseInt(value, 10));
                }
                updateProjection(value);
            });
        });

        if (amountSlider) {
            amountSlider.addEventListener('input', () => {
                amountInput.value = amountSlider.value;
                updateProjection(amountSlider.value);
            });
        }

        amountInput.addEventListener('input', () => {
            if (amountSlider) {
                const clamped = Math.max(10000, Math.min(2000000, parseInt(amountInput.value, 10) || 10000));
                amountSlider.value = String(clamped);
            }
            updateProjection(amountInput.value);
        });

        updateProjection(amountInput.value);
    })();
</script>
@endsection
