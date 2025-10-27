<?php

namespace App\Http\Controllers;

use App\Events\SendClientNotification;
use App\Http\Requests\StoreCompteRequest;
use App\Models\Client;
use App\Models\Compte;
use App\Models\User;
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
     *      path="/api/v1/{api_name}/comptes",
     *      operationId="storeCompte",
     *      tags={"Comptes"},
     *      summary="Créer un nouveau compte",
     *      description="Crée un nouveau compte bancaire avec un client existant ou nouveau",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
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
     *      )
     * )
     */
    public function store(StoreCompteRequest $request)
    {
        // Vérifier que l'utilisateur authentifié est un admin
        $user = auth()->user();
        if (!$user || !$user->admin) {
            return $this->error(CompteValide::errorMessages()['unauthorized'], CompteValide::httpStatusCodes()['forbidden']);
        }

        DB::beginTransaction();

        try {
            $clientData = $request->input('client');
            $client = null;

            // Vérifier si le client existe ou le créer
            if (isset($clientData['id'])) {
                $client = Client::findOrFail($clientData['id']);
            } else {
                // Créer un nouvel utilisateur
                $password = Str::random(12);
                $codeAuthentification = Str::random(6);

                $user = User::create([
                    'name' => $clientData['titulaire'],
                    'email' => $clientData['email'],
                    'password' => Hash::make($password),
                ]);

                // Créer le client
                $client = Client::create([
                    'user_id' => $user->id,
                    'adresse' => $clientData['adresse'],
                    'telephone' => $clientData['telephone'],
                    'nci' => $clientData['nci'],
                    'code_authentification' => $codeAuthentification,
                ]);
            }

            // Générer un numéro de compte unique
            $numeroCompte = $this->generateNumeroCompte();

            // Créer le compte
            $compte = Compte::create([
                'numeroCompte' => $numeroCompte,
                'client_id' => $client->id,
                'type' => $request->input('type'),
                'devise' => $request->input('devise'),
                'dateCreation' => now(),
                'statut' => 'actif',
                'derniereModification' => now(),
                'version' => 1,
            ]);

            // Créer une transaction initiale pour le solde initial
            $compte->transactions()->create([
                'type' => 'depot',
                'montant' => $request->input('soldeInitial'),
                'description' => 'Solde initial',
                'dateTransaction' => now(),
            ]);

            DB::commit();

            // Envoyer les notifications si c'est un nouveau client
            if (!isset($clientData['id'])) {
                event(new SendClientNotification($client, $password, $codeAuthentification));
            }

            return $this->success([
                'id' => $compte->id,
                'numeroCompte' => $compte->numeroCompte,
                'titulaire' => $client->user->name,
                'type' => $compte->type,
                'solde' => $compte->solde,
                'devise' => $compte->devise,
                'dateCreation' => $compte->dateCreation->toIso8601String(),
                'statut' => $compte->statut,
                'metadata' => [
                    'derniereModification' => $compte->derniereModification->toIso8601String(),
                    'version' => $compte->version,
                ],
            ], CompteValide::successMessages()['compte_created'], CompteValide::httpStatusCodes()['created']);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Vérifier si c'est une erreur de contrainte d'unicité
            if ($e->getCode() == 23000) {
                return $this->error(
                    'Une erreur de contrainte d\'unicité s\'est produite. Vérifiez que l\'email, le téléphone ou le NCI ne sont pas déjà utilisés.',
                    CompteValide::httpStatusCodes()['bad_request']
                );
            }

            return $this->error(
                CompteValide::errorMessages()['unexpected_error'],
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();

            return $this->error(
                'Données de validation invalides: ' . $e->getMessage(),
                CompteValide::httpStatusCodes()['bad_request']
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error(
                CompteValide::errorMessages()['unexpected_error'],
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        }
    }

    // Méthode pour créer des comptes sans authentification (pour les tests)
    public function storeTest(Request $request)
    {
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
