@extends('layouts.app')

@section('title', 'Kelola: ' . $course->title)

@section('content')
@php
    $statusMap = [
        'draft' => ['Draft', 'bg-slate-100 text-slate-700 border-slate-200'],
        'pending_approval' => ['Menunggu Persetujuan', 'bg-amber-50 text-amber-800 border-amber-200'],
        'published' => ['Terbit', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
        'rejected' => ['Ditolak', 'bg-rose-50 text-rose-700 border-rose-200'],
    ];
    [$statusLabel, $statusBadge] = $statusMap[$course->status] ?? [$course->status, 'bg-slate-100 text-slate-700 border-slate-200'];
    $inputClass = 'w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600';
    $btnPrimary = 'min-h-[44px] px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2';
    $btnDanger = 'min-h-[36px] px-3 rounded-lg border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600';
@endphp

<div class="max-w-5xl mx-auto space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-slate-200 pb-5">
        <div>
            <a href="{{ route('instructor.courses.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">&larr; Kembali ke Studio</a>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-2">{{ $course->title }}</h1>
            <span class="inline-block mt-2 px-2.5 py-0.5 rounded-full border text-[11px] font-bold {{ $statusBadge }}">{{ $statusLabel }}</span>
        </div>
        @if(in_array($course->status, ['draft', 'rejected'], true))
            <form method="POST" action="{{ route('instructor.courses.submit', $course) }}">
                @csrf
                <button type="submit" class="{{ $btnPrimary }}">Ajukan ke Admin</button>
            </form>
        @endif
    </div>

    @if($course->status === 'published')
        <p role="note" class="text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
            Course ini sudah terbit. Menyimpan perubahan informasi, menambah materi, atau menambah/mengubah bab akan mengirim course kembali ke admin.
            Selama menunggu, course tidak tampil di katalog. Siswa yang sudah membeli tetap bisa belajar dari materi yang sudah disetujui.
        </p>
    @endif

    <!-- 1. Metadata -->
    <section aria-labelledby="meta-heading" class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
        <h2 id="meta-heading" class="text-base font-extrabold text-slate-900 mb-4">1. Informasi Course</h2>
        @if($course->thumbnail)
            <img src="{{ route('course.thumbnail', $course->id) }}" alt="Thumbnail saat ini" class="w-full max-w-xs rounded-xl border border-slate-200 mb-5">
        @endif
        <form method="POST" action="{{ route('instructor.courses.update', $course) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('instructor.courses._form', ['course' => $course])
            <div class="flex justify-end">
                <button type="submit" class="{{ $btnPrimary }}">Simpan Perubahan</button>
            </div>
        </form>
    </section>

    <!-- 2. Curriculum Builder -->
    <section aria-labelledby="curriculum-heading" class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-6">
        <h2 id="curriculum-heading" class="text-base font-extrabold text-slate-900">2. Kurikulum (Bab &amp; Materi)</h2>

        @forelse($course->sections as $section)
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 flex flex-wrap items-center gap-3 justify-between border-b border-slate-200">
                    <form method="POST" action="{{ route('instructor.sections.update', $section) }}" class="flex items-center gap-2 flex-grow">
                        @csrf
                        @method('PUT')
                        <span class="text-[11px] font-mono font-bold text-slate-500">BAB {{ $section->section_order }}</span>
                        <label class="sr-only" for="section-title-{{ $section->id }}">Judul bab</label>
                        <input id="section-title-{{ $section->id }}" name="title" type="text" value="{{ $section->title }}" required maxlength="150"
                               class="flex-grow min-h-[36px] rounded-lg border border-slate-300 bg-white px-3 py-1 text-sm font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">
                        <button type="submit" class="min-h-[36px] px-3 rounded-lg bg-slate-900 text-white text-xs font-bold hover:bg-slate-800">Simpan</button>
                    </form>
                    <form method="POST" action="{{ route('instructor.sections.destroy', $section) }}" onsubmit="return confirm('Hapus bab ini? Materi di dalamnya tetap tersimpan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="{{ $btnDanger }}">Hapus Bab</button>
                    </form>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse($section->lessons as $lesson)
                        @include('instructor.courses._lesson_row', ['lesson' => $lesson, 'course' => $course, 'btnDanger' => $btnDanger])
                    @empty
                        <li class="px-4 py-3 text-xs text-slate-500">Belum ada materi di bab ini.</li>
                    @endforelse
                </ul>
            </div>
        @empty
            <p class="text-sm text-slate-500">Belum ada bab. Tambahkan bab untuk mengelompokkan materi (opsional).</p>
        @endforelse

        @if($looseLessons->isNotEmpty())
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 border-b border-slate-200">Materi tanpa bab</div>
                <ul class="divide-y divide-slate-100">
                    @foreach($looseLessons as $lesson)
                        @include('instructor.courses._lesson_row', ['lesson' => $lesson, 'course' => $course, 'btnDanger' => $btnDanger])
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('instructor.sections.store', $course) }}" class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-slate-100">
            @csrf
            <label class="sr-only" for="new-section-title">Judul bab baru</label>
            <input id="new-section-title" name="title" type="text" required minlength="3" maxlength="150" placeholder="Judul bab baru, mis. Pengenalan & Persiapan" class="{{ $inputClass }}">
            <button type="submit" class="{{ $btnPrimary }} shrink-0">+ Tambah Bab</button>
        </form>
    </section>

    <!-- 3. Secure Upload -->
    <section aria-labelledby="upload-heading" class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs">
        <h2 id="upload-heading" class="text-base font-extrabold text-slate-900">3. Unggah Materi Pembelajaran</h2>
        <p class="text-xs text-slate-500 mt-1 mb-5">
            Video MP4 maks. 50MB &bull; PDF maks. 10MB. File diperiksa isi aslinya, dipindai dari kode berbahaya, diganti namanya menjadi UUID, dan disimpan di penyimpanan privat.
        </p>
        <form method="POST" action="{{ route('instructor.lessons.store', $course) }}" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            @csrf
            <div class="sm:col-span-2">
                <label for="lesson-title" class="block text-xs font-bold text-slate-700 mb-1.5">Judul Materi</label>
                <input id="lesson-title" name="title" type="text" required minlength="3" maxlength="150" value="{{ old('title') }}" class="{{ $inputClass }}">
            </div>
            <div>
                <label for="lesson-type" class="block text-xs font-bold text-slate-700 mb-1.5">Jenis Materi</label>
                <select id="lesson-type" name="content_type" required class="{{ $inputClass }}">
                    <option value="video" @selected(old('content_type') === 'video')>Video (.mp4)</option>
                    <option value="pdf" @selected(old('content_type') === 'pdf')>Modul / Slide (.pdf)</option>
                </select>
            </div>
            <div>
                <label for="lesson-section" class="block text-xs font-bold text-slate-700 mb-1.5">Masukkan ke Bab</label>
                <select id="lesson-section" name="section_id" class="{{ $inputClass }}">
                    <option value="">— Tanpa bab —</option>
                    @foreach($course->sections as $section)
                        <option value="{{ $section->id }}" @selected((string) old('section_id') === (string) $section->id)>Bab {{ $section->section_order }}: {{ $section->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="lesson-duration" class="block text-xs font-bold text-slate-700 mb-1.5">Durasi (menit, opsional)</label>
                <input id="lesson-duration" name="duration_minutes" type="number" min="0" max="1440" step="1" value="{{ old('duration_minutes', 0) }}" class="{{ $inputClass }} font-mono">
            </div>
            <div>
                <label for="lesson-file" class="block text-xs font-bold text-slate-700 mb-1.5">File</label>
                <input id="lesson-file" name="file" type="file" required accept="video/mp4,application/pdf"
                       class="block w-full text-sm text-slate-700 file:mr-4 file:min-h-[44px] file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-slate-800">
            </div>
            <div class="sm:col-span-2 flex justify-end">
                <button type="submit" class="{{ $btnPrimary }}">Unggah Materi</button>
            </div>
        </form>
    </section>

    <!-- Danger zone -->
    <section aria-labelledby="danger-heading" class="bg-white rounded-2xl border border-rose-200 p-6 shadow-2xs">
        <h2 id="danger-heading" class="text-base font-extrabold text-rose-700">Zona Berbahaya</h2>
        <p class="text-xs text-slate-600 mt-1 mb-4">Menghapus course juga menghapus seluruh file materinya. Course yang sudah memiliki siswa tidak dapat dihapus.</p>
        <form method="POST" action="{{ route('instructor.courses.destroy', $course) }}" onsubmit="return confirm('Hapus course ini beserta seluruh materinya secara permanen?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="{{ $btnDanger }}">Hapus Course</button>
        </form>
    </section>
</div>
@endsection
