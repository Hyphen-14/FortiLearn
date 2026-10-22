@extends('layouts.app')

@section('title', 'Kwitansi Resmi — ' . $transaction->reference_number)

@section('content')
<div class="max-w-2xl mx-auto my-6 space-y-6">
    <div class="flex justify-between items-center print:hidden">
        <a href="{{ route('transactions.index') }}" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center space-x-1">
            <span>&larr; Kembali ke Riwayat Transaksi</span>
        </a>

        <button onclick="window.print()" class="px-3.5 py-1.5 rounded-xl border border-slate-300 hover:border-slate-400 bg-white text-xs font-semibold text-slate-700 transition flex items-center space-x-1.5 shadow-2xs">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Cetak Kwitansi</span>
        </button>
    </div>

    <!-- Printable Official Invoice Document -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8 md:p-10 relative overflow-hidden">
        <!-- Verified Watermark / Stamp -->
        <div class="absolute -right-8 -top-8 w-36 h-36 rounded-full bg-emerald-50 border-4 border-emerald-200 flex items-center justify-center rotate-12 opacity-80 pointer-events-none">
            <span class="text-xs font-black uppercase tracking-widest text-emerald-600 text-center leading-tight">
                VERIFIED<br>TRANSACTION
            </span>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-start pb-8 border-b border-slate-100">
            <div>
                <div class="flex items-center space-x-2 mb-1">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-slate-900">Forti<span class="text-indigo-600">Learn</span></span>
                </div>
                <div class="text-xs text-slate-400">Bukti Transaksi Sah & Terotentikasi</div>
            </div>

            <div class="text-right">
                <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">No. Referensi</span>
                <div class="font-mono text-sm font-extrabold text-slate-900">{{ $transaction->reference_number }}</div>
                <div class="text-xs text-slate-400">{{ $transaction->created_at->format('d F Y, H:i:s') }} WIB</div>
            </div>
        </div>

        <!-- User / Billing Details -->
        <div class="grid grid-cols-2 gap-6 py-6 border-b border-slate-100 text-xs">
            <div>
                <span class="font-bold uppercase tracking-wider text-slate-400 block mb-1">Diterbitkan Untuk:</span>
                <div class="font-bold text-slate-900 text-sm">{{ $transaction->user->name }}</div>
                <div class="text-slate-500">{{ $transaction->user->email }}</div>
                <div class="text-slate-400 mt-0.5">Role Akun: {{ strtoupper($transaction->user->role) }}</div>
            </div>
            <div class="text-right">
                <span class="font-bold uppercase tracking-wider text-slate-400 block mb-1">Metode Pembayaran:</span>
                <div class="font-bold text-slate-800 text-sm">Dompet Internal FortiLearn</div>
                <div class="text-emerald-600 font-semibold mt-0.5">Status: SELESAI (LUNAS)</div>
                <div class="text-[11px] text-slate-400">Tipe Mutasi: {{ strtoupper($transaction->type) }}</div>
            </div>
        </div>

        <!-- Itemized Table -->
        <div class="py-6 border-b border-slate-100">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 uppercase font-bold border-b border-slate-100 pb-2">
                        <th class="pb-2">Rincian Deskripsi</th>
                        <th class="pb-2 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr>
                        <td class="py-4">
                            <div class="font-bold text-slate-900 text-sm">{{ $transaction->description }}</div>
                            @if($transaction->course)
                                <div class="text-slate-400 text-xs mt-0.5">
                                    Kursus: {{ $transaction->course->title }} &bull; Instruktur: {{ $transaction->course->instructor->name }}
                                </div>
                            @endif
                        </td>
                        <td class="py-4 text-right font-extrabold text-slate-900 text-sm">
                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Financial Balance Summary -->
        <div class="pt-6 space-y-2 text-xs">
            <div class="flex justify-between text-slate-500">
                <span>Saldo Sebelum Transaksi:</span>
                <span class="font-mono">Rp {{ number_format($transaction->balance_before, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-slate-500">
                <span>Jumlah Mutasi:</span>
                <span class="font-mono font-bold {{ $transaction->type === 'topup' ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $transaction->type === 'topup' ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                </span>
            </div>
            <div class="flex justify-between text-slate-900 font-bold text-sm pt-2 border-t border-slate-100">
                <span>Saldo Setelah Transaksi:</span>
                <span class="font-mono text-indigo-600">Rp {{ number_format($transaction->balance_after, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Security Guard Verification Footer Notice -->
        <div class="mt-8 pt-6 border-t border-slate-100 text-[11px] text-slate-400 leading-relaxed bg-slate-50 -mx-8 -mb-8 p-6 md:-mx-10 md:-mb-10">
            <div class="flex items-center space-x-1.5 text-slate-700 font-bold mb-1">
                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Perlindungan Keamanan Akses (Anti-IDOR Policy)</span>
            </div>
            Kwitansi ini dilindungi oleh otorisasi <code>TransactionPolicy@viewReceipt</code>. Akses tidak sah dari pihak lain dengan memanipulasi parameter ID transaksi akan secara otomatis diblokir dengan respons <strong>HTTP 403 Forbidden</strong>.
        </div>
    </div>
</div>
@endsection

