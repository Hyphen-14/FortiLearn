@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
@php
    $roleLabels = ['student' => 'Student', 'instructor' => 'Instruktur', 'admin' => 'Admin'];
    $inputClass = 'w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600';
@endphp
<div class="max-w-6xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kelola Pengguna</h1>
        <p class="text-slate-600 text-sm mt-1">
            Nonaktifkan akun yang disalahgunakan. Akun yang dinonaktifkan langsung keluar dari semua sesi dan tidak bisa login.
            Akun admin tidak bisa diubah dari halaman ini.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
        <div class="lg:col-span-2">
            <label for="q" class="block text-xs font-bold text-slate-700 mb-1.5">Cari nama atau email</label>
            <input type="search" id="q" name="q" maxlength="100" value="{{ $filters['q'] ?? '' }}" placeholder="nama@email.com" class="{{ $inputClass }}">
        </div>
        <div>
            <label for="role" class="block text-xs font-bold text-slate-700 mb-1.5">Peran</label>
            <select id="role" name="role" class="{{ $inputClass }}">
                <option value="">Semua peran</option>
                @foreach($roleLabels as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="state" class="block text-xs font-bold text-slate-700 mb-1.5">Status</label>
            <select id="state" name="state" class="{{ $inputClass }}">
                <option value="">Semua status</option>
                <option value="active" @selected(($filters['state'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['state'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap gap-2">
            <button type="submit" class="min-h-[44px] px-5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Terapkan filter</button>
            @if(array_filter($filters))
                <a href="{{ route('admin.users.index') }}" class="min-h-[44px] inline-flex items-center px-4 rounded-xl border border-slate-300 text-slate-700 text-sm font-bold hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">Hapus filter</a>
            @endif
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
        @if($users->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-600">Tidak ada pengguna yang cocok dengan filter ini.</p>
        @else
            {{-- relative: keeps the absolutely positioned sr-only labels inside the scroller on narrow screens --}}
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-slate-600 bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-2 font-bold">Pengguna</th>
                            <th scope="col" class="px-5 py-2 font-bold">Peran</th>
                            <th scope="col" class="px-5 py-2 font-bold">Status</th>
                            <th scope="col" class="px-5 py-2 font-bold">Terdaftar</th>
                            <th scope="col" class="px-5 py-2 font-bold"><span class="sr-only">Aksi</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $user)
                            <tr>
                                <td class="px-5 py-3">
                                    <div class="font-bold text-slate-900">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-600 break-all">{{ $user->email }}</div>
                                </td>
                                <td class="px-5 py-3 text-slate-700 whitespace-nowrap">{{ $roleLabels[$user->role] ?? $user->role }}</td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if($user->is_active)
                                        <span class="text-emerald-800 font-bold">Aktif</span>
                                    @else
                                        <span class="text-rose-700 font-bold">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-700 whitespace-nowrap">{{ $user->created_at?->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    @can('changeStatus', $user)
                                        <form method="POST" action="{{ $user->is_active ? route('admin.users.deactivate', $user) : route('admin.users.activate', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            @if($user->is_active)
                                                <button type="submit" class="min-h-[44px] px-4 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600">
                                                    Nonaktifkan<span class="sr-only"> {{ $user->email }}</span>
                                                </button>
                                            @else
                                                <button type="submit" class="min-h-[44px] px-4 rounded-xl border border-emerald-300 text-emerald-800 hover:bg-emerald-50 text-xs font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                                                    Aktifkan<span class="sr-only"> {{ $user->email }}</span>
                                                </button>
                                            @endif
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500">{{ $user->id === auth()->id() ? 'Akun Anda' : 'Tidak dapat diubah' }}</span>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
