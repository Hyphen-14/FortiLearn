<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Services\FileUploadService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use Tests\TestCase;

class SecurityArchitectureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Mass Assignment protection on User model.
     * Attack: User attempts to register/update with role=admin and huge balance.
     */
    public function test_mass_assignment_cannot_elevate_role_or_tamper_balance(): void
    {
        $user = User::create([
            'name' => 'Hacker Bob',
            'email' => 'hacker@example.com',
            'password' => bcrypt('Secret123!'),
            'role' => 'admin',       // Injected malicious attribute
            'balance' => 9999999.00, // Injected malicious balance
        ]);

        $user->refresh();

        // Assert: Model must strictly ignore unfillable role and balance
        $this->assertEquals('student', $user->role, 'Mass assignment vulnerability detected! Role was elevated.');
        $this->assertEquals(0.00, (float) $user->balance, 'Mass assignment vulnerability detected! Balance was modified.');
    }

    /**
     * Test 2: File Upload Service rejects executable / webshell files.
     * Attack: Malicious user uploads PHP shell masquerading as video/document.
     */
    public function test_file_upload_service_rejects_webshell_and_unauthorized_mimes(): void
    {
        $service = new FileUploadService();

        // Simulate a webshell file masquerading as video
        $fakeFile = UploadedFile::fake()->createWithContent(
            'shell.php',
            "<?php system(\$_GET['cmd']); ?>"
        );

        $this->expectException(InvalidArgumentException::class);
        $service->storeSecureLessonFile($fakeFile, 'video');
    }

    /**
     * Test 3: Payment Service prevents purchasing when balance is insufficient.
     */
    public function test_payment_service_prevents_purchasing_with_insufficient_balance(): void
    {
        $instructor = User::create([
            'name' => 'Instructor',
            'email' => 'inst@test.com',
            'password' => bcrypt('password'),
        ]);

        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Advanced Cyber Defense',
            'slug' => 'advanced-cyber-defense',
            'description' => 'Course description',
            'price' => 250000.00,
            'status' => 'published',
        ]);

        $brokeStudent = User::create([
            'name' => 'Broke Student',
            'email' => 'broke@test.com',
            'password' => bcrypt('password'),
        ]);
        // Default balance is 0.00

        $paymentService = new PaymentService();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Saldo tidak mencukupi untuk membeli course ini.');

        $paymentService->purchaseCourse($brokeStudent, $course->id);
    }
}
