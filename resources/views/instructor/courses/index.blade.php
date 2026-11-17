@extends('layouts.app')

@section('title', 'Studio Instruktur')

@section('content')
@php
    $statusMap = [
        'draft' => ['Draft', 'bg-slate-100 text-slate-700 border-slate-200'],
        'pending_approval' => ['Menunggu Persetujuan', 'bg-amber-50 text-amber-800 border-amber-200'],
        'published' => ['Terbit', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
    ];
@endphp
<div class="max-w-6xl mx-auto space-y-7">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-700 block mb-1">Studio Instruktur</span>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Course Anda</h1>
            <p class="text-slate-600 text-sm mt-1">Buat course, susun kurikulum, dan unggah materi video/PDF dengan pemeriksaan keamanan otomatis.</p>
        </div>
        <a href="{{ route('instructor.courses.create') }}"
           class="min-h-[44px] inline-flex items-center justify-center px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
            + Buat Course Baru
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['Total Course', $stats['total']], ['Terbit', $stats['published']], ['Menunggu Review', $stats['pending']], ['Total Siswa', $stats['students']]] as [$label, $value])
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
                <span class="text-xs text-slate-500 block">{{ $label }}</span>
                <span class="text-2xl font-mono font-extrabold text-slate-900 tabular-nums">{{ $value }}</span>
            </div>
        @endforeach
    </div>

    @if($courses->isEmpty())
        <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-10 text-center">
            <p class="text-slate-700 font-bold">Belum ada course.</p>
            <p class="text-slate-500 text-sm mt-1">Mulai dengan membuat course pertama Anda.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($courses as $course)
                @php [$label, $badge] = $statusMap[$course->status] ?? [$course->status, 'bg-slate-100 text-slate-700 border-slate-200']; @endphp
                <article class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden flex flex-col shadow-2xs">
                    <div class="h-36 bg-[#090D16] flex items-center justify-center overflow-hidden">
                        @if($course->thumbnail)
                            <img src="{{ route('course.thumbnail', $course->id) }}" alt="Thumbnail {{ $course->title }}" class="w-full h-full object-cover" loading="lazy">
                        @else
                            <span class="text-emerald-400 font-mono text-xs">Tanpa thumbnail</span>
                        @endif
                    </div>
                    <div class="p-4 flex-grow flex flex-col gap-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full border text-[11px] font-bold {{ $badge }}">{{ $label }}</span>
                            <span class="text-[11px] font-mono text-slate-500">{{ $course->category }}</span>
                        </div>
                        <h2 class="font-extrabold text-slate-900 leading-snug">{{ $course->title }}</h2>
                        <div class="text-xs text-slate-500 flex items-center gap-3 mt-auto">
                            <span>{{ $course->lessons_count }} materi</span>
                            <span>{{ $course->enrollments_count }} siswa</span>
                            <span class="ml-auto font-mono font-bold text-slate-800">Rp {{ number_format($course->price, 0, ',', '.') }}</span>
                        </div>
                        <a href="{{ route('instructor.courses.edit', $course) }}"
                           class="min-h-[44px] inline-flex items-center justify-center rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                            Kelola Kurikulum &amp; Materi
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
