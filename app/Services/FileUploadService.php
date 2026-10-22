<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileUploadService
{
    /**
     * Allowed MIME types and their validated extensions.
     */
    private const ALLOWED_MIME_TYPES = [
        'video/mp4' => 'mp4',
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /**
     * Maximum allowed file sizes in bytes (e.g. 50MB for video, 10MB for pdf).
     */
    private const MAX_VIDEO_SIZE = 52428800; // 50MB
    private const MAX_DOC_SIZE = 10485760;   // 10MB

    /**
     * Inspect file magic bytes, validate size, sanitize name, and store in private storage.
     *
     * @throws InvalidArgumentException
     */
    public function storeSecureLessonFile(UploadedFile $file, string $expectedType): array
    {
        if (! $file->isValid()) {
            throw new InvalidArgumentException('File upload gagal atau korup.');
        }

        // 1. Size Validation
        $maxSize = ($expectedType === 'video') ? self::MAX_VIDEO_SIZE : self::MAX_DOC_SIZE;
        if ($file->getSize() > $maxSize) {
            throw new InvalidArgumentException('Ukuran file melebihi batas maksimum yang diizinkan.');
        }

        // 2. Strict Magic Bytes Inspection using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        if (! array_key_exists($realMime, self::ALLOWED_MIME_TYPES)) {
            throw new InvalidArgumentException("Tipe MIME ($realMime) tidak diizinkan. Hanya MP4, PDF, JPG, PNG.");
        }

        $validatedExtension = self::ALLOWED_MIME_TYPES[$realMime];

        // Ensure expected type matches detected MIME
        if ($expectedType === 'video' && $validatedExtension !== 'mp4') {
            throw new InvalidArgumentException('Materi video harus berformat MP4 yang valid.');
        }
        if ($expectedType === 'pdf' && $validatedExtension !== 'pdf') {
            throw new InvalidArgumentException('Materi dokumen harus berformat PDF yang valid.');
        }

        // 3. Prevent Embedded Executables / Webshell Payload Inspection
        $fileHeader = file_get_contents($file->getRealPath(), false, null, 0, 1024);
        if (str_contains($fileHeader, '<?php') || str_contains($fileHeader, '<?=') || str_contains($fileHeader, '<script')) {
            throw new InvalidArgumentException('File mengandung signature kode berbahaya (Webshell/Script injection dicegah).');
        }

        // 4. Random UUID Filename (mencegah path traversal dan direct guess attack)
        $secureFilename = Str::uuid()->toString() . '.' . $validatedExtension;

        // 5. Store in private directory (NEVER inside public/ folder)
        $storagePath = 'private/courses/' . $secureFilename;
        Storage::disk('local')->putFileAs('private/courses', $file, $secureFilename);

        // Sanitize original filename for display
        $originalName = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', $file->getClientOriginalName());

        return [
            'file_path' => $storagePath,
            'original_filename' => $originalName,
            'mime_type' => $realMime,
            'extension' => $validatedExtension,
        ];
    }
}

