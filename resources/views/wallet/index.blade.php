@extends('layouts.app')

@section('title', 'Dompet Internal & Simulasi Saldo')

@section('content')
<div class="max-w-4xl mx-auto space-y-8">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dompet Internal Siswa</h1>
        <p class="text-slate-500 text-sm mt-1">Kelola saldo belajar Anda dan lakukan simulasi top-up untuk pembelian kursus.</p>
    </div>

    <!-- Balance & Top-Up Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Balance Card -->
        <div class="md:col-span-1 bg-gradient-to-br from-indigo-900 via-slate-900 to-indigo-950 rounded-2xl p-6 text-white shadow-md flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start mb-4">
                    <span class="text-xs uppercase font-bold tracking-wider text-indigo-300">Total Saldo Aktif</span>
                    <span class="p-1.5 rounded-lg bg-white/10 text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-3xl font-extrabold tracking-tight mb-1">
                    Rp {{ number_format($user->balance, 0, ',', '.') }}
                </div>
                <div class="text-xs text-indigo-200">
                    Akun: {{ $user->name }}
                </div>
            </div>

            <div class="pt-6 border-t border-white/10 text-[11px] text-slate-300 flex items-center space-x-1.5">
                <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>Terproteksi Database Row Locking</span>
            </div>
        </div>

        <!-- Top-Up Form Card -->
        <div class="md:col-span-2 bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
            <h2 class="text-base font-bold text-slate-900 mb-1">Simulasi Isi Saldo (Top-Up)</h2>
            <p class="text-xs text-slate-500 mb-6">Pilih salah satu nominal instan atau masukkan jumlah yang diinginkan.</p>

            <form action="{{ route('wallet.topup') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Quick Amount Buttons (Fill Input via JS) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Pilihan Nominal Cepat</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <button type="button" onclick="document.getElementById('amount').value = 50000"
                            class="py-2 px-3 text-xs font-bold rounded-lg border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50 text-slate-700 transition">
                            Rp 50.000
                        </button>
                        <button type="button" onclick="document.getElementById('amount').value = 100000"
                            class="py-2 px-3 text-xs font-bold rounded-lg border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50 text-slate-700 transition">
                            Rp 100.000
                        </button>
                        <button type="button" onclick="document.getElementById('amount').value = 250000"
                            class="py-2 px-3 text-xs font-bold rounded-lg border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50 text-slate-700 transition">
                            Rp 250.000
                        </button>
                        <button type="button" onclick="document.getElementById('amount').value = 500000"
                            class="py-2 px-3 text-xs font-bold rounded-lg border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50 text-slate-700 transition">
                            Rp 500.000
                        </button>
                    </div>
                </div>

                <div>
                    <label for="amount" class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">
                        Nominal Top-Up (Rupiah)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-bold text-sm">
                            Rp
                        </div>
                        <input type="number" name="amount" id="amount" required min="10000" max="10000000" step="1000" placeholder="100000"
                            class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm font-semibold">
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block">Minimal Rp 10.000 &bull; Maksimal Rp 10.000.000 per transaksi</span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-xs transition text-sm">
                    Proses Top-Up Saldo
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Wallet Activity Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-base font-bold text-slate-900">Aktivitas Dompet Terbaru</h3>
            <a href="{{ route('transactions.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">
                Lihat Semua Riwayat &rarr;
            </a>
        </div>

        <div class="divide-y divide-slate-100 overflow-x-auto">
            @forelse($recentTransactions as $trx)
                <div class="p-4 sm:px-6 flex items-center justify-between hover:bg-slate-50/50 transition">
                    <div class="flex items-center space-x-3.5">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $trx->type === 'topup' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                            @if($trx->type === 'topup')
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-900">{{ $trx->description }}</div>
                            <div class="text-xs text-slate-400">{{ $trx->created_at->format('d M Y, H:i') }} &bull; Ref: <span class="font-mono">{{ $trx->reference_number }}</span></div>
                        </div>
                    </div>

                    <div class="text-right">
                        <div class="text-sm font-extrabold {{ $trx->type === 'topup' ? 'text-emerald-600' : 'text-slate-900' }}">
                            {{ $trx->type === 'topup' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </div>
                        <a href="{{ route('transactions.show', $trx->id) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                            Kwitansi &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-sm text-slate-400">
                    Belum ada riwayat transaksi dompet.
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
