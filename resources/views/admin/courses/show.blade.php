@extends('layouts.app')

@section('title', 'Tinjau Course')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <a href="{{ route('admin.courses.index', ['status' => $course->status]) }}" class="inline-flex items-center min-h-[44px] text-xs font-bold text-emerald-700 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 rounded">
        Kembali ke daftar {{ $statuses[$course->status] ?? '' }}
    </a>

    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 md:p-6 space-y-3">
        <div class="flex flex-wrap items-center gap-2">
            @include('admin._status', ['status' => $course->status])
            <span class="text-[11px] font-mono text-slate-600">{{ $course->category }}</span>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $course->title }}</h1>
        <p class="text-sm text-slate-600">
            {{ $course->instructor?->name ?? 'Instruktur dihapus' }}
            @if($course->instructor) ({{ $course->instructor->email }}) @endif
            · Rp {{ number_format($course->price, 0, ',', '.') }}
        </p>
        <p class="text-sm text-slate-800 whitespace-pre-line">{{ $course->description }}</p>
    </div>

    <section aria-labelledby="lessons-heading" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 id="lessons-heading" class="font-extrabold text-slate-900">Materi ({{ $course->lessons->count() }})</h2>
            <p class="text-xs text-slate-600 mt-0.5">Buka setiap materi untuk memastikan isinya sesuai deskripsi course.</p>
        </div>
        @if($course->lessons->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-600">Course ini belum punya materi. Tolak dengan alasan agar instruktur melengkapinya.</p>
        @else
            <ol class="divide-y divide-slate-100">
                @foreach($course->lessons as $lesson)
                    <li class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <div class="min-w-0 flex-grow">
                            <p class="font-bold text-slate-900">{{ $lesson->lesson_order }}. {{ $lesson->title }}</p>
                            <p class="text-xs text-slate-600">{{ strtoupper($lesson->content_type) }} · {{ $lesson->original_filename }}</p>
                        </div>
                        <a href="{{ route('lesson.stream', [$course->id, $lesson->id]) }}" target="_blank" rel="noopener"
                           class="shrink-0 min-h-[44px] inline-flex items-center justify-center px-4 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                            Buka materi<span class="sr-only"> {{ $lesson->title }} (tab baru)</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>

    @if($course->status === 'pending_approval')
        <section aria-labelledby="decision-heading" class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 md:p-6 space-y-5">
            <h2 id="decision-heading" class="font-extrabold text-slate-900">Keputusan</h2>

            <form method="POST" action="{{ route('admin.courses.approve', $course) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    Terbitkan ke katalog
                </button>
            </form>

            <form method="POST" action="{{ route('admin.courses.reject', $course) }}" class="pt-5 border-t border-slate-100 space-y-2">
                @csrf
                @method('PATCH')
                <label for="reason" class="block text-xs font-bold text-slate-700">Alasan penolakan (dikirim ke instruktur)</label>
                <textarea id="reason" name="reason" rows="3" required minlength="5" maxlength="500"
                          aria-describedby="reason-hint"
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-900 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:border-emerald-600">{{ old('reason') }}</textarea>
                <p id="reason-hint" class="text-xs text-slate-600">5 sampai 500 karakter. Sebutkan materi atau bagian yang perlu diperbaiki.</p>
                <button type="submit" class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 text-sm font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600">
                    Tolak course
                </button>
            </form>
        </section>
    @else
        <section class="bg-slate-50 rounded-2xl border border-slate-200 p-5 text-sm text-slate-700 space-y-1">
            @if($course->reviewer)
                <p>Diputuskan oleh <strong>{{ $course->reviewer->name }}</strong> pada {{ $course->reviewed_at?->format('d M Y H:i') }}.</p>
            @endif
            @if($course->status === 'rejected' && $course->rejection_reason)
                <p>Alasan: {{ $course->rejection_reason }}</p>
            @endif
            @if($course->status === 'draft')
                <p>Course masih draft. Keputusan bisa diambil setelah instruktur mengajukannya.</p>
            @endif
        </section>
    @endif
</div>
@endsection
