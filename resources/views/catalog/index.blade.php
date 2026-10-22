@extends('layouts.app')

@section('title', 'Katalog Kursus Cyber Security')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-8 md:p-12 mb-10 shadow-xl border border-slate-800">
    <div class="relative z-10 max-w-3xl">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-xs font-semibold mb-4">
            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            <span>Platform Pembelajaran Keamanan Siber Terproteksi</span>
        </div>
        <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4 leading-tight">
            Kuasai Pertahanan Web & Rekayasa Perangkat Lunak Aman
        </h1>
        <p class="text-slate-300 text-base md:text-lg mb-8 leading-relaxed">
            Eksplorasi kursus mendalam dari instruktur ahli siber. Seluruh transaksi pembelian kursus dilindungi sistem dompet internal anti-manipulasi harga dan bebas *race condition*.
        </p>

        <!-- Search & Filter Form (Safe Parameterized Search) -->
        <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari judul kursus atau materi (misal: Pentest, OWASP)..."
                    class="w-full pl-10 pr-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-400 transition text-sm">
            </div>

            <select name="sort" onchange="this.form.submit()"
                class="px-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-indigo-400 text-sm">
                <option value="latest" class="text-slate-800" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru</option>
                <option value="price_asc" class="text-slate-800" {{ $sort === 'price_asc' ? 'selected' : '' }}>Harga: Terendah ke Tertinggi</option>
                <option value="price_desc" class="text-slate-800" {{ $sort === 'price_desc' ? 'selected' : '' }}>Harga: Tertinggi ke Terendah</option>
            </select>

            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition shadow-md">
                Cari
            </button>
        </form>
    </div>
</div>

<!-- Courses Grid -->
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-xl font-bold text-slate-900">
        Daftar Kursus Tersedia ({{ $courses->total() }})
    </h2>
    @if($search)
        <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
            &larr; Reset Pencarian
        </a>
    @endif
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($courses as $course)
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition flex flex-col group">
            <!-- Course Header / Cover Badge -->
            <div class="h-44 bg-gradient-to-tr from-slate-800 to-indigo-900 p-5 flex flex-col justify-between text-white relative">
                <div class="flex justify-between items-start">
                    <span class="px-2.5 py-1 rounded-md bg-white/20 backdrop-blur-sm text-xs font-semibold tracking-wide uppercase">
                        Cyber Security
                    </span>
                    <span class="px-2.5 py-1 rounded-md bg-emerald-500/90 text-white text-xs font-bold shadow-xs">
                        Tersertifikasi
                    </span>
                </div>
                <div>
                    <div class="text-xs text-indigo-200 flex items-center space-x-1.5 mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ $course->instructor->name }}</span>
                    </div>
                    <div class="text-xs text-slate-300">
                        {{ $course->lessons->count() }} Modul Pelajaran
                    </div>
                </div>
            </div>

            <!-- Course Details -->
            <div class="p-6 flex-grow flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-indigo-600 transition mb-2 line-clamp-2">
                        <a href="{{ route('catalog.show', $course->slug) }}">{{ $course->title }}</a>
                    </h3>
                    <p class="text-slate-600 text-sm line-clamp-3 mb-6">
                        {{ $course->description }}
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase font-semibold text-slate-400">Harga Kursus</div>
                        <div class="text-xl font-extrabold text-slate-900">
                            Rp {{ number_format($course->price, 0, ',', '.') }}
                        </div>
                    </div>

                    @auth
                        @if(auth()->user()->hasEnrolled($course->id))
                            <span class="px-3.5 py-2 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold inline-flex items-center space-x-1">
                                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Sudah Terdaftar</span>
                            </span>
                        @else
                            <a href="{{ route('checkout.confirm', $course->id) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-xs transition">
                                Beli Sekarang
                            </a>
                        @endif
                    @else
                        <a href="{{ route('checkout.confirm', $course->id) }}" class="px-4 py-2 bg-slate-900 hover:bg-indigo-600 text-white text-sm font-semibold rounded-xl shadow-xs transition">
                            Daftar Kelas
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-3 text-center py-16 bg-white rounded-2xl border border-slate-200">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-base font-bold text-slate-700">Tidak ada kursus yang cocok</h3>
            <p class="text-sm text-slate-500 mt-1">Coba kata kunci lain atau reset filter pencarian Anda.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
<div class="mt-8">
    {{ $courses->links() }}
</div>
@endsection

