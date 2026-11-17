<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Self-review finding (Chapter 5): an approved course could be edited by its instructor
 * and stay published, so the new content reached the catalog without admin review.
 * Any content change now sends the course back to the moderation queue, while students
 * who already paid keep access to the lessons that were approved.
 */
class CourseReModerationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $instructor;

    private User $enrolled;

    private User $outsider;

    private Course $course;

    private Lesson $approvedLesson;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->admin = $this->makeUser('admin@fortilearn.test', 'admin');
        $this->instructor = $this->makeUser('instructor@fortilearn.test', 'instructor');
        $this->enrolled = $this->makeUser('enrolled@fortilearn.test', 'student');
        $this->outsider = $this->makeUser('outsider@fortilearn.test', 'student');

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Kursus Yang Sudah Disetujui',
            'slug' => 'kursus-disetujui',
            'description' => 'Deskripsi asli yang sudah direview oleh admin FortiLearn.',
            'price' => 100000,
            'category' => 'Frontend',
        ]);
        $this->course->status = 'pending_approval';
        $this->course->save();

        $this->approvedLesson = $this->storeLesson('Materi Disetujui');

        $this->actingAs($this->admin)->patch(route('admin.courses.approve', $this->course));
        $this->course->refresh();

        Enrollment::create(['user_id' => $this->enrolled->id, 'course_id' => $this->course->id, 'enrolled_at' => now()]);
        $this->travel(5)->seconds();
    }

    private function makeUser(string $email, string $role): User
    {
        $user = User::create(['name' => 'User '.$email, 'email' => $email, 'password' => 'Password123!']);
        $user->role = $role;
        $user->is_active = true;
        $user->save();

        return $user;
    }

    private function pdf(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('modul.pdf', "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n");
    }

    private function storeLesson(string $title): Lesson
    {
        $this->actingAs($this->instructor)->post(route('instructor.lessons.store', $this->course), [
            'title' => $title, 'content_type' => 'pdf', 'file' => $this->pdf(),
        ]);

        return Lesson::where('title', $title)->firstOrFail();
    }

    private function editMetadata(array $overrides = [])
    {
        return $this->actingAs($this->instructor)->put(route('instructor.courses.update', $this->course), array_merge([
            'title' => 'Judul Diganti Setelah Terbit',
            'category' => 'Frontend',
            'description' => 'Deskripsi baru yang belum pernah dilihat admin sama sekali.',
            'price' => 999000,
        ], $overrides));
    }

    public function test_setup_course_is_published_and_approved(): void
    {
        $this->assertSame('published', $this->course->status);
        $this->assertNotNull($this->course->approved_at);
    }

    public function test_editing_metadata_of_published_course_sends_it_back_to_review(): void
    {
        $this->editMetadata()->assertRedirect();

        $this->assertSame('pending_approval', $this->course->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'course.resubmitted_after_edit', 'user_id' => $this->instructor->id]);

        $this->get(route('catalog.index'))->assertDontSee('Judul Diganti Setelah Terbit');
        $this->actingAs($this->outsider)->get(route('catalog.show', $this->course->slug))->assertNotFound();
    }

    public function test_adding_a_lesson_to_published_course_sends_it_back_to_review(): void
    {
        $this->storeLesson('Materi Baru Tanpa Review');

        $this->assertSame('pending_approval', $this->course->fresh()->status);
    }

    public function test_adding_or_renaming_a_section_sends_it_back_to_review(): void
    {
        $this->actingAs($this->instructor)->post(route('instructor.sections.store', $this->course), ['title' => 'Bab Baru']);

        $this->assertSame('pending_approval', $this->course->fresh()->status);
    }

    public function test_new_buyers_cannot_purchase_a_course_waiting_for_re_review(): void
    {
        $this->editMetadata();

        $this->actingAs($this->outsider)->post(route('checkout.process', $this->course));
        $this->assertDatabaseMissing('enrollments', ['user_id' => $this->outsider->id]);
    }

    public function test_enrolled_student_keeps_access_to_approved_lessons_only(): void
    {
        $newLesson = $this->storeLesson('Materi Baru Tanpa Review');

        $this->actingAs($this->enrolled)->get(route('catalog.my-courses'))->assertSee($this->course->title);
        $this->actingAs($this->enrolled)->get(route('catalog.show', $this->course->slug))
            ->assertOk()
            ->assertSee('Materi Disetujui')
            ->assertDontSee('Materi Baru Tanpa Review');

        $this->actingAs($this->enrolled)->get(route('lesson.stream', [$this->course->id, $this->approvedLesson->id]))->assertOk();
        $this->actingAs($this->enrolled)->get(route('lesson.stream', [$this->course->id, $newLesson->id]))->assertNotFound();
    }

    public function test_re_approval_releases_the_new_lesson_to_enrolled_students(): void
    {
        $newLesson = $this->storeLesson('Materi Baru Tanpa Review');
        $this->travel(5)->seconds();

        $this->actingAs($this->admin)->patch(route('admin.courses.approve', $this->course))->assertRedirect();

        $this->assertSame('published', $this->course->fresh()->status);
        $this->actingAs($this->enrolled)->get(route('lesson.stream', [$this->course->id, $newLesson->id]))->assertOk();
    }

    public function test_rejecting_the_edit_keeps_unreviewed_lesson_hidden(): void
    {
        $newLesson = $this->storeLesson('Materi Baru Tanpa Review');
        $this->travel(5)->seconds();

        $this->actingAs($this->admin)->patch(route('admin.courses.reject', $this->course), ['reason' => 'Materi baru tidak relevan.']);

        $this->actingAs($this->enrolled)->get(route('lesson.stream', [$this->course->id, $this->approvedLesson->id]))->assertOk();
        $this->actingAs($this->enrolled)->get(route('lesson.stream', [$this->course->id, $newLesson->id]))->assertNotFound();
    }

    public function test_instructor_is_warned_before_editing_a_published_course(): void
    {
        $this->actingAs($this->instructor)->get(route('instructor.courses.edit', $this->course))
            ->assertOk()
            ->assertSee('akan mengirim course kembali ke admin');
    }

    public function test_admin_review_page_marks_lessons_added_after_approval(): void
    {
        $this->storeLesson('Materi Baru Tanpa Review');

        $this->actingAs($this->admin)->get(route('admin.courses.show', $this->course))
            ->assertOk()
            ->assertSeeInOrder(['Materi Baru Tanpa Review', 'Baru sejak terakhir disetujui']);
    }

    public function test_instructor_and_admin_can_still_preview_unreviewed_lesson(): void
    {
        $newLesson = $this->storeLesson('Materi Baru Tanpa Review');

        $this->actingAs($this->instructor)->get(route('lesson.stream', [$this->course->id, $newLesson->id]))->assertOk();
        $this->actingAs($this->admin)->get(route('lesson.stream', [$this->course->id, $newLesson->id]))->assertOk();
    }

    public function test_editing_a_draft_course_does_not_change_its_status(): void
    {
        $this->course->status = 'draft';
        $this->course->save();

        $this->editMetadata();

        $this->assertSame('draft', $this->course->fresh()->status);
        $this->assertDatabaseMissing('audit_logs', ['event_type' => 'course.resubmitted_after_edit']);
    }

    public function test_deleting_a_lesson_does_not_trigger_re_review(): void
    {
        $extra = $this->storeLesson('Materi Tambahan');
        $this->actingAs($this->admin)->patch(route('admin.courses.approve', $this->course));

        $this->actingAs($this->instructor)->delete(route('instructor.lessons.destroy', $extra));

        $this->assertSame('published', $this->course->fresh()->status);
        $this->assertFalse(Storage::disk('local')->exists(FileUploadService::toDiskPath($extra->file_path)));
    }
}
