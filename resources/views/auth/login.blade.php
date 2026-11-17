@extends('layouts.app')

@section('title', 'Masuk ke Akun')

@section('content')
<div class="max-w-md mx-auto my-4">
    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="p-6 bg-[#090D16] text-white border-b border-slate-800">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-400 block mb-1">
                Selamat Datang Kembali
            </span>
            <h1 class="text-xl font-extrabold text-white tracking-tight">Masuk ke FortiLearn</h1>
            <p class="text-slate-400 text-xs mt-1">
                Lanjutkan progres belajar Anda dan akses seluruh materi kursus kapan saja.
            </p>
        </div>

        <div class="p-6 md:p-8">
            @if (session('status'))
                <div role="status" class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form id="login-form" action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

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
                           autofocus
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
                           autocomplete="current-password"
                           required
                           placeholder="Masukkan kata sandi Anda"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="inline-flex items-center gap-2 text-slate-700 font-medium min-h-[36px]">
                        <input type="checkbox"
                               name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="inline-flex items-center min-h-[36px] font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                        Lupa kata sandi?
                    </a>
                </div>

                <button type="submit"
                        class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl transition text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    Masuk Sekarang
                </button>
            </form>

            <!-- Quick Demo Account Selector -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-bold text-slate-600">
                        Akun Demo Cepat (Klik untuk Masuk)
                    </h2>
                    <span class="text-[11px] font-semibold text-emerald-700">Siap Pakai</span>
                </div>

                <div class="space-y-2 text-xs">
                    <button type="button"
                            data-fill-email="student1@secureacademy.test"
                            data-fill-password="PasswordStudent123!"
                            class="w-full min-h-[48px] p-3 rounded-xl bg-slate-50 hover:bg-emerald-50/60 border border-slate-200 hover:border-emerald-500 text-left flex justify-between items-center gap-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        <div>
                            <div class="font-bold text-slate-900">Alice Maharani (Saldo Rp 500.000)</div>
                            <div class="font-mono text-slate-500 text-[11px]">student1@secureacademy.test</div>
                        </div>
                        <span class="px-2.5 py-1 bg-white rounded-lg border border-slate-200 text-slate-800 text-[11px] font-bold shrink-0">
                            Pilih Akun
                        </span>
                    </button>

                    <button type="button"
                            data-fill-email="student2@secureacademy.test"
                            data-fill-password="PasswordStudent123!"
                            class="w-full min-h-[48px] p-3 rounded-xl bg-slate-50 hover:bg-emerald-50/60 border border-slate-200 hover:border-emerald-500 text-left flex justify-between items-center gap-2 transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        <div>
                            <div class="font-bold text-slate-900">Budi Hartono (Saldo Rp 100.000)</div>
                            <div class="font-mono text-slate-500 text-[11px]">student2@secureacademy.test</div>
                        </div>
                        <span class="px-2.5 py-1 bg-white rounded-lg border border-slate-200 text-slate-800 text-[11px] font-bold shrink-0">
                            Pilih Akun
                        </span>
                    </button>
                </div>
            </div>

            <div class="mt-6 text-center text-xs text-slate-600">
                Belum memiliki akun?
                <a href="{{ route('register') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                    Daftar gratis sekarang
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        'use strict';
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        document.querySelectorAll('[data-fill-email]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (emailInput && passwordInput) {
                    emailInput.value = btn.getAttribute('data-fill-email') || '';
                    passwordInput.value = btn.getAttribute('data-fill-password') || '';
                    emailInput.focus();
                }
            });
        });
    })();
</script>
@endsection
