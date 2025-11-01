<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class TransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/comptes/{id}/transactions",
     *     summary="Liste les transactions d’un compte spécifique (Admin seulement)",
     *     tags={"Comptes"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des transactions et solde du compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="compte_id", type="string", format="uuid", example="c9c9d947-2fef-4bfc-b8c6-a2cf8bdcdfef"),
     *                 @OA\Property(property="solde", type="number", format="float", example=1500.00),
     *                 @OA\Property(property="transactions", type="array",
     *                     @OA\Items(ref="#/components/schemas/Transaction")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte introuable",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Compte introuvable")
     *             )
     *         )
     *     )
     * )
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
