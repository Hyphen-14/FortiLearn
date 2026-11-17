@php
    $stars = fn ($n) => str_repeat('★', (int) $n) . str_repeat('☆', 5 - (int) $n);
    $canWrite = auth()->check() && auth()->user()->isStudent() && $isEnrolled;
@endphp
<section id="reviews" aria-labelledby="reviews-heading" class="bg-white rounded-2xl border border-slate-200/90 p-6 md:p-8 shadow-2xs mt-6 scroll-mt-24">
    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
        <div>
            <h2 id="reviews-heading" class="text-lg font-extrabold text-slate-900">Ulasan Siswa</h2>
            <p class="text-xs text-slate-500 mt-0.5">Hanya siswa yang sudah membeli kelas ini yang dapat memberi ulasan.</p>
        </div>
        <div class="text-right">
            <span class="text-2xl font-mono font-extrabold text-slate-900 tabular-nums">{{ $ratingCount ? number_format($ratingAvg, 1) : '—' }}</span>
            <span class="text-amber-500">{{ $stars(round($ratingAvg ?? 0)) }}</span>
            <span class="block text-[11px] text-slate-500">{{ $ratingCount }} ulasan</span>
        </div>
    </div>

    @if($canWrite)
        <form method="POST" action="{{ route('reviews.store', $course->id) }}" class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
            @csrf
            <fieldset>
                <legend class="text-sm font-bold text-slate-900 mb-1">{{ $myReview ? 'Ubah ulasan Anda' : 'Tulis ulasan Anda' }}</legend>
                <div class="flex items-center gap-4 flex-wrap">
                    @for($i = 5; $i >= 1; $i--)
                        <label class="inline-flex items-center gap-1.5 text-sm cursor-pointer">
                            <input type="radio" name="rating" value="{{ $i }}" required @checked(old('rating', $myReview->rating ?? null) == $i) class="accent-emerald-600">
                            <span class="text-amber-500">{{ $i }}★</span>
                        </label>
                    @endfor
                </div>
            </fieldset>
            <div>
                <label for="review-comment" class="sr-only">Komentar</label>
                <textarea id="review-comment" name="comment" rows="3" maxlength="1000" placeholder="Ceritakan pengalaman belajar Anda (opsional)"
                          class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600">{{ old('comment', $myReview->comment ?? '') }}</textarea>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit" class="min-h-[44px] px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2">
                    {{ $myReview ? 'Perbarui Ulasan' : 'Kirim Ulasan' }}
                </button>
            </div>
        </form>
        @if($myReview)
            <form method="POST" action="{{ route('reviews.destroy', $course->id) }}" class="-mt-4 mb-6">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">Hapus ulasan saya</button>
            </form>
        @endif
    @endif

    @forelse($reviews as $r)
        <article class="py-4 {{ ! $loop->first ? 'border-t border-slate-100' : '' }}">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <span class="text-amber-500 tracking-wide" aria-label="{{ $r->rating }} dari 5 bintang">{{ $stars($r->rating) }}</span>
                <span class="text-sm font-bold text-slate-900">{{ $r->user->name ?? 'Siswa' }}</span>
                <span class="text-[11px] font-mono text-slate-400 ml-auto">{{ $r->created_at->format('d M Y') }}</span>
            </div>
            @if($r->comment)<p class="text-sm text-slate-700 mt-1.5 whitespace-pre-line">{{ $r->comment }}</p>@endif
        </article>
    @empty
        <p class="py-6 text-center text-sm text-slate-500">Belum ada ulasan. Jadilah yang pertama setelah menyelesaikan kelas ini.</p>
    @endforelse
</section>
