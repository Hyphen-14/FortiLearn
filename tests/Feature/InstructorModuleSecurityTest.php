<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Jobdesk 2 - Instructor module, secure upload & private streaming.
 */
class InstructorModuleSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $instructorA;
    private User $instructorB;
    private User $student;
    private User $outsider;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->instructorA = $this->makeUser('ia@test.com', 'instructor');
        $this->instructorB = $this->makeUser('ib@test.com', 'instructor');
        $this->student = $this->makeUser('s1@test.com', 'student');
        $this->outsider = $this->makeUser('s2@test.com', 'student');

        $this->course = $this->makeCourse($this->instructorA, 'published');
    }

    // ---------------------------------------------------------------- helpers

    private function makeUser(string $email, string $role): User
    {
        $user = User::create(['name' => ucfirst($role), 'email' => $email, 'password' => 'Password123!']);
        $user->role = $role;
        $user->is_active = true;
        $user->save();

        return $user;
    }

    private function makeCourse(User $owner, string $status = 'draft'): Course
    {
        $course = new Course([
            'title' => 'Course ' . uniqid(),
            'category' => 'Backend',
            'description' => 'Deskripsi course untuk pengujian keamanan.',
            'price' => 100000,
        ]);
        $course->instructor_id = $owner->id;
        $course->status = $status;
        $course->slug = 'course-' . uniqid();
        $course->save();

        return $course;
    }

    private function mp4(int $padding = 2048): UploadedFile
    {
        $bytes = "\x00\x00\x00\x20ftypisom\x00\x00\x02\x00isomiso2avc1mp41" . str_repeat("\x01", $padding);

        return UploadedFile::fake()->createWithContent('Materi Video.mp4', $bytes);
    }

    private function pdf(string $extra = ''): UploadedFile
    {
        $bytes = "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n" . $extra;

        return UploadedFile::fake()->createWithContent('modul.pdf', $bytes);
    }

    private function png(): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');

        return UploadedFile::fake()->createWithContent('thumb.png', $png);
    }

    private function addLesson(Course $course, string $type = 'pdf'): Lesson
    {
        $stored = app(FileUploadService::class)->storeSecureLessonFile($type === 'pdf' ? $this->pdf() : $this->mp4(), $type);

        $lesson = new Lesson([
            'title' => 'Lesson ' . uniqid(),
            'lesson_order' => 1,
            'content_type' => $type,
            'file_path' => $stored['file_path'],
            'original_filename' => $stored['original_filename'],
            'duration_seconds' => 60,
        ]);
        $lesson->course_id = $course->id;
        $lesson->save();

        return $lesson;
    }

    // ------------------------------------------------------------------- RBAC

    public function test_guest_is_redirected_and_student_gets_403_on_instructor_area(): void
    {
        $this->get(route('instructor.courses.index'))->assertRedirect(route('login'));

        $this->actingAs($this->student)->get(route('instructor.courses.index'))->assertStatus(403);
        $this->actingAs($this->student)->post(route('instructor.courses.store'), [])->assertStatus(403);
    }

    public function test_instructor_dashboard_lists_only_own_courses(): void
    {
        $other = $this->makeCourse($this->instructorB, 'draft');

        $response = $this->actingAs($this->instructorA)->get(route('instructor.courses.index'));

        $response->assertOk();
        $response->assertSee($this->course->title);
        $response->assertDontSee($other->title);
    }

    // ------------------------------------------------------ mass assignment

    public function test_course_creation_ignores_injected_status_instructor_and_slug(): void
    {
        $response = $this->actingAs($this->instructorA)->post(route('instructor.courses.store'), [
            'title' => 'Course Baru Aman',
            'category' => 'Cyber Security',
            'description' => 'Deskripsi yang cukup panjang untuk lolos validasi.',
            'price' => 50000,
            // malicious extras
            'status' => 'published',
            'instructor_id' => $this->instructorB->id,
            'slug' => 'hacked-slug',
        ]);

        $course = Course::where('title', 'Course Baru Aman')->firstOrFail();
        $response->assertRedirect(route('instructor.courses.edit', $course));

        $this->assertSame('draft', $course->status);
        $this->assertSame($this->instructorA->id, $course->instructor_id);
        $this->assertNotSame('hacked-slug', $course->slug);
    }

    public function test_course_validation_rejects_negative_price_and_bad_category(): void
    {
        $this->actingAs($this->instructorA)->post(route('instructor.courses.store'), [
            'title' => 'Course Tidak Valid',
            'category' => '<script>alert(1)</script>',
            'description' => 'Deskripsi yang cukup panjang untuk lolos validasi.',
            'price' => -1,
        ])->assertSessionHasErrors(['category', 'price']);

        $this->assertDatabaseMissing('courses', ['title' => 'Course Tidak Valid']);
    }

    public function test_instructor_cannot_self_publish_only_submit_for_approval(): void
    {
        $draft = $this->makeCourse($this->instructorA, 'draft');

        // No lesson yet -> refused
        $this->actingAs($this->instructorA)->post(route('instructor.courses.submit', $draft))->assertSessionHas('error');
        $this->assertSame('draft', $draft->fresh()->status);

        $this->addLesson($draft);
        $this->actingAs($this->instructorA)->post(route('instructor.courses.submit', $draft))->assertSessionHas('success');
        $this->assertSame('pending_approval', $draft->fresh()->status);

        // a published course cannot be "re-submitted"
        $this->actingAs($this->instructorA)->post(route('instructor.courses.submit', $this->course))->assertSessionHas('error');
    }

    // ------------------------------------------------------------ IDOR / BOLA

    public function test_instructor_cannot_touch_another_instructors_course_section_or_lesson(): void
    {
        $section = new Section(['title' => 'Bab Milik A', 'section_order' => 1]);
        $this->course->sections()->save($section);
        $lesson = $this->addLesson($this->course);

        $this->actingAs($this->instructorB);

        $this->get(route('instructor.courses.edit', $this->course))->assertStatus(403);
        $this->put(route('instructor.courses.update', $this->course), [
            'title' => 'Dibajak', 'category' => 'Backend', 'description' => str_repeat('x', 30), 'price' => 0,
        ])->assertStatus(403);
        $this->delete(route('instructor.courses.destroy', $this->course))->assertStatus(403);
        $this->post(route('instructor.sections.store', $this->course), ['title' => 'Bab Palsu'])->assertStatus(403);
        $this->put(route('instructor.sections.update', $section), ['title' => 'Bab Dibajak'])->assertStatus(403);
        $this->delete(route('instructor.sections.destroy', $section))->assertStatus(403);
        $this->delete(route('instructor.lessons.destroy', $lesson))->assertStatus(403);
        $this->post(route('instructor.lessons.store', $this->course), [
            'title' => 'Materi Palsu', 'content_type' => 'pdf', 'file' => $this->pdf(),
        ])->assertStatus(403);

        $this->assertDatabaseHas('courses', ['id' => $this->course->id, 'price' => 100000]);
        $this->assertDatabaseHas('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseMissing('lessons', ['title' => 'Materi Palsu']);
    }

    public function test_lesson_cannot_be_attached_to_a_section_of_another_course(): void
    {
        $foreignCourse = $this->makeCourse($this->instructorB);
        $foreignSection = new Section(['title' => 'Bab Asing', 'section_order' => 1]);
        $foreignCourse->sections()->save($foreignSection);

        $this->actingAs($this->instructorA)->post(route('instructor.lessons.store', $this->course), [
            'title' => 'Materi Lintas Course',
            'content_type' => 'pdf',
            'section_id' => $foreignSection->id,
            'file' => $this->pdf(),
        ])->assertSessionHasErrors('section_id');

        $this->assertDatabaseMissing('lessons', ['title' => 'Materi Lintas Course']);
    }

    // ----------------------------------------------------- secure upload happy

    public function test_valid_mp4_and_pdf_are_stored_privately_with_uuid_names(): void
    {
        $this->actingAs($this->instructorA);

        $this->post(route('instructor.lessons.store', $this->course), [
            'title' => 'Video Pengantar', 'content_type' => 'video', 'file' => $this->mp4(), 'duration_minutes' => 12,
        ])->assertSessionHasNoErrors();

        $this->post(route('instructor.lessons.store', $this->course), [
            'title' => 'Modul PDF', 'content_type' => 'pdf', 'file' => $this->pdf(),
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, $this->course->lessons()->count());

        foreach ($this->course->lessons as $lesson) {
            $this->assertMatchesRegularExpression(
                '#^private/courses/[0-9a-f-]{36}\.(mp4|pdf)$#',
                $lesson->file_path,
                'File must be stored under private/courses with a UUID name'
            );
            $this->assertNotNull(FileUploadService::toDiskPath($lesson->file_path));
            Storage::disk('local')->assertExists(FileUploadService::toDiskPath($lesson->file_path));
            $this->assertStringNotContainsString('Video', $lesson->file_path);
            $this->assertStringNotContainsString('modul', $lesson->file_path);
        }

        $this->assertFileDoesNotExist(public_path('courses'));
        $this->assertFileDoesNotExist(public_path('storage'));
        $this->assertSame(720, (int) Lesson::where('title', 'Video Pengantar')->value('duration_seconds'));
    }

    public function test_original_filename_is_sanitised_and_never_used_for_storage(): void
    {
        $evil = UploadedFile::fake()->createWithContent(
            '../../../public/shell<>.php.pdf',
            "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\n%%EOF\n"
        );

        $result = app(FileUploadService::class)->storeSecureLessonFile($evil, 'pdf');

        $this->assertStringNotContainsString('..', $result['file_path']);
        $this->assertStringNotContainsString('shell', $result['file_path']);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9_\- ]+\.pdf$/', $result['original_filename']);
    }

    // ------------------------------------------------- secure upload attacks

    public function test_php_webshell_disguised_as_video_or_pdf_is_rejected_and_audited(): void
    {
        $this->actingAs($this->instructorA);

        $shell = UploadedFile::fake()->createWithContent('video.mp4', "<?php system(\$_GET['cmd']); ?>");

        $this->post(route('instructor.lessons.store', $this->course), [
            'title' => 'Webshell', 'content_type' => 'video', 'file' => $shell,
        ])->assertSessionHasErrors('file');

        $this->assertSame(0, $this->course->lessons()->count());
        $this->assertSame([], Storage::disk('local')->allFiles());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->instructorA->id,
            'event_type' => 'file.upload_rejected',
        ]);
    }

    public function test_polyglot_pdf_with_php_hidden_after_first_kilobyte_is_rejected(): void
    {
        // Valid PDF header, the payload is far beyond the first 1024 bytes
        $polyglot = $this->pdf(str_repeat('A', 5000) . "\n<?php eval(\$_POST['x']); ?>");

        $this->expectException(InvalidArgumentException::class);
        app(FileUploadService::class)->storeSecureLessonFile($polyglot, 'pdf');
    }

    public function test_signature_split_across_scan_chunks_is_still_detected(): void
    {
        // Place "<script" so it straddles the 1 MiB chunk boundary
        $filler = str_repeat('A', 1048576 - 3);
        $file = $this->pdf($filler . '<script>alert(1)</script>');

        $this->expectException(InvalidArgumentException::class);
        app(FileUploadService::class)->storeSecureLessonFile($file, 'pdf');
    }

    public function test_mime_spoofing_is_blocked_by_magic_bytes_not_extension(): void
    {
        $service = app(FileUploadService::class);

        // Plain text renamed .mp4
        $text = UploadedFile::fake()->createWithContent('lecture.mp4', 'just some text, not a video at all');
        try {
            $service->storeSecureLessonFile($text, 'video');
            $this->fail('Text file masquerading as MP4 was accepted.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        // Windows executable header renamed .pdf
        $exe = UploadedFile::fake()->createWithContent('modul.pdf', "MZ\x90\x00\x03\x00\x00\x00" . str_repeat("\x00", 200));
        $this->expectException(InvalidArgumentException::class);
        $service->storeSecureLessonFile($exe, 'pdf');
    }

    public function test_real_file_in_the_wrong_slot_is_rejected(): void
    {
        $service = app(FileUploadService::class);

        try {
            $service->storeSecureLessonFile($this->pdf(), 'video');
            $this->fail('A PDF must not be accepted as video.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $this->expectException(InvalidArgumentException::class);
        $service->storeSecureLessonFile($this->mp4(), 'pdf');
    }

    public function test_svg_and_html_uploads_are_rejected(): void
    {
        $service = app(FileUploadService::class);

        $svg = UploadedFile::fake()->createWithContent('thumb.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        try {
            $service->storeSecureLessonFile($svg, 'image');
            $this->fail('SVG (XSS vector) must be rejected.');
        } catch (InvalidArgumentException) {
            $this->assertTrue(true);
        }

        $html = UploadedFile::fake()->createWithContent('thumb.png', '<html><body>hi</body></html>');
        $this->expectException(InvalidArgumentException::class);
        $service->storeSecureLessonFile($html, 'image');
    }

    public function test_size_limits_are_enforced_per_type(): void
    {
        $service = app(FileUploadService::class);

        // PDF over 10MB
        $bigPdf = UploadedFile::fake()->createWithContent('big.pdf', "%PDF-1.4\n" . str_repeat('A', 10 * 1048576 + 10));
        try {
            $service->storeSecureLessonFile($bigPdf, 'pdf');
            $this->fail('PDF over 10MB must be rejected.');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('10MB', $e->getMessage());
        }

        // Empty file
        $empty = UploadedFile::fake()->createWithContent('empty.pdf', '');
        $this->expectException(InvalidArgumentException::class);
        $service->storeSecureLessonFile($empty, 'pdf');
    }

    public function test_thumbnail_upload_accepts_real_png_and_rejects_fake_image(): void
    {
        $this->actingAs($this->instructorA)->post(route('instructor.courses.store'), [
            'title' => 'Course Dengan Thumbnail',
            'category' => 'Frontend',
            'description' => 'Deskripsi yang cukup panjang untuk lolos validasi.',
            'price' => 0,
            'thumbnail' => $this->png(),
        ])->assertSessionHasNoErrors();

        $course = Course::where('title', 'Course Dengan Thumbnail')->firstOrFail();
        $this->assertMatchesRegularExpression('#^private/courses/[0-9a-f-]{36}\.png$#', $course->thumbnail);

        $this->actingAs($this->instructorA)->post(route('instructor.courses.store'), [
            'title' => 'Course Thumbnail Palsu',
            'category' => 'Frontend',
            'description' => 'Deskripsi yang cukup panjang untuk lolos validasi.',
            'price' => 0,
            'thumbnail' => UploadedFile::fake()->createWithContent('x.png', "<?php echo 'pwn'; ?>"),
        ])->assertSessionHasErrors('thumbnail');

        $this->assertDatabaseMissing('courses', ['title' => 'Course Thumbnail Palsu']);
    }

    // ------------------------------------------------------ secure streaming

    public function test_streaming_requires_authentication(): void
    {
        $lesson = $this->addLesson($this->course);

        $this->get(route('lesson.stream', [$this->course->id, $lesson->id]))->assertRedirect(route('login'));
    }

    public function test_unenrolled_student_gets_403_and_attempt_is_audited(): void
    {
        $lesson = $this->addLesson($this->course);

        $this->actingAs($this->outsider)
            ->get(route('lesson.stream', [$this->course->id, $lesson->id]))
            ->assertStatus(403);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->outsider->id,
            'event_type' => 'access.stream_denied',
        ]);
    }

    public function test_enrolled_student_streams_pdf_with_safe_headers_and_no_path_leak(): void
    {
        $lesson = $this->addLesson($this->course, 'pdf');
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'enrolled_at' => now()]);

        $response = $this->actingAs($this->student)->get(route('lesson.stream', [$this->course->id, $lesson->id]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringStartsWith('%PDF-1.4', $response->streamedContent());
        $this->assertStringNotContainsString('storage', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringNotContainsString(basename($lesson->file_path), (string) $response->headers->get('Content-Disposition'));
    }

    public function test_video_streaming_supports_http_range_requests(): void
    {
        $lesson = $this->addLesson($this->course, 'video');
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'enrolled_at' => now()]);
        $total = Storage::disk('local')->size(FileUploadService::toDiskPath($lesson->file_path));

        $partial = $this->actingAs($this->student)
            ->withHeaders(['Range' => 'bytes=0-99'])
            ->get(route('lesson.stream', [$this->course->id, $lesson->id]));

        $partial->assertStatus(206);
        $partial->assertHeader('Content-Type', 'video/mp4');
        $partial->assertHeader('Content-Range', "bytes 0-99/{$total}");
        $this->assertSame(100, strlen($partial->streamedContent()));

        $bad = $this->actingAs($this->student)
            ->withHeaders(['Range' => 'bytes=999999999-'])
            ->get(route('lesson.stream', [$this->course->id, $lesson->id]));
        $bad->assertStatus(416);
    }

    public function test_lesson_id_from_another_course_cannot_be_streamed_through_my_course(): void
    {
        // Student legitimately owns course A but tries to read a lesson of course B by swapping the ID
        $otherCourse = $this->makeCourse($this->instructorB, 'published');
        $foreignLesson = $this->addLesson($otherCourse);
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'enrolled_at' => now()]);

        $this->actingAs($this->student)
            ->get(route('lesson.stream', [$this->course->id, $foreignLesson->id]))
            ->assertStatus(404);
    }

    public function test_unpublished_course_content_is_hidden_from_students_but_previewable_by_owner(): void
    {
        $draft = $this->makeCourse($this->instructorA, 'draft');
        $lesson = $this->addLesson($draft);
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $draft->id, 'enrolled_at' => now()]);

        $this->actingAs($this->student)->get(route('lesson.stream', [$draft->id, $lesson->id]))->assertStatus(404);
        $this->actingAs($this->instructorB)->get(route('lesson.stream', [$draft->id, $lesson->id]))->assertStatus(404);
        $this->actingAs($this->instructorA)->get(route('lesson.stream', [$draft->id, $lesson->id]))->assertOk();
    }

    public function test_tampered_db_path_cannot_traverse_outside_private_courses(): void
    {
        $lesson = $this->addLesson($this->course);
        $lesson->file_path = 'private/courses/../../../.env';
        $lesson->save();

        $this->actingAs($this->instructorA)
            ->get(route('lesson.stream', [$this->course->id, $lesson->id]))
            ->assertStatus(404);
    }

    public function test_uploaded_files_are_not_publicly_reachable(): void
    {
        $lesson = $this->addLesson($this->course);
        $uuidFile = basename($lesson->file_path);

        $this->get('/storage/courses/' . $uuidFile)->assertStatus(404);
        $this->get('/courses/' . $uuidFile)->assertStatus(404);
        $this->get('/private/courses/' . $uuidFile)->assertStatus(404);
    }

    public function test_thumbnail_endpoint_hides_drafts_from_the_public(): void
    {
        $draft = $this->makeCourse($this->instructorA, 'draft');
        $stored = app(FileUploadService::class)->storeSecureLessonFile($this->png(), 'image');
        $draft->thumbnail = $stored['file_path'];
        $draft->save();

        $this->get(route('course.thumbnail', $draft->id))->assertStatus(404);
        $this->actingAs($this->instructorA)->get(route('course.thumbnail', $draft->id))->assertOk()->assertHeader('Content-Type', 'image/png');

        $draft->status = 'published';
        $draft->save();
        auth()->logout();
        $this->get(route('course.thumbnail', $draft->id))->assertOk();
    }

    // ------------------------------------------------------------- deletion

    public function test_deleting_a_course_removes_its_files_from_disk(): void
    {
        $course = $this->makeCourse($this->instructorA);
        $lesson = $this->addLesson($course);
        $path = FileUploadService::toDiskPath($lesson->file_path);
        Storage::disk('local')->assertExists($path);

        $this->actingAs($this->instructorA)->delete(route('instructor.courses.destroy', $course))
            ->assertRedirect(route('instructor.courses.index'));

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_course_with_enrolled_students_cannot_be_deleted(): void
    {
        $lesson = $this->addLesson($this->course);
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $this->course->id, 'enrolled_at' => now()]);

        $this->actingAs($this->instructorA)->delete(route('instructor.courses.destroy', $this->course))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('courses', ['id' => $this->course->id]);
        Storage::disk('local')->assertExists(FileUploadService::toDiskPath($lesson->file_path));
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'course.delete_blocked']);
    }

    public function test_delete_helper_ignores_non_uuid_paths(): void
    {
        Storage::disk('local')->put('courses/sample-1-1.pdf', 'demo');
        Storage::disk('local')->put('important.txt', 'keep me');

        $service = app(FileUploadService::class);
        $service->delete('private/courses/sample-1-1.pdf');
        $service->delete('private/courses/../important.txt');
        $service->delete('../../.env');

        Storage::disk('local')->assertExists('courses/sample-1-1.pdf');
        Storage::disk('local')->assertExists('important.txt');
    }

    // -------------------------------------------------------- curriculum

    public function test_curriculum_builder_sections_and_ordering(): void
    {
        $this->actingAs($this->instructorA);

        $this->post(route('instructor.sections.store', $this->course), ['title' => 'Bab Pertama'])->assertSessionHasNoErrors();
        $this->post(route('instructor.sections.store', $this->course), ['title' => 'Bab Kedua'])->assertSessionHasNoErrors();

        $sections = $this->course->sections()->get();
        $this->assertSame([1, 2], $sections->pluck('section_order')->all());

        $this->post(route('instructor.lessons.store', $this->course), [
            'title' => 'Materi di Bab Dua', 'content_type' => 'pdf', 'file' => $this->pdf(), 'section_id' => $sections[1]->id,
        ])->assertSessionHasNoErrors();

        $lesson = Lesson::where('title', 'Materi di Bab Dua')->firstOrFail();
        $this->assertSame($sections[1]->id, $lesson->section_id);

        // deleting the section keeps the lesson
        $this->delete(route('instructor.sections.destroy', $sections[1]))->assertSessionHas('success');
        $this->assertNull($lesson->fresh()->section_id);

        $this->get(route('instructor.courses.edit', $this->course))->assertOk()->assertSee('Bab Pertama');
    }

    public function test_instructor_pages_render(): void
    {
        $this->actingAs($this->instructorA);

        $this->get(route('instructor.courses.create'))->assertOk()->assertSee('Buat Course Baru');
        $this->get(route('instructor.courses.edit', $this->course))->assertOk()->assertSee('Unggah Materi');
    }

    public function test_deactivated_instructor_is_blocked_by_role_middleware(): void
    {
        $this->instructorA->is_active = false;
        $this->instructorA->save();

        $this->actingAs($this->instructorA)->get(route('instructor.courses.index'))->assertRedirect(route('login'));
    }

    public function test_user_model_still_blocks_role_mass_assignment(): void
    {
        $user = User::create([
            'name' => 'Sneaky', 'email' => 'sneaky@test.com', 'password' => 'Password123!',
            'role' => 'instructor', 'balance' => 1000000,
        ]);

        $this->assertSame('student', $user->fresh()->role);
    }
}
