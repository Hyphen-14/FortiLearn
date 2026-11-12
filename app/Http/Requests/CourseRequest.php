<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating / editing a course (metadata only).
 * Fields such as instructor_id, status and slug are intentionally NOT accepted from the client.
 */
class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_active && in_array(auth()->user()->role, ['instructor', 'admin'], true);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:150'],
            'category' => ['required', 'string', 'max:60', 'regex:/^[\pL\pN &\/\-]+$/u'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            // Real validation (magic bytes, size) happens in FileUploadService; this is only a cheap pre-filter.
            'thumbnail' => ['nullable', 'file', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul course wajib diisi.',
            'title.min' => 'Judul course minimal 5 karakter.',
            'category.regex' => 'Kategori hanya boleh berisi huruf, angka, spasi, &, / dan -.',
            'description.min' => 'Deskripsi minimal 20 karakter.',
            'price.integer' => 'Harga harus berupa bilangan bulat (Rupiah).',
            'price.max' => 'Harga maksimum Rp 10.000.000.',
            'thumbnail.max' => 'Ukuran thumbnail maksimum 10MB.',
        ];
    }
}
