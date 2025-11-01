<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Liste les transactions d’un compte pour un admin connecté.
     */
    public function showByCompte($id)
    {
        // Vérifie si le compte existe
        $compte = Compte::find($id);

        if (!$compte) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Compte introuvable'
                ]
            ], 404);
        }

        // Récupère les transactions liées à ce compte
        $transactions = Transaction::where('compte_id', $id)->get();

        // Calcule le solde = dépôts - retraits
        $solde = $transactions->where('type', 'depot')->sum('montant')
                - $transactions->where('type', 'retrait')->sum('montant');

        return response()->json([
            'success' => true,
            'data' => [
                'compte_id' => $id,
                'solde' => $solde,
                'transactions' => $transactions
            ]
        ]);
    }
}