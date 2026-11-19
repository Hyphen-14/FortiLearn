<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /** Read-only view of reviews on the instructor's own courses. */
    public function index(Request $request): View
    {
        $courses = Course::where('instructor_id', auth()->id())
            ->withCount('reviews')->withAvg('reviews', 'rating')
            ->orderBy('title')->get(['id', 'title']);

        $selected = $courses->firstWhere('id', (int) $request->query('course'));
        $scopeIds = $selected ? [$selected->id] : $courses->pluck('id')->all();

        $base = Review::whereIn('course_id', $scopeIds);

        $distribution = (clone $base)->selectRaw('rating, COUNT(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $count = (int) $distribution->sum();

        return view('instructor.reviews', [
            'courses' => $courses,
            'selected' => $selected,
            'reviews' => (clone $base)->with(['user:id,name', 'course:id,title'])->latest()->paginate(10)->withQueryString(),
            'distribution' => collect(range(5, 1))->mapWithKeys(fn ($r) => [$r => (int) ($distribution[$r] ?? 0)]),
            'count' => $count,
            'average' => $count > 0 ? round((float) (clone $base)->avg('rating'), 1) : null,
        ]);
    }
}
