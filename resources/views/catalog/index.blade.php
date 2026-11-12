@extends('layouts.app')

@section('title', 'Eksplorasi Kursus Profesional')

@section('content')
@php
    $allCategories = ['Frontend', 'Backend', 'UI/UX Design', 'Data & AI', 'Bisnis', 'Cyber Security'];
    if (!empty($categories)) {
        $allCategories = array_values(array_unique(array_merge($allCategories, $categories)));
    }
    $activeCategory = $category ?? 'all';
    $initialFilterMode = request()->query('filter', 'all');

    $enrolledIds = [];
    if (auth()->check()) {
        $enrolledIds = auth()->user()->enrollments()->pluck('course_id')->toArray();
    }
    $enrolledCount = count($enrolledIds);
    $totalModules = $courses->getCollection()->sum(fn($c) => $c->lessons->count());
    $totalDurationSeconds = $courses->getCollection()->sum(fn($c) => $c->lessons->sum('duration_seconds'));
    $totalHours = max(1, round($totalDurationSeconds / 3600, 1));
    $enrolledCollection = $courses->getCollection()->filter(fn($c) => in_array($c->id, $enrolledIds));
@endphp

<!-- 2-Column Asymmetric EdTech Workspace -->
<div class="grid grid-cols-1 xl:grid-cols-12 gap-7 items-start">

    <!-- LEFT / CENTER MAIN COLUMN (8 of 12 Columns on XL) -->
    <div class="xl:col-span-8 space-y-7">

        <!-- 1. Commercial Illustrated Hero Banner -->
        <section class="relative overflow-hidden rounded-2xl bg-[#090D16] text-white p-6 sm:p-8 border border-slate-800 shadow-sm">
            <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <div class="md:col-span-7 space-y-3.5">
                    <span class="inline-block text-[11px] font-mono font-bold uppercase tracking-widest text-emerald-400">
                        KURSUS ONLINE PROFESIONAL
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                        Tingkatkan Keahlian Anda Bersama Instruktur Industri Terbaik
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-[54ch]">
                        Pelajari kurikulum berbasis proyek nyata mulai dari pengembangan web, desain produk UI/UX, pengolahan data, hingga strategi bisnis.
                    </p>
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <a href="#katalog-kursus"
                           class="min-h-[44px] inline-flex items-center gap-2.5 pl-5 pr-3.5 py-2 rounded-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold transition shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                            <span>Mulai Belajar</span>
                            <span class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white translate-x-[0.5px]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                </svg>
                            </span>
                        </a>
                        @auth
                            <a href="{{ route('wallet.index') }}"
                               class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-800/90 hover:bg-slate-800 text-slate-200 border border-slate-700 text-xs font-semibold transition">
                                <span>Isi Saldo Dompet</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="min-h-[44px] inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-800/90 hover:bg-slate-800 text-slate-200 border border-slate-700 text-xs font-semibold transition">
                                <span>Coba Akun Demo</span>
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Custom Vector Illustration -->
                <div class="md:col-span-5 flex justify-center md:justify-end">
                    <svg class="w-56 h-40 sm:w-64 sm:h-44 select-none" viewBox="0 0 280 190" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="155" cy="95" r="78" fill="#059669" fill-opacity="0.12"/>
                        <circle cx="155" cy="95" r="56" stroke="#10B981" stroke-opacity="0.22" stroke-dasharray="4 4"/>
                        <rect x="26" y="138" width="58" height="14" rx="4" fill="#059669"/>
                        <rect x="30" y="141" width="50" height="8" rx="2" fill="#ECFDF5"/>
                        <rect x="32" y="122" width="52" height="14" rx="4" fill="#0284C7"/>
                        <rect x="36" y="125" width="44" height="8" rx="2" fill="#E0F2FE"/>
                        <rect x="24" y="106" width="56" height="14" rx="4" fill="#F59E0B"/>
                        <rect x="28" y="109" width="48" height="8" rx="2" fill="#FEF3C7"/>
                        <rect x="200" y="136" width="56" height="16" rx="4" fill="#E11D48"/>
                        <rect x="204" y="140" width="48" height="8" rx="2" fill="#FFE4E6"/>
                        <rect x="206" y="118" width="50" height="16" rx="4" fill="#10B981"/>
                        <rect x="210" y="122" width="42" height="8" rx="2" fill="#D1FAE5"/>
                        <rect x="86" y="54" width="112" height="76" rx="8" fill="#1E293B" stroke="#334155" stroke-width="2"/>
                        <rect x="94" y="62" width="96" height="60" rx="4" fill="#0F172A"/>
                        <rect x="102" y="70" width="36" height="24" rx="4" fill="#059669" fill-opacity="0.3" stroke="#10B981" stroke-width="1.5"/>
                        <polygon points="117,77 117,87 126,82" fill="#34D399"/>
                        <rect x="146" y="72" width="36" height="6" rx="3" fill="#38BDF8"/>
                        <rect x="146" y="83" width="28" height="5" rx="2.5" fill="#64748B"/>
                        <rect x="102" y="102" width="80" height="6" rx="3" fill="#1E293B"/>
                        <rect x="102" y="102" width="54" height="6" rx="3" fill="#10B981"/>
                        <path d="M74 132H210L202 144H82L74 132Z" fill="#475569"/>
                        <rect x="184" y="28" width="54" height="32" rx="8" fill="#0F172A" stroke="#10B981" stroke-width="1.5"/>
                        <circle cx="198" cy="44" r="7" fill="#10B981" fill-opacity="0.25"/>
                        <path d="M195 44L197 46L202 41" stroke="#34D399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <rect x="210" y="39" width="20" height="4" rx="2" fill="#F8FAFC"/>
                        <rect x="210" y="46" width="14" height="3" rx="1.5" fill="#94A3B8"/>
                    </svg>
                </div>
            </div>
        </section>

        <!-- 2. Interactive Category Filter Bar & Sort Selector (Clean Flex-Wrap, Zero Ugly Scrollbar) -->
        <section id="filter-kategori" aria-label="Filter Kategori Kursus" class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Category Filter Pills -->
                <div class="flex flex-wrap items-center gap-2" role="tablist" aria-label="Kategori Kursus">
                    <button type="button"
                            data-category-filter="all"
                            class="category-pill min-h-[36px] px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition border {{ $activeCategory === 'all' || !$activeCategory ? 'bg-[#090D16] text-white border-[#090D16]' : 'bg-slate-50 text-slate-600 border-slate-200/90 hover:border-slate-300 hover:text-slate-900' }}">
                        Semua Bidang
                    </button>
                    @foreach($allCategories as $cat)
                        <button type="button"
                                data-category-filter="{{ strtolower($cat) }}"
                                class="category-pill min-h-[36px] px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition border {{ strtolower((string) $activeCategory) === strtolower($cat) ? 'bg-[#090D16] text-white border-[#090D16]' : 'bg-slate-50 text-slate-600 border-slate-200/90 hover:border-slate-300 hover:text-slate-900' }}">
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>

                <!-- Sort Selector -->
                <form id="catalog-sort-form" action="{{ route('catalog.index') }}" method="GET" class="shrink-0 flex items-center gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    @if($search)
                        <input type="hidden" name="q" value="{{ $search }}">
                    @endif
                    @if($category)
                        <input type="hidden" name="category" value="{{ $category }}">
                    @endif
                    <label for="catalog-sort-select" class="text-xs font-semibold text-slate-500 whitespace-nowrap">Urutkan:</label>
                    <select name="sort"
                            id="catalog-sort-select"
                            class="min-h-[36px] px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/90 text-xs font-bold text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                        <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                    </select>
                </form>
            </div>
        </section>

        <!-- 3. "Kursus Saya" Strip (Shown when user has enrolled courses) -->
        @auth
            @if($enrolledCollection->isNotEmpty())
                <section id="kursus-saya" aria-labelledby="my-courses-heading" class="space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <h2 id="my-courses-heading" class="text-base sm:text-lg font-extrabold text-slate-900">Kursus Saya</h2>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-mono font-bold tabular-nums">
                                {{ $enrolledCollection->count() }} Aktif
                            </span>
                        </div>
                        <span class="text-xs font-semibold text-emerald-700">Akses Seumur Hidup</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($enrolledCollection as $enrolledCourse)
                            <div class="bg-white rounded-2xl border border-emerald-200/90 p-4 flex items-center justify-between gap-4 shadow-2xs hover:shadow-sm transition">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-[#090D16] text-emerald-400 flex items-center justify-center shrink-0 font-mono text-xs font-bold">
                                        {{ strtoupper(substr($enrolledCourse->category ?? 'KR', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md mb-1">
                                            {{ $enrolledCourse->category ?? 'Program Utama' }} &bull; Terdaftar
                                        </span>
                                        <h3 class="text-sm font-bold text-slate-900 truncate">
                                            <a href="{{ route('catalog.show', $enrolledCourse->slug) }}" class="hover:text-emerald-700">
                                                {{ $enrolledCourse->title }}
                                            </a>
                                        </h3>
                                        <p class="text-xs text-slate-500 truncate">
                                            {{ $enrolledCourse->instructor->name }} &bull; {{ $enrolledCourse->lessons->count() }} Modul
                                        </p>
                                    </div>
                                </div>

                                <a href="{{ route('catalog.show', $enrolledCourse->slug) }}"
                                   class="min-h-[40px] min-w-[40px] px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold inline-flex items-center gap-1.5 shrink-0 transition"
                                   title="Lanjutkan Belajar">
                                    <span class="hidden sm:inline">Lanjut</span>
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endauth

        <!-- 4. Popular & Available Courses Grid -->
        <section id="katalog-kursus"
                 data-initial-filter="{{ $initialFilterMode }}"
                 aria-labelledby="popular-courses-heading"
                 class="space-y-4">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5">
                    <h2 id="popular-courses-heading" class="text-base sm:text-lg font-extrabold text-slate-900">
                        Katalog Kursus Pilihan
                    </h2>
                    <span id="visible-course-counter" class="px-2.5 py-0.5 rounded-full bg-slate-200/80 text-slate-700 text-xs font-mono font-bold tabular-nums">
                        {{ $courses->total() }} kelas
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            id="show-all-courses-btn"
                            hidden
                            class="text-xs font-bold text-emerald-700 hover:underline">
                        Tampilkan Semua Kelas
                    </button>
                    @if($search || ($category && $category !== 'all'))
                        <a href="{{ route('catalog.index') }}"
                           class="text-xs font-semibold text-emerald-700 hover:underline">
                            Reset Pencarian
                        </a>
                    @endif
                </div>
            </div>

            <div id="catalog-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($courses as $course)
                    @php
                        $userEnrolled = in_array($course->id, $enrolledIds);
                        $catName = $course->category ?? 'Teknologi';
                        $catKey = strtolower($catName);
                        $totalMinutes = max(15, (int) ceil($course->lessons->sum('duration_seconds') / 60));

                        $coverStyles = match(true) {
                            str_contains($catKey, 'front') => [
                                'bg' => 'bg-[#0F172A]',
                                'accent' => 'text-sky-400',
                                'badge' => 'bg-sky-50 text-sky-700 border-sky-200/80',
                                'tagline' => 'MODERN WEB UI',
                            ],
                            str_contains($catKey, 'back') => [
                                'bg' => 'bg-[#090D16]',
                                'accent' => 'text-emerald-400',
                                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                'tagline' => 'CLOUD & API ARCHITECTURE',
                            ],
                            str_contains($catKey, 'ui') || str_contains($catKey, 'design') => [
                                'bg' => 'bg-[#1E1B4B]',
                                'accent' => 'text-amber-300',
                                'badge' => 'bg-amber-50 text-amber-800 border-amber-200/80',
                                'tagline' => 'PRODUCT DESIGN & FIGMA',
                            ],
                            str_contains($catKey, 'data') || str_contains($catKey, 'ai') => [
                                'bg' => 'bg-[#064E3B]',
                                'accent' => 'text-teal-300',
                                'badge' => 'bg-teal-50 text-teal-800 border-teal-200/80',
                                'tagline' => 'PYTHON & MACHINE LEARNING',
                            ],
                            str_contains($catKey, 'bisnis') || str_contains($catKey, 'prod') => [
                                'bg' => 'bg-[#7C2D12]',
                                'accent' => 'text-orange-300',
                                'badge' => 'bg-orange-50 text-orange-800 border-orange-200/80',
                                'tagline' => '2X OUTPUT & PRODUCTIVITY',
                            ],
                            default => [
                                'bg' => 'bg-[#090D16]',
                                'accent' => 'text-emerald-400',
                                'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                'tagline' => 'PROFESSIONAL MASTERCLASS',
                            ],
                        };
                    @endphp

                    <article data-course-card
                             data-course-id="{{ $course->id }}"
                             data-course-enrolled="{{ $userEnrolled ? '1' : '0' }}"
                             data-course-category="{{ $catKey }}"
                             data-search-haystack="{{ strtolower($course->title . ' ' . $course->description . ' ' . $catName . ' ' . $course->instructor->name) }}"
                             class="bg-white rounded-2xl border border-slate-200/90 hover:border-slate-300 p-3.5 flex flex-col justify-between shadow-2xs hover:shadow-md transition group">

                        <div>
                            <!-- Visual Course Cover Thumbnail -->
                            <div class="h-36 rounded-xl {{ $coverStyles['bg'] }} p-4 relative overflow-hidden flex flex-col justify-between mb-3.5">
                                <svg class="absolute -right-4 -bottom-4 w-28 h-28 opacity-15 text-white pointer-events-none" viewBox="0 0 100 100" fill="none" aria-hidden="true">
                                    <circle cx="50" cy="50" r="40" stroke="currentColor" stroke-width="6"/>
                                    <rect x="25" y="25" width="50" height="35" rx="4" stroke="currentColor" stroke-width="4"/>
                                    <path d="M20 75H80" stroke="currentColor" stroke-width="5" stroke-linecap="round"/>
                                </svg>

                                <div class="relative z-10 flex items-center justify-between gap-2">
                                    <span class="text-[10px] font-mono font-bold uppercase tracking-widest {{ $coverStyles['accent'] }}">
                                        {{ $coverStyles['tagline'] }}
                                    </span>

                                    <!-- Top-Right Action Pair: Wishlist Heart Toggle + Quick Syllabus Preview -->
                                    <div class="flex items-center gap-1.5">
                                        <button type="button"
                                                data-wishlist-toggle="{{ $course->id }}"
                                                class="wishlist-btn w-7 h-7 rounded-full bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400"
                                                title="Simpan ke Favorit / Wishlist"
                                                aria-label="Simpan {{ $course->title }} ke Favorit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                            </svg>
                                        </button>

                                        <button type="button"
                                                data-open-dialog="modal-course-{{ $course->id }}"
                                                class="w-7 h-7 rounded-full bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400"
                                                title="Pratinjau Materi Kursus"
                                                aria-label="Pratinjau Materi {{ $course->title }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="relative z-10">
                                    <div class="text-white font-extrabold text-sm leading-snug line-clamp-2 pr-4">
                                        {{ $course->title }}
                                    </div>
                                    <div class="text-[11px] text-slate-300 mt-1 font-mono tabular-nums">
                                        {{ $course->lessons->count() }} Modul &bull; {{ $totalMinutes }} Menit
                                    </div>
                                </div>
                            </div>

                            <!-- Category Tag + Star Rating Row -->
                            <div class="flex items-center justify-between gap-2 mb-2 px-0.5">
                                <span class="px-2.5 py-0.5 rounded-md border text-[10px] font-extrabold uppercase tracking-wider {{ $coverStyles['badge'] }}">
                                    {{ $catName }}
                                </span>
                                <div class="flex items-center gap-1 text-xs font-mono font-bold text-slate-700 tabular-nums">
                                    <svg class="w-3.5 h-3.5 text-amber-400 fill-current" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>4.9</span>
                                </div>
                            </div>

                            <!-- Course Title & Description -->
                            <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition leading-snug mb-1.5 px-0.5 line-clamp-2">
                                <a href="{{ route('catalog.show', $course->slug) }}" class="focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                                    {{ $course->title }}
                                </a>
                            </h3>

                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-4 px-0.5">
                                {{ $course->description }}
                            </p>
                        </div>

                        <!-- Card Footer: Instructor Row + Price & CTA Button -->
                        <div class="pt-3 border-t border-slate-100 space-y-3 px-0.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 font-bold text-[11px] flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($course->instructor->name, 0, 2)) }}
                                    </div>
                                    <div class="truncate">
                                        <div class="text-xs font-bold text-slate-800 truncate">{{ $course->instructor->name }}</div>
                                        <div class="text-[10px] text-slate-400">Instruktur Ahli</div>
                                    </div>
                                </div>

                                <button type="button"
                                        data-open-dialog="modal-course-{{ $course->id }}"
                                        class="w-7 h-7 rounded-full bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white flex items-center justify-center shrink-0 transition"
                                        title="Lihat Daftar Modul">
                                    <svg class="w-3.5 h-3.5 translate-x-[0.5px]" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                        <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center justify-between gap-2 pt-1">
                                <div class="text-sm font-mono font-extrabold text-slate-900 tabular-nums">
                                    Rp {{ number_format($course->price, 0, ',', '.') }}
                                </div>

                                @if($userEnrolled)
                                    <a href="{{ route('catalog.show', $course->slug) }}"
                                       class="min-h-[38px] px-3.5 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 hover:bg-emerald-100 text-xs font-bold inline-flex items-center justify-center gap-1 transition">
                                        <span>Sudah Terdaftar</span>
                                    </a>
                                @else
                                    <a href="{{ route('checkout.confirm', $course->id) }}"
                                       class="min-h-[38px] px-4 py-1.5 rounded-full bg-[#090D16] hover:bg-emerald-600 text-white text-xs font-bold inline-flex items-center justify-center transition shadow-2xs">
                                        Beli Kursus
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Native HTML <dialog closedby="any"> Syllabus Modal Preview -->
                        <dialog id="modal-course-{{ $course->id }}"
                                closedby="any"
                                aria-labelledby="dialog-title-{{ $course->id }}"
                                class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white text-slate-900 p-0 shadow-xl m-auto">
                            <div class="p-6 border-b border-slate-200 bg-[#090D16] text-white flex items-start justify-between gap-4">
                                <div>
                                    <span class="text-xs font-mono uppercase tracking-wider text-emerald-400 block mb-1">
                                        {{ $catName }} &bull; Pratinjau Silabus
                                    </span>
                                    <h4 id="dialog-title-{{ $course->id }}" class="text-lg font-bold text-white leading-snug">
                                        {{ $course->title }}
                                    </h4>
                                    <p class="text-xs text-slate-400 mt-1">
                                        Instruktur: {{ $course->instructor->name }} &bull; {{ $course->lessons->count() }} Modul Pembelajaran
                                    </p>
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

                            <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
                                <p class="text-sm text-slate-600 leading-relaxed">
                                    {{ $course->description }}
                                </p>

                                <div class="border-t border-slate-200 pt-4">
                                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">
                                        Materi yang Akan Anda Pelajari ({{ $course->lessons->count() }} Modul)
                                    </h5>
                                    <div class="space-y-2">
                                        @forelse($course->lessons as $lesson)
                                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3 text-xs">
                                                <div class="flex items-center gap-2.5">
                                                    <span class="w-6 h-6 rounded-lg bg-slate-200 text-slate-700 font-mono font-bold flex items-center justify-center tabular-nums">
                                                        {{ $lesson->lesson_order }}
                                                    </span>
                                                    <div>
                                                        <div class="font-semibold text-slate-900">{{ $lesson->title }}</div>
                                                        <div class="text-slate-500 capitalize text-[11px]">
                                                            {{ $lesson->content_type === 'video' ? 'Video HD' : 'Panduan PDF' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="font-mono text-slate-600 tabular-nums shrink-0">
                                                    {{ $lesson->duration_seconds > 0 ? gmdate('i:s', $lesson->duration_seconds) . ' mnt' : 'E-Book' }}
                                                </span>
                                            </div>
                                        @empty
                                            <p class="text-xs text-slate-500">Daftar modul sedang disiapkan oleh instruktur.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <div>
                                    <span class="text-xs text-slate-500 block">Investasi Sekali Bayar</span>
                                    <span class="text-lg font-mono font-extrabold text-slate-900 tabular-nums">
                                        Rp {{ number_format($course->price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <a href="{{ route('catalog.show', $course->slug) }}"
                                       class="min-h-[44px] flex-1 sm:flex-initial inline-flex items-center justify-center px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-xs font-semibold text-slate-700 transition">
                                        Lihat Halaman Kelas
                                    </a>
                                    @if(! $userEnrolled)
                                        <a href="{{ route('checkout.confirm', $course->id) }}"
                                           class="min-h-[44px] flex-1 sm:flex-initial inline-flex items-center justify-center px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-xs font-bold text-white transition">
                                            Beli Kursus Sekarang
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </dialog>
                    </article>
                @empty
                    <div class="col-span-full text-center py-14 px-6 bg-white rounded-2xl border border-slate-200">
                        <h3 class="text-base font-bold text-slate-900">Belum ada kursus yang sesuai pencarian Anda</h3>
                        <p class="text-sm text-slate-500 mt-1 mb-4">Coba kata kunci lain atau pilih kategori kursus yang berbeda.</p>
                        <a href="{{ route('catalog.index') }}"
                           class="min-h-[44px] inline-flex items-center px-5 py-2 rounded-xl bg-[#090D16] hover:bg-slate-800 text-white text-xs font-semibold transition">
                            Lihat Semua Kursus
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Dynamic Empty State for Category / Wishlist / Enrolled Filter -->
            <div id="client-filter-empty" hidden class="text-center py-12 px-6 bg-white rounded-2xl border border-slate-200 mt-4">
                <h3 id="client-empty-title" class="text-base font-bold text-slate-900">Tidak ada kursus pada filter ini</h3>
                <p id="client-empty-desc" class="text-xs text-slate-500 mt-1 mb-4">Klik tombol di bawah untuk menampilkan kembali seluruh pilihan kursus di katalog.</p>
                <div class="flex items-center justify-center gap-2">
                    <button type="button"
                            id="reset-client-filter-btn"
                            class="min-h-[40px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                        Tampilkan Semua Kelas
                    </button>
                    @guest
                        <a href="{{ route('login') }}"
                           class="min-h-[40px] inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                            Masuk ke Akun
                        </a>
                    @endguest
                </div>
            </div>

            @if($courses->hasPages())
                <div class="pt-4">
                    {{ $courses->links() }}
                </div>
            @endif
        </section>
    </div>

    <!-- RIGHT SIDEBAR WIDGET COLUMN (4 of 12 Columns on XL) -->
    <aside aria-label="Ringkasan dan Statistik Belajar" class="xl:col-span-4 space-y-6">

        <!-- 1. 2x2 Circular Ring Progress Stat Cards -->
        <div class="grid grid-cols-2 gap-3.5">
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 flex items-center gap-3 shadow-2xs">
                <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                    <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                        <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="text-emerald-600" stroke-dasharray="{{ min(100, max(20, (int) round(($enrolledCount / max(1, $courses->total())) * 100))) }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <span class="absolute text-[11px] font-mono font-extrabold text-slate-900 tabular-nums">
                        {{ $enrolledCount }}/{{ $courses->total() }}
                    </span>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 leading-tight">Kelas Diikuti</div>
                    <div class="text-[11px] text-slate-500 mt-0.5 truncate">Koleksi belajar</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 flex items-center gap-3 shadow-2xs">
                <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                    <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                        <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="text-sky-500" stroke-dasharray="75, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <span class="absolute text-[11px] font-mono font-extrabold text-slate-900 tabular-nums">
                        {{ $totalModules }}
                    </span>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 leading-tight">Modul Materi</div>
                    <div class="text-[11px] text-slate-500 mt-0.5 truncate">Video &amp; E-Book</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 flex items-center gap-3 shadow-2xs">
                <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                    <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                        <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="text-emerald-500" stroke-dasharray="68, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <span class="absolute text-[11px] font-mono font-extrabold text-slate-900 tabular-nums">
                        {{ $totalHours }}j
                    </span>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 leading-tight">Durasi Kelas</div>
                    <div class="text-[11px] text-slate-500 mt-0.5 truncate">Kurikulum padat</div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/90 p-4 flex items-center gap-3 shadow-2xs">
                <div class="relative w-12 h-12 shrink-0 flex items-center justify-center">
                    <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                        <path class="text-slate-100" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        <path class="text-amber-500" stroke-dasharray="85, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                    </svg>
                    <span class="absolute text-[11px] font-mono font-extrabold text-slate-900 tabular-nums">
                        5 Hr
                    </span>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-bold text-slate-900 leading-tight">Target Rutin</div>
                    <div class="text-[11px] text-slate-500 mt-0.5 truncate">Konsistensi</div>
                </div>
            </div>
        </div>

        <!-- 2. "Statistik Belajar Anda" Chart Widget -->
        <section aria-labelledby="stats-widget-heading" class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
            <div class="flex items-center justify-between gap-2 mb-4">
                <h3 id="stats-widget-heading" class="text-base font-extrabold text-slate-900">Statistik Belajar Anda</h3>
                <span class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">
                    Mingguan
                </span>
            </div>

            <div class="flex items-center gap-4 border-b border-slate-100 pb-3 mb-4 text-xs font-bold">
                <button type="button"
                        data-stat-tab="hours"
                        class="stat-tab-btn text-slate-900 border-b-2 border-emerald-600 pb-1 transition">
                    Jam Belajar
                </button>
                <button type="button"
                        data-stat-tab="wallet"
                        class="stat-tab-btn text-slate-400 hover:text-slate-700 pb-1 transition">
                    Dompet &amp; Saldo
                </button>
            </div>

            <div id="stat-panel-hours">
                <div class="relative pt-2 pb-1">
                    <svg class="w-full h-44 overflow-visible" viewBox="0 0 320 155" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Grafik aktivitas jam belajar mingguan">
                        <line x1="28" y1="20" x2="305" y2="20" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="28" y1="48" x2="305" y2="48" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="28" y1="76" x2="305" y2="76" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="28" y1="104" x2="305" y2="104" stroke="#F1F5F9" stroke-width="1"/>
                        <line x1="28" y1="130" x2="305" y2="130" stroke="#E2E8F0" stroke-width="1"/>

                        <text x="8" y="24" class="text-[10px] fill-slate-400 font-mono">4j</text>
                        <text x="8" y="52" class="text-[10px] fill-slate-400 font-mono">3j</text>
                        <text x="8" y="80" class="text-[10px] fill-slate-400 font-mono">2j</text>
                        <text x="8" y="108" class="text-[10px] fill-slate-400 font-mono">1j</text>
                        <text x="8" y="133" class="text-[10px] fill-slate-400 font-mono">0</text>

                        <path d="M40 116 C 65 110, 72 90, 84 88 C 102 85, 114 60, 128 60 C 142 60, 156 102, 172 102 C 188 102, 200 22, 216 22 C 232 22, 244 50, 260 50 C 276 50, 288 76, 300 76 L 300 130 L 40 130 Z"
                              fill="#059669" fill-opacity="0.08"/>

                        <path d="M40 116 C 65 110, 72 90, 84 88 C 102 85, 114 60, 128 60 C 142 60, 156 102, 172 102 C 188 102, 200 22, 216 22 C 232 22, 244 50, 260 50 C 276 50, 288 76, 300 76"
                              stroke="#059669" stroke-width="2.5" stroke-linecap="round"/>

                        <circle cx="40" cy="116" r="4" fill="#FFFFFF" stroke="#059669" stroke-width="2.5"/>
                        <rect x="26" y="92" width="28" height="16" rx="4" fill="#F1F5F9"/>
                        <text x="40" y="103" text-anchor="middle" class="text-[9px] fill-slate-700 font-mono font-bold">0,5j</text>

                        <circle cx="84" cy="88" r="4" fill="#FFFFFF" stroke="#059669" stroke-width="2.5"/>
                        <rect x="70" y="64" width="28" height="16" rx="4" fill="#F1F5F9"/>
                        <text x="84" y="75" text-anchor="middle" class="text-[9px] fill-slate-700 font-mono font-bold">1,5j</text>

                        <circle cx="128" cy="60" r="4" fill="#FFFFFF" stroke="#059669" stroke-width="2.5"/>
                        <rect x="114" y="36" width="28" height="16" rx="4" fill="#F1F5F9"/>
                        <text x="128" y="47" text-anchor="middle" class="text-[9px] fill-slate-700 font-mono font-bold">2,5j</text>

                        <circle cx="172" cy="102" r="4" fill="#FFFFFF" stroke="#059669" stroke-width="2.5"/>
                        <rect x="160" y="78" width="24" height="16" rx="4" fill="#F1F5F9"/>
                        <text x="172" y="89" text-anchor="middle" class="text-[9px] fill-slate-700 font-mono font-bold">1j</text>

                        <circle cx="216" cy="22" r="4.5" fill="#059669" stroke="#ECFDF5" stroke-width="2"/>
                        <rect x="202" y="0" width="28" height="16" rx="4" fill="#090D16"/>
                        <text x="216" y="11" text-anchor="middle" class="text-[9px] fill-emerald-400 font-mono font-bold">4,0j</text>

                        <circle cx="260" cy="50" r="4" fill="#FFFFFF" stroke="#059669" stroke-width="2.5"/>
                        <rect x="248" y="26" width="24" height="16" rx="4" fill="#F1F5F9"/>
                        <text x="260" y="37" text-anchor="middle" class="text-[9px] fill-slate-700 font-mono font-bold">3j</text>

                        <circle cx="300" cy="76" r="4" fill="#FFFFFF" stroke="#059669" stroke-width="2.5"/>
                        <rect x="288" y="52" width="24" height="16" rx="4" fill="#F1F5F9"/>
                        <text x="300" y="63" text-anchor="middle" class="text-[9px] fill-slate-700 font-mono font-bold">2j</text>

                        <text x="40" y="148" text-anchor="middle" class="text-[10px] fill-slate-500 font-medium">Sen</text>
                        <text x="84" y="148" text-anchor="middle" class="text-[10px] fill-slate-500 font-medium">Sel</text>
                        <text x="128" y="148" text-anchor="middle" class="text-[10px] fill-slate-500 font-medium">Rab</text>
                        <text x="172" y="148" text-anchor="middle" class="text-[10px] fill-slate-500 font-medium">Kam</text>
                        <text x="216" y="148" text-anchor="middle" class="text-[10px] fill-slate-900 font-bold">Jum</text>
                        <text x="260" y="148" text-anchor="middle" class="text-[10px] fill-slate-500 font-medium">Sab</text>
                        <text x="300" y="148" text-anchor="middle" class="text-[10px] fill-slate-500 font-medium">Min</text>
                    </svg>
                </div>
            </div>

            <div id="stat-panel-wallet" hidden class="space-y-3 pt-1">
                <div class="p-4 rounded-xl bg-[#090D16] text-white">
                    <span class="text-[11px] font-mono uppercase tracking-wider text-slate-400 block">Saldo Dompet Saat Ini</span>
                    <div class="text-xl font-mono font-extrabold text-emerald-400 tabular-nums mt-0.5">
                        Rp {{ number_format(auth()->check() ? auth()->user()->balance : 0, 0, ',', '.') }}
                    </div>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Gunakan Dompet Belajar untuk membeli kursus secara instan tanpa biaya tambahan.
                </p>
                <div class="flex items-center gap-2">
                    <a href="{{ auth()->check() ? route('wallet.index') : route('login') }}"
                       class="min-h-[38px] flex-1 inline-flex items-center justify-center px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition">
                        Top-Up Saldo
                    </a>
                    @auth
                        <a href="{{ route('transactions.index') }}"
                           class="min-h-[38px] flex-1 inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition">
                            Riwayat Mutasi
                        </a>
                    @endauth
                </div>
            </div>
        </section>

        <!-- 3. Bottom Commercial Promo / Wallet CTA Card -->
        <section class="relative overflow-hidden rounded-2xl bg-emerald-50/90 border border-emerald-200/80 p-5">
            <div class="relative z-10 max-w-[210px] space-y-2">
                <h3 class="text-base font-extrabold text-slate-900 leading-snug">
                    Belajar Tanpa Batas!
                </h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Isi saldo Dompet Belajar Anda dan dapatkan akses instan ke seluruh materi video, modul PDF, serta sertifikat resmi.
                </p>
                <div class="pt-1.5">
                    <a href="{{ auth()->check() ? route('wallet.index') : route('register') }}"
                       class="min-h-[40px] inline-flex items-center justify-center px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition shadow-2xs">
                        {{ auth()->check() ? 'Buka Dompet Belajar' : 'Daftar Akun Gratis' }}
                    </a>
                </div>
            </div>

            <svg class="w-24 h-24 absolute -right-2 -bottom-2 text-emerald-600/20 pointer-events-none" viewBox="0 0 100 100" fill="currentColor" aria-hidden="true">
                <path d="M68 22L28 40H16C12.7 40 10 42.7 10 46V54C10 57.3 12.7 60 16 60H22L28 78H38L33 60H40L68 78V22Z"/>
                <circle cx="76" cy="50" r="10"/>
            </svg>
        </section>

    </aside>
</div>

<!-- Client-Side Interactive Filtering, Wishlist Toggle & Sidebar Sync -->
<script>
    (function () {
        'use strict';

        const searchInput = document.getElementById('catalog-search-input');
        const sortSelect = document.getElementById('catalog-sort-select');
        const sortForm = document.getElementById('catalog-sort-form');
        const catalogSection = document.getElementById('katalog-kursus');
        const headingEl = document.getElementById('popular-courses-heading');
        const cards = Array.from(document.querySelectorAll('[data-course-card]'));
        const categoryPills = Array.from(document.querySelectorAll('[data-category-filter]'));
        const counterBadge = document.getElementById('visible-course-counter');
        const emptyState = document.getElementById('client-filter-empty');
        const emptyTitle = document.getElementById('client-empty-title');
        const emptyDesc = document.getElementById('client-empty-desc');
        const resetFilterBtn = document.getElementById('reset-client-filter-btn');
        const showAllBtn = document.getElementById('show-all-courses-btn');
        const wishlistButtons = Array.from(document.querySelectorAll('[data-wishlist-toggle]'));
        const sidebarNavLinks = Array.from(document.querySelectorAll('[data-sidebar-nav]'));

        let currentCategory = '{{ strtolower((string) ($activeCategory ?: "all")) }}';
        let currentMode = catalogSection ? (catalogSection.getAttribute('data-initial-filter') || 'all') : 'all';

        if (sortSelect && sortForm) {
            sortSelect.addEventListener('change', () => {
                sortForm.submit();
            });
        }

        function renderWishlistButtonStates() {
            const savedList = typeof window.getFortiWishlist === 'function' ? window.getFortiWishlist() : [];
            wishlistButtons.forEach((btn) => {
                const id = String(btn.getAttribute('data-wishlist-toggle'));
                const isSaved = savedList.includes(id);
                const svg = btn.querySelector('svg');
                if (isSaved) {
                    btn.className = 'wishlist-btn w-7 h-7 rounded-full bg-rose-500 text-white flex items-center justify-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400';
                    if (svg) svg.setAttribute('fill', 'currentColor');
                } else {
                    btn.className = 'wishlist-btn w-7 h-7 rounded-full bg-white/15 hover:bg-white/30 text-white flex items-center justify-center transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400';
                    if (svg) svg.setAttribute('fill', 'none');
                }
            });
            if (typeof window.updateSidebarWishlistBadge === 'function') {
                window.updateSidebarWishlistBadge();
            }
        }

        wishlistButtons.forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const courseId = String(btn.getAttribute('data-wishlist-toggle'));
                const list = typeof window.getFortiWishlist === 'function' ? window.getFortiWishlist() : [];
                const idx = list.indexOf(courseId);
                if (idx >= 0) {
                    list.splice(idx, 1);
                } else {
                    list.push(courseId);
                }
                try {
                    localStorage.setItem('fortilearn_wishlist', JSON.stringify(list));
                } catch (err) {}
                renderWishlistButtonStates();
                if (currentMode === 'wishlist') {
                    applyFilters();
                }
            });
        });

        function applyFilters() {
            const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
            const savedList = typeof window.getFortiWishlist === 'function' ? window.getFortiWishlist() : [];
            let visibleCount = 0;

            cards.forEach((card) => {
                const courseId = String(card.getAttribute('data-course-id') || '');
                const isEnrolled = card.getAttribute('data-course-enrolled') === '1';
                const cardCat = (card.getAttribute('data-course-category') || '').toLowerCase();
                const haystack = (card.getAttribute('data-search-haystack') || '').toLowerCase();

                const matchesCategory = currentCategory === 'all' || cardCat === currentCategory;
                const matchesQuery = query === '' || haystack.includes(query);
                const matchesMode =
                    currentMode === 'enrolled' ? isEnrolled :
                    currentMode === 'wishlist' ? savedList.includes(courseId) :
                    true;

                const isVisible = matchesCategory && matchesQuery && matchesMode;
                card.hidden = !isVisible;
                if (isVisible) visibleCount++;
            });

            if (headingEl) {
                if (currentMode === 'wishlist') {
                    headingEl.textContent = 'Daftar Favorit & Wishlist Saya';
                } else if (currentMode === 'enrolled') {
                    headingEl.textContent = 'Daftar Kursus Saya';
                } else {
                    headingEl.textContent = 'Katalog Kursus Pilihan';
                }
            }

            if (showAllBtn) {
                showAllBtn.hidden = (currentMode === 'all' && currentCategory === 'all' && query === '');
            }

            if (counterBadge) {
                counterBadge.textContent = visibleCount + ' kelas';
            }

            if (emptyState) {
                emptyState.hidden = visibleCount > 0;
                if (visibleCount === 0 && emptyTitle && emptyDesc) {
                    if (currentMode === 'wishlist') {
                        emptyTitle.textContent = 'Belum ada kursus di daftar Favorit / Wishlist Anda';
                        emptyDesc.textContent = 'Klik ikon Hati pada pojok kanan atas kartu kursus mana pun untuk menyimpannya ke daftar ini.';
                    } else if (currentMode === 'enrolled') {
                        emptyTitle.textContent = 'Anda belum memiliki kursus aktif';
                        emptyDesc.textContent = 'Silakan masuk ke akun Anda atau pilih kelas dari katalog untuk mulai belajar.';
                    } else {
                        emptyTitle.textContent = 'Tidak ada kursus pada kategori atau kata kunci ini';
                        emptyDesc.textContent = 'Klik tombol di bawah untuk menampilkan kembali seluruh pilihan kursus.';
                    }
                }
            }
        }

        function setCategoryPillActive(catValue) {
            currentCategory = catValue;
            categoryPills.forEach((p) => {
                const isSelected = (p.getAttribute('data-category-filter') || 'all').toLowerCase() === currentCategory;
                p.className = isSelected
                    ? 'category-pill min-h-[36px] px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition border bg-[#090D16] text-white border-[#090D16]'
                    : 'category-pill min-h-[36px] px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition border bg-slate-50 text-slate-600 border-slate-200/90 hover:border-slate-300 hover:text-slate-900';
            });
        }

        categoryPills.forEach((pill) => {
            pill.addEventListener('click', () => {
                const selectedCat = (pill.getAttribute('data-category-filter') || 'all').toLowerCase();
                setCategoryPillActive(selectedCat);
                applyFilters();
            });
        });

        function resetAllFilters() {
            currentMode = 'all';
            if (searchInput) searchInput.value = '';
            setCategoryPillActive('all');
            applyFilters();
        }

        if (resetFilterBtn) {
            resetFilterBtn.addEventListener('click', resetAllFilters);
        }
        if (showAllBtn) {
            showAllBtn.addEventListener('click', resetAllFilters);
        }

        if (searchInput && cards.length > 0) {
            searchInput.addEventListener('input', applyFilters);
        }

        // Statistics Widget Tab Switcher
        const statTabButtons = document.querySelectorAll('[data-stat-tab]');
        const hoursPanel = document.getElementById('stat-panel-hours');
        const walletPanel = document.getElementById('stat-panel-wallet');

        statTabButtons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const target = btn.getAttribute('data-stat-tab');
                statTabButtons.forEach((b) => {
                    b.className = b === btn
                        ? 'stat-tab-btn text-slate-900 border-b-2 border-emerald-600 pb-1 transition'
                        : 'stat-tab-btn text-slate-400 hover:text-slate-700 pb-1 transition';
                });
                if (hoursPanel && walletPanel) {
                    hoursPanel.hidden = target !== 'hours';
                    walletPanel.hidden = target !== 'wallet';
                }
            });
        });

        renderWishlistButtonStates();
        applyFilters();
    })();
</script>
@endsection
