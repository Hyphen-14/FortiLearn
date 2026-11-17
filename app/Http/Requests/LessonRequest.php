<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_active && in_array(auth()->user()->role, ['instructor', 'admin'], true);
    }

    public function rules(): array
    {
        /** @var Course $course */
        $course = $this->route('course');

        return [
            'title' => ['required', 'string', 'min:3', 'max:150'],
            'content_type' => ['required', Rule::in(['video', 'pdf'])],
            // section must belong to THIS course (blocks cross-course / cross-tenant references)
            'section_id' => ['nullable', 'integer', Rule::exists('sections', 'id')->where('course_id', $course->id)],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'file' => ['required', 'file', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File materi wajib dipilih.',
            'file.max' => 'Ukuran file maksimum 50MB (video) atau 10MB (PDF).',
            'file.uploaded' => 'Upload gagal. Pastikan ukuran file tidak melebihi batas server (upload_max_filesize).',
            'section_id.exists' => 'Bab yang dipilih tidak valid untuk course ini.',
        ];
    }
}
