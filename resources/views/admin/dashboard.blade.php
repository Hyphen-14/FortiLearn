@extends('layouts.app')

@section('title', 'Ringkasan Admin')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ringkasan Admin</h1>
        <p class="text-slate-600 text-sm mt-1">Course yang menunggu keputusan Anda dan aktivitas mencurigakan terbaru.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <section aria-labelledby="queue-heading" class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-100">
                <h2 id="queue-heading" class="font-extrabold text-slate-900">
                    Antrean review
                    <span class="ml-1 text-sm font-mono text-slate-500 tabular-nums">({{ $pendingCount }})</span>
                </h2>
                <a href="{{ route('admin.courses.index') }}" class="inline-flex items-center min-h-[44px] text-xs font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
                    Buka semua antrean
                </a>
            </div>

            @if($pendingCourses->isEmpty())
                <p class="px-5 py-8 text-sm text-slate-600">
                    Tidak ada course yang menunggu review. Course baru muncul di sini setelah instruktur menekan "Ajukan ke Admin".
                </p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach($pendingCourses as $course)
                        <li class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <div class="min-w-0 flex-grow">
                                <p class="font-bold text-slate-900 truncate">{{ $course->title }}</p>
                                <p class="text-xs text-slate-600">
                                    {{ $course->instructor?->name ?? 'Instruktur dihapus' }} · {{ $course->lessons_count }} materi · diajukan {{ $course->updated_at->locale('id')->diffForHumans() }}
                                </p>
                            </div>
                            <a href="{{ route('admin.courses.show', $course) }}"
                               class="shrink-0 min-h-[44px] inline-flex items-center justify-center px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                                Tinjau
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section aria-labelledby="facts-heading" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5">
            <h2 id="facts-heading" class="font-extrabold text-slate-900 mb-3">Perlu perhatian</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-slate-600">Event keamanan, 24 jam terakhir</dt>
                    <dd class="font-mono font-extrabold text-slate-900 tabular-nums">{{ $securityEvents24h }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-3">
                    <dt class="text-slate-600">Akun nonaktif</dt>
                    <dd class="font-mono font-extrabold text-slate-900 tabular-nums">{{ $inactiveUsers }}</dd>
                </div>
            </dl>
            <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col gap-1 text-xs font-bold">
                <a href="{{ route('admin.users.index', ['state' => 'inactive']) }}" class="inline-flex items-center min-h-[36px] text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">Lihat akun nonaktif</a>
                <a href="{{ route('admin.audit-logs.index', ['security' => 1]) }}" class="inline-flex items-center min-h-[36px] text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">Lihat semua event keamanan</a>
            </div>
        </section>
    </div>

    <section aria-labelledby="events-heading" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 id="events-heading" class="font-extrabold text-slate-900">Event keamanan terbaru</h2>
            <p class="text-xs text-slate-600 mt-0.5">Login gagal, lockout, akses ditolak, dan upload berbahaya yang diblokir.</p>
        </div>

        @if($recentEvents->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-600">Belum ada event keamanan yang tercatat.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-slate-600 bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-2 font-bold">Waktu</th>
                            <th scope="col" class="px-5 py-2 font-bold">Event</th>
                            <th scope="col" class="px-5 py-2 font-bold">Pengguna / email</th>
                            <th scope="col" class="px-5 py-2 font-bold">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentEvents as $event)
                            <tr>
                                <td class="px-5 py-2 whitespace-nowrap text-slate-700">{{ $event->created_at->format('d M H:i') }}</td>
                                <td class="px-5 py-2 font-mono text-xs text-slate-900 whitespace-nowrap">{{ $event->event_type }}</td>
                                <td class="px-5 py-2 text-slate-700 break-all">{{ $event->user?->email ?? ($event->payload['email'] ?? 'Tamu') }}</td>
                                <td class="px-5 py-2 font-mono text-xs text-slate-700 whitespace-nowrap">{{ $event->ip_address }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection
