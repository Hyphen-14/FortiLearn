@extends('layouts.app')

@section('title', 'Login Instruktur')

@section('content')
<div class="max-w-md mx-auto my-4">
    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="p-6 bg-[#090D16] text-white border-b border-slate-800">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-400 block mb-1">
                Studio Instruktur
            </span>
            <h1 class="text-xl font-extrabold text-white tracking-tight">Login Instruktur</h1>
            <p class="text-slate-400 text-xs mt-1">
                Masuk untuk mengelola kursus, memantau penjualan, dan membaca ulasan siswa.
            </p>
        </div>

        <div class="p-6 md:p-8">
            <form id="instructor-login-form" action="{{ route('instructor.login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Email Instruktur</label>
                    <input type="email" name="email" id="email" inputmode="email" autocomplete="username" required autofocus
                           value="{{ old('email') }}" placeholder="instruktur@email.com"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi</label>
                    <input type="password" name="password" id="password" autocomplete="current-password" required
                           placeholder="Masukkan kata sandi Anda"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <label class="inline-flex items-center gap-2 text-xs text-slate-700 font-medium min-h-[36px]">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                    <span>Ingat saya di perangkat ini</span>
                </label>

                <button type="submit"
                        class="w-full min-h-[44px] py-2.5 px-4 bg-[#090D16] hover:bg-slate-800 active:bg-slate-900 text-white font-bold rounded-xl transition text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    Masuk ke Studio
                </button>
            </form>

            <div class="mt-6 space-y-2 text-center text-xs text-slate-600">
                <div>
                    Belum punya akun instruktur?
                    <a href="{{ route('register.instructor') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">Daftar sebagai instruktur</a>
                </div>
                <div>
                    Bukan instruktur?
                    <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">Masuk sebagai siswa</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
