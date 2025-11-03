<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class ClientTransactionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/comptes/{compte_id}/transactions",
     *     summary="Liste des transactions d'un compte pour un client authentifié",
     *     tags={"Transactions Client"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="compte_id",
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
     *             @OA\Property(property="message", type="string", example="Liste des transactions du compte"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="client", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", example="cli_12345"),
     *                     @OA\Property(property="titulaire", type="string", example="nom Niang")
     *                 ),
     *                 @OA\Property(property="compte_id", type="string", format="uuid", example="cmp_001"),
     *                 @OA\Property(property="solde", type="number", format="float", example=150000),
     *                 @OA\Property(property="transactions", type="array",
     *                     @OA\Items(ref="#/components/schemas/Transaction")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé ou accès non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Compte non trouvé ou accès non autorisé")
     *         )
     *     )
     * )
     */
    public function showByCompte($compte_id)
    {
        // Récupérer l'utilisateur authentifié (le middleware IsClient a déjà vérifié)
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non authentifié'
            ], 401);
        }

        $client = $user->client()->first(); // Charger explicitement la relation

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        // Vérifier si le compte appartient au client
        $compte = Compte::where('id', $compte_id)
                        ->where('client_id', $client->id)
                        ->first();

        if (!$compte) {
            return response()->json([
                'success' => false,
                'message' => 'Compte non trouvé ou accès non autorisé'
            ], 404);
        }

        // Récupérer les transactions du compte
        $transactions = Transaction::where('compte_id', $compte_id)->get();

        // Calculer le solde
        $solde = $transactions->where('type', 'depot')->sum('montant')
                - $transactions->where('type', 'retrait')->sum('montant');

        // Retourner la réponse au format spécifié
        return response()->json([
            'success' => true,
            'message' => 'Liste des transactions du compte',
            'data' => [
                'client' => [
                    'id' => $client->id,
                    'titulaire' => $user->name // Nom et prénom du titulaire
                ],
                'compte_id' => $compte_id,
                'solde' => $solde,
                'transactions' => $transactions
            ]
        ]);
    }
}