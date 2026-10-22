<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Display the course catalog with search and filtering.
     * Security: Eloquent parameter binding strictly prevents SQL Injection.
     */
    public function index(Request $request): View
    {
        $query = Course::query()
            ->with(['instructor', 'lessons'])
            ->where('status', 'published');

        // Safe Search: Parameterized LIKE query
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Safe Sorting Whitelist
        $sort = $request->query('sort');
        if ($sort === 'price_asc') {
            $query->orderBy('price', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest();
        }

        $courses = $query->paginate(9)->withQueryString();

        return view('catalog.index', [
            'courses' => $courses,
            'search' => $search,
            'sort' => $sort,
        ]);
    }

    /**
     * Display a specific course detail page.
     */
    public function show(string $slug): View
    {
        $course = Course::with(['instructor', 'lessons'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $isEnrolled = false;
        if (auth()->check()) {
            $isEnrolled = auth()->user()->hasEnrolled($course->id);
        }

        return view('catalog.show', [
            'course' => $course,
            'isEnrolled' => $isEnrolled,
        ]);
    }
}

