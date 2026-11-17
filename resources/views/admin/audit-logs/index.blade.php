@extends('layouts.app')

@section('title', 'Audit Log')

@section('content')
@php
    $inputClass = 'w-full min-h-[44px] px-3.5 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600';
@endphp
<div class="max-w-6xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Audit Log</h1>
        <p class="text-slate-600 text-sm mt-1">Catatan login, akses yang ditolak, upload yang diblokir, dan tindakan admin. Hanya bisa dibaca, tidak bisa diubah dari aplikasi.</p>
    </div>

    <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
        <div class="sm:w-80">
            <label for="event" class="block text-xs font-bold text-slate-700 mb-1.5">Jenis event</label>
            <select id="event" name="event" class="{{ $inputClass }}">
                <option value="">Semua event</option>
                @foreach($eventTypes as $type)
                    <option value="{{ $type }}" @selected(($filters['event'] ?? '') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>
        <label class="inline-flex items-center gap-2 min-h-[44px] text-sm text-slate-700 font-medium">
            <input type="checkbox" name="security" value="1" @checked($filters['security'] ?? false) class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
            Hanya event keamanan
        </label>
        <button type="submit" class="min-h-[44px] px-5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-sm font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">Terapkan filter</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
        @if($logs->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-600">Tidak ada event yang cocok dengan filter ini.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs text-slate-600 bg-slate-50">
                        <tr>
                            <th scope="col" class="px-5 py-2 font-bold">Waktu</th>
                            <th scope="col" class="px-5 py-2 font-bold">Event</th>
                            <th scope="col" class="px-5 py-2 font-bold">Pelaku</th>
                            <th scope="col" class="px-5 py-2 font-bold">IP</th>
                            <th scope="col" class="px-5 py-2 font-bold">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 align-top">
                        @foreach($logs as $log)
                            <tr>
                                <td class="px-5 py-2 whitespace-nowrap text-slate-700">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td class="px-5 py-2 font-mono text-xs text-slate-900 whitespace-nowrap">{{ $log->event_type }}</td>
                                <td class="px-5 py-2 text-slate-700 break-all">{{ $log->user?->email ?? 'Tamu' }}</td>
                                <td class="px-5 py-2 font-mono text-xs text-slate-700 whitespace-nowrap">{{ $log->ip_address }}</td>
                                <td class="px-5 py-2 min-w-[16rem]">
                                    @if(! empty($log->payload))
                                        <code class="block font-mono text-[11px] text-slate-700 break-all">{{ json_encode($log->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</code>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-100">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
