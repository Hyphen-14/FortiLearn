@extends('layouts.app')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="max-w-md mx-auto my-4">
    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="p-6 bg-[#090D16] text-white border-b border-slate-800">
            <h1 class="text-xl font-extrabold text-white tracking-tight">Buat Kata Sandi Baru</h1>
            <p class="text-slate-400 text-xs mt-1">
                Setelah disimpan, semua sesi login lain pada akun ini akan diakhiri.
            </p>
        </div>

        <div class="p-6 md:p-8">
            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

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
                           maxlength="255"
                           value="{{ old('email', $email) }}"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Kata Sandi Baru
                    </label>
                    <input type="password"
                           name="password"
                           id="password"
                           autocomplete="new-password"
                           required
                           autofocus
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
                        Ulangi Kata Sandi Baru
                    </label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           autocomplete="new-password"
                           required
                           minlength="8"
                           maxlength="72"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <button type="submit"
                        class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl transition text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    Simpan Kata Sandi Baru
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-600">
                Tautan kedaluwarsa?
                <a href="{{ route('password.request') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                    Minta tautan baru
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
