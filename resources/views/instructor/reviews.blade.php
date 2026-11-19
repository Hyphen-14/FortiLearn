@extends('layouts.instructor')

@section('title', 'Ulasan Siswa')

@section('content')
@php $stars = fn ($n) => str_repeat('★', (int) $n) . str_repeat('☆', 5 - (int) $n); @endphp
<div class="max-w-5xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-700 block mb-1">Ulasan Siswa</span>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Apa kata siswa Anda</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Summary -->
        <section aria-label="Ringkasan rating" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <div class="text-center">
                <span class="block text-5xl font-mono font-extrabold text-slate-900 tabular-nums">{{ $average !== null ? number_format($average, 1) : '—' }}</span>
                <span class="text-amber-500 tracking-wide">{{ $stars(round($average ?? 0)) }}</span>
                <span class="block text-xs text-slate-500 mt-1">{{ $count }} ulasan</span>
            </div>
            <div class="space-y-1.5">
                @foreach($distribution as $star => $total)
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-6 font-mono text-slate-600">{{ $star }}★</span>
                        <div class="flex-grow h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full bg-amber-400" style="width: {{ $count ? $total / $count * 100 : 0 }}%"></div></div>
                        <span class="w-6 text-right font-mono text-slate-500 tabular-nums">{{ $total }}</span>
                    </div>
                @endforeach
            </div>

            <form method="GET" action="{{ route('instructor.reviews.index') }}" class="pt-2">
                <label for="course-filter" class="text-xs font-bold text-slate-600 block mb-1">Filter kursus</label>
                <select id="course-filter" name="course" onchange="this.form.submit()"
                        class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                    <option value="">Semua kursus</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" @selected($selected && $selected->id === $c->id)>{{ $c->title }} ({{ $c->reviews_count }})</option>
                    @endforeach
                </select>
                <noscript><button class="mt-2 px-3 py-2 text-xs font-bold bg-slate-900 text-white rounded-lg">Terapkan</button></noscript>
            </form>
        </section>

        <!-- List -->
        <section aria-label="Daftar ulasan" class="md:col-span-2 bg-white rounded-2xl border border-slate-200 p-6">
            @forelse($reviews as $r)
                <article class="py-4 {{ ! $loop->first ? 'border-t border-slate-100' : '' }}">
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="text-amber-500 tracking-wide" aria-label="{{ $r->rating }} dari 5 bintang">{{ $stars($r->rating) }}</span>
                        <span class="text-sm font-bold text-slate-900">{{ $r->user->name ?? 'Siswa' }}</span>
                        <span class="text-[11px] font-mono text-slate-400 ml-auto">{{ $r->created_at->format('d M Y') }}</span>
                    </div>
                    <span class="text-xs text-emerald-700 font-semibold">{{ $r->course->title ?? '-' }}</span>
                    @if($r->comment)
                        <p class="text-sm text-slate-700 mt-2 whitespace-pre-line">{{ $r->comment }}</p>
                    @else
                        <p class="text-xs text-slate-400 mt-2 italic">Hanya memberi rating.</p>
                    @endif
                </article>
            @empty
                <p class="py-10 text-center text-sm text-slate-500">Belum ada ulasan{{ $selected ? ' untuk kursus ini' : '' }}.</p>
            @endforelse
            <div class="pt-2">{{ $reviews->links() }}</div>
        </section>
    </div>
</div>
@endsection
