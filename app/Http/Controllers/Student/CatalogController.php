<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the main course catalog with search, category filter, and sorting.
     */
    public function index(Request $request): View
    {
        $query = Course::query()
            ->with(['instructor', 'lessons'])
            ->where('status', 'published');

        $category = $request->query('category');
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%')
                  ->orWhere('category', 'like', '%' . $search . '%');
            });
        }

        $sort = $request->query('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        $courses = $query->paginate(9)->withQueryString();

        $categories = Course::query()
            ->where('status', 'published')
            ->select('category')
            ->distinct()
            ->pluck('category')
            ->all();

        return view('catalog.index', [
            'courses' => $courses,
            'search' => $search,
            'sort' => $sort,
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    /**
     * Display the dedicated Categories & Learning Paths page.
     */
    public function categories(): View
    {
        $courses = Course::with(['instructor', 'lessons'])
            ->where('status', 'published')
            ->get();

        $groupedCategories = $courses->groupBy(fn (Course $course) => $course->category ?: 'Teknologi');

        return view('catalog.categories', [
            'groupedCategories' => $groupedCategories,
            'totalCourses' => $courses->count(),
        ]);
    }

    /**
     * Display the dedicated "Kursus Saya" (My Enrolled Courses) page.
     */
    public function myCourses(): View
    {
        $enrolledCourses = collect();

        if (auth()->check()) {
            $enrolledCourseIds = auth()->user()->enrollments()->pluck('course_id');
            // Paid courses stay listed while an instructor edit waits for re-review.
            $enrolledCourses = Course::with(['instructor', 'lessons'])
                ->whereIn('id', $enrolledCourseIds)
                ->whereIn('status', ['published', 'pending_approval', 'rejected'])
                ->get()
                ->filter(fn (Course $course) => $course->isAccessibleBy(auth()->user()))
                ->values();
        }

        return view('catalog.my-courses', [
            'enrolledCourses' => $enrolledCourses,
        ]);
    }

    /**
     * Display the dedicated Wishlist / Saved Courses page.
     */
    public function wishlist(): View
    {
        $courses = Course::with(['instructor', 'lessons'])
            ->where('status', 'published')
            ->get();

        return view('catalog.wishlist', [
            'courses' => $courses,
        ]);
    }

    /**
     * Display the dedicated Help Center & Guide page.
     */
    public function help(): View
    {
        return view('catalog.help');
    }

    /**
     * Display a specific course detail & interactive classroom player page.
     */
    public function show(string $slug): View
    {
        $course = Course::with(['instructor', 'lessons'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Public: published only. Enrolled students also see a course waiting for re-review,
        // but never the lessons added after its last approval.
        abort_unless($course->isAccessibleBy(auth()->user()), 404);
        $course->setRelation('lessons', $course->lessons->filter(
            fn ($lesson) => $course->lessonIsReleasedTo($lesson, auth()->user())
        )->values());

        $isEnrolled = false;
        if (auth()->check()) {
            $isEnrolled = auth()->user()->hasEnrolled($course->id)
                || auth()->user()->isAdmin()
                || $course->instructor_id === auth()->id();
        }

        $ratingCount = $course->reviews()->count();

        return view('catalog.show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
            'reviews' => $course->reviews()->with('user:id,name')->latest()->limit(20)->get(),
            'ratingCount' => $ratingCount,
            'ratingAvg' => $ratingCount ? round((float) $course->reviews()->avg('rating'), 1) : null,
            'myReview' => auth()->check() ? $course->reviews()->where('user_id', auth()->id())->first() : null,
        ]);
    }
}
