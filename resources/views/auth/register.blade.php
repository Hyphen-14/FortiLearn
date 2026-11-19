@extends('layouts.app')

@php
    $isInstructor = ($accountType ?? 'student') === 'instructor';
@endphp

@section('title', $isInstructor ? 'Daftar Sebagai Instruktur' : 'Daftar Akun Baru')

@section('content')
<div class="max-w-md mx-auto my-4">
    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="p-6 bg-[#090D16] text-white border-b border-slate-800">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-400 block mb-1">
                {{ $isInstructor ? 'Studio Instruktur FortiLearn' : 'Mulai Perjalanan Belajar Anda' }}
            </span>
            <h1 class="text-xl font-extrabold text-white tracking-tight">
                {{ $isInstructor ? 'Daftar Sebagai Instruktur' : 'Buat Akun FortiLearn' }}
            </h1>
            <p class="text-slate-400 text-xs mt-1">
                {{ $isInstructor
                    ? 'Buat kursus, unggah materi video/PDF, dan pantau penjualan serta ulasan siswa dari dashboard studio Anda.'
                    : 'Daftar gratis untuk mengakses katalog kursus profesional dan fitur Dompet Belajar.' }}
            </p>
        </div>

        <div class="p-6 md:p-8">
            <form action="{{ $isInstructor ? route('register.instructor') : route('register') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Nama Lengkap
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           autocomplete="name"
                           required
                           value="{{ old('name') }}"
                           placeholder="Nama Lengkap Anda"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           inputmode="email"
                           autocomplete="username"
                           required
                           value="{{ old('email') }}"
                           placeholder="nama@email.com"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Kata Sandi
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           autocomplete="new-password"
                           required
                           minlength="8"
                           maxlength="72"
                           aria-describedby="password-rules"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                    <p id="password-rules" class="mt-1.5 text-xs text-slate-600">
                        8 sampai 72 karakter, berisi huruf besar, huruf kecil, angka, dan simbol.
                    </p>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Konfirmasi Kata Sandi
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           autocomplete="new-password"
                           required
                           minlength="8"
                           placeholder="Ulangi kata sandi Anda"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <button type="submit"
                        class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl transition text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    {{ $isInstructor ? 'Daftar Sebagai Instruktur' : 'Daftar Sekarang' }}
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-600">
                Sudah memiliki akun?
                <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                    Masuk di sini
                </a>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
                @if($isInstructor)
                    Ingin belajar, bukan mengajar?
                    <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                        Daftar sebagai siswa
                    </a>
                @else
                    Ingin membuat dan menjual kursus?
                    <a href="{{ route('register.instructor') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                        Daftar sebagai instruktur
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
