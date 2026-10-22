<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\TopUpRequest;
use App\Models\WalletTransaction;
use App\Services\PaymentService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WalletController extends Controller
{
    /**
     * Display the student's internal wallet balance and recent transactions.
     */
    public function index(): View
    {
        $user = auth()->user();

        $recentTransactions = WalletTransaction::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('wallet.index', [
            'user' => $user,
            'recentTransactions' => $recentTransactions,
        ]);
    }

    /**
     * Process internal wallet top-up simulation.
     */
    public function topUp(TopUpRequest $request, PaymentService $paymentService): RedirectResponse
    {
        $amount = (float) $request->validated('amount');

        try {
            $transaction = $paymentService->topUpBalance(auth()->user(), $amount);

            return redirect()->route('wallet.index')
                ->with('success', 'Top-up sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil diproses! Saldo baru: Rp ' . number_format($transaction->balance_after, 0, ',', '.'));
        } catch (Exception $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
