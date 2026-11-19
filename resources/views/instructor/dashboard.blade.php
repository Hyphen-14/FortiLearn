@extends('layouts.instructor')

@section('title', 'Dashboard')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $maxMonth = max(1, (float) $months->max('total'));
    $pipelineMeta = [
        'draft' => ['Draft', 'bg-slate-400', 'text-slate-700'],
        'pending_approval' => ['Menunggu Persetujuan', 'bg-amber-400', 'text-amber-700'],
        'published' => ['Terbit', 'bg-emerald-500', 'text-emerald-700'],
        'rejected' => ['Ditolak', 'bg-rose-500', 'text-rose-700'],
    ];
    $pipelineTotal = max(1, array_sum($pipeline));
    $statusBadge = [
        'draft' => 'bg-slate-100 text-slate-700',
        'pending_approval' => 'bg-amber-50 text-amber-800',
        'published' => 'bg-emerald-50 text-emerald-800',
        'rejected' => 'bg-rose-50 text-rose-700',
    ];
    $statusLabel = ['draft' => 'Draft', 'pending_approval' => 'Menunggu', 'published' => 'Terbit', 'rejected' => 'Ditolak'];
    $stars = fn ($n) => str_repeat('★', (int) $n) . str_repeat('☆', 5 - (int) $n);
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Hero -->
    <section class="rounded-2xl bg-[#090D16] text-white p-6 lg:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative">
        <div class="relative z-10">
            <span class="text-[11px] font-mono font-bold uppercase tracking-widest text-emerald-400">Studio Instruktur</span>
            <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight mt-1">Halo, {{ auth()->user()->name }}</h1>
            <p class="text-slate-400 text-sm mt-2 max-w-xl">Pantau penjualan, siswa, dan ulasan kursus Anda dalam satu tempat.</p>
        </div>
        <div class="relative z-10 flex flex-wrap gap-3">
            <a href="{{ route('instructor.courses.create') }}" class="min-h-[44px] inline-flex items-center px-5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">+ Buat Kursus</a>
            <a href="{{ route('instructor.courses.index') }}" class="min-h-[44px] inline-flex items-center px-5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">Kelola Kursus</a>
        </div>
        <div class="absolute -right-10 -top-10 w-56 h-56 rounded-full bg-emerald-500/10 blur-2xl" aria-hidden="true"></div>
    </section>

    <!-- KPI row -->
    <section aria-label="Ringkasan" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 block">Total Pendapatan Kotor</span>
            <span class="text-xl lg:text-2xl font-mono font-extrabold text-slate-900 tabular-nums block mt-1">{{ $rp($stats['revenue']) }}</span>
            <span class="text-[11px] text-emerald-700 font-semibold">{{ $rp($stats['revenue_month']) }} bulan ini</span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 block">Total Siswa / Pembeli</span>
            <span class="text-2xl font-mono font-extrabold text-slate-900 tabular-nums block mt-1">{{ $stats['students'] }}</span>
            <a href="{{ route('instructor.sales.index') }}" class="text-[11px] text-emerald-700 font-semibold hover:underline">Lihat daftar &rarr;</a>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 block">Rating Rata-rata</span>
            <span class="text-2xl font-mono font-extrabold text-slate-900 tabular-nums block mt-1">
                {{ $stats['rating'] !== null ? number_format($stats['rating'], 1) : '—' }}
                @if($stats['rating'] !== null)<span class="text-amber-500 text-lg" aria-hidden="true">★</span>@endif
            </span>
            <a href="{{ route('instructor.reviews.index') }}" class="text-[11px] text-emerald-700 font-semibold hover:underline">{{ $stats['reviews'] }} ulasan &rarr;</a>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5">
            <span class="text-xs text-slate-500 block">Total Kursus</span>
            <span class="text-2xl font-mono font-extrabold text-slate-900 tabular-nums block mt-1">{{ $stats['courses'] }}</span>
            <span class="text-[11px] text-slate-500">{{ $pipeline['published'] }} terbit &middot; {{ $pipeline['draft'] }} draft</span>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Revenue chart -->
        <section aria-labelledby="rev-h" class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 id="rev-h" class="font-extrabold text-slate-900">Pendapatan 6 Bulan Terakhir</h2>
                <span class="text-[11px] font-mono text-slate-400">Gross</span>
            </div>
            <div class="flex items-end gap-3 h-48" role="img" aria-label="Grafik batang pendapatan 6 bulan terakhir">
                @foreach($months as $m)
                    @php $h = $m['total'] > 0 ? max(4, round($m['total'] / $maxMonth * 100)) : 2; @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full gap-2 min-w-0">
                        <span class="text-[10px] font-mono text-slate-500 tabular-nums truncate max-w-full">{{ $m['total'] > 0 ? number_format($m['total'] / 1000, 0, ',', '.') . 'k' : '0' }}</span>
                        <div class="w-full rounded-t-lg {{ $m['total'] > 0 ? 'bg-emerald-500' : 'bg-slate-200' }}" style="height: {{ $h }}%"></div>
                        <span class="text-[11px] font-semibold text-slate-500">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Publish pipeline -->
        <section aria-labelledby="pipe-h" class="bg-white rounded-2xl border border-slate-200 p-6">
            <h2 id="pipe-h" class="font-extrabold text-slate-900 mb-1">Status Publikasi</h2>
            <p class="text-xs text-slate-500 mb-4">Kursus tetap Draft sampai Anda mengajukannya. Admin yang menerbitkan.</p>
            <div class="flex h-3 rounded-full overflow-hidden bg-slate-100 mb-5" role="img" aria-label="Proporsi status kursus">
                @foreach($pipelineMeta as $key => [$label, $bar])
                    @if($pipeline[$key] > 0)
                        <div class="{{ $bar }}" style="width: {{ $pipeline[$key] / $pipelineTotal * 100 }}%"></div>
                    @endif
                @endforeach
            </div>
            <ul class="space-y-3">
                @foreach($pipelineMeta as $key => [$label, $bar, $text])
                    <li class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full {{ $bar }}"></span>{{ $label }}</span>
                        <span class="font-mono font-bold tabular-nums {{ $text }}">{{ $pipeline[$key] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>

    <!-- Needs attention -->
    @if($needsAttention->isNotEmpty())
        <section aria-labelledby="attn-h" class="bg-amber-50 border border-amber-200 rounded-2xl p-6">
            <h2 id="attn-h" class="font-extrabold text-amber-900 mb-3">Perlu Tindakan</h2>
            <ul class="space-y-2">
                @foreach($needsAttention as $c)
                    <li class="flex flex-wrap items-center justify-between gap-3 bg-white/70 rounded-xl px-4 py-3">
                        <div class="min-w-0">
                            <span class="font-bold text-slate-900 text-sm block truncate">{{ $c->title }}</span>
                            <span class="text-xs text-slate-600">
                                {{ $c->status === 'rejected' ? 'Ditolak admin — perbaiki lalu ajukan ulang.' : 'Masih draft tanpa materi — unggah video/PDF pertama.' }}
                            </span>
                        </div>
                        <a href="{{ route('instructor.courses.edit', $c) }}" class="min-h-[40px] inline-flex items-center px-4 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold">Buka</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Top courses -->
        <section aria-labelledby="top-h" class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 id="top-h" class="font-extrabold text-slate-900">Kursus Terlaris</h2>
                <a href="{{ route('instructor.courses.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Semua kursus</a>
            </div>
            @if($topCourses->isEmpty())
                <p class="p-8 text-center text-sm text-slate-500">Belum ada kursus. <a href="{{ route('instructor.courses.create') }}" class="text-emerald-700 font-bold hover:underline">Buat yang pertama</a>.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-[11px] uppercase tracking-wider text-slate-400 text-left">
                            <tr><th class="px-6 py-3 font-bold">Kursus</th><th class="px-3 py-3 font-bold">Status</th><th class="px-3 py-3 font-bold text-right">Siswa</th><th class="px-3 py-3 font-bold text-right">Rating</th><th class="px-6 py-3 font-bold text-right">Pendapatan</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($topCourses as $c)
                                <tr>
                                    <td class="px-6 py-3 font-semibold text-slate-900 max-w-[16rem] truncate"><a href="{{ route('instructor.courses.edit', $c) }}" class="hover:text-emerald-700">{{ $c->title }}</a></td>
                                    <td class="px-3 py-3"><span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $statusBadge[$c->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabel[$c->status] ?? $c->status }}</span></td>
                                    <td class="px-3 py-3 text-right font-mono tabular-nums">{{ $c->enrollments_count }}</td>
                                    <td class="px-3 py-3 text-right font-mono tabular-nums">{{ $c->reviews_avg_rating ? number_format($c->reviews_avg_rating, 1) . ' ★' : '—' }}</td>
                                    <td class="px-6 py-3 text-right font-mono font-bold tabular-nums">{{ $rp($c->revenue) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <!-- Recent buyers -->
        <section aria-labelledby="buy-h" class="bg-white rounded-2xl border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 id="buy-h" class="font-extrabold text-slate-900">Pembeli Terbaru</h2>
                <a href="{{ route('instructor.sales.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Semua</a>
            </div>
            @forelse($recentEnrollments as $e)
                <div class="flex items-center gap-3 py-2.5 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                    <span class="w-9 h-9 rounded-lg bg-slate-900 text-emerald-400 text-xs font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($e->user->name ?? '?', 0, 2)) }}</span>
                    <div class="min-w-0 flex-grow">
                        <span class="block text-sm font-semibold text-slate-900 truncate">{{ $e->user->name ?? 'Pengguna' }}</span>
                        <span class="block text-[11px] text-slate-500 truncate">{{ $e->course->title ?? '-' }}</span>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400 shrink-0">{{ $e->enrolled_at?->diffForHumans(short: true) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-500">Belum ada pembelian.</p>
            @endforelse
        </section>
    </div>

    <!-- Recent reviews -->
    <section aria-labelledby="rev2-h" class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 id="rev2-h" class="font-extrabold text-slate-900">Ulasan Terbaru</h2>
            <a href="{{ route('instructor.reviews.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">Semua ulasan</a>
        </div>
        @forelse($recentReviews as $r)
            <article class="py-3 {{ ! $loop->last ? 'border-b border-slate-100' : '' }}">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span class="text-amber-500 text-sm tracking-wide" aria-label="{{ $r->rating }} dari 5 bintang">{{ $stars($r->rating) }}</span>
                    <span class="text-sm font-bold text-slate-900">{{ $r->user->name ?? 'Siswa' }}</span>
                    <span class="text-xs text-slate-500">pada {{ $r->course->title ?? '-' }}</span>
                </div>
                @if($r->comment)<p class="text-sm text-slate-700 mt-1 whitespace-pre-line">{{ $r->comment }}</p>@endif
            </article>
        @empty
            <p class="text-sm text-slate-500">Belum ada ulasan dari siswa.</p>
        @endforelse
    </section>
</div>
@endsection
