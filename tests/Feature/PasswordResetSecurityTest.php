<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Jobdesk 1: forgot / reset password (anti user-enumeration, reset-link poisoning,
 * token expiry and single use, session revocation, audit trail).
 */
class PasswordResetSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const OLD_PASSWORD = 'PasswordStudent123!';
    private const NEW_PASSWORD = 'BrandNewPass456#';

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::create([
            'name' => 'Reset Student',
            'email' => 'reset@fortilearn.test',
            'password' => self::OLD_PASSWORD,
        ]);
        $this->student->is_active = true;
        $this->student->save();
    }

    private function resetPayload(string $token, array $overrides = []): array
    {
        return array_merge([
            'token' => $token,
            'email' => $this->student->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ], $overrides);
    }

    public function test_forgot_password_page_renders_and_login_links_to_it(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('Lupa Kata Sandi');
        $this->get(route('login'))->assertSee(route('password.request'), false);
    }

    public function test_reset_link_is_sent_to_registered_email_and_audited(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $this->student->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');

        Notification::assertSentTo($this->student, ResetPassword::class);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.password_reset_requested']);
    }

    public function test_response_is_identical_for_unknown_email_to_prevent_enumeration(): void
    {
        Notification::fake();

        $known = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $this->student->email]);
        $unknown = $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'nobody@fortilearn.test']);

        $unknown->assertRedirect(route('password.request'))->assertSessionHasNoErrors();
        $this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));
        Notification::assertSentTimes(ResetPassword::class, 1);
    }

    public function test_reset_link_uses_app_url_even_when_host_header_is_spoofed(): void
    {
        Notification::fake();
        config(['app.url' => 'http://localhost:8000']);

        $this->withServerVariables(['HTTP_HOST' => 'evil.attacker.test'])
            ->post(route('password.email'), ['email' => $this->student->email]);

        Notification::assertSentTo($this->student, ResetPassword::class, function (ResetPassword $n) {
            $url = $n->toMail($this->student)->actionUrl;

            return str_starts_with($url, 'http://localhost:8000/reset-password/')
                && ! str_contains($url, 'evil.attacker.test');
        });
    }

    public function test_valid_token_resets_password_rotates_remember_token_and_is_audited(): void
    {
        $this->student->forceFill(['remember_token' => 'old-remember-token'])->save();
        $token = Password::createToken($this->student);

        $this->post(route('password.update'), $this->resetPayload($token))
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $fresh = $this->student->fresh();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $fresh->password));
        $this->assertNotSame('old-remember-token', $fresh->remember_token);
        $this->assertGuest();
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.password_reset_completed', 'user_id' => $this->student->id]);
    }

    public function test_invalid_token_is_rejected_and_audited(): void
    {
        $this->from(route('password.reset', 'forged-token'))
            ->post(route('password.update'), $this->resetPayload('forged-token'))
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $this->student->fresh()->password));
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'auth.password_reset_failed']);
    }

    public function test_token_cannot_be_used_twice(): void
    {
        $token = Password::createToken($this->student);
        $this->post(route('password.update'), $this->resetPayload($token));

        $this->post(route('password.update'), $this->resetPayload($token, [
            'password' => 'AnotherPass789$',
            'password_confirmation' => 'AnotherPass789$',
        ]))->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $this->student->fresh()->password));
    }

    public function test_token_expires_after_thirty_minutes(): void
    {
        $token = Password::createToken($this->student);
        $this->travel(31)->minutes();

        $this->post(route('password.update'), $this->resetPayload($token))->assertSessionHasErrors('email');
        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $this->student->fresh()->password));
    }

    public function test_reset_enforces_the_same_password_policy_as_register(): void
    {
        $token = Password::createToken($this->student);

        $this->post(route('password.update'), $this->resetPayload($token, [
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
        ]))->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check(self::OLD_PASSWORD, $this->student->fresh()->password));
    }

    public function test_reset_terminates_all_existing_database_sessions_of_the_user(): void
    {
        config(['session.driver' => 'database']);
        DB::table('sessions')->insert([
            'id' => 'stolen-session-id',
            'user_id' => $this->student->id,
            'ip_address' => '203.0.113.9',
            'user_agent' => 'attacker',
            'payload' => base64_encode('x'),
            'last_activity' => time(),
        ]);
        $token = Password::createToken($this->student);

        $this->post(route('password.update'), $this->resetPayload($token))->assertRedirect(route('login'));

        $this->assertDatabaseMissing('sessions', ['id' => 'stolen-session-id']);
    }

    public function test_forgot_password_endpoint_is_rate_limited_per_ip(): void
    {
        Notification::fake();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('password.email'), ['email' => "probe{$i}@fortilearn.test"])->assertStatus(302);
        }

        $this->post(route('password.email'), ['email' => 'probe9@fortilearn.test'])->assertStatus(429);
    }

    public function test_reset_form_does_not_echo_token_into_markup_unescaped(): void
    {
        $payload = '"><img src=x onerror=alert(1)>';

        $this->get(route('password.reset', ['token' => $payload, 'email' => $payload]))
            ->assertOk()
            ->assertDontSee('<img src=x onerror=alert(1)>', false);
    }
}
