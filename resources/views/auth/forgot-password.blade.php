@extends('layouts.app')

@section('title', 'Lupa Kata Sandi')

@section('content')
<div class="max-w-md mx-auto my-4">
    <div class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-2xs">
        <div class="p-6 bg-[#090D16] text-white border-b border-slate-800">
            <h1 class="text-xl font-extrabold text-white tracking-tight">Lupa Kata Sandi</h1>
            <p class="text-slate-400 text-xs mt-1">
                Masukkan email akun Anda. Kami kirim tautan untuk membuat kata sandi baru, berlaku 30 menit.
            </p>
        </div>

        <div class="p-6 md:p-8">
            @if (session('status'))
                <div role="status" class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">
                        Alamat Email
                    </label>
                    <input type="email"
                           name="email"
                           id="email"
                           inputmode="email"
                           autocomplete="email"
                           required
                           autofocus
                           maxlength="255"
                           value="{{ old('email') }}"
                           placeholder="nama@email.com"
                           class="w-full min-h-[44px] px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600 transition text-sm">
                </div>

                <button type="submit"
                        class="w-full min-h-[44px] py-2.5 px-4 bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white font-bold rounded-xl transition text-sm shadow-xs focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    Kirim Tautan Reset
                </button>
            </form>

            <div class="mt-6 text-center text-xs text-slate-600">
                Sudah ingat kata sandinya?
                <a href="{{ route('login') }}" class="font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                    Kembali ke halaman masuk
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
