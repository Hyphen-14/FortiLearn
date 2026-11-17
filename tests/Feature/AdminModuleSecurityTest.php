<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Jobdesk 1: admin engine (user activation, course moderation, audit log viewer)
 * and the access-control rules around it.
 */
class AdminModuleSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $otherAdmin;

    private User $instructor;

    private User $student;

    private Course $pending;

    private Course $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->makeUser('admin@fortilearn.test', 'admin');
        $this->otherAdmin = $this->makeUser('admin2@fortilearn.test', 'admin');
        $this->instructor = $this->makeUser('instructor@fortilearn.test', 'instructor');
        $this->student = $this->makeUser('student@fortilearn.test', 'student');

        $this->pending = $this->makeCourse('Kursus Menunggu Review', 'pending_approval');
        $this->draft = $this->makeCourse('Kursus Masih Draft', 'draft');
    }

    private function makeUser(string $email, string $role, bool $active = true): User
    {
        $user = User::create(['name' => 'User '.$email, 'email' => $email, 'password' => 'Password123!']);
        $user->role = $role;
        $user->is_active = $active;
        $user->save();

        return $user;
    }

    private function makeCourse(string $title, string $status): Course
    {
        $course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(4),
            'description' => 'Deskripsi '.$title,
            'price' => 150000,
        ]);
        $course->status = $status;
        $course->save();

        $course->lessons()->create([
            'title' => 'Materi 1 '.$title,
            'lesson_order' => 1,
            'content_type' => 'pdf',
            'file_path' => 'courses/'.Str::uuid().'.pdf',
            'original_filename' => 'materi.pdf',
        ]);

        return $course;
    }

    /** @return array<int, array{0: string, 1: string}> */
    private function adminEndpoints(): array
    {
        return [
            ['get', route('admin.dashboard')],
            ['get', route('admin.users.index')],
            ['patch', route('admin.users.deactivate', $this->student)],
            ['patch', route('admin.users.activate', $this->student)],
            ['get', route('admin.courses.index')],
            ['get', route('admin.courses.show', $this->pending)],
            ['patch', route('admin.courses.approve', $this->pending)],
            ['patch', route('admin.courses.reject', $this->pending)],
            ['get', route('admin.audit-logs.index')],
        ];
    }

    // ---------------------------------------------------------------- access control

    public function test_guest_is_redirected_from_every_admin_endpoint(): void
    {
        foreach ($this->adminEndpoints() as [$method, $url]) {
            $this->{$method}($url)->assertRedirect(route('login'));
        }
    }

    public function test_student_and_instructor_get_403_on_every_admin_endpoint(): void
    {
        foreach ([$this->student, $this->instructor] as $user) {
            foreach ($this->adminEndpoints() as [$method, $url]) {
                $this->actingAs($user)->{$method}($url)->assertForbidden();
            }
        }

        $this->assertSame('pending_approval', $this->pending->fresh()->status);
        $this->assertTrue($this->student->fresh()->is_active);
    }

    public function test_state_changing_admin_actions_reject_get(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.deactivate', $this->student))->assertMethodNotAllowed();
        $this->actingAs($this->admin)->get(route('admin.courses.approve', $this->pending))->assertMethodNotAllowed();
    }

    // ---------------------------------------------------------------- dashboard

    public function test_dashboard_shows_pending_queue_and_recent_security_events(): void
    {
        AuditLog::create(['event_type' => 'auth.lockout', 'ip_address' => '198.51.100.7', 'user_agent' => 'x', 'payload' => ['email' => 'victim@x.test']]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Kursus Menunggu Review')
            ->assertDontSee('Kursus Masih Draft')
            ->assertSee('auth.lockout');
    }

    // ---------------------------------------------------------------- users

    public function test_users_index_lists_and_searches_users(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.index', ['q' => 'student@']))
            ->assertOk()
            ->assertSee('student@fortilearn.test')
            ->assertDontSee('instructor@fortilearn.test');
    }

    public function test_users_search_treats_sql_and_wildcards_as_literal_text(): void
    {
        $this->actingAs($this->admin)->get(route('admin.users.index', ['q' => "%' OR 1=1 --"]))
            ->assertOk()
            ->assertDontSee('student@fortilearn.test');

        $this->actingAs($this->admin)->get(route('admin.users.index', ['q' => '%']))
            ->assertOk()
            ->assertDontSee('student@fortilearn.test');
    }

    public function test_admin_can_deactivate_student_which_revokes_sessions_and_is_audited(): void
    {
        config(['session.driver' => 'database']);
        DB::table('sessions')->insert([
            'id' => 'student-live-session', 'user_id' => $this->student->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'x', 'payload' => base64_encode('x'), 'last_activity' => time(),
        ]);

        $this->actingAs($this->admin)->patch(route('admin.users.deactivate', $this->student))->assertRedirect();

        $this->assertFalse($this->student->fresh()->is_active);
        $this->assertDatabaseMissing('sessions', ['id' => 'student-live-session']);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'admin.user_deactivated', 'user_id' => $this->admin->id]);
    }

    public function test_admin_can_reactivate_user_and_payload_cannot_change_role(): void
    {
        $this->student->is_active = false;
        $this->student->save();

        $this->actingAs($this->admin)
            ->patch(route('admin.users.activate', $this->student), ['role' => 'admin', 'balance' => 999999])
            ->assertRedirect();

        $fresh = $this->student->fresh();
        $this->assertTrue($fresh->is_active);
        $this->assertSame('student', $fresh->role);
        $this->assertSame(0.0, (float) $fresh->balance);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'admin.user_activated']);
    }

    public function test_admin_cannot_deactivate_self_or_another_admin(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.users.deactivate', $this->admin))->assertForbidden();
        $this->actingAs($this->admin)->patch(route('admin.users.deactivate', $this->otherAdmin))->assertForbidden();

        $this->assertTrue($this->admin->fresh()->is_active);
        $this->assertTrue($this->otherAdmin->fresh()->is_active);
    }

    // ---------------------------------------------------------------- course moderation

    public function test_courses_index_defaults_to_pending_queue(): void
    {
        $this->actingAs($this->admin)->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('Kursus Menunggu Review')
            ->assertDontSee('Kursus Masih Draft');
    }

    public function test_courses_index_rejects_unknown_status_filter(): void
    {
        $this->actingAs($this->admin)->get(route('admin.courses.index', ['status' => "published' OR '1'='1"]))
            ->assertSessionHasErrors('status');
    }

    public function test_admin_can_review_course_content_before_deciding(): void
    {
        $this->actingAs($this->admin)->get(route('admin.courses.show', $this->pending))
            ->assertOk()
            ->assertSee('Materi 1 Kursus Menunggu Review')
            ->assertSee(route('lesson.stream', [$this->pending->id, $this->pending->lessons->first()->id]), false);
    }

    public function test_admin_approves_pending_course(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.courses.approve', $this->pending))->assertRedirect();

        $fresh = $this->pending->fresh();
        $this->assertSame('published', $fresh->status);
        $this->assertSame($this->admin->id, $fresh->reviewed_by);
        $this->assertNotNull($fresh->reviewed_at);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'admin.course_approved']);

        $this->get(route('catalog.show', $fresh->slug))->assertOk();
    }

    public function test_reject_requires_a_reason_and_stores_it(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.courses.reject', $this->pending), ['reason' => ''])
            ->assertSessionHasErrors('reason');
        $this->assertSame('pending_approval', $this->pending->fresh()->status);

        $this->actingAs($this->admin)
            ->patch(route('admin.courses.reject', $this->pending), ['reason' => 'Materi 1 tidak sesuai deskripsi.'])
            ->assertRedirect();

        $fresh = $this->pending->fresh();
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame('Materi 1 tidak sesuai deskripsi.', $fresh->rejection_reason);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'admin.course_rejected']);
    }

    public function test_only_pending_courses_can_be_moderated(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.courses.approve', $this->draft))->assertSessionHas('error');
        $this->assertSame('draft', $this->draft->fresh()->status);

        $this->actingAs($this->admin)->patch(route('admin.courses.approve', $this->pending));
        $this->actingAs($this->admin)
            ->patch(route('admin.courses.reject', $this->pending), ['reason' => 'Terlambat'])
            ->assertSessionHas('error');
        $this->assertSame('published', $this->pending->fresh()->status);
    }

    public function test_instructor_sees_rejection_reason_escaped(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('admin.courses.reject', $this->pending), ['reason' => '<script>alert("x")</script> Perbaiki judul']);

        $this->actingAs($this->instructor)->get(route('instructor.courses.index'))
            ->assertOk()
            ->assertSee('Perbaiki judul')
            ->assertDontSee('<script>alert("x")</script>', false);
    }

    public function test_course_title_is_escaped_in_admin_pages(): void
    {
        $evil = $this->makeCourse('<img src=x onerror=alert(1)> Evil', 'pending_approval');

        $this->actingAs($this->admin)->get(route('admin.courses.index'))
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
        $this->actingAs($this->admin)->get(route('admin.courses.show', $evil))
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }

    // ---------------------------------------------------------------- audit log viewer

    public function test_audit_log_viewer_filters_by_event_and_escapes_payload(): void
    {
        AuditLog::create(['event_type' => 'auth.login_failed', 'ip_address' => '203.0.113.5', 'user_agent' => 'x', 'payload' => ['email' => '<script>alert(1)</script>@x.test']]);
        AuditLog::create(['event_type' => 'access.role_denied', 'ip_address' => '203.0.113.6', 'user_agent' => 'x', 'payload' => ['path' => 'admin/dashboard']]);

        $this->actingAs($this->admin)->get(route('admin.audit-logs.index', ['event' => 'auth.login_failed']))
            ->assertOk()
            ->assertSee('203.0.113.5')
            ->assertDontSee('203.0.113.6')
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_audit_log_viewer_rejects_unknown_event_filter(): void
    {
        $this->actingAs($this->admin)->get(route('admin.audit-logs.index', ['event' => "x' OR 1=1"]))
            ->assertSessionHasErrors('event');
    }

    public function test_sidebar_shows_admin_links_only_to_admins(): void
    {
        $this->actingAs($this->admin)->get(route('catalog.index'))->assertSee(route('admin.audit-logs.index'), false);
        $this->actingAs($this->student)->get(route('catalog.index'))->assertDontSee(route('admin.audit-logs.index'), false);
    }
}
