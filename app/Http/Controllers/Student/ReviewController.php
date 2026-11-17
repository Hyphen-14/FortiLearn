<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Create or update the signed-in student's review for a course.
     * Only enrolled students may review (anti-fake-review); user_id/course_id come from the server.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();

        abort_unless($course->status === 'published', 404);

        if (! $user->hasEnrolled($course->id)) {
            return back()->with('error', 'Hanya siswa yang sudah membeli course ini yang dapat memberi ulasan.');
        }

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $comment = trim(strip_tags((string) ($data['comment'] ?? '')));

        $review = Review::where('course_id', $course->id)->where('user_id', $user->id)->first() ?? new Review();
        $review->course_id = $course->id;
        $review->user_id = $user->id;
        $review->fill([
            'rating' => $data['rating'],
            'comment' => $comment !== '' ? $comment : null,
        ]);
        $review->save();

        return redirect()->to(route('catalog.show', $course->slug) . '#reviews')
            ->with('success', 'Terima kasih! Ulasan Anda telah disimpan.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        Review::where('course_id', $course->id)->where('user_id', $request->user()->id)->delete();

        return redirect()->to(route('catalog.show', $course->slug) . '#reviews')
            ->with('success', 'Ulasan Anda telah dihapus.');
    }
}
