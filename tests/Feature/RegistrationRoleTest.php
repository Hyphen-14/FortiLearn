<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    private function payload(string $email, array $extra = []): array
    {
        return array_merge([
            'name' => 'Nama Tester',
            'email' => $email,
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ], $extra);
    }

    public function test_both_register_pages_render_with_their_own_copy(): void
    {
        $this->get('/register')->assertOk()->assertSee('Buat Akun FortiLearn')->assertSee('Daftar sebagai instruktur');
        $this->get('/register/instructor')->assertOk()->assertSee('Daftar Sebagai Instruktur')->assertSee('Daftar sebagai siswa');
    }

    public function test_student_registration_creates_student_and_goes_to_catalog(): void
    {
        $this->post('/register', $this->payload('s@test.com'))->assertRedirect(route('catalog.index'));

        $this->assertSame('student', User::where('email', 's@test.com')->first()->role);
    }

    public function test_instructor_registration_creates_instructor_and_goes_to_studio(): void
    {
        $this->post('/register/instructor', $this->payload('i@test.com'))->assertRedirect(route('instructor.dashboard'));

        $user = User::where('email', 'i@test.com')->first();
        $this->assertSame('instructor', $user->role);
        $this->assertTrue((bool) $user->is_active);
        $this->assertSame(0.0, (float) $user->balance);
        $this->assertAuthenticatedAs($user);
    }

    public function test_client_cannot_escalate_role_balance_or_status_on_either_form(): void
    {
        $evil = ['role' => 'admin', 'balance' => 99999999, 'is_active' => 0, 'account_type' => 'admin'];

        $this->post('/register', $this->payload('a@test.com', $evil));
        $a = User::where('email', 'a@test.com')->first();
        $this->assertSame('student', $a->role);
        $this->assertSame(0.0, (float) $a->balance);

        auth()->logout();
        $this->post('/register/instructor', $this->payload('b@test.com', $evil));
        $b = User::where('email', 'b@test.com')->first();
        $this->assertSame('instructor', $b->role);
        $this->assertSame(0.0, (float) $b->balance);
        $this->assertTrue((bool) $b->is_active);
    }

    public function test_instructor_endpoint_validates_like_student_endpoint(): void
    {
        $this->post('/register/instructor', $this->payload('dup@test.com'));
        auth()->logout();

        $this->post('/register/instructor', $this->payload('dup@test.com'))->assertSessionHasErrors('email');
        $this->post('/register/instructor', $this->payload('x@test.com', ['password_confirmation' => 'beda']))->assertSessionHasErrors('password');
        $this->assertSame(1, User::where('email', 'dup@test.com')->count());
    }

    public function test_logged_in_users_cannot_open_register_pages(): void
    {
        $this->post('/register', $this->payload('s@test.com'));

        $this->get('/register/instructor')->assertRedirect();
    }
}
