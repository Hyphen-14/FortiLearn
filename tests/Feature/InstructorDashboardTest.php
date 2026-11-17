<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email, float $balance = 0): User
    {
        $u = User::create(['name' => 'Person-' . strtok($email, '@'), 'email' => $email, 'password' => 'Password123!']);
        $u->role = $role;
        $u->balance = $balance;
        $u->is_active = true;
        $u->save();

        return $u;
    }

    private function makeCourse(User $instructor, string $slug, string $status = 'published', float $price = 100000): Course
    {
        $c = new Course(['title' => "Course $slug", 'category' => 'Frontend', 'description' => 'Desc', 'price' => $price]);
        $c->instructor_id = $instructor->id;
        $c->slug = $slug;
        $c->status = $status;
        $c->save();

        return $c;
    }

    private function buy(User $student, Course $course): void
    {
        Enrollment::create(['user_id' => $student->id, 'course_id' => $course->id, 'enrolled_at' => now()]);
        WalletTransaction::create([
            'reference_number' => 'T-' . $student->id . '-' . $course->id,
            'user_id' => $student->id, 'course_id' => $course->id, 'type' => 'purchase',
            'amount' => $course->price, 'balance_before' => 500000, 'balance_after' => 400000, 'status' => 'completed',
        ]);
    }

    public function test_instructor_login_lands_on_studio_dashboard(): void
    {
        $this->makeUser('instructor', 'ins@test.com');

        $this->post('/login', ['email' => 'ins@test.com', 'password' => 'Password123!'])
            ->assertRedirect(route('instructor.dashboard'));
    }

    public function test_dashboard_uses_instructor_layout_and_only_own_numbers(): void
    {
        $ins = $this->makeUser('instructor', 'ins@test.com');
        $other = $this->makeUser('instructor', 'other@test.com');
        $s1 = $this->makeUser('student', 's1@test.com');
        $s2 = $this->makeUser('student', 's2@test.com');

        $mine = $this->makeCourse($ins, 'mine', 'published', 100000);
        $theirs = $this->makeCourse($other, 'theirs', 'published', 999000);
        $this->buy($s1, $mine);
        $this->buy($s2, $theirs);

        $res = $this->actingAs($ins)->get(route('instructor.dashboard'));

        $res->assertOk()
            ->assertSee('Studio Instruktur')
            ->assertSee('Rp 100.000')
            ->assertDontSee('Rp 999.000')
            ->assertDontSee('Dompet Belajar');   // student navigation must not leak into the studio
    }

    public function test_student_and_guest_cannot_open_studio_pages(): void
    {
        $student = $this->makeUser('student', 's@test.com');

        $routes = ['instructor.dashboard', 'instructor.sales.index', 'instructor.reviews.index'];

        foreach ($routes as $name) {
            $this->get(route($name))->assertRedirect(route('login'));
        }
        foreach ($routes as $name) {
            $this->actingAs($student)->get(route($name))->assertForbidden();
        }
    }

    public function test_instructor_cannot_open_student_wallet_pages(): void
    {
        $ins = $this->makeUser('instructor', 'ins@test.com');

        $this->actingAs($ins)->get(route('wallet.index'))->assertForbidden();
        $this->actingAs($ins)->get(route('transactions.index'))->assertForbidden();
    }

    public function test_sales_page_lists_only_own_buyers_and_ignores_foreign_course_filter(): void
    {
        $ins = $this->makeUser('instructor', 'ins@test.com');
        $other = $this->makeUser('instructor', 'other@test.com');
        $mineBuyer = $this->makeUser('student', 'a@test.com');
        $foreignBuyer = $this->makeUser('student', 'b@test.com');

        $mine = $this->makeCourse($ins, 'mine');
        $theirs = $this->makeCourse($other, 'theirs');
        $this->buy($mineBuyer, $mine);
        $this->buy($foreignBuyer, $theirs);

        $this->actingAs($ins)->get(route('instructor.sales.index'))
            ->assertOk()->assertSee($mineBuyer->name)->assertDontSee($foreignBuyer->name)->assertDontSee('a@test.com');

        // Filtering by someone else's course id must not reveal their buyers.
        $this->actingAs($ins)->get(route('instructor.sales.index', ['course' => $theirs->id]))
            ->assertOk()->assertDontSee($foreignBuyer->name);
    }

    public function test_only_enrolled_student_can_review_and_one_review_per_course(): void
    {
        $ins = $this->makeUser('instructor', 'ins@test.com');
        $buyer = $this->makeUser('student', 'buyer@test.com');
        $outsider = $this->makeUser('student', 'out@test.com');
        $course = $this->makeCourse($ins, 'c1');
        $this->buy($buyer, $course);

        $this->actingAs($outsider)->post(route('reviews.store', $course->id), ['rating' => 5])->assertSessionHas('error');
        $this->assertSame(0, Review::count());

        $this->actingAs($buyer)->post(route('reviews.store', $course->id), ['rating' => 4, 'comment' => 'Bagus'])->assertRedirect();
        $this->actingAs($buyer)->post(route('reviews.store', $course->id), ['rating' => 5, 'comment' => 'Sangat bagus'])->assertRedirect();

        $this->assertSame(1, Review::count());
        $this->assertSame(5, Review::first()->rating);
    }

    public function test_review_input_is_validated_and_html_is_neutralised(): void
    {
        $ins = $this->makeUser('instructor', 'ins@test.com');
        $buyer = $this->makeUser('student', 'buyer@test.com');
        $course = $this->makeCourse($ins, 'c1');
        $this->buy($buyer, $course);

        $this->actingAs($buyer)->post(route('reviews.store', $course->id), ['rating' => 9])->assertSessionHasErrors('rating');
        $this->actingAs($buyer)->post(route('reviews.store', $course->id), ['rating' => 0])->assertSessionHasErrors('rating');

        $this->actingAs($buyer)->post(route('reviews.store', $course->id), [
            'rating' => 5, 'comment' => '<script>alert(1)</script>Mantap',
        ]);
        $this->assertStringNotContainsString('<script>', Review::first()->comment);

        // Client-supplied user_id must never be honoured.
        $this->actingAs($buyer)->post(route('reviews.store', $course->id), ['rating' => 3, 'user_id' => $ins->id]);
        $this->assertSame($buyer->id, Review::first()->user_id);
    }

    public function test_instructor_cannot_post_review_and_unpublished_course_is_hidden(): void
    {
        $ins = $this->makeUser('instructor', 'ins@test.com');
        $draft = $this->makeCourse($ins, 'draft', 'draft');
        $published = $this->makeCourse($ins, 'pub');

        $this->actingAs($ins)->post(route('reviews.store', $published->id), ['rating' => 5])->assertForbidden();

        $buyer = $this->makeUser('student', 'buyer@test.com');
        $this->buy($buyer, $draft);
        $this->actingAs($buyer)->post(route('reviews.store', $draft->id), ['rating' => 5])->assertNotFound();
    }

    public function test_reviews_page_shows_own_reviews_with_average(): void
    {
        $ins = $this->makeUser('instructor', 'ins@test.com');
        $buyer = $this->makeUser('student', 'buyer@test.com');
        $course = $this->makeCourse($ins, 'c1');
        $this->buy($buyer, $course);

        $this->actingAs($buyer)->post(route('reviews.store', $course->id), ['rating' => 4, 'comment' => 'Materi jelas']);

        $this->actingAs($ins)->get(route('instructor.reviews.index'))
            ->assertOk()->assertSee('Materi jelas')->assertSee('4.0');
    }
}
