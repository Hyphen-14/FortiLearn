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
        if ($amount < 10000 || $amount > 10000000) {
            throw new Exception('Nominal top-up tidak valid (harus antara Rp 10.000 hingga Rp 10.000.000).');
        }

        return DB::transaction(function () use ($user, $amount) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $balanceBefore = (float) $lockedUser->balance;
            $balanceAfter = round($balanceBefore + $amount, 2);

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

    public function purchaseCourse(User $user, int $courseId): array
    {
        return DB::transaction(function () use ($user, $courseId) {
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $course = Course::where('id', $courseId)->firstOrFail();

            if ($course->status !== 'published') {
                throw new Exception('Course ini tidak tersedia untuk dibeli.');
            }

            $alreadyEnrolled = Enrollment::where('user_id', $lockedUser->id)
                ->where('course_id', $course->id)
                ->exists();

            if ($alreadyEnrolled) {
                throw new Exception('Anda sudah terdaftar pada course ini.');
            }

            $currentBalance = (float) $lockedUser->balance;
            $usedFreeCredit = false;

            if ((int) $lockedUser->free_course_credits > 0) {
                $usedFreeCredit = true;
                $effectivePrice = 0.00;
                $newBalance = $currentBalance;
                $lockedUser->free_course_credits = (int) $lockedUser->free_course_credits - 1;
            } else {
                $effectivePrice = (float) $course->price;
                if ($currentBalance < $effectivePrice) {
                    throw new Exception('Saldo tidak mencukupi untuk membeli course ini.');
                }
                $newBalance = round($currentBalance - $effectivePrice, 2);
                if ($newBalance < 0) {
                    throw new Exception('Saldo tidak mencukupi untuk membeli course ini.');
                }
                $lockedUser->balance = $newBalance;
            }

            $lockedUser->save();

            $transaction = WalletTransaction::create([
                'reference_number' => 'TRX-BUY-' . strtoupper(Str::random(10)),
                'user_id' => $lockedUser->id,
                'course_id' => $course->id,
                'type' => 'purchase',
                'amount' => $effectivePrice,
                'balance_before' => $currentBalance,
                'balance_after' => $newBalance,
                'status' => 'completed',
                'description' => $usedFreeCredit
                    ? 'Klaim kursus gratis pertama: ' . $course->title
                    : 'Pembelian course: ' . $course->title,
            ]);

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

