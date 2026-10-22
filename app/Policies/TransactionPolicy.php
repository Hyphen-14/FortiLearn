<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WalletTransaction;

class TransactionPolicy
{
    /**
     * Determine whether the user can view the specific transaction receipt/invoice.
     * Prevents IDOR (Insecure Direct Object References).
     */
    public function viewReceipt(User $user, WalletTransaction $transaction): bool
    {
        // Admin can audit all transactions
        if ($user->isAdmin()) {
            return true;
        }

        // Student can strictly ONLY view their own transaction
        return $transaction->user_id === $user->id;
    }
}

