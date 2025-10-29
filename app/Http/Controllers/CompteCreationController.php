<?php

namespace App\Http\Controllers;

use App\Events\SendClientNotification;
use App\Http\Requests\StoreCompteRequest;
use App\Jobs\CreateAccountJob;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
use App\Rules\CompteValide;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Comptes",
 *     description="API Endpoints for Compte Management"
 * )
 */
class CompteCreationController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *      path="/api/v1/comptes",
     *      operationId="storeCompte",
     *      tags={"Comptes"},
     *      summary="Créer un nouveau compte",
     *      description="Crée un nouveau compte bancaire avec un client existant ou nouveau",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"type", "soldeInitial", "devise", "client"},
     *              @OA\Property(property="type", type="string", enum={"cheque", "epargne"}, example="cheque"),
     *              @OA\Property(property="soldeInitial", type="number", format="float", minimum=10000, example=500000),
     *              @OA\Property(property="devise", type="string", enum={"FCFA", "USD", "EUR"}, example="FCFA"),
     *              @OA\Property(property="client", type="object",
     *                  oneOf={
     *                      @OA\Schema(
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          description="Client existant"
     *                      ),
     *                      @OA\Schema(
     *                          required={"titulaire", "nci", "email", "telephone", "adresse"},
     *                          @OA\Property(property="titulaire", type="string", example="Hawa BB Wane"),
     *                          @OA\Property(property="nci", type="string", pattern="^\d{13}$", example="1234567890123"),
     *                          @OA\Property(property="email", type="string", format="email", example="cheikh.sy@example.com"),
     *                          @OA\Property(property="telephone", type="string", pattern="^\+2217\d{8}$", example="+221771234567"),
     *                          @OA\Property(property="adresse", type="string", example="Dakar, Sénégal"),
     *                          description="Nouveau client"
     *                      )
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Compte créé avec succès",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", format="uuid", example="660f9511-f30c-52e5-b827-557766551111"),
     *                  @OA\Property(property="numeroCompte", type="string", example="C00123460"),
     *                  @OA\Property(property="titulaire", type="string", example="Cheikh Sy"),
     *                  @OA\Property(property="type", type="string", example="cheque"),
     *                  @OA\Property(property="solde", type="number", format="float", example=500000),
     *                  @OA\Property(property="devise", type="string", example="FCFA"),
     *                  @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                  @OA\Property(property="statut", type="string", example="actif"),
     *                  @OA\Property(property="metadata", type="object",
     *                      @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-19T10:30:00Z"),
     *                      @OA\Property(property="version", type="integer", example=1)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Données invalides",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="error", type="object",
     *                  @OA\Property(property="code", type="string", example="VALIDATION_ERROR"),
     *                  @OA\Property(property="message", type="string", example="Les données fournies sont invalides"),
     *                  @OA\Property(property="details", type="object", example={"titulaire": "Le nom du titulaire est requis", "soldeInitial": "Le solde initial doit être supérieur à 0"})
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erreur interne du serveur",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="error", type="object",
     *                  @OA\Property(property="code", type="string", example="INTERNAL_ERROR"),
     *                  @OA\Property(property="message", type="string", example="Une erreur inattendue s'est produite")
     *              )
     *          )
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function store(StoreCompteRequest $request)
    {
        try {
            $clientData = $request->input('client');
            $compteData = [
                'type' => $request->input('type'),
                'devise' => $request->input('devise'),
                'soldeInitial' => $request->input('soldeInitial'),
            ];

            $isNewClient = !isset($clientData['id']);

            // Dispatcher le job pour la création asynchrone du compte
            CreateAccountJob::dispatch($clientData, $compteData, $isNewClient);

            // Retourner une réponse immédiate
            return $this->success([
                'message' => 'Votre demande de création de compte a été prise en compte avec succès. Un email et un SMS de confirmation vous seront envoyés.',
                'status' => 'processing'
            ], 'Demande de création de compte enregistrée', CompteValide::httpStatusCodes()['accepted']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error(
                'Données de validation invalides: ' . $e->getMessage(),
                CompteValide::httpStatusCodes()['bad_request']
            );
        } catch (\Exception $e) {
            return $this->error(
                CompteValide::errorMessages()['unexpected_error'],
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        }
    }

    // Méthode pour créer des comptes avec contournement sécurisé (pour les tests)
    public function storeTest(Request $request)
    {
        // Vérifier si on est en environnement de développement ou si un token spécial est fourni
        $isDevelopment = app()->environment('local', 'development');
        $hasTestToken = $request->header('X-Test-Token') === config('app.test_token', 'test-token-secure-123');

        if (!$isDevelopment && !$hasTestToken) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ACCESS_DENIED',
                    'message' => 'Accès refusé. Cette route est réservée aux tests en développement.'
                ]
            ], 403);
        }

        // Créer une instance de StoreCompteRequest avec les données de la requête
        $storeRequest = new StoreCompteRequest();
        $storeRequest->merge($request->all());
        $storeRequest->setContainer(app());
        $storeRequest->setValidator(app('validator')->make($request->all(), $storeRequest->rules()));

        // Simuler l'authentification d'un admin pour les tests
        $admin = \App\Models\Admin::first();
        if ($admin) {
            \Illuminate\Support\Facades\Auth::login($admin->user);
        }

        return $this->store($storeRequest);
    }

    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'C' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Compte::where('numeroCompte', $numero)->exists());

        return $numero;
    }
}
