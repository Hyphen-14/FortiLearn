<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SectionController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        Gate::authorize('update', $course);

        $data = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        $course->flagForReReview($request, 'section_added');

        $section = new Section([
            'title' => $data['title'],
            'section_order' => ($course->sections()->max('section_order') ?? 0) + 1,
        ]);
        $course->sections()->save($section);

        return back()->with('success', 'Bab baru berhasil ditambahkan.');
    }

    public function update(Request $request, Section $section): RedirectResponse
    {
        Gate::authorize('update', $section->course);

        $data = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:150'],
        ]);

        $section->course->flagForReReview($request, 'section_renamed');
        $section->update(['title' => $data['title']]);

        return back()->with('success', 'Judul bab diperbarui.');
    }

    /** Lessons inside the section are kept (section_id becomes NULL); only the heading is removed. */
    public function destroy(Section $section): RedirectResponse
    {
        Gate::authorize('update', $section->course);

        $section->delete();

        return back()->with('success', 'Bab dihapus. Materi di dalamnya tetap tersimpan.');
    }
}
