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
    /**
     * Display checkout confirmation page.
     * Anti-tampering: Price is fetched directly from database.
     */
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
        $isBalanceSufficient = $userBalance >= $coursePrice;
        $remainingBalance = $isBalanceSufficient ? ($userBalance - $coursePrice) : 0;

        return view('checkout.confirm', [
            'course' => $course,
            'user' => $user,
            'userBalance' => $userBalance,
            'coursePrice' => $coursePrice,
            'isBalanceSufficient' => $isBalanceSufficient,
            'remainingBalance' => $remainingBalance,
        ]);
    }

    /**
     * Process course purchase atomically.
     * Security:
     * 1. Price is NEVER accepted from $request->all() (Anti-Price Tampering).
     * 2. Transaction uses DB lockForUpdate (Anti-Race Condition & Double Spending).
     * 3. CSRF token verified by Laravel VerifyCsrfToken middleware.
     */
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

