<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Services\FileUploadService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class CourseController extends Controller
{
    /** Instructor dashboard: only the instructor's own courses are ever queried (anti-IDOR by scoping). */
    public function index(): View
    {
        $courses = Course::withCount(['lessons', 'enrollments'])
            ->where('instructor_id', auth()->id())
            ->latest()
            ->get();

        return view('instructor.courses.index', [
            'courses' => $courses,
            'stats' => [
                'total' => $courses->count(),
                'published' => $courses->where('status', 'published')->count(),
                'pending' => $courses->where('status', 'pending_approval')->count(),
                'students' => (int) $courses->sum('enrollments_count'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('instructor.courses.create');
    }

    public function store(CourseRequest $request, FileUploadService $uploader): RedirectResponse
    {
        $data = $request->validated();
        $thumbnailPath = null;

        if ($request->hasFile('thumbnail')) {
            try {
                $thumbnailPath = $uploader->storeSecureLessonFile($request->file('thumbnail'), 'image')['file_path'];
            } catch (InvalidArgumentException $e) {
                $this->auditRejectedUpload($request, 'thumbnail', $e);

                return back()->withInput()->withErrors(['thumbnail' => $e->getMessage()]);
            }
        }

        $course = new Course([
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'],
            'price' => $data['price'],
        ]);
        // Server-controlled attributes (never from request input)
        $course->instructor_id = $request->user()->id;
        $course->status = 'draft';
        $course->slug = $this->uniqueSlug($data['title']);
        $course->thumbnail = $thumbnailPath;
        $course->save();

        return redirect()->route('instructor.courses.edit', $course)
            ->with('success', 'Course berhasil dibuat sebagai draft. Tambahkan bab dan materi pembelajaran di bawah ini.');
    }

    public function edit(Course $course): View
    {
        Gate::authorize('update', $course);

        $course->load(['sections.lessons', 'lessons']);

        return view('instructor.courses.edit', [
            'course' => $course,
            'looseLessons' => $course->lessons->whereNull('section_id'),
        ]);
    }

    public function update(CourseRequest $request, Course $course, FileUploadService $uploader): RedirectResponse
    {
        Gate::authorize('update', $course);

        $data = $request->validated();
        $newThumbnail = null;

        if ($request->hasFile('thumbnail')) {
            try {
                $newThumbnail = $uploader->storeSecureLessonFile($request->file('thumbnail'), 'image')['file_path'];
            } catch (InvalidArgumentException $e) {
                $this->auditRejectedUpload($request, 'thumbnail', $e);

                return back()->withInput()->withErrors(['thumbnail' => $e->getMessage()]);
            }
        }

        $wasPublished = $course->status === 'published';
        $course->flagForReReview($request, 'metadata');

        $course->fill([
            'title' => $data['title'],
            'category' => $data['category'],
            'description' => $data['description'],
            'price' => $data['price'],
        ]);

        if ($newThumbnail !== null) {
            $uploader->delete($course->thumbnail);
            $course->thumbnail = $newThumbnail;
        }

        $course->save();

        return back()->with('success', $wasPublished
            ? 'Metadata diperbarui. Course dikirim ulang ke admin dan tidak tampil di katalog sampai disetujui lagi.'
            : 'Metadata course berhasil diperbarui.');
    }

    /** Send a draft/rejected course to the admin for moderation. Instructors can never self-publish. */
    public function submit(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        if (! in_array($course->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Hanya course berstatus draft atau ditolak yang dapat diajukan.');
        }

        if (! $course->lessons()->exists()) {
            return back()->with('error', 'Tambahkan minimal satu materi sebelum mengajukan course.');
        }

        $course->status = 'pending_approval';
        $course->save();

        return back()->with('success', 'Course diajukan ke admin untuk ditinjau.');
    }

    public function destroy(Request $request, Course $course, FileUploadService $uploader): RedirectResponse
    {
        Gate::authorize('delete', $course);

        // Financial integrity: never delete content that students already paid for.
        if ($course->enrollments()->exists()) {
            Audit::record('course.delete_blocked', $request, ['course_id' => $course->id]);

            return back()->with('error', 'Course tidak dapat dihapus karena sudah memiliki siswa terdaftar.');
        }

        foreach ($course->lessons as $lesson) {
            $uploader->delete($lesson->file_path);
        }
        $uploader->delete($course->thumbnail);

        $course->delete();

        return redirect()->route('instructor.courses.index')
            ->with('success', 'Course beserta seluruh materinya berhasil dihapus.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::limit(Str::slug($title), 100, '') ?: 'course';

        return $base . '-' . Str::lower(Str::random(6));
    }

    private function auditRejectedUpload(Request $request, string $slot, InvalidArgumentException $e): void
    {
        $name = $request->file($slot)?->getClientOriginalName() ?? '';

        Audit::record('file.upload_rejected', $request, [
            'slot' => $slot,
            'reason' => $e->getMessage(),
            'client_filename' => Str::limit(preg_replace('/[^\x20-\x7E]/', '?', $name), 100, ''),
        ]);
    }
}
