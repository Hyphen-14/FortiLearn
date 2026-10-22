<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCheckoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;
    private User $student1;
    private User $student2;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Instructor & Course
        $this->instructor = User::create([
            'name' => 'Prof. Cyber Instructor',
            'email' => 'instructor@test.com',
            'password' => bcrypt('Password123!'),
            'role' => 'instructor',
            'balance' => 0.00,
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructor->id,
            'title' => 'Web Security & Penetration Testing',
            'slug' => 'web-security-pentesting',
            'description' => 'Hands-on web security defense course.',
            'price' => 200000.00, // Rp 200.000
            'status' => 'published',
        ]);

        // Setup Student 1 with Rp 500.000 (direct assignment because balance & role are unfillable by security design)
        $this->student1 = User::create([
            'name' => 'Alice Student',
            'email' => 'alice@test.com',
            'password' => bcrypt('Password123!'),
        ]);
        $this->student1->role = 'student';
        $this->student1->balance = 500000.00;
        $this->student1->is_active = true;
        $this->student1->save();

        // Setup Student 2 with Rp 50.000 (insufficient)
        $this->student2 = User::create([
            'name' => 'Bob Student',
            'email' => 'bob@test.com',
            'password' => bcrypt('Password123!'),
        ]);
        $this->student2->role = 'student';
        $this->student2->balance = 50000.00;
        $this->student2->is_active = true;
        $this->student2->save();
    }

    /**
     * Test 1: Student can browse catalog safely.
     */
    public function test_catalog_displays_published_courses_and_safe_search(): void
    {
        $response = $this->get(route('catalog.index', ['q' => 'Security']));

        $response->assertStatus(200);
        $response->assertSee('Web Security & Penetration Testing');
        $response->assertSee('200.000');
    }

    /**
     * Test 2: Top-Up validation rejects negative, zero, or overflow amounts.
     */
    public function test_top_up_validation_strictly_rejects_negative_and_zero_values(): void
    {
        $this->actingAs($this->student1);

        // Attempt 1: Negative amount (-50000)
        $responseNeg = $this->post(route('wallet.topup'), ['amount' => -50000]);
        $responseNeg->assertSessionHasErrors('amount');

        // Attempt 2: Zero amount
        $responseZero = $this->post(route('wallet.topup'), ['amount' => 0]);
        $responseZero->assertSessionHasErrors('amount');

        // Attempt 3: Below minimum (5000)
        $responseBelow = $this->post(route('wallet.topup'), ['amount' => 5000]);
        $responseBelow->assertSessionHasErrors('amount');

        // Balance must remain unchanged
        $this->assertEquals(500000.00, (float) $this->student1->fresh()->balance);
    }

    /**
     * Test 3: Successful Top-Up adds balance atomically.
     */
    public function test_student_can_top_up_balance_successfully(): void
    {
        $this->actingAs($this->student1);

        $response = $this->post(route('wallet.topup'), ['amount' => 100000]);

        $response->assertRedirect(route('wallet.index'));
        $this->assertEquals(600000.00, (float) $this->student1->fresh()->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->student1->id,
            'type' => 'topup',
            'amount' => 100000.00,
            'balance_after' => 600000.00,
        ]);
    }

    /**
     * Test 4: Successful Course Purchase with sufficient balance.
     */
    public function test_student_can_purchase_course_with_sufficient_balance(): void
    {
        $this->actingAs($this->student1);

        $response = $this->post(route('checkout.process', $this->course->id));

        // Must redirect to invoice
        $transaction = WalletTransaction::where('user_id', $this->student1->id)
            ->where('type', 'purchase')
            ->first();

        $this->assertNotNull($transaction);
        $response->assertRedirect(route('transactions.show', $transaction->id));

        // Balance should decrease from 500.000 to 300.000
        $this->assertEquals(300000.00, (float) $this->student1->fresh()->balance);

        // Enrollment record must exist
        $this->assertTrue($this->student1->hasEnrolled($this->course->id));
    }

    /**
     * Test 5: Anti-Price Tampering: Attacker attempts to inject price=0 or discount in POST request.
     */
    public function test_anti_price_tampering_ignores_client_provided_price(): void
    {
        $this->actingAs($this->student1);

        // Attacker intercepts request and injects malicious price payload
        $this->post(route('checkout.process', $this->course->id), [
            'price' => 0,
            'amount' => 1000,
            'discount' => 999999,
        ]);

        // Verify: The system ignored client payload and charged the full server DB price (Rp 200.000)
        $this->assertEquals(300000.00, (float) $this->student1->fresh()->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'user_id' => $this->student1->id,
            'type' => 'purchase',
            'amount' => 200000.00,
        ]);
    }

    /**
     * Test 6: Insufficient balance prevents purchase and rolls back.
     */
    public function test_purchase_fails_when_balance_is_insufficient(): void
    {
        $this->actingAs($this->student2); // Only has Rp 50.000, course is Rp 200.000

        $response = $this->post(route('checkout.process', $this->course->id));

        $response->assertRedirect(route('checkout.confirm', $this->course->id));
        $response->assertSessionHas('error');

        // Balance must not change (no negative balance!)
        $this->assertEquals(50000.00, (float) $this->student2->fresh()->balance);

        // Must NOT be enrolled
        $this->assertFalse($this->student2->hasEnrolled($this->course->id));
    }

    /**
     * Test 7: Anti-IDOR: Student 2 cannot access Student 1's invoice.
     */
    public function test_anti_idor_student_cannot_view_another_student_invoice(): void
    {
        // 1. Student 1 purchases course
        $this->actingAs($this->student1);
        $this->post(route('checkout.process', $this->course->id));

        $student1Transaction = WalletTransaction::where('user_id', $this->student1->id)
            ->where('type', 'purchase')
            ->firstOrFail();

        // 2. Student 1 can view their own invoice
        $responseOwner = $this->get(route('transactions.show', $student1Transaction->id));
        $responseOwner->assertStatus(200);
        $responseOwner->assertSee($student1Transaction->reference_number);

        // 3. Switch to Student 2 (Attacker) and attempt to view Student 1's invoice
        $this->actingAs($this->student2);
        $responseAttacker = $this->get(route('transactions.show', $student1Transaction->id));

        // Must be blocked with HTTP 403 Forbidden!
        $responseAttacker->assertStatus(403);
    }

    /**
     * Test 8: Anti-Duplicate Enrollment: Cannot purchase the same course twice.
     */
    public function test_student_cannot_purchase_the_same_course_twice(): void
    {
        $this->actingAs($this->student1);

        // First purchase: Success
        $this->post(route('checkout.process', $this->course->id));
        $this->assertEquals(300000.00, (float) $this->student1->fresh()->balance);

        // Second purchase attempt: Must fail
        $response = $this->post(route('checkout.process', $this->course->id));
        $response->assertSessionHas('error', 'Anda sudah terdaftar pada course ini.');

        // Balance must not be deducted again
        $this->assertEquals(300000.00, (float) $this->student1->fresh()->balance);
        $this->assertEquals(1, Enrollment::where('user_id', $this->student1->id)->where('course_id', $this->course->id)->count());
    }
}
