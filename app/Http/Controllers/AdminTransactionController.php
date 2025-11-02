<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\Compte;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use App\Traits\ApiResponseTrait;

class AdminTransactionController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *     path="/api/v1/admin/transactions",
     *     summary="Créer une nouvelle transaction (Admin seulement)",
     *     tags={"Transactions Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"compte_id", "type", "montant", "description"},
     *             @OA\Property(property="compte_id", type="string", format="uuid", example="C001456"),
     *             @OA\Property(property="type", type="string", enum={"DEPOT", "RETRAIT"}, example="DEPOT"),
     *             @OA\Property(property="montant", type="number", format="float", example=500000),
     *             @OA\Property(property="description", type="string", minLength=5, maxLength=255, example="Dépôt espèces guichet"),
     *             @OA\Property(property="validation_automatique", type="boolean", example=true, description="Optionnel, défaut: false")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaction créée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transaction", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="numero_transaction", type="string", example="TR0001-20231102"),
     *                     @OA\Property(property="compte_id", type="string", format="uuid", example="C001456"),
     *                     @OA\Property(property="compte_numero", type="string", example="1234567890"),
     *                     @OA\Property(property="titulaire", type="string", example="John Doe"),
     *                     @OA\Property(property="type", type="string", enum={"DEPOT", "RETRAIT"}, example="DEPOT"),
     *                     @OA\Property(property="montant", type="number", format="float", example=500000),
     *                     @OA\Property(property="montant_formatte", type="string", example="+500 000 FCFA"),
     *                     @OA\Property(property="solde_avant", type="number", format="float", example=1000000),
     *                     @OA\Property(property="solde_apres", type="number", format="float", example=1500000),
     *                     @OA\Property(property="description", type="string", example="Dépôt espèces guichet"),
     *                     @OA\Property(property="statut", type="string", enum={"EN_ATTENTE", "VALIDEE"}, example="EN_ATTENTE"),
     *                     @OA\Property(property="date_creation", type="string", format="date-time", example="2023-11-02T12:00:00Z"),
     *                     @OA\Property(property="cree_par", type="string", example="Admin User")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Transaction créée avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation échouée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Le montant doit être supérieur à 0"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="UNAUTHENTICATED"),
     *                 @OA\Property(property="message", type="string", example="Utilisateur non authentifié")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="ACCESS_DENIED"),
     *                 @OA\Property(property="message", type="string", example="Accès refusé")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Compte non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="code", type="string", example="NOT_FOUND"),
     *                 @OA\Property(property="message", type="string", example="Compte non trouvé")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur serveur")
     *         )
     *     )
     * )
     */
    public function store(StoreTransactionRequest $request)
    {
        $validated = $request->validated();

        try {
            // Vérifier que le compte existe et est actif
            $compte = Compte::findOrFail($validated['compte_id']);

            if ($compte->statut !== 'actif') {
                return $this->error(
                    'Le compte est suspendu ou clôturé',
                    400
                );
            }

            // Calculer le solde actuel
            $soldeActuel = $compte->solde;

            // Pour un retrait, vérifier que le solde est suffisant
            if ($validated['type'] === 'RETRAIT' && $soldeActuel < $validated['montant']) {
                return $this->error(
                    'Solde insuffisant pour effectuer ce retrait',
                    400
                );
            }

            // Calculer le solde après transaction
            $soldeApres = $validated['type'] === 'DEPOT'
                ? $soldeActuel + $validated['montant']
                : $soldeActuel - $validated['montant'];

            // Générer le numéro de transaction
            $numeroTransaction = 'TR' . str_pad(Transaction::count() + 1, 4, '0', STR_PAD_LEFT) . '-' . now()->format('Ymd');

            // Déterminer le statut
            $statut = $validated['validation_automatique'] ?? false ? 'VALIDEE' : 'EN_ATTENTE';

            DB::transaction(function () use ($validated, $compte, $soldeActuel, $soldeApres, $numeroTransaction, $statut) {
                // Créer la transaction
                $transaction = Transaction::create([
                    'compte_id' => $validated['compte_id'],
                    'type' => $validated['type'] === 'DEPOT' ? 'depot' : 'retrait',
                    'montant' => $validated['montant'],
                    'devise' => 'XOF',
                    'description' => $validated['description'],
                    'dateTransaction' => now(),
                    'statut' => $statut,
                    'cree_par' => Auth::id(),
                    'numero_transaction' => $numeroTransaction,
                    'solde_avant' => $soldeActuel,
                    'solde_apres' => $soldeApres,
                ]);

                // Si validation automatique, mettre à jour le solde du compte
                if ($statut === 'VALIDEE') {
                    // Mettre à jour le solde du compte
                    $compte->update(['derniereModification' => now()]);
                }
            });

            // Récupérer la transaction créée
            $transaction = Transaction::where('numero_transaction', $numeroTransaction)->first();

            // Formater la réponse
            $responseData = [
                'id' => $transaction->id,
                'numero_transaction' => $transaction->numero_transaction,
                'compte_id' => $transaction->compte_id,
                'compte_numero' => $compte->numeroCompte,
                'titulaire' => $compte->client->user->name ?? 'N/A',
                'type' => $validated['type'],
                'montant' => $transaction->montant,
                'montant_formatte' => ($validated['type'] === 'DEPOT' ? '+' : '-') . number_format($transaction->montant, 0, ',', ' ') . ' FCFA',
                'solde_avant' => $transaction->solde_avant,
                'solde_apres' => $transaction->solde_apres,
                'description' => $transaction->description,
                'statut' => $transaction->statut,
                'date_creation' => $transaction->created_at->toIso8601String(),
                'cree_par' => Auth::user()?->name ?? 'Admin',
            ];

            return $this->success(
                ['transaction' => $responseData],
                'Transaction créée avec succès',
                200
            );

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error(
                'Compte non trouvé',
                404
            );
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de transaction: ' . $e->getMessage());
            return $this->error(
                'Erreur serveur',
                500
            );
        }
    }
}
