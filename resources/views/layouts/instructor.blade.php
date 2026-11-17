<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Studio Instruktur') | FortiLearn Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F1F5F9;
            color: #0F172A;
        }
        .font-mono, .tabular-nums {
            font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, monospace;
            font-variant-numeric: tabular-nums;
        }
        a, button, summary, select, [role="button"] { cursor: pointer; }
        button:disabled, input:disabled { cursor: not-allowed; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-dvh antialiased selection:bg-emerald-600 selection:text-white">
@php
    $user = auth()->user();
    $nav = [
        ['route' => 'instructor.dashboard', 'match' => 'instructor.dashboard', 'label' => 'Dashboard', 'icon' => 'M4 5a1 1 0 011-1h5a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h5a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zm10-4a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z'],
        ['route' => 'instructor.courses.index', 'match' => 'instructor.courses.*', 'label' => 'Kursus Saya', 'icon' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
        ['route' => 'instructor.sales.index', 'match' => 'instructor.sales.*', 'label' => 'Penjualan & Siswa', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['route' => 'instructor.reviews.index', 'match' => 'instructor.reviews.*', 'label' => 'Ulasan Siswa', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
    ];
@endphp

<div class="min-h-dvh flex flex-col lg:flex-row">

    <!-- Studio Sidebar (dark, visually distinct from the student workspace) -->
    <aside aria-label="Menu Studio Instruktur"
           class="bg-[#090D16] text-slate-300 lg:w-64 shrink-0 flex flex-col lg:sticky lg:top-0 lg:h-dvh z-30">
        <div class="flex items-center justify-between gap-3 px-4 py-4 lg:py-6 lg:flex-col lg:items-stretch lg:gap-6">
            <a href="{{ route('instructor.dashboard') }}"
               class="flex items-center gap-3 min-h-[44px] rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                <span class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </span>
                <span>
                    <span class="block text-lg font-extrabold tracking-tight text-white leading-none">Forti<span class="text-emerald-400">Learn</span></span>
                    <span class="block text-[10px] font-mono font-bold uppercase tracking-widest text-emerald-400/80 mt-1">Studio Instruktur</span>
                </span>
            </a>

            <a href="{{ route('instructor.courses.create') }}"
               class="hidden lg:inline-flex min-h-[44px] items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400">
                <span aria-hidden="true">+</span> Buat Kursus Baru
            </a>
        </div>

        <nav aria-label="Navigasi Studio" class="flex lg:flex-col gap-1 px-3 pb-3 lg:pb-0 lg:flex-grow overflow-x-auto no-scrollbar">
            <span class="hidden lg:block px-3 pb-1 pt-2 text-[10px] font-mono font-bold uppercase tracking-widest text-slate-500">Kelola</span>
            @foreach($nav as $item)
                @php $active = request()->routeIs($item['match']); @endphp
                <a href="{{ route($item['route']) }}"
                   @if($active) aria-current="page" @endif
                   class="relative min-h-[44px] shrink-0 flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 {{ $active ? 'bg-emerald-500/15 text-emerald-300' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    @if($active)<span class="hidden lg:block absolute left-0 top-2 bottom-2 w-1 bg-emerald-400 rounded-r-full"></span>@endif
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/></svg>
                    <span class="whitespace-nowrap">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="hidden lg:flex flex-col gap-2 p-4 border-t border-white/10">
            <a href="{{ route('catalog.index') }}"
               class="min-h-[40px] flex items-center gap-2 px-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-white/5 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Lihat Katalog Publik
            </a>
            <div class="flex items-center gap-3 px-3 py-2 rounded-xl bg-white/5">
                <span class="w-9 h-9 rounded-lg bg-emerald-600 text-white text-xs font-bold flex items-center justify-center shrink-0">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                <span class="min-w-0">
                    <span class="block text-xs font-bold text-white truncate">{{ $user->name }}</span>
                    <span class="block text-[11px] text-emerald-400">Instruktur</span>
                </span>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full min-h-[40px] flex items-center gap-2 px-3 rounded-xl text-xs font-bold text-rose-400 hover:bg-rose-500/10 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Keluar Sesi
                </button>
            </form>
        </div>
    </aside>

    <!-- Workspace -->
    <div class="flex-grow min-w-0 flex flex-col">
        <!-- Mobile-only quick actions (desktop has them in the sidebar) -->
        <div class="lg:hidden flex items-center justify-between gap-2 px-4 py-2 bg-white border-b border-slate-200">
            <a href="{{ route('instructor.courses.create') }}" class="min-h-[40px] inline-flex items-center px-4 rounded-xl bg-emerald-600 text-white text-xs font-bold">+ Kursus Baru</a>
            <div class="flex items-center gap-2">
                <a href="{{ route('catalog.index') }}" class="min-h-[40px] inline-flex items-center px-3 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Katalog</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="min-h-[40px] px-3 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50">Keluar</button>
                </form>
            </div>
        </div>

        <!-- Flash notifications -->
        <div class="px-4 sm:px-6 lg:px-10 pt-5 empty:hidden">
            @foreach(['success' => 'bg-emerald-50 border-emerald-200 text-emerald-900', 'error' => 'bg-rose-50 border-rose-200 text-rose-900', 'info' => 'bg-slate-100 border-slate-300 text-slate-800'] as $key => $classes)
                @if(session($key))
                    <div role="{{ $key === 'error' ? 'alert' : 'status' }}" class="mb-2 p-4 rounded-xl border text-sm font-medium {{ $classes }}">{{ session($key) }}</div>
                @endif
            @endforeach
            @if($errors->any())
                <div role="alert" class="mb-2 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm">
                    <div class="font-bold mb-1">Mohon periksa kembali input Anda:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </div>

        <main class="flex-grow px-4 sm:px-6 lg:px-10 py-6 lg:py-8">
            @yield('content')
        </main>

        <footer class="px-4 sm:px-6 lg:px-10 py-5 text-xs text-slate-400 border-t border-slate-200 bg-white/60">
            FortiLearn Studio &middot; &copy; {{ date('Y') }}
        </footer>
    </div>
</div>
</body>
</html>
