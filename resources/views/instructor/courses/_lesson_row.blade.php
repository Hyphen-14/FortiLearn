<li class="px-4 py-3 flex flex-wrap items-center gap-3 justify-between">
    <div class="flex items-center gap-3 min-w-0">
        <span class="shrink-0 px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $lesson->content_type === 'video' ? 'bg-sky-50 text-sky-700' : 'bg-violet-50 text-violet-700' }}">
            {{ strtoupper($lesson->content_type) }}
        </span>
        <div class="min-w-0">
            <p class="text-sm font-bold text-slate-900 truncate">{{ $lesson->lesson_order }}. {{ $lesson->title }}</p>
            <p class="text-[11px] text-slate-500 truncate">{{ $lesson->original_filename }}@if($lesson->duration_seconds) &bull; {{ (int) round($lesson->duration_seconds / 60) }} menit @endif</p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('lesson.stream', [$course->id, $lesson->id]) }}" target="_blank" rel="noopener"
           class="min-h-[36px] inline-flex items-center px-3 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-xs font-bold text-slate-700">Pratinjau</a>
        <form method="POST" action="{{ route('instructor.lessons.destroy', $lesson) }}" onsubmit="return confirm('Hapus materi ini beserta filenya?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="{{ $btnDanger }}">Hapus</button>
        </form>
    </div>
</li>
