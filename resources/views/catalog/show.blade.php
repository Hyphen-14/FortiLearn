@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div class="mb-6">
    <a href="{{ route('catalog.index') }}" class="text-sm font-semibold text-slate-500 hover:text-indigo-600 transition flex items-center space-x-1">
        <span>&larr; Kembali ke Katalog</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
    <!-- Left Column: Course Main Content -->
    <div class="lg:col-span-2 space-y-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-xs">
            <div class="flex items-center space-x-2 text-xs font-bold text-indigo-600 uppercase tracking-wider mb-3">
                <span class="px-2.5 py-1 bg-indigo-50 rounded-md">Cyber Security Specialization</span>
                <span>&bull;</span>
                <span class="text-slate-500">Materi Terverifikasi</span>
            </div>

            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight mb-4">
                {{ $course->title }}
            </h1>

            <p class="text-slate-600 text-base leading-relaxed mb-6">
                {{ $course->description }}
            </p>

            <div class="flex items-center space-x-4 pt-6 border-t border-slate-100 text-sm text-slate-600">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                        {{ substr($course->instructor->name, 0, 2) }}
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Instruktur</div>
                        <div class="font-bold text-slate-800">{{ $course->instructor->name }}</div>
                    </div>
                </div>
                <div class="h-8 border-l border-slate-200"></div>
                <div>
                    <div class="text-xs text-slate-400">Total Pelajaran</div>
                    <div class="font-bold text-slate-800">{{ $course->lessons->count() }} Modul</div>
                </div>
            </div>
        </div>

        <!-- Curriculum / Lessons Syllabus -->
        <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-xs">
            <h2 class="text-lg font-bold text-slate-900 mb-2">Silabus & Kurikulum Pelajaran</h2>
            <p class="text-slate-500 text-sm mb-6">
                Daftar materi yang akan dipelajari dalam kursus ini.
            </p>

            <div class="space-y-3">
                @forelse($course->lessons as $lesson)
                    <div class="p-4 rounded-xl border border-slate-200 hover:border-indigo-200 hover:bg-slate-50/50 transition flex items-center justify-between">
                        <div class="flex items-center space-x-3.5">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0">
                                @if($lesson->content_type === 'video')
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-800">
                                    {{ $lesson->lesson_order }}. {{ $lesson->title }}
                                </div>
                                <div class="text-xs text-slate-400 capitalize">
                                    Format: {{ $lesson->content_type }}
                                    @if($lesson->duration_seconds > 0)
                                        &bull; {{ gmdate("i:s", $lesson->duration_seconds) }} menit
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($isEnrolled)
                                <span class="px-2.5 py-1 rounded bg-emerald-50 text-emerald-700 text-xs font-semibold">Tersedia</span>
                            @else
                                <span class="px-2.5 py-1 rounded bg-slate-100 text-slate-500 text-xs font-semibold flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    <span>Terkunci</span>
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 italic">Belum ada materi yang ditambahkan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Column: Sticky Pricing & Action Card -->
    <div class="lg:col-span-1 sticky top-24">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="text-xs uppercase font-bold text-slate-400 tracking-wider mb-1">Investasi Belajar</div>
            <div class="text-3xl font-extrabold text-slate-900 mb-6">
                Rp {{ number_format($course->price, 0, ',', '.') }}
            </div>

            @if($isEnrolled)
                <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                    <div class="font-bold mb-1">Status: Terdaftar</div>
                    <p class="text-xs text-emerald-700">Anda sudah memiliki akses ke seluruh kurikulum dan video materi kursus ini.</p>
                </div>
                <button disabled class="w-full py-3 px-4 bg-emerald-600 text-white font-semibold rounded-xl text-sm shadow-xs cursor-default">
                    Akses Kelas Aktif
                </button>
            @else
                <a href="{{ route('checkout.confirm', $course->id) }}" class="block text-center w-full py-3.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-sm shadow-md shadow-indigo-100 transition mb-3">
                    Beli & Akses Sekarang
                </a>
                <p class="text-xs text-center text-slate-400">
                    Pembayaran aman menggunakan saldo dompet internal FortiLearn.
                </p>
            @endif

            <div class="mt-6 pt-6 border-t border-slate-100 space-y-3 text-xs text-slate-600">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Akses seumur hidup ke seluruh materi</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Terhindar dari manipulasi harga & race condition</span>
                </div>
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Kwitansi invoice resmi berotentikasi</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

