@extends('layouts.app')

@section('title', 'Daftar Akun Baru')

@section('content')
<div class="max-w-md mx-auto my-8">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Daftar Akun Baru</h1>
            <p class="text-slate-500 text-sm mt-1">Mulai belajar materi keamanan siber bersertifikasi</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">Alamat Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-slate-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" id="password" required minlength="8"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
                <span class="text-xs text-slate-400 mt-1 block">Minimal 8 karakter</span>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="8"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600 transition text-sm">
            </div>

            <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-sm transition">
                Daftar Sebagai Siswa
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            Sudah memiliki akun?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Masuk di sini</a>
        </div>
    </div>
</div>
@endsection

