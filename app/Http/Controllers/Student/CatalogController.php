<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\DummyMediaGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            $enrolledCourses = Course::with(['instructor', 'lessons'])
                ->whereIn('id', $enrolledCourseIds)
                ->where('status', 'published')
                ->get();
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
            ->where('status', 'published')
            ->firstOrFail();

        $isEnrolled = false;
        if (auth()->check()) {
            $isEnrolled = auth()->user()->hasEnrolled($course->id)
                || auth()->user()->isAdmin()
                || $course->instructor_id === auth()->id();
        }

        return view('catalog.show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
        ]);
    }

    /**
     * Stream or download a protected lesson file (PDF / MP4) from private storage.
     * Security: Enforces CoursePolicy@viewContent to prevent unauthorized IDOR access.
     */
    public function streamLesson(Request $request, string $slug, Lesson $lesson): StreamedResponse
    {
        $course = Course::with('instructor')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        if ((int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }

        // Strict Policy Check: Only enrolled students, course instructor, or admin can access
        Gate::authorize('viewContent', $course);

        $relativePath = DummyMediaGenerator::ensureLessonMediaExists($course, $lesson);
        $disk = Storage::disk('local');

        $mimeType = $lesson->content_type === 'pdf' ? 'application/pdf' : 'video/mp4';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        $safeFilename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $lesson->original_filename ?: ('modul-' . $lesson->id . ($lesson->content_type === 'pdf' ? '.pdf' : '.mp4')));

        return $disk->response($relativePath, $safeFilename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => $disposition . '; filename="' . $safeFilename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
