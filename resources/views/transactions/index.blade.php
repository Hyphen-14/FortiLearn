@extends('layouts.app')

@section('title', 'Riwayat Transaksi & Pembelian')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 border-b border-slate-200 pb-5">
        <div>
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-700 block mb-1">
                Catatan Transaksi Akun
            </span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Pembelian &amp; Isi Saldo</h1>
            <p class="text-slate-600 text-sm mt-1">
                Daftar lengkap aktivitas pengisian saldo dompet dan pembelian kelas online Anda.
            </p>
        </div>

        <a href="{{ route('wallet.index') }}"
           class="min-h-[44px] inline-flex items-center px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
            Buka Dompet Belajar
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-[#090D16] text-slate-300 text-xs uppercase tracking-wider border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-bold">No. Referensi &amp; Waktu</th>
                        <th scope="col" class="px-6 py-4 font-bold">Jenis Transaksi</th>
                        <th scope="col" class="px-6 py-4 font-bold">Keterangan / Kelas</th>
                        <th scope="col" class="px-6 py-4 font-bold text-right">Nominal</th>
                        <th scope="col" class="px-6 py-4 font-bold text-right">Sisa Saldo</th>
                        <th scope="col" class="px-6 py-4 font-bold text-center">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-900 text-xs tabular-nums">{{ $trx->reference_number }}</div>
                                <div class="text-[11px] font-mono text-slate-500 mt-0.5 tabular-nums">{{ $trx->created_at->format('d M Y, H:i') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($trx->type === 'topup')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                        Isi Saldo
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-bold">
                                        Pembelian Kelas
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">{{ $trx->description }}</div>
                                @if($trx->course)
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        Instruktur: {{ $trx->course->instructor->name }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-mono font-extrabold tabular-nums {{ $trx->type === 'topup' ? 'text-emerald-700' : 'text-slate-900' }}">
                                {{ $trx->type === 'topup' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-mono font-semibold text-slate-600 tabular-nums">
                                Rp {{ number_format($trx->balance_after, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('transactions.show', $trx->id) }}"
                                   class="min-h-[38px] inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-[#090D16] hover:text-white hover:border-[#090D16] text-xs font-bold text-slate-700 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                                    <span>Kwitansi</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500 text-sm">
                                Belum ada catatan transaksi pada akun Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
