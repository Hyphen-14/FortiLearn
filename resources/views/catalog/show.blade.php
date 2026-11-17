@extends('layouts.app')

@section('title', $course->title)

@section('content')
@php
    $firstVideoLesson = $course->lessons->firstWhere('content_type', 'video') ?? $course->lessons->first();
    $initialLessonTitle = $firstVideoLesson ? ($firstVideoLesson->lesson_order . '. ' . $firstVideoLesson->title) : '01. Pengenalan & Orientasi Kelas';
    $initialDuration = $firstVideoLesson && $firstVideoLesson->duration_seconds > 0 ? $firstVideoLesson->duration_seconds : 900;
@endphp

<nav aria-label="Breadcrumb" class="mb-5 flex items-center justify-between gap-3">
    <a href="{{ route('catalog.index') }}"
       class="min-h-[40px] inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white border border-slate-200/90 hover:bg-slate-50 text-xs font-bold text-slate-700 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>Kembali ke Katalog Kelas</span>
    </a>

    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isEnrolled ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-50 border border-amber-200 text-amber-800' }}">
        {{ $isEnrolled ? 'Akses Penuh Aktif' : 'Mode Pratinjau Gratis (Modul 01)' }}
    </span>
</nav>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-7 items-start">
    <!-- Left Column: Interactive HD Video Player, Course Overview & Syllabus (8 Cols) -->
    <div class="lg:col-span-8 space-y-6">

        <!-- 1. Interactive HD Classroom Video Player Stage -->
        <section id="classroom-player-container"
                 aria-label="Pemutar Video Pembelajaran"
                 data-course-title="{{ $course->title }}"
                 data-course-category="{{ $course->category ?? 'Teknologi' }}"
                 data-instructor-name="{{ $course->instructor->name }}"
                 data-initial-lesson="{{ $initialLessonTitle }}"
                 data-initial-duration="{{ $initialDuration }}"
                 class="bg-[#090D16] rounded-2xl border border-slate-800 overflow-hidden shadow-md">

            <!-- Top Player Status Bar -->
            <div class="px-4 py-3 bg-slate-900/90 border-b border-slate-800 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shrink-0"></span>
                    <span id="player-active-module-title" class="text-xs font-bold text-white truncate">
                        {{ $initialLessonTitle }}
                    </span>
                </div>
                <span id="player-badge-mode" class="px-2.5 py-0.5 rounded-md bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-[10px] font-mono font-bold uppercase shrink-0">
                    {{ $isEnrolled ? 'HD 1080P STREAM' : 'FREE PREVIEW HD' }}
                </span>
            </div>

            <!-- 16:9 Canvas Video Stage -->
            <div class="relative aspect-video bg-[#090D16] select-none overflow-hidden">
                <canvas id="classroom-video-canvas" width="960" height="540" class="w-full h-full block"></canvas>

                <!-- Center Play Button Overlay -->
                <button type="button"
                        id="player-center-play"
                        aria-label="Putar Video Materi"
                        class="absolute inset-0 w-full h-full flex items-center justify-center bg-slate-950/35 hover:bg-slate-950/20 transition group focus-visible:outline-none">
                    <span class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-emerald-600 group-hover:bg-emerald-500 group-hover:scale-105 text-white flex items-center justify-center shadow-xl transition">
                        <svg class="w-8 h-8 translate-x-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                        </svg>
                    </span>
                </button>
            </div>

            <!-- Bottom Video Player Control Bar -->
            <div class="p-3.5 sm:px-5 bg-[#090D16] border-t border-slate-800 space-y-2.5">
                <!-- Interactive Timeline Seek Slider -->
                <div class="flex items-center gap-3">
                    <label for="player-seek-slider" class="sr-only">Posisi waktu video</label>
                    <input type="range"
                           id="player-seek-slider"
                           min="0"
                           max="1000"
                           value="0"
                           class="w-full h-1.5 bg-slate-800 rounded-lg accent-emerald-500">
                </div>

                <!-- Buttons & Time Readout Row -->
                <div class="flex items-center justify-between gap-3 text-xs text-slate-300">
                    <div class="flex items-center gap-3">
                        <button type="button"
                                id="player-play-btn"
                                class="min-h-[36px] px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold inline-flex items-center gap-1.5 transition">
                            <span id="player-play-label">Putar Video</span>
                        </button>

                        <span id="player-time-display" class="font-mono text-xs text-slate-300 tabular-nums">
                            00:00 / {{ gmdate('i:s', $initialDuration) }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span id="player-chapter-pill" class="hidden sm:inline-block px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-[11px] font-mono text-emerald-400">
                            Bagian 1/3: Konsep Inti
                        </span>

                        <label for="player-speed-select" class="sr-only">Kecepatan Putar</label>
                        <select id="player-speed-select"
                                class="min-h-[34px] px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-xs font-mono font-bold text-slate-200">
                            <option value="1">1.0x</option>
                            <option value="1.25">1.25x</option>
                            <option value="1.5">1.5x</option>
                            <option value="2">2.0x</option>
                        </select>

                        <button type="button"
                                id="player-fullscreen-btn"
                                class="min-h-[34px] min-w-[34px] p-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 border border-slate-700 text-slate-300 hover:text-white inline-flex items-center justify-center transition"
                                title="Layar Penuh">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Course Overview Card -->
        <article class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs p-6 md:p-8">
            <div class="flex flex-wrap items-center gap-2.5 text-xs mb-3">
                <span class="px-2.5 py-0.5 bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold uppercase tracking-wider rounded-md">
                    {{ $course->category ?? 'Program Profesional' }}
                </span>
                <span class="text-slate-400">&bull;</span>
                <span class="inline-flex items-center gap-1 font-mono font-bold text-amber-500">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span>4.9 (Ulasan Terverifikasi)</span>
                </span>
            </div>

            <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight mb-3 leading-tight">
                {{ $course->title }}
            </h1>

            <p class="text-slate-600 text-sm leading-relaxed mb-6 max-w-[65ch]">
                {{ $course->description }}
            </p>

            <div class="pt-5 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($course->instructor->name, 0, 2)) }}
                    </div>
                    <div>
                        <span class="text-slate-400 block">Instruktur Kelas</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $course->instructor->name }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Materi</span>
                        <span class="font-mono font-bold text-slate-900 text-sm tabular-nums">{{ $course->lessons->count() }} Modul Lengkap</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Estimasi Durasi</span>
                        <span class="font-mono font-bold text-slate-900 text-sm tabular-nums">
                            {{ max(15, (int) ceil($course->lessons->sum('duration_seconds') / 60)) }} Menit Belajar
                        </span>
                    </div>
                </div>
            </div>
        </article>

        <!-- 3. Interactive Curriculum Playlist (<details name="syllabus-group">) -->
        <section aria-labelledby="syllabus-heading" class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h2 id="syllabus-heading" class="text-lg font-extrabold text-slate-900">Daftar Putar Kurikulum &amp; Modul</h2>
                    <p class="text-slate-500 text-xs mt-0.5">
                        Pilih modul di bawah ini untuk memutar video di pemutar atas atau mengunduh panduan PDF.
                    </p>
                </div>
                <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full tabular-nums">
                    {{ $course->lessons->count() }} Modul Tersedia
                </span>
            </div>

            <div class="space-y-3">
                @forelse($course->lessons as $index => $lesson)
                    @php
                        $canPlayThisLesson = $isEnrolled || $index === 0;
                    @endphp
                    <details name="syllabus-group" class="group rounded-xl border border-slate-200 bg-white open:bg-slate-50/70 open:border-slate-300 transition" {{ $index === 0 ? 'open' : '' }}>
                        <summary class="min-h-[54px] p-4 flex items-center justify-between gap-4 select-none rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                            <div class="flex items-center gap-3.5">
                                <span class="w-8 h-8 rounded-lg bg-[#090D16] text-emerald-400 font-mono text-xs font-bold flex items-center justify-center shrink-0 tabular-nums">
                                    {{ str_pad((string) $lesson->lesson_order, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <div>
                                    <div class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition">
                                        {{ $lesson->title }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 tabular-nums">
                                        {{ $lesson->content_type === 'video' ? 'Video Pembelajaran HD' : 'Modul Bacaan & Studi Kasus (PDF)' }}
                                        @if($lesson->duration_seconds > 0)
                                            &bull; <span class="font-mono">{{ gmdate('i:s', $lesson->duration_seconds) }} menit</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 shrink-0">
                                @if($isEnrolled)
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold">
                                        Terbuka
                                    </span>
                                @elseif($index === 0)
                                    <span class="px-2.5 py-1 rounded-full bg-sky-100 text-sky-800 text-xs font-bold">
                                        Pratinjau Gratis
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <span>Terkunci</span>
                                    </span>
                                @endif
                                <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </summary>

                        <div class="px-4 pb-4 pt-3 border-t border-slate-200/70 text-xs text-slate-600 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <p class="leading-relaxed">
                                @if($lesson->content_type === 'video')
                                    Materi video dilengkapi demonstrasi langkah demi langkah, ilustrasi arsitektur, dan contoh kode terstruktur.
                                @else
                                    Dokumen panduan referensi resmi berformat PDF yang dapat Anda baca langsung di browser atau unduh untuk belajar mandiri.
                                @endif
                            </p>

                            <div class="flex items-center gap-2 shrink-0">
                                @if($lesson->content_type === 'video')
                                    @if($canPlayThisLesson)
                                        <button type="button"
                                                data-play-lesson-title="{{ $lesson->lesson_order . '. ' . $lesson->title }}"
                                                data-play-lesson-duration="{{ max(60, (int) $lesson->duration_seconds) }}"
                                                class="min-h-[38px] px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold inline-flex items-center gap-1.5 transition">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                            </svg>
                                            <span>Putar di Pemutar Atas</span>
                                        </button>
                                    @else
                                        <a href="{{ route('checkout.confirm', $course->id) }}"
                                           class="min-h-[38px] px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white font-bold inline-flex items-center transition">
                                            Beli Kelas untuk Membuka
                                        </a>
                                    @endif
                                @else
                                    @if($isEnrolled)
                                        <a href="{{ route('catalog.lesson.stream', [$course->slug, $lesson->id]) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="min-h-[38px] px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold inline-flex items-center gap-1.5 transition">
                                            <span>Buka PDF</span>
                                        </a>
                                        <a href="{{ route('catalog.lesson.stream', [$course->slug, $lesson->id, 'download' => 1]) }}"
                                           class="min-h-[38px] px-3.5 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-bold inline-flex items-center transition">
                                            <span>Unduh</span>
                                        </a>
                                    @else
                                        <a href="{{ route('checkout.confirm', $course->id) }}"
                                           class="min-h-[38px] px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-emerald-600 text-white font-bold inline-flex items-center transition">
                                            Beli Kelas untuk Unduh PDF
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="p-8 text-center rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-500">
                        Belum ada modul pelajaran yang diterbitkan untuk kursus ini.
                    </div>
                @endforelse
            </div>
        </section>

        @include('catalog._reviews')
    </div>

    <!-- Right Column: Sticky Purchase & Benefits Card (4 Cols) -->
    <aside class="lg:col-span-4 sticky top-20">
        <div class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Investasi Belajar</span>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold">
                    Sekali Bayar
                </span>
            </div>

            <div class="text-3xl font-mono font-extrabold text-slate-900 tabular-nums mb-6">
                Rp {{ number_format($course->price, 0, ',', '.') }}
            </div>

            @if($isEnrolled)
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs leading-relaxed">
                    <div class="font-bold text-emerald-800 mb-0.5">Anda Sudah Terdaftar di Kelas Ini</div>
                    Seluruh modul video dan bahan bacaan PDF telah aktif untuk akun Anda. Klik tombol <strong>Putar Video</strong> untuk mulai belajar.
                </div>
                <button type="button"
                        id="sidebar-start-watching-btn"
                        class="w-full min-h-[44px] py-3 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs transition shadow-xs">
                    Mulai Putar Materi Sekarang
                </button>
            @else
                <div class="space-y-2.5">
                    <a href="{{ route('checkout.confirm', $course->id) }}"
                       class="w-full min-h-[44px] inline-flex items-center justify-center py-3 px-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl text-sm transition shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        Beli &amp; Buka Semua Modul
                    </a>

                    <button type="button"
                            data-open-dialog="quick-checkout-dialog"
                            class="w-full min-h-[44px] inline-flex items-center justify-center py-2.5 px-4 bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-semibold rounded-xl text-xs transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        Cek Kecukupan Saldo Dompet
                    </button>
                </div>
            @endif

            <div class="mt-6 pt-6 border-t border-slate-100 space-y-3 text-xs text-slate-600">
                <div class="font-bold text-slate-900 mb-1">Keuntungan Kelas Ini:</div>
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Akses penuh seumur hidup tanpa biaya langganan bulanan</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Pemutar video interaktif HD &amp; panduan PDF siap unduh</span>
                </div>
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Bukti pembayaran &amp; kwitansi digital resmi</span>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- Native <dialog closedby="any"> Quick Balance Check Modal -->
@if(! $isEnrolled)
    @php
        $currentBalance = auth()->check() ? (float) auth()->user()->balance : 0.0;
        $coursePriceFloat = (float) $course->price;
        $projectedAfter = $currentBalance - $coursePriceFloat;
        $canAfford = $currentBalance >= $coursePriceFloat;
    @endphp
    <dialog id="quick-checkout-dialog"
            closedby="any"
            aria-labelledby="quick-checkout-title"
            class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white text-slate-900 p-0 shadow-xl m-auto">
        <div class="p-6 border-b border-slate-200 bg-[#090D16] text-white flex items-start justify-between gap-4">
            <div>
                <span class="text-xs font-mono uppercase tracking-wider text-emerald-400 block mb-1">
                    Simulasi Pembayaran Kelas
                </span>
                <h3 id="quick-checkout-title" class="text-base font-bold text-white">
                    Ringkasan Saldo Dompet Belajar
                </h3>
            </div>
            <button type="button"
                    data-close-dialog
                    aria-label="Tutup dialog"
                    class="min-h-[40px] min-w-[40px] inline-flex items-center justify-center rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4 text-sm">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                <div class="flex justify-between items-center text-xs text-slate-600">
                    <span>Kelas Pilihan:</span>
                    <span class="font-bold text-slate-900">{{ $course->title }}</span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-600">
                    <span>Saldo Dompet Anda:</span>
                    <span class="font-mono font-bold text-slate-900 tabular-nums">
                        Rp {{ number_format($currentBalance, 0, ',', '.') }}
                    </span>
                </div>
                <div class="flex justify-between items-center text-xs text-slate-600">
                    <span>Harga Kelas:</span>
                    <span class="font-mono font-bold text-rose-600 tabular-nums">
                        - Rp {{ number_format($coursePriceFloat, 0, ',', '.') }}
                    </span>
                </div>
                <div class="pt-2.5 border-t border-slate-200 flex justify-between items-center text-sm font-bold">
                    <span>Sisa Saldo Setelah Pembelian:</span>
                    <span class="font-mono tabular-nums {{ $canAfford ? 'text-emerald-700' : 'text-rose-600' }}">
                        @if($canAfford)
                            Rp {{ number_format($projectedAfter, 0, ',', '.') }}
                        @else
                            Kurang Rp {{ number_format(abs($projectedAfter), 0, ',', '.') }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button"
                        data-close-dialog
                        class="min-h-[44px] px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-xs font-semibold text-slate-700 transition">
                    Tutup
                </button>
                <a href="{{ route('checkout.confirm', $course->id) }}"
                   class="min-h-[44px] inline-flex items-center justify-center px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition">
                    Lanjut ke Pembayaran
                </a>
            </div>
        </div>
    </dialog>
@endif

<!-- Interactive HD Classroom Canvas Video Synthesizer Engine -->
<script>
    (function () {
        'use strict';

        const container = document.getElementById('classroom-player-container');
        const canvas = document.getElementById('classroom-video-canvas');
        if (!container || !canvas) return;

        const ctx = canvas.getContext('2d');
        const playBtn = document.getElementById('player-play-btn');
        const playLabel = document.getElementById('player-play-label');
        const centerPlayBtn = document.getElementById('player-center-play');
        const seekSlider = document.getElementById('player-seek-slider');
        const timeDisplay = document.getElementById('player-time-display');
        const moduleTitleEl = document.getElementById('player-active-module-title');
        const chapterPill = document.getElementById('player-chapter-pill');
        const speedSelect = document.getElementById('player-speed-select');
        const fullscreenBtn = document.getElementById('player-fullscreen-btn');
        const sidebarStartBtn = document.getElementById('sidebar-start-watching-btn');

        const courseTitle = container.getAttribute('data-course-title') || 'FortiLearn Course';
        const courseCategory = (container.getAttribute('data-course-category') || 'Teknologi').toUpperCase();
        const instructorName = container.getAttribute('data-instructor-name') || 'Instruktur FortiLearn';

        let activeLessonTitle = container.getAttribute('data-initial-lesson') || '01. Modul Utama';
        let totalDuration = parseInt(container.getAttribute('data-initial-duration') || '900', 10) || 900;
        let currentTime = 0;
        let isPlaying = false;
        let playbackRate = 1.0;
        let lastFrameTime = performance.now();

        function formatSeconds(sec) {
            const s = Math.max(0, Math.floor(sec));
            const mins = Math.floor(s / 60);
            const rem = s % 60;
            return String(mins).padStart(2, '0') + ':' + String(rem).padStart(2, '0');
        }

        function drawLectureFrame(now) {
            const dt = (now - lastFrameTime) / 1000;
            lastFrameTime = now;

            if (isPlaying) {
                currentTime += dt * playbackRate * 8; // Accelerated demo time so slide transitions are easy to see
                if (currentTime >= totalDuration) {
                    currentTime = 0;
                    setPlayingState(false);
                }
                if (seekSlider) {
                    seekSlider.value = String(Math.round((currentTime / totalDuration) * 1000));
                }
            }

            const progress = Math.min(1, Math.max(0, currentTime / totalDuration));
            const chapterIdx = progress < 0.33 ? 1 : (progress < 0.66 ? 2 : 3);
            const chapterTitle =
                chapterIdx === 1 ? 'Bagian 1/3: Konsep & Arsitektur' :
                chapterIdx === 2 ? 'Bagian 2/3: Implementasi Proyek' :
                'Bagian 3/3: Evaluasi & Best Practice';

            if (chapterPill) chapterPill.textContent = chapterTitle;
            if (timeDisplay) timeDisplay.textContent = formatSeconds(currentTime) + ' / ' + formatSeconds(totalDuration);

            // 1. Background Studio Canvas
            ctx.fillStyle = '#090D16';
            ctx.fillRect(0, 0, 960, 540);

            // Subtle Grid Lines
            ctx.strokeStyle = 'rgba(148, 163, 184, 0.06)';
            ctx.lineWidth = 1;
            for (let x = 0; x < 960; x += 48) {
                ctx.beginPath();
                ctx.moveTo(x, 0);
                ctx.lineTo(x, 540);
                ctx.stroke();
            }
            for (let y = 0; y < 540; y += 48) {
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(960, y);
                ctx.stroke();
            }

            // 2. Top Slide Header Bar
            ctx.fillStyle = '#0F172A';
            ctx.fillRect(48, 38, 864, 64);
            ctx.strokeStyle = '#1E293B';
            ctx.strokeRect(48, 38, 864, 64);

            ctx.fillStyle = '#10B981';
            ctx.font = 'bold 13px "JetBrains Mono", monospace';
            ctx.fillText('FORTILEARN STUDIO  |  ' + courseCategory, 72, 64);

            ctx.fillStyle = '#F8FAFC';
            ctx.font = 'bold 18px "Plus Jakarta Sans", sans-serif';
            ctx.fillText(activeLessonTitle.slice(0, 62), 72, 88);

            // 3. Left Presentation Slide Content Box
            ctx.fillStyle = '#0F172A';
            ctx.fillRect(48, 124, 510, 320);
            ctx.strokeStyle = '#1E293B';
            ctx.strokeRect(48, 124, 510, 320);

            ctx.fillStyle = '#34D399';
            ctx.font = 'bold 14px "JetBrains Mono", monospace';
            ctx.fillText(chapterTitle.toUpperCase(), 72, 160);

            ctx.fillStyle = '#FFFFFF';
            ctx.font = 'bold 22px "Plus Jakarta Sans", sans-serif';
            ctx.fillText(courseTitle.slice(0, 38), 72, 194);
            if (courseTitle.length > 38) {
                ctx.fillText(courseTitle.slice(38, 76), 72, 222);
            }

            // Slide Bullet Points with Animated Highlight
            const bullets = chapterIdx === 1
                ? [
                    '1. Pemahaman fondasi & struktur alur kerja modern',
                    '2. Pemetaan komponen utama dan standar industri',
                    '3. Persiapan lingkungan kerja & peralatan praktik'
                ]
                : chapterIdx === 2
                ? [
                    '1. Langkah demi langkah membangun fitur produksi',
                    '2. Integrasi antar modul secara efisien dan rapi',
                    '3. Studi kasus nyata dan penanganan skenario kritis'
                ]
                : [
                    '1. Pengujian kualitas dan optimasi performa akhir',
                    '2. Pemeriksaan daftar standar kesiapan komersial',
                    '3. Kesimpulan materi & persiapan modul berikutnya'
                ];

            bullets.forEach((b, i) => {
                const yPos = 272 + i * 44;
                ctx.fillStyle = 'rgba(5, 150, 105, 0.14)';
                ctx.fillRect(72, yPos - 22, 462, 32);
                ctx.fillStyle = '#E2E8F0';
                ctx.font = '600 14px "Plus Jakarta Sans", sans-serif';
                ctx.fillText(b, 86, yPos - 1);
            });

            // 4. Right Interactive Code / Architecture Monitor
            ctx.fillStyle = '#0F172A';
            ctx.fillRect(580, 124, 332, 320);
            ctx.strokeStyle = '#1E293B';
            ctx.strokeRect(580, 124, 332, 320);

            // Window Dots
            ctx.fillStyle = '#F43F5E';
            ctx.beginPath(); ctx.arc(602, 146, 5, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#F59E0B';
            ctx.beginPath(); ctx.arc(620, 146, 5, 0, Math.PI * 2); ctx.fill();
            ctx.fillStyle = '#10B981';
            ctx.beginPath(); ctx.arc(638, 146, 5, 0, Math.PI * 2); ctx.fill();

            ctx.fillStyle = '#94A3B8';
            ctx.font = '12px "JetBrains Mono", monospace';
            ctx.fillText('live-demo-workspace.ts', 660, 150);

            // Animated Code / Diagram Bars inside Monitor
            const pulse = Math.sin(now / 350) * 0.5 + 0.5;
            const codeLines = [
                { w: 180, color: '#38BDF8' },
                { w: 240, color: '#34D399' },
                { w: 150, color: '#94A3B8' },
                { w: 260, color: '#FBBF24' },
                { w: 210, color: '#38BDF8' },
                { w: 190 + pulse * 45, color: '#10B981' },
                { w: 130, color: '#94A3B8' },
            ];

            codeLines.forEach((line, idx) => {
                const y = 185 + idx * 26;
                ctx.fillStyle = line.color;
                ctx.globalAlpha = idx === (chapterIdx + 2) ? 0.95 : 0.65;
                ctx.fillRect(604 + (idx % 2) * 16, y, line.w, 10);
            });
            ctx.globalAlpha = 1.0;

            // Live Audio Waveform Visualizer at Bottom of Right Monitor
            ctx.fillStyle = '#64748B';
            ctx.font = '11px "JetBrains Mono", monospace';
            ctx.fillText('Instruktur: ' + instructorName, 604, 390);

            for (let b = 0; b < 24; b++) {
                const waveH = isPlaying
                    ? 6 + Math.abs(Math.sin(now / 140 + b * 0.55)) * 22
                    : 6;
                ctx.fillStyle = '#10B981';
                ctx.fillRect(604 + b * 12, 428 - waveH, 7, waveH);
            }

            // 5. Bottom Progress Bar inside Canvas
            ctx.fillStyle = '#1E293B';
            ctx.fillRect(48, 472, 864, 8);
            ctx.fillStyle = '#10B981';
            ctx.fillRect(48, 472, Math.max(8, 864 * progress), 8);

            requestAnimationFrame(drawLectureFrame);
        }

        function setPlayingState(play) {
            isPlaying = play;
            if (playLabel) {
                playLabel.textContent = isPlaying ? 'Jeda Video' : 'Putar Video';
            }
            if (centerPlayBtn) {
                centerPlayBtn.style.opacity = isPlaying ? '0' : '1';
                centerPlayBtn.style.pointerEvents = isPlaying ? 'none' : 'auto';
            }
        }

        if (playBtn) {
            playBtn.addEventListener('click', () => {
                setPlayingState(!isPlaying);
            });
        }

        if (centerPlayBtn) {
            centerPlayBtn.addEventListener('click', () => {
                setPlayingState(true);
            });
        }

        canvas.addEventListener('click', () => {
            if (isPlaying) setPlayingState(false);
        });

        if (seekSlider) {
            seekSlider.addEventListener('input', () => {
                const ratio = parseInt(seekSlider.value, 10) / 1000;
                currentTime = ratio * totalDuration;
            });
        }

        if (speedSelect) {
            speedSelect.addEventListener('change', () => {
                playbackRate = parseFloat(speedSelect.value) || 1.0;
            });
        }

        if (fullscreenBtn) {
            fullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement && container.requestFullscreen) {
                    container.requestFullscreen().catch(() => {});
                } else if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                }
            });
        }

        if (sidebarStartBtn) {
            sidebarStartBtn.addEventListener('click', () => {
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setPlayingState(true);
            });
        }

        // Wire "Putar di Pemutar Atas" buttons inside the syllabus accordion
        document.querySelectorAll('[data-play-lesson-title]').forEach((btn) => {
            btn.addEventListener('click', () => {
                activeLessonTitle = btn.getAttribute('data-play-lesson-title') || activeLessonTitle;
                totalDuration = parseInt(btn.getAttribute('data-play-lesson-duration') || '900', 10) || 900;
                currentTime = 0;
                if (moduleTitleEl) moduleTitleEl.textContent = activeLessonTitle;
                if (seekSlider) seekSlider.value = '0';
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setPlayingState(true);
            });
        });

        requestAnimationFrame(drawLectureFrame);
    })();
</script>
@endsection
