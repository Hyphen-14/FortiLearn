<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\LessonRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\FileUploadService;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use InvalidArgumentException;

class LessonController extends Controller
{
    public function store(LessonRequest $request, Course $course, FileUploadService $uploader): RedirectResponse
    {
        Gate::authorize('update', $course);

        $data = $request->validated();

        try {
            $stored = $uploader->storeSecureLessonFile($request->file('file'), $data['content_type']);
        } catch (InvalidArgumentException $e) {
            $name = $request->file('file')->getClientOriginalName();

            Audit::record('file.upload_rejected', $request, [
                'slot' => $data['content_type'],
                'course_id' => $course->id,
                'reason' => $e->getMessage(),
                'client_filename' => Str::limit(preg_replace('/[^\x20-\x7E]/', '?', $name), 100, ''),
            ]);

            return back()->withInput()->withErrors(['file' => $e->getMessage()]);
        }

        $course->flagForReReview($request, 'lesson_added');

        $lesson = new Lesson([
            'title' => $data['title'],
            'lesson_order' => ($course->lessons()->max('lesson_order') ?? 0) + 1,
            'content_type' => $data['content_type'],
            'file_path' => $stored['file_path'],
            'original_filename' => $stored['original_filename'],
            'duration_seconds' => ((int) ($data['duration_minutes'] ?? 0)) * 60,
        ]);
        $lesson->course_id = $course->id;
        $lesson->section_id = $data['section_id'] ?? null;
        $lesson->save();

        return back()->with('success', 'Materi "' . $lesson->title . '" berhasil diunggah dengan aman.');
    }

    public function destroy(Lesson $lesson, FileUploadService $uploader): RedirectResponse
    {
        Gate::authorize('update', $lesson->course);

        $uploader->delete($lesson->file_path);
        $lesson->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }
}
