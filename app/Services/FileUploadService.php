<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class FileUploadService
{
    /** Allowed real MIME types => canonical extension. */
    private const ALLOWED_MIME_TYPES = [
        'video/mp4' => 'mp4',
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /** Which extension(s) are acceptable for each upload slot. */
    private const SLOT_EXTENSIONS = [
        'video' => ['mp4'],
        'pdf' => ['pdf'],
        'image' => ['jpg', 'png'],
    ];

    private const MAX_VIDEO_SIZE = 52428800; // 50MB
    private const MAX_DOC_SIZE = 10485760;   // 10MB (PDF & images)

    /** Disk-relative directory (disk "local" root is storage/app/private). */
    private const DISK_DIR = 'courses';

    /** Prefix kept in the database column for readability / backwards compatibility. */
    private const DB_PREFIX = 'private/';

    private const FORBIDDEN_SIGNATURES = ['<?php', '<?=', '<script'];

    /**
     * Validate and store an uploaded file.
     *
     * @param  string  $expectedType  one of: video | pdf | image
     * @return array{file_path:string, original_filename:string, mime_type:string, extension:string}
     *
     * @throws InvalidArgumentException
     */
    public function storeSecureLessonFile(UploadedFile $file, string $expectedType): array
    {
        if (! isset(self::SLOT_EXTENSIONS[$expectedType])) {
            throw new InvalidArgumentException('Jenis unggahan tidak dikenal.');
        }

        if (! $file->isValid() || ! is_file($file->getRealPath())) {
            throw new InvalidArgumentException('File upload gagal atau korup.');
        }

        // 1. Size validation (server-side, checks both file size and disk footprint)
        $clientReportedSize = (int) $file->getSize();
        $realSize = filesize($file->getRealPath());
        $maxSize = $expectedType === 'video' ? self::MAX_VIDEO_SIZE : self::MAX_DOC_SIZE;
        $limitMb = (int) ($maxSize / 1048576);

        if ($clientReportedSize > $maxSize || ($realSize !== false && $realSize > $maxSize)) {
            throw new InvalidArgumentException("Ukuran file melebihi batas maksimum yang diizinkan. (Maksimal {$limitMb}MB)");
        }

        if (($realSize === 0 && $clientReportedSize === 0) || ($realSize === false && $clientReportedSize <= 0)) {
            throw new InvalidArgumentException('File kosong tidak diperbolehkan.');
        }

        // 2. Prevent Dangerous Extensions & Script Masquerading
        $clientExt = strtolower($file->getClientOriginalExtension());
        $dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'sh', 'exe', 'bat', 'pl', 'py', 'cgi'];
        if (in_array($clientExt, $dangerousExtensions, true)) {
            throw new InvalidArgumentException('Ekstensi file berbahaya terdeteksi dan diblokir.');
        }

        // 3. Strict Magic Bytes Inspection using finfo (never trust client-supplied MIME or extension)
        $realMime = null;
        $filePath = $file->getRealPath();
        if ($filePath && file_exists($filePath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detected = @finfo_file($finfo, $filePath);
            finfo_close($finfo);
            if ($detected !== false) {
                $realMime = $detected;
            }
        }

        if (! $realMime && $filePath && file_exists($filePath)) {
            $headerBytes = @file_get_contents($filePath, false, null, 0, 4096);
            if ($headerBytes !== false && $headerBytes !== '') {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $realMime = finfo_buffer($finfo, $headerBytes);
                finfo_close($finfo);
            }
        }

        if (! $realMime) {
            $realMime = $file->getMimeType();
        }

        if ($realMime === false || ! array_key_exists($realMime, self::ALLOWED_MIME_TYPES)) {
            throw new InvalidArgumentException('Tipe file tidak diizinkan. Hanya MP4, PDF, JPG, PNG yang valid.');
        }

        $validatedExtension = self::ALLOWED_MIME_TYPES[$realMime];

        // 3. Detected type must match the requested slot
        if (! in_array($validatedExtension, self::SLOT_EXTENSIONS[$expectedType], true)) {
            $messages = [
                'video' => 'Materi video harus berformat MP4 yang valid.',
                'pdf' => 'Materi dokumen harus berformat PDF yang valid.',
                'image' => 'Thumbnail harus berformat JPG atau PNG yang valid.',
            ];
            throw new InvalidArgumentException($messages[$expectedType]);
        }

        // 4. Scan for embedded script / webshell signatures (header check + full streaming scan)
        $fileHeader = ($filePath && file_exists($filePath)) ? @file_get_contents($filePath, false, null, 0, 2048) : '';
        if ($fileHeader === false && method_exists($file, 'getContent')) {
            try {
                $fileHeader = substr($file->getContent(), 0, 2048);
            } catch (\Throwable) {
                $fileHeader = '';
            }
        }

        if ($fileHeader && (str_contains($fileHeader, '<?php') || str_contains($fileHeader, '<?=') || str_contains($fileHeader, '<script'))) {
            throw new InvalidArgumentException('File mengandung signature kode berbahaya (Webshell/Script injection dicegah).');
        }

        if ($filePath && file_exists($filePath) && $this->containsForbiddenSignature($filePath)) {
            throw new InvalidArgumentException('File mengandung signature kode berbahaya (Webshell/Script injection dicegah).');
        }

        // 5. Images must be decodable as real images
        if ($expectedType === 'image' && @getimagesize($file->getRealPath()) === false) {
            throw new InvalidArgumentException('Thumbnail bukan gambar yang valid.');
        }

        // 6. Random UUID filename — the client-supplied name never touches the filesystem
        $secureFilename = Str::uuid()->toString() . '.' . $validatedExtension;

        // 7. Private storage: storage/app/private/courses/<uuid>.<ext>
        Storage::disk('local')->putFileAs(self::DISK_DIR, $file, $secureFilename);

        return [
            'file_path' => self::DB_PREFIX . self::DISK_DIR . '/' . $secureFilename,
            'original_filename' => $this->sanitizeDisplayName($file->getClientOriginalName(), $validatedExtension),
            'mime_type' => $realMime,
            'extension' => $validatedExtension,
        ];
    }

    /**
     * Delete a previously stored file. Only paths inside private/courses/ with a UUID name are honoured,
     * so a tampered DB value can never delete arbitrary files.
     */
    public function delete(?string $dbPath): void
    {
        $diskPath = self::toDiskPath($dbPath);
        if ($diskPath !== null && Storage::disk('local')->exists($diskPath)) {
            Storage::disk('local')->delete($diskPath);
        }
    }

    /**
     * Convert a DB path (private/courses/<uuid>.ext) to a disk-relative path, or null when the
     * value is not an upload produced by this service (e.g. seeded sample media).
     */
    public static function toDiskPath(?string $dbPath): ?string
    {
        if ($dbPath === null || $dbPath === '') {
            return null;
        }

        $pattern = '#^private/courses/[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.(mp4|pdf|jpg|png)$#';
        if (! preg_match($pattern, $dbPath)) {
            return null;
        }

        return substr($dbPath, strlen(self::DB_PREFIX));
    }

    /**
     * Stream-scan a file in chunks (with overlap) so signatures split across chunk borders are still caught.
     */
    private function containsForbiddenSignature(string $path): bool
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return true; // fail closed
        }

        $overlap = max(array_map('strlen', self::FORBIDDEN_SIGNATURES)) - 1;
        $carry = '';

        try {
            while (! feof($handle)) {
                $chunk = fread($handle, 1048576);
                if ($chunk === false) {
                    return true;
                }
                $buffer = strtolower($carry . $chunk);
                foreach (self::FORBIDDEN_SIGNATURES as $signature) {
                    if (str_contains($buffer, $signature)) {
                        return true;
                    }
                }
                $carry = substr($buffer, -$overlap);
            }
        } finally {
            fclose($handle);
        }

        return false;
    }

    /**
     * Name used only for display (never for storage). Strips path parts and unsafe characters.
     */
    private function sanitizeDisplayName(string $clientName, string $extension): string
    {
        $base = pathinfo(basename(str_replace('\\', '/', $clientName)), PATHINFO_FILENAME);
        $base = preg_replace('/[^a-zA-Z0-9_\- ]/', '', $base) ?? '';
        $base = trim(Str::limit($base, 80, ''));

        return ($base !== '' ? $base : 'materi') . '.' . $extension;
    }
}
