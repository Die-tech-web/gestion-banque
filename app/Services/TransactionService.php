<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class TransactionService
{
    /**
     * Get transactions for a specific account and calculate the balance.
     *
     * @param string $compteId
     * @return array
     */
    public function getAccountTransactions(string $compteId): array
    {
        $compte = Compte::findOrFail($compteId);

        $transactions = $compte->transactions()->orderBy('dateTransaction', 'desc')->get();

        $balance = $this->calculateBalance($transactions);

        return [
            'compte' => $compte,
            'transactions' => $transactions,
            'balance' => $balance,
        ];
    }

    /**
     * Calculate the balance based on a collection of transactions.
     *
     * @param Collection $transactions
     * @return float
     */
    private function calculateBalance(Collection $transactions): float
    {
        $depots = $transactions->where('type', 'depot')->sum('montant');
        $retraits = $transactions->where('type', 'retrait')->sum('montant');

        return $depots - $retraits;
    }
}
