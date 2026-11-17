<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Laravel skips CSRF verification whenever it runs under PHPUnit, so no other test
 * proves the protection exists. This class switches the check back on and asserts
 * that every state-changing auth/admin route rejects a request without a token.
 */
class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app->make(Encrypter::class)) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
    }

    private function user(string $role): User
    {
        $user = User::create(['name' => $role, 'email' => "{$role}@fortilearn.test", 'password' => 'Password123!']);
        $user->role = $role;
        $user->is_active = true;
        $user->save();

        return $user;
    }

    public function test_guest_auth_forms_reject_requests_without_csrf_token(): void
    {
        $this->post('/login', ['email' => 'x@fortilearn.test', 'password' => 'whatever'])->assertStatus(419);
        $this->post(route('register'), [])->assertStatus(419);
        $this->post(route('password.email'), ['email' => 'x@fortilearn.test'])->assertStatus(419);
        $this->post(route('password.update'), [])->assertStatus(419);
    }

    public function test_logout_rejects_request_without_csrf_token(): void
    {
        $student = $this->user('student');

        $this->actingAs($student)->post(route('logout'))->assertStatus(419);
        $this->assertAuthenticatedAs($student);
    }

    public function test_admin_actions_reject_requests_without_csrf_token(): void
    {
        $admin = $this->user('admin');
        $instructor = $this->user('instructor');
        $student = $this->user('student');

        $course = Course::create([
            'instructor_id' => $instructor->id, 'title' => 'CSRF Target', 'slug' => 'csrf-target',
            'description' => 'x', 'price' => 1000,
        ]);
        $course->status = 'pending_approval';
        $course->save();

        $this->actingAs($admin)->patch(route('admin.users.deactivate', $student))->assertStatus(419);
        $this->actingAs($admin)->patch(route('admin.courses.approve', $course))->assertStatus(419);
        $this->actingAs($admin)->patch(route('admin.courses.reject', $course), ['reason' => 'forged request'])->assertStatus(419);

        $this->assertTrue($student->fresh()->is_active);
        $this->assertSame('pending_approval', $course->fresh()->status);
    }

    public function test_request_with_valid_token_is_accepted(): void
    {
        $this->get(route('login'));
        $token = session()->token();

        $this->post('/login', ['_token' => $token, 'email' => 'nobody@fortilearn.test', 'password' => 'x'])
            ->assertStatus(302);
    }
}
