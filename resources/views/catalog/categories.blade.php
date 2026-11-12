@extends('layouts.app')

@section('title', 'Kategori & Jalur Belajar')

@section('content')
@php
    $categoryMeta = [
        'Frontend' => [
            'desc' => 'Kuasai pembuatan antarmuka web modern, responsif, dan interaktif menggunakan React, TypeScript, serta standar CSS terkini.',
            'bg' => 'bg-[#0F172A]',
            'accent' => 'text-sky-400',
            'badge' => 'bg-sky-50 text-sky-700 border-sky-200',
            'level' => 'Pemula s/d Mahir',
        ],
        'Backend' => [
            'desc' => 'Bangun arsitektur server berkinerja tinggi, perancangan RESTful API, pemodelan database relasional, dan microservices.',
            'bg' => 'bg-[#090D16]',
            'accent' => 'text-emerald-400',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'level' => 'Menengah s/d Mahir',
        ],
        'UI/UX Design' => [
            'desc' => 'Pelajari proses perancangan produk digital mulai dari riset pengguna, wireframing, design system, hingga prototyping di Figma.',
            'bg' => 'bg-[#1E1B4B]',
            'accent' => 'text-amber-300',
            'badge' => 'bg-amber-50 text-amber-800 border-amber-200',
            'level' => 'Semua Tingkat',
        ],
        'Data & AI' => [
            'desc' => 'Olahan data menjadi keputusan bisnis cerdas dengan Python, analisis statistik, visualisasi data, dan machine learning.',
            'bg' => 'bg-[#064E3B]',
            'accent' => 'text-teal-300',
            'badge' => 'bg-teal-50 text-teal-800 border-teal-200',
            'level' => 'Pemula s/d Menengah',
        ],
        'Bisnis' => [
            'desc' => 'Tingkatkan produktivitas tim, kepemimpinan produk digital, penyusunan strategi bisnis, dan manajemen waktu efektif.',
            'bg' => 'bg-[#7C2D12]',
            'accent' => 'text-orange-300',
            'badge' => 'bg-orange-50 text-orange-800 border-orange-200',
            'level' => 'Semua Tingkat',
        ],
        'Cyber Security' => [
            'desc' => 'Pelajari praktik pemrograman aman, pengujian keamanan aplikasi web, serta perlindungan infrastruktur dan data pengguna.',
            'bg' => 'bg-[#090D16]',
            'accent' => 'text-emerald-400',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'level' => 'Menengah s/d Mahir',
        ],
    ];
@endphp

<div class="space-y-7">
    <!-- Page Header Banner -->
    <section class="rounded-2xl bg-[#090D16] text-white p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-emerald-400 block">
                DIREKTORI BIDANG KEAHLIAN
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Pilih Jalur Belajar Sesuai Tujuan Karier Anda
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                Telusuri {{ $groupedCategories->count() }} bidang keahlian utama dengan total {{ $totalCourses }} kelas terstruktur yang dirancang oleh praktisi industri.
            </p>
        </div>

        <a href="{{ route('catalog.index') }}"
           class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold shrink-0 transition">
            Lihat Semua Kelas ({{ $totalCourses }})
        </a>
    </section>

    <!-- Categories & Learning Paths Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($groupedCategories as $catName => $catCourses)
            @php
                $meta = $categoryMeta[$catName] ?? [
                    'desc' => 'Kumpulan kelas pilihan untuk meningkatkan kompetensi profesional Anda di bidang ' . $catName . '.',
                    'bg' => 'bg-[#090D16]',
                    'accent' => 'text-emerald-400',
                    'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'level' => 'Semua Tingkat',
                ];
                $totalCatLessons = $catCourses->sum(fn($c) => $c->lessons->count());
            @endphp

            <article class="bg-white rounded-2xl border border-slate-200/90 hover:border-slate-300 p-5 flex flex-col justify-between shadow-2xs hover:shadow-md transition">
                <div>
                    <!-- Category Visual Banner -->
                    <div class="h-28 rounded-xl {{ $meta['bg'] }} p-4 flex flex-col justify-between mb-4 relative overflow-hidden">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono font-bold uppercase tracking-widest {{ $meta['accent'] }}">
                                JALUR KARIER PROFESIONAL
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full bg-white/15 text-white text-[11px] font-mono font-bold tabular-nums">
                                {{ $catCourses->count() }} Kelas
                            </span>
                        </div>
                        <h2 class="text-lg font-extrabold text-white tracking-tight">
                            {{ $catName }}
                        </h2>
                    </div>

                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-2.5 py-0.5 rounded-md border text-[10px] font-extrabold uppercase tracking-wider {{ $meta['badge'] }}">
                            {{ $meta['level'] }}
                        </span>
                        <span class="text-xs font-mono text-slate-500 tabular-nums">
                            {{ $totalCatLessons }} Modul Materi
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        {{ $meta['desc'] }}
                    </p>

                    <!-- Course List Preview inside Category -->
                    <div class="border-t border-slate-100 pt-3 mb-5 space-y-2">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">
                            Kelas dalam Bidang Ini:
                        </span>
                        @foreach($catCourses as $c)
                            <a href="{{ route('catalog.show', $c->slug) }}"
                               class="p-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50/60 border border-slate-200/80 hover:border-emerald-300 flex items-center justify-between gap-2 text-xs transition">
                                <span class="font-bold text-slate-800 truncate">{{ $c->title }}</span>
                                <span class="font-mono font-bold text-emerald-700 shrink-0 tabular-nums">
                                    Rp {{ number_format($c->price, 0, ',', '.') }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <a href="{{ route('catalog.index', ['category' => $catName]) }}"
                   class="w-full min-h-[42px] inline-flex items-center justify-center px-4 py-2 rounded-xl bg-[#090D16] hover:bg-emerald-600 text-white text-xs font-bold transition">
                    Filter Bidang {{ $catName }}
                </a>
            </article>
        @endforeach
    </div>
</div>
@endsection

