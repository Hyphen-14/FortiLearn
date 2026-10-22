<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\WalletTransaction;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Top-up internal wallet with strict validation and atomic transaction.
     *
     * @throws Exception
     */
    public function topUpBalance(User $user, float $amount): WalletTransaction
    {
        if ($amount <= 0 || $amount > 10000000) { // Limit max top up 10 juta
            throw new Exception('Nominal top-up tidak valid (harus antara Rp 10.000 hingga Rp 10.000.000).');
        }

        return DB::transaction(function () use ($user, $amount) {
            // Pessimistic Locking to protect concurrent balance updates
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $balanceBefore = (float) $lockedUser->balance;
            $balanceAfter = $balanceBefore + $amount;

            $lockedUser->balance = $balanceAfter;
            $lockedUser->save();

            return WalletTransaction::create([
                'reference_number' => 'TRX-TOPUP-' . strtoupper(Str::random(10)),
                'user_id' => $lockedUser->id,
                'course_id' => null,
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'description' => 'Top-up saldo wallet internal',
            ]);
        });
    }

    /**
     * Purchase course with atomic transaction & pessimistic locking (anti-race condition & price tampering).
     *
     * @throws Exception
     */
    public function purchaseCourse(User $user, int $courseId): array
    {
        return DB::transaction(function () use ($user, $courseId) {
            // 1. Pessimistic Lock on User row (prevents concurrent race condition/double-spending)
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            // 2. Anti-Price Tampering: Always fetch price directly from DB
            $course = Course::where('id', $courseId)->firstOrFail();

            if ($course->status !== 'published') {
                throw new Exception('Course ini tidak tersedia untuk dibeli.');
            }

            // 3. Prevent duplicate enrollment
            $alreadyEnrolled = Enrollment::where('user_id', $lockedUser->id)
                ->where('course_id', $course->id)
                ->exists();

            if ($alreadyEnrolled) {
                throw new Exception('Anda sudah terdaftar pada course ini.');
            }

            $price = (float) $course->price;
            $currentBalance = (float) $lockedUser->balance;

            // 4. Strict balance check
            if ($currentBalance < $price) {
                throw new Exception('Saldo tidak mencukupi untuk membeli course ini.');
            }

            // 5. Atomic Balance Deduction
            $newBalance = $currentBalance - $price;
            $lockedUser->balance = $newBalance;
            $lockedUser->save();

            // 6. Create Transaction Log
            $transaction = WalletTransaction::create([
                'reference_number' => 'TRX-BUY-' . strtoupper(Str::random(10)),
                'user_id' => $lockedUser->id,
                'course_id' => $course->id,
                'type' => 'purchase',
                'amount' => $price,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'status' => 'completed',
                'description' => 'Pembelian course: ' . $course->title,
            ]);

            // 7. Grant Enrollment
            $enrollment = Enrollment::create([
                'user_id' => $lockedUser->id,
                'course_id' => $course->id,
                'enrolled_at' => now(),
            ]);

            return [
                'transaction' => $transaction,
                'enrollment' => $enrollment,
            ];
        });
    }
}

