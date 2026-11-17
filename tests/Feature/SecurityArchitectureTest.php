<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\FileUploadService;
use App\Services\PaymentService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class SecurityArchitectureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $instructor;
    private User $student;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        // Setup Admin User
        $this->admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@secureacademy.test',
            'password' => bcrypt('PasswordAdmin123!'),
        ]);
        $this->admin->role = 'admin';
        $this->admin->is_active = true;
        $this->admin->save();

        // Setup Instructor User
        $this->instructor = User::create([
            'name' => 'Dr. Security Instructor',
            'email' => 'instructor@secureacademy.test',
            'password' => bcrypt('PasswordInstruktur123!'),
        ]);
        $this->instructor->role = 'instructor';
        $this->instructor->is_active = true;
        $this->instructor->save();

        // Setup Student User
        $this->student = User::create([
            'name' => 'Bob Student',
            'email' => 'student@secureacademy.test',
            'password' => bcrypt('PasswordStudent123!'),
        ]);
        $this->student->role = 'student';
        $this->student->balance = 200000.00;
        $this->student->is_active = true;
        $this->student->save();

        // Setup Sample Course
        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Advanced Cyber Security Defense',
            'slug' => 'advanced-cyber-security-defense',
            'description' => 'Comprehensive security defense course.',
            'price' => 200000.00,
            'status' => 'published',
        ]);
    }

    /**
     * Helper to create real temporary files for strict MIME and magic bytes inspection.
     */
    private function createTempUploadFile(string $filename, string $content, ?string $mime = null): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'sec_test_');
        file_put_contents($tempPath, $content);

        return new UploadedFile($tempPath, $filename, $mime, null, true);
    }

    // =========================================================================
    // 1. SECURITY HEADERS MIDDLEWARE TESTS
    // =========================================================================

    /**
     * Test: Responses contain hardened security headers configured by SecurityHeaders middleware.
     * Verifies: X-Content-Type-Options, X-Frame-Options, Content-Security-Policy, Referrer-Policy.
     */
    public function test_security_headers_middleware_attaches_all_required_headers(): void
    {
        $response = $this->get(route('catalog.index'));

        $response->assertStatus(200);

        // 1. Prevent MIME-sniffing
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        // 2. Prevent Clickjacking
        $response->assertHeader('X-Frame-Options', 'DENY');

        // 3. Prevent XSS via Content Security Policy (CSP)
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);

        // 4. Referrer Policy
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Additional defense headers
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    // =========================================================================
    // 2. BROKEN ACCESS CONTROL (RBAC) TESTS
    // =========================================================================

    /**
     * Test: Unauthenticated guest attempting to access admin route is redirected to login.
     */
    public function test_broken_access_control_guest_redirected_from_admin_route(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * Test: Authenticated Student is blocked with HTTP 403 Forbidden from accessing Admin routes.
     */
    public function test_broken_access_control_student_forbidden_from_admin_dashboard(): void
    {
        $this->actingAs($this->student);

        // Attempt accessing admin dashboard
        $responseDashboard = $this->get('/admin/dashboard');
        $responseDashboard->assertStatus(403);

        // Attempt accessing admin users management
        $responseUsers = $this->get('/admin/users');
        $responseUsers->assertStatus(403);

        // Attempt accessing admin courses moderation
        $responseCourses = $this->get('/admin/courses');
        $responseCourses->assertStatus(403);
    }

    /**
     * Test: Authenticated Instructor is blocked with HTTP 403 Forbidden from accessing Admin routes.
     */
    public function test_broken_access_control_instructor_forbidden_from_admin_dashboard(): void
    {
        $this->actingAs($this->instructor);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /**
     * Test: Legitimate Admin user can access Admin dashboard successfully.
     */
    public function test_authorized_admin_can_access_admin_dashboard(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Ringkasan Admin');
    }

    // =========================================================================
    // 3. MASS ASSIGNMENT VULNERABILITY TESTS
    // =========================================================================

    /**
     * Test: Eloquent User model ignores role and balance in mass assignment (User::create).
     */
    public function test_mass_assignment_model_level_cannot_elevate_role_or_tamper_balance(): void
    {
        $user = User::create([
            'name' => 'Attacker Eve',
            'email' => 'eve@example.com',
            'password' => bcrypt('Password123!'),
            'role' => 'admin',           // Injected malicious attribute
            'balance' => 9999999.00,     // Injected malicious balance
            'is_active' => true,
        ]);

        $user->refresh();

        // Model must strictly ignore role and balance
        $this->assertEquals('student', $user->role, 'Mass assignment vulnerability detected! Role was elevated.');
        $this->assertEquals(0.00, (float) $user->balance, 'Mass assignment vulnerability detected! Balance was tampered.');
    }

    /**
     * Test: HTTP Registration ignores injected role and balance parameters.
     */
    public function test_mass_assignment_http_registration_ignores_injected_role_and_balance(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Mallory Hacker',
            'email' => 'mallory@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'admin',           // Attempt privilege escalation
            'balance' => 8888888.00,     // Attempt balance injection
        ]);

        $response->assertRedirect(route('catalog.index'));

        $user = User::where('email', 'mallory@example.com')->firstOrFail();

        $this->assertEquals('student', $user->role, 'HTTP Mass Assignment succeeded: role was elevated!');
        $this->assertEquals(0.00, (float) $user->balance, 'HTTP Mass Assignment succeeded: balance was injected!');
    }

    /**
     * Test: Model update ignores mass-assigned role and balance modifications.
     */
    public function test_mass_assignment_update_cannot_tamper_role_or_balance(): void
    {
        $this->student->update([
            'name' => 'Updated Bob',
            'role' => 'admin',
            'balance' => 7777777.00,
        ]);

        $this->student->refresh();

        $this->assertEquals('Updated Bob', $this->student->name);
        $this->assertEquals('student', $this->student->role);
        $this->assertEquals(200000.00, (float) $this->student->balance);
    }

    // =========================================================================
    // 4. FILE UPLOAD & WEBSHELL ATTACK TESTS (UNIT & FEATURE)
    // =========================================================================

    /**
     * Unit Test: FileUploadService rejects executable webshell with PHP code.
     */
    public function test_unit_file_upload_service_rejects_webshell_php_code(): void
    {
        $service = new FileUploadService();
        $fakeFile = $this->createTempUploadFile('webshell.php', "<?php system(\$_GET['cmd']); ?>");

        $this->expectException(InvalidArgumentException::class);
        $service->storeSecureLessonFile($fakeFile, 'video');
    }

    /**
     * Unit Test: FileUploadService rejects short open tag and script injection.
     */
    public function test_unit_file_upload_service_rejects_short_open_tags_and_scripts(): void
    {
        $service = new FileUploadService();

        // 1. Short open tag execution
        $shortTagFile = $this->createTempUploadFile('backdoor.php', "<?= passthru(\$_REQUEST['c']); ?>");
        try {
            $service->storeSecureLessonFile($shortTagFile, 'video');
            $this->fail('Short open tag PHP execution was not blocked.');
        } catch (InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        // 2. Script injection in document
        $scriptFile = $this->createTempUploadFile('xss.pdf', "<script>alert('XSS')</script>");
        try {
            $service->storeSecureLessonFile($scriptFile, 'pdf');
            $this->fail('Script tag injection was not blocked.');
        } catch (InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    /**
     * Unit Test: FileUploadService rejects disguised extensions (MIME & extension spoofing).
     */
    public function test_unit_file_upload_service_rejects_disguised_extensions(): void
    {
        $service = new FileUploadService();

        // Double extension disguise
        $doubleExtFile = $this->createTempUploadFile('lesson.php.mp4', "<?php phpinfo(); ?>");

        $this->expectException(InvalidArgumentException::class);
        $service->storeSecureLessonFile($doubleExtFile, 'video');
    }

    /**
     * Unit Test: FileUploadService rejects oversized files to prevent DoS.
     */
    public function test_unit_file_upload_service_rejects_oversized_files(): void
    {
        $service = new FileUploadService();

        // Fake file exceeding 10MB document limit (11MB)
        $oversizedFile = UploadedFile::fake()->create('huge.pdf', 11264);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Ukuran file melebihi batas maksimum yang diizinkan.');

        $service->storeSecureLessonFile($oversizedFile, 'pdf');
    }

    /**
     * Feature Test: HTTP endpoint rejects webshell upload attempt from Instructor and prevents disk storage.
     */
    public function test_feature_http_upload_rejects_webshell_and_never_stores_executable_file(): void
    {
        $this->actingAs($this->instructor);

        $maliciousFile = $this->createTempUploadFile(
            'c99_shell.php',
            "<?php echo 'webshell active'; system(\$_GET['cmd']); ?>"
        );

        $response = $this->postJson(route('instructor.lessons.upload', $this->course->id), [
            'file' => $maliciousFile,
            'type' => 'video',
        ]);

        // Must be rejected with HTTP 422 Unprocessable Entity
        $response->assertStatus(422);
        $response->assertJsonStructure(['error']);

        // Verify storage is completely clean: no executable files written
        $storedFiles = Storage::disk('local')->allFiles('private/courses');
        $this->assertEmpty($storedFiles, 'Webshell file was improperly stored in private storage!');
    }

    // =========================================================================
    // 5. CONCURRENCY & RACE CONDITION WALLET TESTING
    // =========================================================================

    /**
     * Feature Test: Concurrency / Pessimistic Locking prevents Double-Spending.
     * Scenario: Student has Rp 200.000 balance. Two checkout requests compete for the same balance.
     * Only 1 transaction may succeed, the second must be rejected, and final balance must never be negative.
     */
    public function test_concurrency_pessimistic_locking_prevents_double_spending_and_negative_balance(): void
    {
        // Initial state: Balance is Rp 200.000, Course price is Rp 200.000
        $this->assertEquals(200000.00, (float) $this->student->balance);

        $paymentService = new PaymentService();

        $successCount = 0;
        $failedCount = 0;
        $failureMessages = [];

        // Simulate two sequential/parallel purchase executions on the same user and course
        for ($i = 0; $i < 2; $i++) {
            try {
                // Execute transaction protected by lockForUpdate()
                $result = $paymentService->purchaseCourse($this->student, $this->course->id);
                $successCount++;
            } catch (Exception $e) {
                $failedCount++;
                $failureMessages[] = $e->getMessage();
            }
        }

        // Verify: Exactly 1 purchase succeeded and exactly 1 failed
        $this->assertEquals(1, $successCount, 'Double-spending vulnerability detected! More than one transaction succeeded.');
        $this->assertEquals(1, $failedCount, 'Concurrent second transaction was not rejected.');

        // Verify: Balance deduction is atomic and exactly Rp 0.00 (NEVER negative!)
        $this->student->refresh();
        $this->assertEquals(0.00, (float) $this->student->balance, 'Balance tampered or negative balance produced!');

        // Verify: Exactly 1 enrollment and 1 purchase transaction in DB
        $this->assertEquals(1, Enrollment::where('user_id', $this->student->id)->where('course_id', $this->course->id)->count());
        $this->assertEquals(1, WalletTransaction::where('user_id', $this->student->id)->where('type', 'purchase')->count());
    }

    /**
     * Feature Test: Insufficient balance transaction strictly rolls back without state changes.
     */
    public function test_purchase_strictly_rolls_back_when_balance_is_insufficient(): void
    {
        // Student with 0 balance
        $poorStudent = User::create([
            'name' => 'Poor Student',
            'email' => 'poor@example.com',
            'password' => bcrypt('password123'),
        ]);
        $poorStudent->balance = 50000.00; // Less than Rp 200.000
        $poorStudent->save();

        $paymentService = new PaymentService();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Saldo tidak mencukupi untuk membeli course ini.');

        try {
            $paymentService->purchaseCourse($poorStudent, $this->course->id);
        } finally {
            // Assert rollback integrity
            $poorStudent->refresh();
            $this->assertEquals(50000.00, (float) $poorStudent->balance);
            $this->assertFalse($poorStudent->hasEnrolled($this->course->id));
            $this->assertDatabaseMissing('wallet_transactions', [
                'user_id' => $poorStudent->id,
                'type' => 'purchase',
            ]);
        }
    }
}
