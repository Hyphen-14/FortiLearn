@extends('layouts.app')

@section('title', 'Kursus Saya')

@section('content')
<div class="space-y-7">
    <!-- Header Banner -->
    <section class="rounded-2xl bg-[#090D16] text-white p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-emerald-400 block">
                DASHBOARD PEMBELAJAR
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Kursus Saya &amp; Progres Materi
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                Akses seluruh kelas yang telah Anda miliki kapan saja tanpa batas waktu.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('catalog.index') }}"
               class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold transition">
                Tambah Kelas Baru
            </a>
        </div>
    </section>

    @guest
        <!-- Guest Prompt Card -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-10 text-center max-w-xl mx-auto my-6 shadow-2xs space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
            </div>
            <h2 class="text-lg font-extrabold text-slate-900">Masuk untuk Melihat Koleksi Kursus Anda</h2>
            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                Silakan masuk ke akun Anda (atau gunakan akun demo 1-klik) untuk mengakses seluruh modul video dan panduan PDF yang telah Anda beli.
            </p>
            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('login') }}"
                   class="min-h-[44px] inline-flex items-center px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold transition">
                    Masuk ke Akun
                </a>
                <a href="{{ route('register') }}"
                   class="min-h-[44px] inline-flex items-center px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold transition">
                    Daftar Akun Baru
                </a>
            </div>
        </div>
    @else
        @if($enrolledCourses->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200/90 p-10 text-center max-w-xl mx-auto my-6 shadow-2xs space-y-4">
                <h2 class="text-lg font-extrabold text-slate-900">Anda Belum Mengikuti Kursus Apa Pun</h2>
                <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                    Jelajahi katalog kursus lintas bidang dan gunakan saldo Dompet Belajar Anda untuk mulai belajar hari ini.
                </p>
                <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('catalog.index') }}"
                       class="min-h-[44px] inline-flex items-center px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold transition">
                        Jelajahi Katalog Kelas
                    </a>
                    <a href="{{ route('wallet.index') }}"
                       class="min-h-[44px] inline-flex items-center px-5 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-bold transition">
                        Cek Saldo Dompet
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($enrolledCourses as $course)
                    @php
                        $totalMinutes = max(15, (int) ceil($course->lessons->sum('duration_seconds') / 60));
                    @endphp
                    <article class="bg-white rounded-2xl border border-emerald-200/90 p-5 flex flex-col justify-between shadow-2xs hover:shadow-md transition">
                        <div>
                            <div class="h-32 rounded-xl bg-[#090D16] p-4 flex flex-col justify-between mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 text-[10px] font-mono font-bold uppercase">
                                        {{ $course->category ?? 'Program Utama' }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-bold">
                                        Aktif Terdaftar
                                    </span>
                                </div>
                                <h2 class="text-base font-extrabold text-white line-clamp-2 leading-snug">
                                    {{ $course->title }}
                                </h2>
                            </div>

                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-2 mb-4">
                                {{ $course->description }}
                            </p>

                            <!-- Progress Bar Visual -->
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 mb-4 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-700">Status Akses Materi</span>
                                    <span class="font-mono font-bold text-emerald-700 tabular-nums">100% Terbuka</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                                    <div class="h-full bg-emerald-600 rounded-full w-full"></div>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono tabular-nums">
                                    {{ $course->lessons->count() }} Modul &bull; {{ $totalMinutes }} Menit &bull; Instruktur: {{ $course->instructor->name }}
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('catalog.show', $course->slug) }}"
                           class="w-full min-h-[44px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                            <span>Buka &amp; Pelajari Materi</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                            </svg>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    @endguest
</div>
@endsection

