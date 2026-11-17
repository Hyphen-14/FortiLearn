@extends('layouts.app')

@section('title', 'Moderasi Course')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Moderasi Course</h1>
        <p class="text-slate-600 text-sm mt-1">Course hanya tampil di katalog setelah Anda terbitkan. Tinjau materi sebelum memutuskan.</p>
    </div>

    <nav aria-label="Filter status course" class="flex flex-wrap gap-2">
        @foreach($statuses as $value => $label)
            <a href="{{ route('admin.courses.index', ['status' => $value]) }}"
               @if($status === $value) aria-current="page" @endif
               class="min-h-[44px] inline-flex items-center gap-2 px-4 rounded-xl border text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 {{ $status === $value ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                {{ $label }}
                <span class="font-mono text-xs tabular-nums {{ $status === $value ? 'text-slate-300' : 'text-slate-500' }}">{{ $counts[$value] ?? 0 }}</span>
            </a>
        @endforeach
    </nav>

    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
        @if($courses->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-600">
                @if($status === 'pending_approval')
                    Tidak ada course yang menunggu review saat ini.
                @else
                    Belum ada course dengan status "{{ $statuses[$status] }}".
                @endif
            </p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($courses as $course)
                    <li class="px-5 py-4 flex flex-col md:flex-row md:items-center gap-3 md:gap-5">
                        <div class="min-w-0 flex-grow space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                @include('admin._status', ['status' => $course->status])
                                <span class="text-[11px] font-mono text-slate-600">{{ $course->category }}</span>
                            </div>
                            <p class="font-bold text-slate-900">{{ $course->title }}</p>
                            <p class="text-xs text-slate-600">
                                {{ $course->instructor?->name ?? 'Instruktur dihapus' }} · {{ $course->lessons_count }} materi ·
                                Rp {{ number_format($course->price, 0, ',', '.') }} · diperbarui {{ $course->updated_at->locale('id')->diffForHumans() }}
                            </p>
                            @if($course->status === 'rejected' && $course->rejection_reason)
                                <p class="text-xs text-rose-800">Alasan ditolak: {{ $course->rejection_reason }}</p>
                            @endif
                            @if($course->reviewer)
                                <p class="text-xs text-slate-500">Diputuskan oleh {{ $course->reviewer->name }}, {{ $course->reviewed_at?->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                        <a href="{{ route('admin.courses.show', $course) }}"
                           class="shrink-0 min-h-[44px] inline-flex items-center justify-center px-4 rounded-xl {{ $course->status === 'pending_approval' ? 'bg-slate-900 hover:bg-slate-800 text-white' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }} text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                            {{ $course->status === 'pending_approval' ? 'Tinjau' : 'Lihat detail' }}<span class="sr-only"> {{ $course->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="px-5 py-3 border-t border-slate-100">{{ $courses->links() }}</div>
        @endif
    </div>
</div>
@endsection
