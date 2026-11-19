@extends('layouts.instructor')

@section('title', 'Penjualan & Siswa')

@section('content')
@php $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
<div class="max-w-6xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-700 block mb-1">Penjualan &amp; Siswa</span>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kursus Anda</h1>
        <p class="text-sm text-slate-600 mt-1">Total pendapatan kotor: <strong class="font-mono">{{ $rp($totalRevenue) }}</strong> dari {{ $courses->sum('enrollments_count') }} siswa terdaftar.</p>
    </div>

    <!-- Per-course summary -->
    <section aria-label="Ringkasan per kursus" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($courses as $c)
            <a href="{{ route('instructor.sales.index', ['course' => $c->id]) }}"
               class="block bg-white rounded-2xl border p-4 transition hover:border-emerald-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $selected && $selected->id === $c->id ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-slate-200' }}">
                <span class="block font-bold text-slate-900 text-sm truncate">{{ $c->title }}</span>
                <div class="flex items-end justify-between mt-3">
                    <div><span class="block text-[11px] text-slate-500">Siswa</span><span class="font-mono font-extrabold text-xl tabular-nums">{{ $c->enrollments_count }}</span></div>
                    <div class="text-right"><span class="block text-[11px] text-slate-500">Pendapatan</span><span class="font-mono font-bold text-sm tabular-nums">{{ $rp($revenue[$c->id] ?? 0) }}</span></div>
                </div>
            </a>
        @empty
            <p class="text-sm text-slate-500 md:col-span-2 xl:col-span-3">Belum ada kursus.</p>
        @endforelse
    </section>

    <!-- Buyer list -->
    <section aria-labelledby="buyers-h" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <h2 id="buyers-h" class="font-extrabold text-slate-900">{{ $selected ? 'Pembeli: ' . $selected->title : 'Semua Pembeli' }}</h2>
            @if($selected)<a href="{{ route('instructor.sales.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Tampilkan semua</a>@endif
        </div>
        @if($enrollments->isEmpty())
            <p class="p-10 text-center text-sm text-slate-500">Belum ada siswa yang membeli.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-[11px] uppercase tracking-wider text-slate-400 text-left">
                        <tr><th class="px-6 py-3 font-bold">Siswa</th><th class="px-3 py-3 font-bold">Kursus</th><th class="px-3 py-3 font-bold">Tanggal Beli</th><th class="px-6 py-3 font-bold text-right">Harga</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($enrollments as $e)
                            <tr>
                                <td class="px-6 py-3 font-semibold text-slate-900">{{ $e->user->name ?? 'Pengguna dihapus' }}</td>
                                <td class="px-3 py-3 text-slate-600 max-w-[18rem] truncate">{{ $e->course->title ?? '-' }}</td>
                                <td class="px-3 py-3 font-mono text-xs text-slate-500">{{ $e->enrolled_at?->format('d M Y H:i') }}</td>
                                <td class="px-6 py-3 text-right font-mono font-bold tabular-nums">{{ $rp($e->course->price ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">{{ $enrollments->links() }}</div>
        @endif
    </section>
</div>
@endsection
