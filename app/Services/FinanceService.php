<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    public function getSafeToSpend(User $user): float
    {
        $totalIncome = $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpense = $user->transactions()->where('type', 'expense')->sum('amount');
        $netLiquid = $totalIncome - $totalExpense;

        $committedSavings = $user->pockets()->where('is_active', true)->sum('current_amount');

        $memories = $user->harveyMemories()->where('is_active', true)->get();
        $unpaidFixedBills = 0;

        foreach ($memories as $memory) {
            $paidThisMonth = $user->transactions()
                ->where('category_id', $memory->category_id)
                ->where('type', 'expense')
                ->whereMonth('transaction_date', Carbon::now()->month)
                ->whereYear('transaction_date', Carbon::now()->year)
                ->sum('amount');

            $restBills = max(0, $memory->amount - $paidThisMonth);
            $unpaidFixedBills += $restBills;
        }

        $safeToSpend = $netLiquid - $committedSavings - $unpaidFixedBills;

        return $safeToSpend;
    }

    public function processAutoAllocate(User $user, float $incomeAmount): void
    {
        DB::transaction(function () use ($user, $incomeAmount) {
            $activePockets = $user->pockets()
                ->where('is_active', true)
                ->whereNotNull('auto_allocate_percentage')
                ->get();

            foreach ($activePockets as $pocket) {
                $allocationAmount = $incomeAmount * ($pocket->auto_allocate_percentage / 100);

                if ($allocationAmount > 0) {
                    $pocket->increment('current_amount', $allocationAmount);

                    Transaction::create([
                        'user_id' => $user->id,
                        'pocket_id' => $pocket->id,
                        'amount' => $allocationAmount,
                        'type' => 'transfer',
                        'description' => "Auto-allocate to {$pocket->name}",
                        'source' => 'system',
                        'transaction_date' => Carbon::now(),
                    ]);
                }
            }
        });
    }

    public function processAutoSweep(User $user): void
    {
        DB::transaction(function () use ($user) {
            $sweepTarget = $user->pockets()->where('is_sweep_target', true)->first();
            if (! $sweepTarget) {
                return;
            }

            $safeToSpend = $this->getSafeToSpend($user);

            if ($safeToSpend > 0) {
                $sweepTarget->increment('current_amount', $safeToSpend);

                Transaction::create([
                    'user_id' => $user->id,
                    'pocket_id' => $sweepTarget->id,
                    'amount' => $safeToSpend,
                    'type' => 'transfer',
                    'description' => 'End of month safe-to-spend auto-sweep',
                    'source' => 'system',
                    'transaction_date' => Carbon::now(),
                ]);
            }
        });
    }
}
