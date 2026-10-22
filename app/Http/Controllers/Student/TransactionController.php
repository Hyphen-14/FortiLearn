<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of the user's transactions.
     * Only displays records owned by the authenticated student.
     */
    public function index(): View
    {
        $transactions = WalletTransaction::with('course')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('transactions.index', [
            'transactions' => $transactions,
        ]);
    }

    /**
     * Display a specific transaction receipt/invoice.
     * Security:
     * Anti-IDOR: Gate::authorize('viewReceipt', $transaction) strictly aborts with HTTP 403
     * if the logged-in user is not the owner (and not an admin).
     */
    public function show(WalletTransaction $transaction): View
    {
        Gate::authorize('viewReceipt', $transaction);

        $transaction->load(['course', 'user']);

        return view('transactions.invoice', [
            'transaction' => $transaction,
        ]);
    }
}

