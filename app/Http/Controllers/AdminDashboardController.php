<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Compte;
use App\Models\Transaction;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

class AdminDashboardController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/api/v1/admin/dashboard",
     *     summary="Afficher le tableau de bord administrateur",
     *     tags={"Transactions Admin"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard chargé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total_compte", type="integer", example=24),
     *                 @OA\Property(property="sous_total_balance", type="object",
     *                     @OA\Property(property="somme_depots", type="number", format="float", example=15750000),
     *                     @OA\Property(property="somme_depots_formatte", type="string", example="15 750 000 FCFA"),
     *                     @OA\Property(property="somme_retraits", type="number", format="float", example=8920000),
     *                     @OA\Property(property="somme_retraits_formatte", type="string", example="8 920 000 FCFA"),
     *                     @OA\Property(property="balance", type="number", format="float", example=6830000),
     *                     @OA\Property(property="balance_formatte", type="string", example="6 830 000 FCFA")
     *                 ),
     *                 @OA\Property(property="nombre_transactions", type="integer", example=248),
     *                 @OA\Property(property="liste_10_dernieres_transactions", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="TR0850"),
     *                         @OA\Property(property="numero_transaction", type="string", example="TR0850-20231102"),
     *                         @OA\Property(property="compte", type="object",
     *                             @OA\Property(property="id", type="string", format="uuid", example="C001456"),
     *                             @OA\Property(property="numero", type="string", example="C001456"),
     *                             @OA\Property(property="titulaire", type="string", example="Boubacar Diallo")
     *                         ),
     *                         @OA\Property(property="type", type="string", enum={"DEPOT", "RETRAIT"}, example="DEPOT"),
     *                         @OA\Property(property="montant", type="number", format="float", example=500000),
     *                         @OA\Property(property="montant_formatte", type="string", example="+500 000 FCFA"),
     *                         @OA\Property(property="description", type="string", example="Dépôt espèces"),
     *                         @OA\Property(property="statut", type="string", enum={"VALIDEE", "EN_ATTENTE", "REJETEE"}, example="VALIDEE"),
     *                         @OA\Property(property="date_creation", type="string", format="date-time", example="2023-11-02T15:30:00Z"),
     *                         @OA\Property(property="date_validation", type="string", format="date-time", nullable=true, example="2023-11-02T15:35:00Z")
     *                     )
     *                 ),
     *                 @OA\Property(property="liste_comptes_crees_aujourdhui", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", example="C001470"),
     *                         @OA\Property(property="numero", type="string", example="C001470"),
     *                         @OA\Property(property="titulaire", type="string", example="Abdoulaye Diallo"),
     *                         @OA\Property(property="type", type="string", enum={"COURANT", "EPARGNE", "CHEQUE"}, example="COURANT"),
     *                         @OA\Property(property="solde_actuel", type="number", format="float", example=500000),
     *                         @OA\Property(property="solde_actuel_formatte", type="string", example="500 000 FCFA"),
     *                         @OA\Property(property="statut", type="string", enum={"ACTIF", "FERME", "SUSPENDU", "BLOQUE"}, example="ACTIF"),
     *                         @OA\Property(property="date_creation", type="string", format="date-time", example="2023-11-02T09:30:00Z"),
     *                         @OA\Property(property="cree_par", type="string", example="Admin Jean Dupont")
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Dashboard chargé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Authentication requise")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé (pas admin)",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Accès refusé. Vous devez être administrateur.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Erreur lors du chargement du dashboard"),
     *             @OA\Property(property="error", type="string", example="Details de l'erreur")
     *         )
     *     )
     * )
     */
    public function dashboard()
    {
        try {
            // 1. Total Compte
            $total_compte = Compte::count();

            // 2. Sous Total ou balance
            $somme_depots = Transaction::where('type', 'depot')
                ->where('statut', 'VALIDEE')
                ->sum('montant');

            $somme_retraits = Transaction::where('type', 'retrait')
                ->where('statut', 'VALIDEE')
                ->sum('montant');

            $balance = $somme_depots - $somme_retraits;

            // 3. Nombre de Transactions
            $nombre_transactions = Transaction::count();

            // 4. Liste des 10 dernières Transactions
            $liste_10_dernieres_transactions = Transaction::with(['compte.client.user'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'numero_transaction' => $transaction->numero_transaction,
                        'compte' => [
                            'id' => $transaction->compte->id,
                            'numero' => $transaction->compte->numeroCompte,
                            'titulaire' => $transaction->compte->client->user->name ?? 'N/A',
                        ],
                        'type' => strtoupper($transaction->type),
                        'montant' => $transaction->montant,
                        'montant_formatte' => ($transaction->type === 'depot' ? '+' : '-') . $this->formatMontant($transaction->montant),
                        'description' => $transaction->description,
                        'statut' => $transaction->statut,
                        'date_creation' => $transaction->created_at->toIso8601String(),
                        'date_validation' => $transaction->statut === 'VALIDEE' ? $transaction->updated_at->toIso8601String() : null,
                    ];
                });

            // 5. Liste des comptes créés dans la journée
            $liste_comptes_crees_aujourdhui = Compte::with(['client.user'])
                ->whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($compte) {
                    $admin = Admin::where('user_id', $compte->cree_par ?? null)->first();
                    return [
                        'id' => $compte->id,
                        'numero' => $compte->numeroCompte,
                        'titulaire' => $compte->client->user->name ?? 'N/A',
                        'type' => strtoupper($compte->type),
                        'solde_actuel' => $compte->solde,
                        'solde_actuel_formatte' => $this->formatMontant($compte->solde),
                        'statut' => strtoupper($compte->statut),
                        'date_creation' => $compte->created_at->toIso8601String(),
                        'cree_par' => $admin ? $admin->user->name : 'N/A',
                    ];
                });

            $data = [
                'total_compte' => $total_compte,
                'sous_total_balance' => [
                    'somme_depots' => $somme_depots,
                    'somme_depots_formatte' => $this->formatMontant($somme_depots),
                    'somme_retraits' => $somme_retraits,
                    'somme_retraits_formatte' => $this->formatMontant($somme_retraits),
                    'balance' => $balance,
                    'balance_formatte' => $this->formatMontant($balance),
                ],
                'nombre_transactions' => $nombre_transactions,
                'liste_10_dernieres_transactions' => $liste_10_dernieres_transactions,
                'liste_comptes_crees_aujourdhui' => $liste_comptes_crees_aujourdhui,
            ];

            return $this->success($data, 'Dashboard chargé avec succès');

        } catch (\Exception $e) {
            return $this->error(
                'Erreur lors du chargement du dashboard',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * Formater un montant en FCFA
     *
     * @param float $montant
     * @return string
     */
    private function formatMontant($montant)
    {
        return number_format($montant, 0, ',', ' ') . ' FCFA';
    }
}