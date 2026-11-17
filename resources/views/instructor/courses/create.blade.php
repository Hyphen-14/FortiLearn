@extends('layouts.app')

@section('title', 'Buat Course Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="border-b border-slate-200 pb-5">
        <a href="{{ route('instructor.courses.index') }}" class="text-xs font-bold text-emerald-700 hover:underline">&larr; Kembali ke Studio</a>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight mt-2">Buat Course Baru</h1>
        <p class="text-slate-600 text-sm mt-1">Course dibuat sebagai <strong>draft</strong>. Setelah materi lengkap, ajukan ke admin untuk dipublikasikan.</p>
    </div>

    <form method="POST" action="{{ route('instructor.courses.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl border border-slate-200/90 p-6 shadow-2xs space-y-6">
        @csrf
        @include('instructor.courses._form')
        <div class="flex justify-end">
            <button type="submit"
                    class="min-h-[44px] px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                Simpan &amp; Lanjut ke Kurikulum
            </button>
        </div>
    </form>
</div>
@endsection
