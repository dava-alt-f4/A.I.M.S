<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\FinanceService;

class TransactionObserver
{
    public function __construct(protected FinanceService $financeService) {}

    public function created(Transaction $transaction): void
    {
        if ($transaction->type === 'income' && $transaction->source !== 'system') {
            $this->financeService->processAutoAllocate(
                $transaction->user,
                (float) $transaction->amount,
            );
        }
    }
}
