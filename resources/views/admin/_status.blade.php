@php
    $statusStyles = [
        'draft' => ['Draft', 'bg-slate-100 text-slate-700 border-slate-200'],
        'pending_approval' => ['Menunggu Review', 'bg-amber-50 text-amber-800 border-amber-200'],
        'published' => ['Terbit', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
    ];
    [$statusLabel, $statusClass] = $statusStyles[$status] ?? [$status, 'bg-slate-100 text-slate-700 border-slate-200'];
@endphp
<span class="inline-flex px-2.5 py-0.5 rounded-full border text-[11px] font-bold whitespace-nowrap {{ $statusClass }}">{{ $statusLabel }}</span>
