@extends('layouts.app')

@section('title', 'Kwitansi Pembayaran: ' . $transaction->reference_number)

@section('content')
<div class="max-w-2xl mx-auto my-2 space-y-6">
    <!-- Top Action Toolbar (Hidden when printing) -->
    <div class="flex justify-between items-center print:hidden">
        <a href="{{ route('transactions.index') }}"
           class="min-h-[40px] inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200/90 hover:bg-slate-50 text-xs font-bold text-slate-700 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Kembali ke Riwayat Transaksi</span>
        </a>

        <button type="button"
                id="print-invoice-btn"
                class="min-h-[40px] px-4 py-2 rounded-xl border border-slate-300 hover:border-slate-400 bg-white hover:bg-slate-50 text-xs font-bold text-slate-800 transition inline-flex items-center gap-2 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Kwitansi</span>
        </button>
    </div>

    <!-- Printable Official Invoice Document -->
    <article class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="p-6 md:p-8 bg-[#090D16] text-white border-b border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-extrabold tracking-tight text-white">Forti<span class="text-emerald-400">Learn</span></div>
                    <div class="text-xs text-slate-400">Bukti Pembayaran Resmi</div>
                </div>
            </div>

            <div class="sm:text-right">
                <span class="text-[11px] uppercase tracking-wider text-slate-400 block">Nomor Referensi</span>
                <div class="font-mono text-sm font-extrabold text-emerald-400 tabular-nums">{{ $transaction->reference_number }}</div>
                <div class="text-xs font-mono text-slate-400 tabular-nums">{{ $transaction->created_at->format('d M Y, H:i') }} WIB</div>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-6">
            <!-- Account & Settlement Metadata -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pb-6 border-b border-slate-100 text-xs">
                <div>
                    <span class="font-bold uppercase tracking-wider text-slate-400 block mb-1">Diterbitkan Untuk:</span>
                    <div class="font-extrabold text-slate-900 text-sm">{{ $transaction->user->name }}</div>
                    <div class="text-slate-600">{{ $transaction->user->email }}</div>
                </div>
                <div class="sm:text-right">
                    <span class="font-bold uppercase tracking-wider text-slate-400 block mb-1">Metode Pembayaran:</span>
                    <div class="font-bold text-slate-900 text-sm">Dompet Belajar FortiLearn</div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                        <span>LUNAS &amp; TERVERIFIKASI</span>
                    </div>
                </div>
            </div>

            <!-- Itemized Breakdown Table -->
            <div class="pb-6 border-b border-slate-100">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 uppercase border-b border-slate-100">
                            <th scope="col" class="pb-2.5 font-bold">Deskripsi Transaksi</th>
                            <th scope="col" class="pb-2.5 text-right font-bold">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="py-4 pr-4">
                                <div class="font-bold text-slate-900 text-sm">{{ $transaction->description }}</div>
                                @if($transaction->course)
                                    <div class="text-slate-500 text-xs mt-1">
                                        Kelas: <strong>{{ $transaction->course->title }}</strong> &bull; Instruktur: {{ $transaction->course->instructor->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-4 text-right font-mono font-extrabold text-slate-900 text-sm tabular-nums whitespace-nowrap">
                                Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Balance Summary -->
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Saldo Sebelum Transaksi:</span>
                    <span class="font-mono font-semibold text-slate-900 tabular-nums">
                        Rp {{ number_format($transaction->balance_before, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Nominal Transaksi:</span>
                    <span class="font-mono font-bold tabular-nums {{ $transaction->type === 'topup' ? 'text-emerald-700' : 'text-rose-600' }}">
                        {{ $transaction->type === 'topup' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-2.5 border-t border-slate-200">
                    <span>Sisa Saldo Akhir:</span>
                    <span class="font-mono text-emerald-700 tabular-nums">
                        Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="pt-2 text-center text-xs text-slate-400">
                Terima kasih telah belajar bersama FortiLearn Academy. Bukti pembayaran ini sah tanpa tanda tangan basah.
            </div>
        </div>
    </article>
</div>

<script>
    (function () {
        'use strict';
        const printBtn = document.getElementById('print-invoice-btn');
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                window.print();
            });
        }
    })();
</script>
@endsection
