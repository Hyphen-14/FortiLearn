<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Jobdesk 1: authentication hardening (brute-force lockout, password policy,
 * session lifecycle, deactivated accounts, audit trail).
 */
class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'PasswordStudent123!';

    private User $student;

    private User $other;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = $this->makeUser('victim@fortilearn.test');
        $this->other = $this->makeUser('other@fortilearn.test');
    }

    private function makeUser(string $email, string $role = 'student', bool $active = true): User
    {
        $user = User::create(['name' => 'User '.$email, 'email' => $email, 'password' => self::PASSWORD]);
        $user->role = $role;
        $user->is_active = $active;
        $user->save();

        return $user;
    }

    private function attemptLogin(string $email, string $password)
    {
        return $this->from(route('login'))->post('/login', ['email' => $email, 'password' => $password]);
    }

    public function test_login_locks_out_email_after_five_failures_even_with_correct_password(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->attemptLogin($this->student->email, 'wrong-password')->assertSessionHasErrors('email');
        }

        $this->attemptLogin($this->student->email, self::PASSWORD)
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.lockout']);
    }

    public function test_lockout_of_one_email_does_not_block_other_accounts_from_same_ip(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->attemptLogin($this->student->email, 'wrong-password');
        }

        $this->attemptLogin($this->other->email, self::PASSWORD)->assertRedirect(route('catalog.index'));
        $this->assertAuthenticatedAs($this->other);
    }

    public function test_lockout_key_is_case_insensitive_on_email(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->attemptLogin(strtoupper($this->student->email), 'wrong-password');
        }

        $this->attemptLogin($this->student->email, self::PASSWORD)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_failed_login_is_audited_without_storing_the_password(): void
    {
        $this->attemptLogin($this->student->email, 'Sup3rSecretGuess!');

        $log = AuditLog::where('event_type', 'auth.login_failed')->firstOrFail();
        $this->assertSame($this->student->email, $log->payload['email']);
        $this->assertStringNotContainsString('Sup3rSecretGuess!', json_encode($log->payload));
    }

    public function test_successful_login_regenerates_session_id_and_is_audited(): void
    {
        $this->get(route('login'));
        $before = session()->getId();

        $this->attemptLogin($this->student->email, self::PASSWORD)->assertRedirect(route('catalog.index'));

        $this->assertNotSame($before, session()->getId());
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.login_success', 'user_id' => $this->student->id]);
    }

    public function test_successful_login_clears_failed_attempt_counter(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->attemptLogin($this->student->email, 'wrong-password');
        }
        $this->attemptLogin($this->student->email, self::PASSWORD);
        $this->post(route('logout'));

        for ($i = 0; $i < 4; $i++) {
            $this->attemptLogin($this->student->email, 'wrong-password');
        }
        $this->attemptLogin($this->student->email, self::PASSWORD)->assertRedirect(route('catalog.index'));
    }

    public function test_deactivated_account_cannot_login_and_attempt_is_audited(): void
    {
        $blocked = $this->makeUser('blocked@fortilearn.test', active: false);

        $this->attemptLogin($blocked->email, self::PASSWORD)->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.login_blocked_inactive', 'user_id' => $blocked->id]);
    }

    public function test_deactivated_student_session_is_terminated_on_non_role_routes(): void
    {
        $this->actingAs($this->student)->get(route('wallet.index'))->assertOk();

        $this->student->is_active = false;
        $this->student->save();

        $this->actingAs($this->student)->get(route('wallet.index'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.inactive_session_terminated', 'user_id' => $this->student->id]);
    }

    public function test_deactivated_student_cannot_post_checkout_or_topup(): void
    {
        $this->student->is_active = false;
        $this->student->save();

        $this->actingAs($this->student)->post(route('wallet.topup'), ['amount' => 50000])->assertRedirect(route('login'));
        $this->assertSame(0.0, (float) $this->student->fresh()->balance);
    }

    public function test_logout_invalidates_session_and_is_audited(): void
    {
        $this->actingAs($this->student)->post(route('logout'))->assertRedirect(route('catalog.index'));

        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.logout', 'user_id' => $this->student->id]);
    }

    public function test_register_rejects_weak_passwords(): void
    {
        foreach (['short1!', 'alllowercase1!', 'NoDigitsHere!', 'NoSymbols123'] as $weak) {
            $this->from(route('register'))->post(route('register'), [
                'name' => 'Weak',
                'email' => 'weak@fortilearn.test',
                'password' => $weak,
                'password_confirmation' => $weak,
            ])->assertSessionHasErrors('password');
        }

        $this->assertDatabaseMissing('users', ['email' => 'weak@fortilearn.test']);
    }

    public function test_register_rejects_passwords_longer_than_bcrypt_limit(): void
    {
        $long = str_repeat('Aa1!', 19); // 76 bytes, bcrypt silently ignores bytes after 72

        $this->from(route('register'))->post(route('register'), [
            'name' => 'Long',
            'email' => 'long@fortilearn.test',
            'password' => $long,
            'password_confirmation' => $long,
        ])->assertSessionHasErrors('password');
    }

    public function test_successful_registration_is_audited(): void
    {
        $this->post(route('register'), [
            'name' => 'New Student',
            'email' => 'new@fortilearn.test',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ])->assertRedirect(route('catalog.index'));

        $user = User::where('email', 'new@fortilearn.test')->firstOrFail();
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.register', 'user_id' => $user->id]);
    }

    public function test_register_endpoint_is_rate_limited_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post(route('register'), ['name' => ''])->assertStatus(302);
        }

        $this->post(route('register'), ['name' => ''])->assertStatus(429);
    }

    public function test_role_denied_access_is_audited(): void
    {
        $this->actingAs($this->student)->get(route('admin.dashboard'))->assertForbidden();

        $this->assertDatabaseHas('audit_logs', ['event_type' => 'access.role_denied', 'user_id' => $this->student->id]);
    }
}
