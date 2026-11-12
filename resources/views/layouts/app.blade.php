<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Eksplorasi Kursus Online') | FortiLearn E-Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --surface-bg: #EEF2F6;
            --surface-shell: #F8FAFC;
            --surface-card: #FFFFFF;
            --surface-obsidian: #090D16;
            --accent-emerald: #059669;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--surface-bg);
            color: #0F172A;
        }

        .font-mono, .tabular-nums {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
            font-variant-numeric: tabular-nums;
        }

        a, button, summary, select, input[type="range"], input[type="checkbox"], [role="button"] {
            cursor: pointer;
        }
        button:disabled, input:disabled, [aria-disabled="true"] {
            cursor: not-allowed;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Modern Web Guidance: animate-to-from-top-layer for <dialog> & [popover] */
        dialog,
        [popover] {
            opacity: 0;
            transform: translateY(10px) scale(0.98);
            transition:
                opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                display 0.22s cubic-bezier(0.16, 1, 0.3, 1),
                overlay 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            transition-behavior: allow-discrete;
        }

        :is(dialog[open], [popover]:popover-open):where(:not([data-closing])) {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        @starting-style {
            :is(dialog[open], [popover]:popover-open):where(:not([data-closing])) {
                opacity: 0;
                transform: translateY(10px) scale(0.98);
            }
        }

        dialog::backdrop,
        [popover]::backdrop {
            background-color: rgb(9 13 22 / 0%);
            backdrop-filter: blur(0px);
            transition:
                background-color 0.22s ease,
                backdrop-filter 0.22s ease,
                display 0.22s ease,
                overlay 0.22s ease;
            transition-behavior: allow-discrete;
        }

        dialog[open]:not([data-closing])::backdrop,
        [popover]:popover-open:not([data-closing])::backdrop {
            background-color: rgb(9 13 22 / 60%);
            backdrop-filter: blur(3px);
        }

        @starting-style {
            dialog[open]:not([data-closing])::backdrop,
            [popover]:popover-open:not([data-closing])::backdrop {
                background-color: rgb(9 13 22 / 0%);
                backdrop-filter: blur(0px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            dialog,
            [popover],
            dialog::backdrop,
            [popover]::backdrop {
                transition: none !important;
                transform: none !important;
            }
        }

        details > summary {
            list-style: none;
        }
        details > summary::-webkit-details-marker {
            display: none;
        }
    </style>
</head>
<body class="min-h-dvh flex flex-col antialiased selection:bg-emerald-600 selection:text-white">
@php
    $sidebarEnrolledCount = auth()->check() ? auth()->user()->enrollments()->count() : 0;
    $isExploreActive = request()->routeIs('catalog.index') || request()->routeIs('catalog.show');
    $isCategoriesActive = request()->routeIs('catalog.categories');
    $isMyCoursesActive = request()->routeIs('catalog.my-courses');
    $isWishlistActive = request()->routeIs('catalog.wishlist');
    $isWalletActive = request()->routeIs('wallet.*') || request()->routeIs('checkout.*');
    $isTransactionsActive = request()->routeIs('transactions.*');
    $isHelpActive = request()->routeIs('catalog.help');
@endphp

    <!-- App Dashboard Workspace Container -->
    <div class="flex-grow flex flex-col lg:flex-row max-w-[1440px] w-full mx-auto my-0 lg:my-4 lg:px-4 gap-0 lg:gap-5">

        <!-- Left Vertical Navigation Sidebar Rail (7 Dedicated Page Routes) -->
        <aside aria-label="Menu Utama Aplikasi"
               class="bg-white lg:rounded-2xl border-b lg:border border-slate-200/90 lg:w-20 xl:w-64 shrink-0 flex flex-col justify-between px-3 py-3 lg:py-5 lg:px-3.5 sticky top-0 lg:top-4 lg:h-[calc(100dvh-2rem)] z-30 shadow-2xs overflow-y-auto no-scrollbar">

            <!-- Top Section: Brand + 3 Menu Groups -->
            <div class="flex items-center lg:flex-col lg:items-stretch justify-between gap-4">

                <!-- Brand Identity -->
                <div class="flex items-center justify-between xl:px-2 lg:pb-3 lg:border-b lg:border-slate-100">
                    <a href="{{ route('catalog.index') }}"
                       class="flex items-center gap-3 min-h-[44px] rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        <div class="w-10 h-10 rounded-xl bg-[#090D16] flex items-center justify-center text-emerald-400 border border-slate-800 shrink-0 shadow-xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="hidden xl:block">
                            <span class="text-lg font-extrabold tracking-tight text-slate-900 leading-none block">Forti<span class="text-emerald-600">Learn</span></span>
                            <span class="text-[11px] font-medium text-slate-400 block mt-0.5">Academy Hub</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Groups (Each Item is a Dedicated Page Route) -->
                <nav aria-label="Menu Samping" class="flex lg:flex-col items-center lg:items-stretch gap-1 lg:space-y-4 overflow-x-auto no-scrollbar">

                    <!-- GROUP 1: BELAJAR & KATALOG -->
                    <div class="flex lg:flex-col items-center lg:items-stretch gap-1">
                        <span class="hidden xl:block px-3 pb-1 text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400">
                            Belajar &amp; Katalog
                        </span>

                        <!-- 1. Eksplorasi Kelas -->
                        <a href="{{ route('catalog.index') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-between gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isExploreActive ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Eksplorasi Kelas">
                            @if($isExploreActive)
                                <span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span class="hidden xl:inline">Eksplorasi Kelas</span>
                            </span>
                        </a>

                        <!-- 2. Kategori Bidang -->
                        <a href="{{ route('catalog.categories') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-between gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isCategoriesActive ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Kategori & Jalur Belajar">
                            @if($isCategoriesActive)
                                <span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="hidden xl:inline">Kategori Bidang</span>
                            </span>
                            <span class="hidden xl:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $isCategoriesActive ? 'bg-emerald-200/70 text-emerald-900' : 'bg-slate-100 text-slate-600' }} tabular-nums">
                                6
                            </span>
                        </a>

                        <!-- 3. Kursus Saya -->
                        <a href="{{ route('catalog.my-courses') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-between gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isMyCoursesActive ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Kursus Saya">
                            @if($isMyCoursesActive)
                                <span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                <span class="hidden xl:inline">Kursus Saya</span>
                            </span>
                            <span class="hidden xl:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold {{ $sidebarEnrolledCount > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }} tabular-nums">
                                {{ $sidebarEnrolledCount }}
                            </span>
                        </a>

                        <!-- 4. Favorit & Wishlist -->
                        <a href="{{ route('catalog.wishlist') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-between gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isWishlistActive ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Favorit & Wishlist">
                            @if($isWishlistActive)
                                <span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span class="hidden xl:inline">Favorit &amp; Wishlist</span>
                            </span>
                            <span id="sidebar-wishlist-count"
                                  class="hidden xl:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-rose-50 text-rose-600 tabular-nums">
                                0
                            </span>
                        </a>
                    </div>

                    @if(auth()->check() && auth()->user()->isInstructor())
                    <!-- GROUP: STUDIO INSTRUKTUR (Jobdesk 2) -->
                    <div class="flex lg:flex-col items-center lg:items-stretch gap-1 lg:pt-2 lg:border-t lg:border-slate-100">
                        <span class="hidden xl:block px-3 pb-1 text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400">
                            Instruktur
                        </span>
                        <a href="{{ route('instructor.courses.index') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-start gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ request()->routeIs('instructor.*') ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Studio Instruktur">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span class="hidden xl:inline">Studio Instruktur</span>
                        </a>
                    </div>
                    @endif

                    <!-- GROUP 2: DOMPET & PEMBELIAN -->
                    <div class="flex lg:flex-col items-center lg:items-stretch gap-1 lg:pt-2 lg:border-t lg:border-slate-100">
                        <span class="hidden xl:block px-3 pb-1 text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400">
                            Dompet &amp; Pembelian
                        </span>

                        <!-- 5. Dompet Belajar -->
                        <a href="{{ route('wallet.index') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-between gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isWalletActive ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Dompet Belajar & Top-Up">
                            @if($isWalletActive)
                                <span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="hidden xl:inline">Dompet Belajar</span>
                            </span>
                            <span class="hidden xl:inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                Top-Up
                            </span>
                        </a>

                        <!-- 6. Riwayat & Kwitansi -->
                        <a href="{{ route('transactions.index') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-between gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isTransactionsActive ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Riwayat Pembelian & Kwitansi">
                            @if($isTransactionsActive)
                                <span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="hidden xl:inline">Riwayat &amp; Kwitansi</span>
                            </span>
                        </a>
                    </div>

                    <!-- GROUP 3: PUSAT LAYANAN -->
                    <div class="flex lg:flex-col items-center lg:items-stretch gap-1 lg:pt-2 lg:border-t lg:border-slate-100">
                        <span class="hidden xl:block px-3 pb-1 text-[10px] font-mono font-bold uppercase tracking-widest text-slate-400">
                            Pusat Layanan
                        </span>

                        <!-- 7. Panduan & Bantuan -->
                        <a href="{{ route('catalog.help') }}"
                           class="relative group min-h-[42px] flex items-center justify-center xl:justify-between gap-3 px-3 py-2 rounded-xl text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $isHelpActive ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}"
                           title="Panduan Belajar & Pusat Bantuan">
                            @if($isHelpActive)
                                <span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-600 rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3">
                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="hidden xl:inline">Panduan &amp; Bantuan</span>
                            </span>
                        </a>
                    </div>
                </nav>
            </div>

            <!-- Bottom Section: Mini Wallet / Account Status Card + Auth Action -->
            <div class="hidden lg:flex flex-col gap-3 pt-4 border-t border-slate-100">
                @auth
                    <div class="hidden xl:block p-3.5 rounded-xl bg-[#090D16] text-white border border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-mono uppercase tracking-wider text-slate-400">Saldo Dompet</span>
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        </div>
                        <div class="text-sm font-mono font-extrabold text-emerald-400 tabular-nums">
                            Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }}
                        </div>
                        <a href="{{ route('wallet.index') }}"
                           class="w-full py-1.5 px-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-bold inline-flex items-center justify-center transition">
                            + Isi Saldo Cepat
                        </a>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="w-full">
                        @csrf
                        <button type="submit"
                                class="w-full min-h-[42px] flex items-center justify-center xl:justify-start gap-3 px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500"
                                title="Keluar dari Akun">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden xl:inline">Keluar Sesi</span>
                        </button>
                    </form>
                @else
                    <div class="hidden xl:block p-3.5 rounded-xl bg-slate-50 border border-slate-200/90 space-y-2">
                        <div class="text-xs font-bold text-slate-900">Mode Pengunjung</div>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Masuk ke akun untuk menyimpan progres kelas dan menggunakan Dompet Belajar.
                        </p>
                        <a href="{{ route('login') }}"
                           class="w-full py-2 px-3 rounded-lg bg-[#090D16] hover:bg-emerald-600 text-white text-xs font-bold inline-flex items-center justify-center transition">
                            Masuk Akun
                        </a>
                    </div>

                    <a href="{{ route('login') }}"
                       class="xl:hidden w-full min-h-[42px] flex items-center justify-center p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition"
                       title="Masuk Akun">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </a>
                @endauth
            </div>
        </aside>

        <!-- Main Workspace Shell -->
        <div class="flex-grow flex flex-col bg-[#F8FAFC] lg:rounded-2xl border-x lg:border border-slate-200/90 overflow-hidden shadow-2xs">

            <!-- Top Search, Filter & Profile Header Bar -->
            <header class="bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 py-3.5 sticky top-0 z-20">
                <div class="flex items-center justify-between gap-4">

                    <!-- Global <search> Bar -->
                    <search aria-label="Pencarian Global Kursus" class="flex-grow max-w-xl">
                        <form id="global-search-form" action="{{ route('catalog.index') }}" method="GET" class="flex items-center gap-2">
                            @if(request()->query('category'))
                                <input type="hidden" name="category" value="{{ request()->query('category') }}">
                            @endif
                            <div class="relative flex-grow">
                                <label for="catalog-search-input" class="sr-only">Cari kursus, topik, atau instruktur</label>
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="search"
                                       name="q"
                                       id="catalog-search-input"
                                       value="{{ request()->query('q') }}"
                                       autocomplete="off"
                                       placeholder="Cari kursus, keahlian, atau instruktur (tekan '/')..."
                                       class="w-full min-h-[44px] pl-10 pr-12 py-2 rounded-xl bg-slate-100/80 hover:bg-slate-100 focus:bg-white border border-slate-200/90 text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 transition text-sm">
                                <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 text-[11px] font-mono text-slate-400 bg-white border border-slate-200 rounded-md absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                                    /
                                </kbd>
                            </div>

                            <button type="submit"
                                    class="min-h-[44px] min-w-[44px] px-3.5 rounded-xl bg-slate-100 hover:bg-emerald-600 text-slate-600 hover:text-white border border-slate-200/90 transition inline-flex items-center justify-center gap-1.5 text-xs font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
                                    title="Terapkan Filter Pencarian">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                                </svg>
                                <span class="hidden md:inline">Cari</span>
                            </button>
                        </form>
                    </search>

                    <!-- Right Actions: Wallet Chip & User Profile -->
                    <div class="flex items-center gap-3 shrink-0">
                        @auth
                            <a href="{{ route('wallet.index') }}"
                               class="min-h-[44px] inline-flex items-center gap-2.5 px-3.5 py-1.5 bg-[#090D16] hover:bg-slate-800 text-white rounded-xl border border-slate-800 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600"
                               title="Dompet Belajar Anda - Klik untuk Top-Up">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <div class="text-left">
                                    <span class="text-[10px] uppercase tracking-wider text-slate-400 block leading-none">Saldo Belajar</span>
                                    <span class="text-xs sm:text-sm font-mono font-bold text-emerald-400 tabular-nums leading-tight">
                                        Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }}
                                    </span>
                                </div>
                                <span class="hidden sm:inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-500/20 text-emerald-300 text-xs font-bold">+</span>
                            </a>

                            <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200">
                                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <div class="text-xs font-bold text-slate-900 leading-tight">{{ auth()->user()->name }}</div>
                                    <div class="text-[11px] text-slate-500 capitalize">{{ auth()->user()->role === 'student' ? 'Member Pembelajar' : auth()->user()->role }}</div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}"
                               class="min-h-[44px] inline-flex items-center px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}"
                               class="min-h-[44px] inline-flex items-center px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-xl transition shadow-2xs">
                                Daftar Gratis
                            </a>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Flash Notifications -->
            <div class="px-4 sm:px-6 lg:px-8 pt-4 empty:hidden">
                @if(session('success'))
                    <div role="status" class="mb-2 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div role="alert" class="mb-2 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('info'))
                    <div role="status" class="mb-2 p-4 rounded-xl bg-slate-100 border border-slate-300 text-slate-800 flex items-start gap-3">
                        <svg class="w-5 h-5 text-slate-700 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm font-medium">{{ session('info') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div role="alert" class="mb-2 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-sm">
                            <div class="font-bold mb-1">Mohon periksa kembali input Anda:</div>
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Main Page View Content -->
            <main class="flex-grow px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
            </main>

            <!-- Clean Commercial Footer -->
            <footer class="bg-white border-t border-slate-200/80 px-4 sm:px-6 lg:px-8 py-5 mt-auto print:hidden">
                <div class="flex flex-col sm:flex-row justify-between items-center text-xs text-slate-500 gap-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-slate-700">FortiLearn E-Learning Marketplace</span>
                        <span>|</span>
                        <span>Platform Kursus Profesional &amp; Sertifikasi Digital</span>
                    </div>
                    <div class="text-slate-400">
                        &copy; {{ date('Y') }} FortiLearn Academy. Seluruh transaksi dilindungi secara otomatis.
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Global Top-Layer <dialog>, Light-Dismiss & Wishlist Sync Engine -->
    <script>
        (function () {
            'use strict';

            function canTransitionDisplay() {
                const probe = document.createElement('div');
                probe.style.transition = 'display 0.1s allow-discrete';
                return probe.style.transition.includes('allow-discrete');
            }

            const supportsDiscreteDisplay = canTransitionDisplay();

            window.closeTopLayer = function (el) {
                if (!el) return;
                if (supportsDiscreteDisplay) {
                    if (typeof el.close === 'function' && el.open) el.close();
                    else if (typeof el.hidePopover === 'function') el.hidePopover();
                    return;
                }
                el.setAttribute('data-closing', '');
                const onEnd = () => {
                    el.removeAttribute('data-closing');
                    if (typeof el.close === 'function' && el.open) el.close();
                    else if (typeof el.hidePopover === 'function') el.hidePopover();
                };
                el.addEventListener('transitionend', onEnd, { once: true });
                setTimeout(() => {
                    if (el.hasAttribute('data-closing')) onEnd();
                }, 260);
            };

            document.querySelectorAll('[data-open-dialog]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-open-dialog');
                    const dialog = document.getElementById(targetId);
                    if (dialog && typeof dialog.showModal === 'function') {
                        dialog.showModal();
                    }
                });
            });

            document.querySelectorAll('[data-close-dialog]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const dialog = btn.closest('dialog');
                    if (dialog) {
                        window.closeTopLayer(dialog);
                    }
                });
            });

            if (!('closedBy' in HTMLDialogElement.prototype)) {
                document.querySelectorAll('dialog[closedby="any"]').forEach((dialog) => {
                    dialog.addEventListener('click', (e) => {
                        if (e.target !== dialog) return;
                        const rect = dialog.getBoundingClientRect();
                        const clickedInDialog =
                            e.clientX >= rect.left &&
                            e.clientX <= rect.right &&
                            e.clientY >= rect.top &&
                            e.clientY <= rect.bottom;
                        if (!clickedInDialog) {
                            window.closeTopLayer(dialog);
                        }
                    });
                });
            }

            // Global Wishlist Counter Sync from localStorage
            window.getFortiWishlist = function () {
                try {
                    const raw = localStorage.getItem('fortilearn_wishlist');
                    const parsed = raw ? JSON.parse(raw) : [];
                    return Array.isArray(parsed) ? parsed.map(String) : [];
                } catch (err) {
                    return [];
                }
            };

            window.updateSidebarWishlistBadge = function () {
                const badge = document.getElementById('sidebar-wishlist-count');
                if (!badge) return;
                const list = window.getFortiWishlist();
                badge.textContent = String(list.length);
            };

            window.updateSidebarWishlistBadge();

            // Global keyboard shortcut '/' or 'Ctrl+K' to focus top search bar
            const searchInput = document.getElementById('catalog-search-input');
            document.addEventListener('keydown', (e) => {
                if (!searchInput) return;
                const activeTag = document.activeElement ? document.activeElement.tagName : '';
                const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeTag);
                if ((e.key === '/' && !isTyping) || ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k')) {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
            });
        })();
    </script>
</body>
</html>
