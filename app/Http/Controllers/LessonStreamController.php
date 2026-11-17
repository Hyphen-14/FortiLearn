<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Services\DummyMediaGenerator;
use App\Services\FileUploadService;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Secure private streaming of lesson media (Jobdesk 2).
 *
 *  - Files live in storage/app/private/courses and are NEVER reachable through a public URL.
 *  - Every request is authorised with CoursePolicy@viewContent (anti-IDOR).
 *  - The lesson must belong to the course in the URL (blocks cross-course ID swapping).
 *  - The response is chunked (8KB) and supports HTTP Range so video seeking works.
 *  - Physical paths are never exposed; the download name is sanitised.
 */
class LessonStreamController extends Controller
{
    private const CHUNK_SIZE = 8192;

    /** GET /courses/{course}/stream/{lesson} */
    public function show(Request $request, Course $course, Lesson $lesson): Response
    {
        return $this->serve($request, $course, $lesson);
    }

    /** GET /courses/{slug}/lessons/{lesson}/stream (legacy URL used by the catalog page) */
    public function showBySlug(Request $request, string $slug, Lesson $lesson): Response
    {
        $course = Course::where('slug', $slug)->firstOrFail();

        return $this->serve($request, $course, $lesson);
    }

    private function serve(Request $request, Course $course, Lesson $lesson): Response
    {
        if ((int) $lesson->course_id !== (int) $course->id) {
            abort(404);
        }

        $user = $request->user();
        $isPrivileged = $user->isAdmin() || (int) $course->instructor_id === (int) $user->id;

        // Unpublished courses are only visible to their instructor / admin.
        if ($course->status !== 'published' && ! $isPrivileged) {
            abort(404);
        }

        $decision = Gate::inspect('viewContent', $course);
        if ($decision->denied()) {
            Audit::record('access.stream_denied', $request, [
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
            ]);
            $decision->authorize(); // aborts with 403
        }

        $diskPath = $this->resolveDiskPath($course, $lesson);
        $disk = Storage::disk('local');

        if ($diskPath === null || ! $disk->exists($diskPath)) {
            abort(404);
        }

        $absolute = $disk->path($diskPath);
        $size = filesize($absolute);
        $mime = $lesson->content_type === 'pdf' ? 'application/pdf' : 'video/mp4';

        [$start, $end, $status] = $this->parseRange($request->header('Range'), $size);
        if ($status === 416) {
            return response('', 416, ['Content-Range' => "bytes */{$size}"]);
        }

        $length = $end - $start + 1;
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';
        $safeName = $this->safeFilename($lesson);

        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => (string) $length,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => $disposition . '; filename="' . $safeName . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];
        if ($status === 206) {
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$size}";
        }

        return new StreamedResponse(function () use ($absolute, $start, $length) {
            $handle = fopen($absolute, 'rb');
            if ($handle === false) {
                return;
            }
            fseek($handle, $start);
            $remaining = $length;
            while ($remaining > 0 && ! feof($handle)) {
                $chunk = fread($handle, min(self::CHUNK_SIZE, $remaining));
                if ($chunk === false || $chunk === '') {
                    break;
                }
                echo $chunk;
                $remaining -= strlen($chunk);
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            fclose($handle);
        }, $status, $headers);
    }

    /**
     * Resolve the disk-relative path. Real uploads must match the UUID pattern created by FileUploadService.
     * Seeded demo lessons ("sample-*.mp4|pdf") are generated on demand by DummyMediaGenerator.
     */
    private function resolveDiskPath(Course $course, Lesson $lesson): ?string
    {
        $uploaded = FileUploadService::toDiskPath($lesson->file_path);
        if ($uploaded !== null) {
            return $uploaded;
        }

        // Demo / seeded media only: strict allow-list so a tampered DB value can never traverse out.
        if (preg_match('#^private/courses/[A-Za-z0-9_\-]+\.(mp4|pdf)$#', (string) $lesson->file_path)) {
            $course->loadMissing('instructor');

            return DummyMediaGenerator::ensureLessonMediaExists($course, $lesson);
        }

        return null;
    }

    /** @return array{0:int,1:int,2:int} [start, end, httpStatus] */
    private function parseRange(?string $header, int $size): array
    {
        if ($header === null || $size === 0) {
            return [0, max($size - 1, 0), 200];
        }

        if (! preg_match('/^bytes=(\d*)-(\d*)$/', trim($header), $m) || ($m[1] === '' && $m[2] === '')) {
            return [0, $size - 1, 200]; // ignore malformed / multi-range requests
        }

        if ($m[1] === '') {                       // suffix range: last N bytes
            $length = (int) $m[2];
            if ($length === 0) {
                return [0, 0, 416];
            }
            $start = max($size - $length, 0);
            $end = $size - 1;
        } else {
            $start = (int) $m[1];
            $end = $m[2] === '' ? $size - 1 : min((int) $m[2], $size - 1);
        }

        if ($start > $end || $start >= $size) {
            return [0, 0, 416];
        }

        return [$start, $end, 206];
    }

    private function safeFilename(Lesson $lesson): string
    {
        $ext = $lesson->content_type === 'pdf' ? '.pdf' : '.mp4';
        $name = $lesson->original_filename ?: ('modul-' . $lesson->id . $ext);

        return preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $name);
    }
}
