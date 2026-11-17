<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves course thumbnails from private storage. Thumbnails of published courses are public
 * (they appear in the catalog); drafts are visible to the owner / admin only.
 */
class CourseThumbnailController extends Controller
{
    public function show(Request $request, Course $course): Response
    {
        if (! $course->isAccessibleBy($request->user())) {
            abort(404);
        }

        $diskPath = FileUploadService::toDiskPath($course->thumbnail);
        $disk = Storage::disk('local');

        if ($diskPath === null || ! $disk->exists($diskPath)) {
            abort(404);
        }

        $mime = str_ends_with($diskPath, '.png') ? 'image/png' : 'image/jpeg';

        return response()->file($disk->path($diskPath), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    }
}
