<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    /** Buyers & sales for the instructor's own courses only. Student emails are intentionally not exposed. */
    public function index(Request $request): View
    {
        $courses = Course::where('instructor_id', auth()->id())
            ->withCount('enrollments')
            ->orderBy('title')
            ->get(['id', 'title', 'price', 'status']);

        // Filter value must be one of the instructor's own course ids; anything else is ignored.
        $selected = $courses->firstWhere('id', (int) $request->query('course'));

        $revenue = WalletTransaction::whereIn('course_id', $courses->pluck('id'))
            ->where('type', 'purchase')->where('status', 'completed')
            ->selectRaw('course_id, SUM(amount) as total')->groupBy('course_id')
            ->pluck('total', 'course_id');

        $enrollments = Enrollment::with(['user:id,name', 'course:id,title,price'])
            ->whereIn('course_id', $selected ? [$selected->id] : $courses->pluck('id'))
            ->latest('enrolled_at')
            ->paginate(15)
            ->withQueryString();

        return view('instructor.sales', [
            'courses' => $courses,
            'selected' => $selected,
            'revenue' => $revenue,
            'enrollments' => $enrollments,
            'totalRevenue' => (float) $revenue->sum(),
        ]);
    }
}
