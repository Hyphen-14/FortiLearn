@extends('layouts.app')

@section('title', 'Favorit & Wishlist Saya')

@section('content')
@php
    $enrolledIds = auth()->check() ? auth()->user()->enrollments()->pluck('course_id')->toArray() : [];
@endphp

<div class="space-y-7">
    <!-- Header Banner -->
    <section class="rounded-2xl bg-[#090D16] text-white p-6 sm:p-8 border border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-rose-400 block">
                KOLEKSI TERSIMPAN
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                Daftar Favorit &amp; Wishlist Kursus
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                Simpan kelas yang menarik perhatian Anda dan lanjutkan pembelian kapan saja saat Anda siap belajar.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <button type="button"
                    id="clear-wishlist-btn"
                    hidden
                    class="min-h-[44px] px-4 py-2.5 rounded-xl border border-slate-700 bg-slate-900 hover:bg-rose-600 hover:border-rose-600 text-slate-300 hover:text-white text-xs font-bold transition">
                Kosongkan Wishlist
            </button>
            <a href="{{ route('catalog.index') }}"
               class="min-h-[44px] inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-bold transition">
                Cari Kelas Lainnya
            </a>
        </div>
    </section>

    <!-- Wishlist Grid (Filtered client-side from saved localStorage IDs) -->
    <div id="wishlist-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($courses as $course)
            @php
                $userEnrolled = in_array($course->id, $enrolledIds);
                $totalMinutes = max(15, (int) ceil($course->lessons->sum('duration_seconds') / 60));
            @endphp
            <article data-wishlist-card="{{ $course->id }}"
                     hidden
                     class="bg-white rounded-2xl border border-slate-200/90 p-5 flex flex-col justify-between shadow-2xs hover:shadow-md transition">
                <div>
                    <div class="h-32 rounded-xl bg-[#090D16] p-4 flex flex-col justify-between mb-4">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 rounded-md bg-white/10 text-emerald-300 text-[10px] font-mono font-bold uppercase">
                                {{ $course->category ?? 'Program Pilihan' }}
                            </span>
                            <button type="button"
                                    data-remove-wishlist="{{ $course->id }}"
                                    class="px-2.5 py-1 rounded-full bg-rose-500 hover:bg-rose-600 text-white text-[11px] font-bold inline-flex items-center gap-1 transition"
                                    title="Hapus dari Wishlist">
                                <span>Hapus</span>
                            </button>
                        </div>
                        <h2 class="text-base font-extrabold text-white line-clamp-2 leading-snug">
                            <a href="{{ route('catalog.show', $course->slug) }}" class="hover:underline">
                                {{ $course->title }}
                            </a>
                        </h2>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed line-clamp-2 mb-4">
                        {{ $course->description }}
                    </p>

                    <div class="text-xs text-slate-500 font-mono tabular-nums mb-4">
                        Instruktur: <strong class="text-slate-800">{{ $course->instructor->name }}</strong> &bull; {{ $course->lessons->count() }} Modul ({{ $totalMinutes }} mnt)
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <div class="text-base font-mono font-extrabold text-slate-900 tabular-nums">
                        Rp {{ number_format($course->price, 0, ',', '.') }}
                    </div>

                    @if($userEnrolled)
                        <a href="{{ route('catalog.show', $course->slug) }}"
                           class="min-h-[40px] px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold inline-flex items-center">
                            Sudah Dimiliki
                        </a>
                    @else
                        <a href="{{ route('checkout.confirm', $course->id) }}"
                           class="min-h-[40px] px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold inline-flex items-center transition">
                            Beli Sekarang
                        </a>
                    @endif
                </div>
            </article>
        @endforeach
    </div>

    <!-- Empty State when 0 courses are in Wishlist -->
    <div id="wishlist-empty-state" class="bg-white rounded-2xl border border-slate-200/90 p-10 text-center max-w-xl mx-auto my-6 shadow-2xs space-y-4">
        <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </div>
        <h2 class="text-lg font-extrabold text-slate-900">Belum Ada Kursus yang Disimpan</h2>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
            Klik tombol ikon <strong>Hati</strong> pada pojok kanan atas kartu kursus di halaman Eksplorasi Kelas untuk menyimpan kursus favorit Anda ke halaman ini.
        </p>
        <div class="pt-2">
            <a href="{{ route('catalog.index') }}"
               class="min-h-[44px] inline-flex items-center px-6 py-2.5 rounded-xl bg-[#090D16] hover:bg-emerald-600 text-white text-xs sm:text-sm font-bold transition">
                Eksplorasi Katalog Kursus
            </a>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';

        const cards = Array.from(document.querySelectorAll('[data-wishlist-card]'));
        const emptyState = document.getElementById('wishlist-empty-state');
        const clearBtn = document.getElementById('clear-wishlist-btn');

        function refreshWishlistPage() {
            const list = typeof window.getFortiWishlist === 'function' ? window.getFortiWishlist() : [];
            let count = 0;

            cards.forEach((card) => {
                const id = String(card.getAttribute('data-wishlist-card'));
                const matched = list.includes(id);
                card.hidden = !matched;
                if (matched) count++;
            });

            if (emptyState) emptyState.hidden = count > 0;
            if (clearBtn) clearBtn.hidden = count === 0;
            if (typeof window.updateSidebarWishlistBadge === 'function') {
                window.updateSidebarWishlistBadge();
            }
        }

        document.querySelectorAll('[data-remove-wishlist]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const targetId = String(btn.getAttribute('data-remove-wishlist'));
                const list = typeof window.getFortiWishlist === 'function' ? window.getFortiWishlist() : [];
                const updated = list.filter((item) => item !== targetId);
                try {
                    localStorage.setItem('fortilearn_wishlist', JSON.stringify(updated));
                } catch (err) {}
                refreshWishlistPage();
            });
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                try {
                    localStorage.setItem('fortilearn_wishlist', JSON.stringify([]));
                } catch (err) {}
                refreshWishlistPage();
            });
        }

        refreshWishlistPage();
    })();
</script>
@endsection

