@php
    $course = $course ?? null;
    $inputClass = 'w-full min-h-[44px] rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600';
    $categories = ['Frontend', 'Backend', 'UI/UX Design', 'Data & AI', 'Bisnis', 'Cyber Security'];
@endphp

<div class="space-y-5">
    <div>
        <label for="title" class="block text-xs font-bold text-slate-700 mb-1.5">Judul Course</label>
        <input id="title" name="title" type="text" required maxlength="150" value="{{ old('title', $course?->title) }}" class="{{ $inputClass }}" placeholder="Contoh: Pemrograman Web Aman dengan Laravel">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label for="category" class="block text-xs font-bold text-slate-700 mb-1.5">Kategori</label>
            <input id="category" name="category" type="text" list="category-list" required maxlength="60" value="{{ old('category', $course?->category) }}" class="{{ $inputClass }}">
            <datalist id="category-list">
                @foreach($categories as $cat)
                    <option value="{{ $cat }}"></option>
                @endforeach
            </datalist>
        </div>
        <div>
            <label for="price" class="block text-xs font-bold text-slate-700 mb-1.5">Harga (Rp)</label>
            <input id="price" name="price" type="number" inputmode="numeric" min="0" max="10000000" step="1" required value="{{ old('price', $course ? (int) $course->price : 0) }}" class="{{ $inputClass }} font-mono">
        </div>
    </div>

    <div>
        <label for="description" class="block text-xs font-bold text-slate-700 mb-1.5">Deskripsi</label>
        <textarea id="description" name="description" rows="5" required minlength="20" maxlength="5000" class="{{ $inputClass }}" placeholder="Jelaskan apa yang akan dipelajari siswa...">{{ old('description', $course?->description) }}</textarea>
    </div>

    <div>
        <label for="thumbnail" class="block text-xs font-bold text-slate-700 mb-1.5">Thumbnail (JPG / PNG, maks. 10MB)</label>
        <input id="thumbnail" name="thumbnail" type="file" accept="image/jpeg,image/png"
               class="block w-full text-sm text-slate-700 file:mr-4 file:min-h-[44px] file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-slate-800">
        <p class="text-[11px] text-slate-500 mt-1.5">Tipe file diperiksa dari isi aslinya (magic bytes), bukan dari ekstensi.</p>
    </div>
</div>
