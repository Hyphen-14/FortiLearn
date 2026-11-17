<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\WalletTransaction;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Instructor analytics home. Every query is scoped to the signed-in instructor's own courses
     * (anti-IDOR by scoping) so no other instructor's data can ever surface here.
     */
    public function index(): View
    {
        $instructorId = auth()->id();

        $courses = Course::where('instructor_id', $instructorId)
            ->withCount(['lessons', 'enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->get();
        $courseIds = $courses->pluck('id');

        // Gross sales = completed purchase transactions on this instructor's courses.
        $purchases = WalletTransaction::whereIn('course_id', $courseIds)
            ->where('type', 'purchase')
            ->where('status', 'completed')
            ->get(['course_id', 'amount', 'created_at']);

        $revenueByCourse = $purchases->groupBy('course_id')->map(fn ($g) => (float) $g->sum('amount'));

        // Last 6 months revenue series (oldest -> newest).
        $months = collect(range(5, 0))->map(function ($i) use ($purchases) {
            $start = now()->startOfMonth()->subMonths($i);
            $end = $start->copy()->addMonth();
            $total = $purchases
                ->filter(fn ($p) => $p->created_at >= $start && $p->created_at < $end)
                ->sum(fn ($p) => (float) $p->amount);

            return ['label' => $start->translatedFormat('M'), 'total' => (float) $total];
        });

        $reviewCount = (int) $courses->sum('reviews_count');
        $avgRating = $reviewCount > 0
            ? round((float) Review::whereIn('course_id', $courseIds)->avg('rating'), 1)
            : null;

        $courseStats = $courses->map(function ($c) use ($revenueByCourse) {
            $c->revenue = $revenueByCourse[$c->id] ?? 0.0;

            return $c;
        });

        return view('instructor.dashboard', [
            'stats' => [
                'revenue' => (float) $purchases->sum('amount'),
                'revenue_month' => (float) $months->last()['total'],
                'students' => (int) $courses->sum('enrollments_count'),
                'courses' => $courses->count(),
                'rating' => $avgRating,
                'reviews' => $reviewCount,
            ],
            'pipeline' => [
                'draft' => $courses->where('status', 'draft')->count(),
                'pending_approval' => $courses->where('status', 'pending_approval')->count(),
                'published' => $courses->where('status', 'published')->count(),
                'rejected' => $courses->where('status', 'rejected')->count(),
            ],
            'months' => $months,
            'topCourses' => $courseStats->sortByDesc('enrollments_count')->take(5)->values(),
            'needsAttention' => $courseStats->filter(
                fn ($c) => $c->status === 'rejected' || ($c->status === 'draft' && $c->lessons_count === 0)
            )->values(),
            'recentEnrollments' => Enrollment::with(['user:id,name', 'course:id,title'])
                ->whereIn('course_id', $courseIds)->latest('enrolled_at')->limit(6)->get(),
            'recentReviews' => Review::with(['user:id,name', 'course:id,title'])
                ->whereIn('course_id', $courseIds)->latest()->limit(4)->get(),
        ]);
    }
}
