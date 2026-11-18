<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorLoginPortalTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email, bool $active = true): User
    {
        $u = User::create(['name' => 'Person-' . strtok($email, '@'), 'email' => $email, 'password' => 'Password123!']);
        $u->role = $role;
        $u->is_active = $active;
        $u->save();

        return $u;
    }

    private function creds(string $email, string $password = 'Password123!'): array
    {
        return ['email' => $email, 'password' => $password];
    }

    public function test_landing_page_shows_instructor_login_button_to_guests_only(): void
    {
        $this->get(route('catalog.index'))->assertOk()->assertSee('Login Instruktur')
            ->assertSee(route('instructor.login'));

        $student = $this->makeUser('student', 's@test.com');
        $this->actingAs($student)->get(route('catalog.index'))->assertOk()->assertDontSee('Login Instruktur');
    }

    public function test_instructor_login_page_renders_with_links(): void
    {
        $this->get('/instructor/login')->assertOk()
            ->assertSee('Login Instruktur')
            ->assertSee(route('register.instructor'))
            ->assertSee(route('login'));
    }

    public function test_instructor_can_login_through_portal_and_lands_on_studio(): void
    {
        $user = $this->makeUser('instructor', 'i@test.com');

        $this->post('/instructor/login', $this->creds('i@test.com'))->assertRedirect(route('instructor.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_student_and_admin_cannot_login_through_instructor_portal(): void
    {
        $this->makeUser('student', 's@test.com');
        $this->makeUser('admin', 'a@test.com');

        foreach (['s@test.com', 'a@test.com'] as $email) {
            $this->post('/instructor/login', $this->creds($email))
                ->assertSessionHasErrors(['email' => 'Kombinasi email atau password tidak sesuai.']);
            $this->assertGuest();
        }
    }

    public function test_portal_does_not_reveal_whether_account_is_a_student(): void
    {
        $this->makeUser('student', 's@test.com');

        $wrongPassword = $this->post('/instructor/login', $this->creds('s@test.com', 'Salah123!'));
        $wrongRole = $this->post('/instructor/login', $this->creds('s@test.com'));

        $this->assertSame(
            session('errors')->get('email'),
            $wrongRole->getSession()->get('errors')->get('email')
        );
        $wrongPassword->assertSessionHasErrors(['email' => 'Kombinasi email atau password tidak sesuai.']);
    }

    public function test_instructor_is_sent_to_instructor_portal_from_student_login(): void
    {
        $this->makeUser('instructor', 'i@test.com');

        $this->post('/login', $this->creds('i@test.com'))
            ->assertSessionHasErrors(['email' => 'Akun instruktur masuk melalui halaman Login Instruktur.']);
        $this->assertGuest();
    }

    public function test_student_login_still_works_on_student_page(): void
    {
        $user = $this->makeUser('student', 's@test.com');

        $this->post('/login', $this->creds('s@test.com'))->assertRedirect(route('catalog.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_deactivated_instructor_cannot_login(): void
    {
        $this->makeUser('instructor', 'off@test.com', active: false);

        $this->post('/instructor/login', $this->creds('off@test.com'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_instructor_portal_is_rate_limited(): void
    {
        $this->makeUser('instructor', 'i@test.com');

        for ($i = 0; $i < 6; $i++) {
            $this->post('/instructor/login', $this->creds('i@test.com', 'Salah123!'));
        }

        $this->post('/instructor/login', $this->creds('i@test.com'))->assertStatus(429);
    }

    public function test_logged_in_user_is_redirected_away_from_instructor_login(): void
    {
        $this->actingAs($this->makeUser('instructor', 'i@test.com'))->get('/instructor/login')->assertRedirect();
    }
}
