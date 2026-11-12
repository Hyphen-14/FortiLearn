<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;

class DummyMediaGenerator
{
    /**
     * Ensure that a physical dummy MP4 or PDF file exists in private storage for the given lesson.
     */
    public static function ensureLessonMediaExists(Course $course, Lesson $lesson): string
    {
        $disk = Storage::disk('local');
        $rawPath = $lesson->file_path ?: ('private/courses/sample-' . $course->id . '-' . $lesson->lesson_order . '.' . ($lesson->content_type === 'video' ? 'mp4' : 'pdf'));
        // Storage::disk('local') root is storage/app/private, so strip leading 'private/' to land in storage/app/private/courses/...
        $diskRelativePath = preg_replace('#^private/#', '', ltrim($rawPath, '/')) ?: $rawPath;

        if ($lesson->content_type === 'pdf') {
            $pdfBytes = self::buildValidPdfDocument(
                $course->title,
                $course->category ?? 'Program Profesional',
                $lesson->lesson_order . '. ' . $lesson->title,
                $course->instructor->name ?? 'Instruktur FortiLearn'
            );
            $disk->put($diskRelativePath, $pdfBytes);
        } else {
            if (! $disk->exists($diskRelativePath)) {
                $mp4Bytes = self::buildValidMp4Container($course->title, $lesson->title);
                $disk->put($diskRelativePath, $mp4Bytes);
            }
        }

        return $diskRelativePath;
    }

    /**
     * Build a 100% valid PDF 1.4 document that renders natively in browser PDF viewers.
     */
    public static function buildValidPdfDocument(
        string $courseTitle,
        string $category,
        string $lessonTitle,
        string $instructorName
    ): string {
        $escape = fn (string $text): string => str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            preg_replace('/[^\x20-\x7E]/', '', $text) ?? ''
        );

        $cTitle = $escape($courseTitle);
        $cat = $escape(strtoupper($category));
        $lTitle = $escape($lessonTitle);
        $inst = $escape($instructorName);

        $lines = [
            'BT',
            '/F1 10 Tf',
            '50 760 Td',
            '(FORTILEARN ACADEMY - MODUL PANDUAN RESMI) Tj',
            '0 -18 Td',
            '/F1 9 Tf',
            '(Kategori Bidang: ' . $cat . '  |  Instruktur: ' . $inst . ') Tj',
            '0 -32 Td',
            '/F1 16 Tf',
            '(' . substr($cTitle, 0, 62) . ') Tj',
            '0 -26 Td',
            '/F1 13 Tf',
            '(Modul: ' . substr($lTitle, 0, 68) . ') Tj',
            '0 -30 Td',
            '/F1 10 Tf',
            '(--------------------------------------------------------------------------------) Tj',
            '0 -24 Td',
            '(Ringkasan Materi & Panduan Praktik:) Tj',
            '0 -20 Td',
            '(1. Pelajari konsep inti yang dijelaskan pada modul video pendamping.) Tj',
            '0 -16 Td',
            '(2. Terapkan struktur arsitektur dan studi kasus pada proyek latihan Anda.) Tj',
            '0 -16 Td',
            '(3. Gunakan daftar periksa kualitas sebelum mempublikasikan hasil kerja.) Tj',
            '0 -16 Td',
            '(4. Seluruh materi ini dilindungi hak akses member terdaftar FortiLearn.) Tj',
            '0 -36 Td',
            '(Status Dokumen: Terverifikasi & Aktif Seumur Hidup) Tj',
            'ET',
        ];

        $streamContent = implode("\n", $lines);
        $streamLen = strlen($streamContent);

        $objects = [];
        $objects[1] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
        $objects[2] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
        $objects[3] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n";
        $objects[4] = "4 0 obj\n<< /Length {$streamLen} >>\nstream\n{$streamContent}\nendstream\nendobj\n";
        $objects[5] = "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $num => $objBody) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $objBody;
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 6\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    /**
     * Build a valid MP4 ISO Base Media container header with metadata payload.
     */
    public static function buildValidMp4Container(string $courseTitle, string $lessonTitle): string
    {
        // Valid ISO Base Media File Format ftyp box (ftypisom)
        $ftyp = hex2bin('000000186674797069736f6d0000020069736f6d69736f32');
        $metaText = "FortiLearn Dummy Video Stream | {$courseTitle} | {$lessonTitle}";
        $freePayload = str_pad($metaText, 256, "\0");
        $freeBoxLen = pack('N', strlen($freePayload) + 8);
        $freeBox = $freeBoxLen . 'free' . $freePayload;

        return $ftyp . $freeBox;
    }
}
