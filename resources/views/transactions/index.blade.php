@extends('layouts.app')

@section('title', 'Riwayat Transaksi & Pembelian')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Transaksi</h1>
            <p class="text-slate-500 text-sm mt-1">Daftar mutasi saldo dompet dan pembelian kursus Anda.</p>
        </div>

        <a href="{{ route('wallet.index') }}" class="px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold text-xs rounded-xl transition border border-indigo-200">
            &larr; Ke Dompet Internal
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-bold tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Ref ID / Waktu</th>
                        <th class="px-6 py-4">Tipe Transaksi</th>
                        <th class="px-6 py-4">Deskripsi / Kursus</th>
                        <th class="px-6 py-4">Nominal</th>
                        <th class="px-6 py-4">Saldo Akhir</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-900 text-xs">{{ $trx->reference_number }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $trx->created_at->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($trx->type === 'topup')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">Top-Up</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold">Pembelian</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $trx->description }}</div>
                                @if($trx->course)
                                    <div class="text-xs text-slate-400">Instruktur: {{ $trx->course->instructor->name }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-extrabold {{ $trx->type === 'topup' ? 'text-emerald-600' : 'text-slate-900' }}">
                                {{ $trx->type === 'topup' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-medium text-slate-500">
                                Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('transactions.show', $trx->id) }}" class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-indigo-600 hover:text-white text-xs font-semibold text-slate-700 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Kwitansi</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Belum ada catatan transaksi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
