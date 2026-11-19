<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function showCheckout(Course $course): View|RedirectResponse
    {
        if ($course->status !== 'published') {
            return redirect()->route('catalog.index')
                ->with('error', 'Course ini tidak tersedia untuk pendaftaran.');
        }

        $user = auth()->user();

        // Check if student already enrolled
        if ($user->hasEnrolled($course->id)) {
            return redirect()->route('catalog.show', $course->slug)
                ->with('info', 'Anda sudah terdaftar di course ini.');
        }

        $userBalance = (float) $user->balance;
        $coursePrice = (float) $course->price;
        $hasFreeCourseCredit = ((int) $user->free_course_credits) > 0;
        $effectivePrice = $hasFreeCourseCredit ? 0.00 : $coursePrice;
        $isBalanceSufficient = $hasFreeCourseCredit || ($userBalance >= $coursePrice);
        $remainingBalance = $isBalanceSufficient ? round($userBalance - $effectivePrice, 2) : 0;

        return view('checkout.confirm', [
            'course' => $course,
            'user' => $user,
            'userBalance' => $userBalance,
            'coursePrice' => $coursePrice,
            'hasFreeCourseCredit' => $hasFreeCourseCredit,
            'effectivePrice' => $effectivePrice,
            'isBalanceSufficient' => $isBalanceSufficient,
            'remainingBalance' => $remainingBalance,
        ]);
    }

    public function processCheckout(Request $request, Course $course, PaymentService $paymentService): RedirectResponse
    {
        $user = auth()->user();

        try {
            $result = $paymentService->purchaseCourse($user, $course->id);
            $transaction = $result['transaction'];

            return redirect()->route('transactions.show', $transaction->id)
                ->with('success', 'Pembelian berhasil! Anda kini resmi terdaftar di kelas ' . $course->title . '.');
        } catch (Exception $e) {
            return redirect()->route('checkout.confirm', $course->id)
                ->with('error', $e->getMessage());
        }
    }
}

