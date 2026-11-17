<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseModerationController extends Controller
{
    public const STATUSES = [
        'pending_approval' => 'Menunggu Review',
        'published' => 'Terbit',
        'rejected' => 'Ditolak',
        'draft' => 'Draft',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ]);
        $status = $validated['status'] ?? 'pending_approval';

        return view('admin.courses.index', [
            'status' => $status,
            'statuses' => self::STATUSES,
            'counts' => Course::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'courses' => Course::where('status', $status)
                ->with(['instructor:id,name,email', 'reviewer:id,name'])
                ->withCount('lessons')
                ->oldest('updated_at')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Course $course): View
    {
        Gate::authorize('moderate', Course::class);

        $course->load(['instructor:id,name,email', 'reviewer:id,name', 'lessons']);

        return view('admin.courses.show', ['course' => $course, 'statuses' => self::STATUSES]);
    }

    public function approve(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('moderate', Course::class);

        return $this->transition($request, $course, 'published', null, 'admin.course_approved',
            "Course \"{$course->title}\" diterbitkan dan tampil di katalog.");
    }

    public function reject(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('moderate', Course::class);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        return $this->transition($request, $course, 'rejected', $validated['reason'], 'admin.course_rejected',
            "Course \"{$course->title}\" ditolak. Alasan dikirim ke instruktur.");
    }

    /**
     * Conditional UPDATE ... WHERE status = 'pending_approval': the check and the write are one
     * atomic statement, so two admins (or a double-click) cannot both act on the same course.
     */
    private function transition(Request $request, Course $course, string $to, ?string $reason, string $event, string $message): RedirectResponse
    {
        $updated = Course::whereKey($course->id)
            ->where('status', 'pending_approval')
            ->update([
                'status' => $to,
                'rejection_reason' => $reason,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return back()->with('error', 'Course ini tidak sedang menunggu review, jadi tidak bisa diproses.');
        }

        Audit::record($event, $request, array_filter([
            'course_id' => $course->id,
            'title' => $course->title,
            'reason' => $reason,
        ]));

        return redirect()->route('admin.courses.index')->with('success', $message);
    }
}
