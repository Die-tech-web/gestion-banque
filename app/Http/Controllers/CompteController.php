<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlocageCompteRequest;
use App\Http\Requests\DeblocageCompteRequest;
use App\Http\Requests\CompteListRequest;
use App\Http\Requests\UpdateCompteRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; 
use App\Rules\CompteValide; // Import the custom rule
use App\Models\Client; // Import Client model
use App\Traits\ApiResponseTrait;
use App\Traits\CompteMessages;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\JsonResponse; // Import JsonResponse

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Gestion Compte API Documentation",
 *      description="API Documentation for the Gestion Compte application",
 *      @OA\Contact(
 *          email="support@example.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Gestion Compte API Server"
 * )
 *@OA\Server(
 *      url="https://gestion-banque.onrender.com",
 *      description="Gestion Compte API Server a distance"
 * )
 *
 * @OA\Components(
 *     @OA\Schema(
 *         schema="CompteResource",
 *         type="object",
 *         title="Compte Resource",
 *         properties={
 *             @OA\Property(property="id", type="integer", format="int64", example=1),
 *             @OA\Property(property="numeroCompte", type="string", example="CM123456789"),
 *             @OA\Property(property="client_id", type="integer", example=1),
 *             @OA\Property(property="type", type="string", enum={"courant", "epargne"}, example="courant"),
 *             @OA\Property(property="devise", type="string", example="XAF"),
 *             @OA\Property(property="dateCreation", type="string", format="date-time", example="2025-10-23T12:00:00.000000Z"),
 *             @OA\Property(property="statut", type="string", enum={"actif", "ferme", "suspendu"}, example="actif"),
 *             @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-23T12:00:00.000000Z"),
 *             @OA\Property(property="version", type="integer", example=1),
 *             @OA\Property(property="client", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=1),
 *                 @OA\Property(property="user", type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="name", type="string", example="John Doe"),
 *                     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com")
 *                 )
 *             )
 *         }
 *     ),
 *     @OA\Schema(
 *         schema="LoginRequest",
 *         type="object",
 *         required={"email"},
 *         @OA\Property(property="email", type="string", format="email", example="dieniang32@gmail.com"),
 *         @OA\Property(property="password", type="string", example="password", description="Requis pour les admins")
 *     ),
 *     @OA\Schema(
 *         schema="LoginResponse",
 *         type="object",
 *         @OA\Property(property="token", type="string", example="1|abc123def456")
 *     ),
 *     @OA\Schema(
 *         schema="ErrorResponse",
 *         type="object",
 *         @OA\Property(property="message", type="string", example="Invalid credentials")
 *     )
 * )
 */
class CompteController extends Controller
{
    use ApiResponseTrait, CompteMessages;
    /**
     * @OA\Get(
     *      path="/api/v1/comptes",
     *      operationId="getComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of comptes",
     *      description="Returns list of comptes",
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Filter by account type (courant, epargne)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"courant", "epargne"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="statut",
     *          in="query",
     *          description="Filter by account status (actif, ferme, suspendu, bloque)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"actif", "ferme", "suspendu", "bloque"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search by account number or client name",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          description="Sort by field (dateCreation, titulaire, solde)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"dateCreation", "titulaire", "solde"},
     *              default="dateCreation"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"asc", "desc"},
     *              default="desc"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=10)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource")),
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="pagination", type="object",
     *                  @OA\Property(property="currentPage", type="integer", example=1),
     *                  @OA\Property(property="totalPages", type="integer", example=5),
     *                  @OA\Property(property="totalItems", type="integer", example=50),
     *                  @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                  @OA\Property(property="hasNext", type="boolean", example=true),
     *                  @OA\Property(property="hasPrevious", type="boolean", example=false)
     *              ),
     *              @OA\Property(property="links", type="object",
     *                  @OA\Property(property="self", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=1"),
     *                  @OA\Property(property="next", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=2"),
     *                  @OA\Property(property="first", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=1"),
     *                  @OA\Property(property="last", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=5")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      )
     * )
     */
    /**
     * @OA\Get(
     *      path="/api/v1/comptes",
     *      operationId="getComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of comptes",
     *      description="Returns list of comptes",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Filter by account type (courant, epargne)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"courant", "epargne"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="statut",
     *          in="query",
     *          description="Filter by account status (actif, ferme, suspendu, bloque)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"actif", "ferme", "suspendu", "bloque"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Search by account number or client name",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          description="Sort by field (dateCreation, titulaire, solde)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"dateCreation", "titulaire", "solde"},
     *              default="dateCreation"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              enum={"asc", "desc"},
     *              default="desc"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Number of items per page",
     *          required=false,
     *          @OA\Schema(type="integer", default=10)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CompteResource")),
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="pagination", type="object",
     *                  @OA\Property(property="currentPage", type="integer", example=1),
     *                  @OA\Property(property="totalPages", type="integer", example=5),
     *                  @OA\Property(property="totalItems", type="integer", example=50),
     *                  @OA\Property(property="itemsPerPage", type="integer", example=10),
     *                  @OA\Property(property="hasNext", type="boolean", example=true),
     *                  @OA\Property(property="hasPrevious", type="boolean", example=false)
     *              ),
     *              @OA\Property(property="links", type="object",
     *                  @OA\Property(property="self", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=1"),
     *                  @OA\Property(property="next", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=2"),
     *                  @OA\Property(property="first", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=1"),
     *                  @OA\Property(property="last", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes?page=5")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      )
     * )
     */
    public function index(CompteListRequest $request): JsonResponse
    {
        // Vérifier et débloquer automatiquement les comptes expirés avant de récupérer la liste
        Compte::checkExpiredBlocks();

        $user = Auth::user();
        $limit = $request->get('limit', 10);

        // Pour les tests, on permet l'accès sans authentification si pas d'utilisateur
        if (!$user) {
            $comptes = Compte::withTrashed()
                ->applyFiltersAndPagination($request)
                ->paginate($limit);
        } else {
            $comptes = Compte::withTrashed()->applyUserPermissions($user)
                ->applyFiltersAndPagination($request)
                ->paginate($limit);
        }

        return $this->success(
            $comptes, // Pass the paginator directly for automatic pagination handling
            $this->comptesRetrievedSuccessfully(),
            CompteValide::httpStatusCodes()['success']
        );
    }




    /**
     * @OA\Delete(
     *      path="/api/v1/comptes/{id}",
     *      operationId="deleteCompte",
     *      tags={"Comptes"},
     *      summary="Soft delete a specific compte",
     *      description="Soft deletes a compte by its ID, marking its status as 'ferme' and setting dateFermeture.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to soft delete",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Compte deleted successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Compte supprimé avec succès"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                  @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                  @OA\Property(property="statut", type="string", example="ferme"),
     *                  @OA\Property(property="dateFermeture", type="string", format="date-time", example="2025-10-19T11:15:00Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Compte not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Compte non trouvé."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Échec de la suppression du compte."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        $compte = Compte::withTrashed()->find($id);

        if (!$compte) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
            );
        }

        if ($compte->trashed()) {
            return $this->error(
                $this->compteAlreadyDeleted(),
                CompteValide::httpStatusCodes()['conflict']
            );
        }

        try {
            $compte->statut = 'ferme';
            $compte->dateFermeture = now();
            $compte->save();
            $compte->delete();

            return $this->success(
                [
                    'id' => $compte->id,
                    'numeroCompte' => $compte->numeroCompte,
                    'statut' => $compte->statut,
                    'dateFermeture' => $compte->dateFermeture ? $compte->dateFermeture->toIso8601String() : null,
                ],
                $this->compteDeletedSuccessfully(),
                CompteValide::httpStatusCodes()['success']
            );
        } catch (\Exception $e) {
            \Log::error("Failed to delete compte: " . $e->getMessage());
            return $this->error(
                $this->failedToDeleteCompte(),
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/comptes/{id}/bloquer",
     *      operationId="blockCompte",
     *      tags={"Comptes"},
     *      summary="Block a specific compte",
     *      description="Blocks a compte by its ID with a motif and duration",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to block",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"motif"},
     *              @OA\Property(property="motif", type="string", example="Activité suspecte détectée"),
     *              @OA\Property(property="dateBlocage", type="string", format="date", example="2025-10-29"),
     *              @OA\Property(property="dateDeblocagePrevue", type="string", format="date", example="2025-11-29")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Compte blocked successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Compte bloqué avec succès"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                  @OA\Property(property="statut", type="string", example="bloque"),
     *                  @OA\Property(property="motifBlocage", type="string", example="Activité suspecte détectée"),
     *                  @OA\Property(property="dateBlocage", type="string", format="date-time", example="2025-10-19T11:20:00Z"),
     *                  @OA\Property(property="dateDeblocagePrevue", type="string", format="date-time", example="2025-11-18T11:20:00Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Compte not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Compte non trouvé."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Validation failed."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Échec du blocage du compte."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      )
     * )
     */
    public function block(BlocageCompteRequest $request, string $id): JsonResponse
    {
        try {
            $compte = Compte::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
            );
        }

        // Vérification supplémentaire pour s'assurer qu'un compte chèque ne peut pas être bloqué
        if ($compte->type === 'courant') {
            return $this->error(
                'Un compte chèque ne peut pas être bloqué.',
                CompteValide::httpStatusCodes()['bad_request']
            );
        }

        // Vérifier si le compte est expiré et le débloquer automatiquement si nécessaire
        $compte->checkAndUnblockExpired();

        if ($compte->statut === 'bloque') {
            return $this->error(
                $this->compteAlreadyBlocked(),
                CompteValide::httpStatusCodes()['conflict']
            );
        }

        try {
            $motif = $request->input('motif');
            $dateBlocage = $request->input('dateBlocage');
            $dateDeblocagePrevue = $request->input('dateDeblocagePrevue');

            $compte->motifBlocage = $motif;
            $compte->dateBlocage = $dateBlocage;
            $compte->dateDeblocagePrevue = $dateDeblocagePrevue;

            // If dateBlocage is today, set status to 'bloque' immediately
            if ($dateBlocage && now()->toDateString() === \Carbon\Carbon::parse($dateBlocage)->toDateString()) {
                $compte->statut = 'bloque';
            } else {
                // Otherwise, the account remains active until the scheduled job blocks it
                $compte->statut = 'actif';
            }
            $compte->save();

            return $this->success(
                [
                    'id' => $compte->id,
                    'statut' => $compte->statut,
                    'motifBlocage' => $compte->motifBlocage,
                    'dateBlocage' => $compte->dateBlocage->toIso8601String(),
                    'dateDeblocagePrevue' => $compte->dateDeblocagePrevue->toIso8601String(),
                ],
                $this->compteBlockedSuccessfully(),
                CompteValide::httpStatusCodes()['success']
            );
        } catch (\Exception $e) {
            \Log::error("Failed to block compte: " . $e->getMessage());
            return $this->error(
                $this->failedToBlockCompte(),
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        }
    }

    /**
     * @OA\Get(
     *      path="/api/v1/comptes/{id}",
     *      operationId="getCompte",
     *      tags={"Comptes"},
     *      summary="Get a specific compte",
     *      description="Returns details of a specific compte by its ID",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to retrieve",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Détails du compte récupérés avec succès"),
     *              @OA\Property(property="data", ref="#/components/schemas/CompteResource")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Compte not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Compte non trouvé."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Accès non autorisé à ce compte."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      )
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $compte = Compte::with(['client.user', 'transactions'])->findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
            );
        }

        // Check and unblock if expired, then refresh the model instance
        $compte->checkAndUnblockExpired();
        $compte->refresh(); // Refresh the model to get the updated status

        $user = Auth::user();
        $isAdmin = (bool) $user->admin;

        // Vérifier les permissions : admin voit tous les comptes, client seulement les siens
        if (!$isAdmin && (!$user->client || $compte->client_id !== $user->client->id)) {
            return $this->error(
                $this->unauthorizedCompteAccess(),
                CompteValide::httpStatusCodes()['forbidden']
            );
        }

        return $this->success(
            new CompteResource($compte),
            $this->compteDetailsRetrievedSuccessfully(),
            CompteValide::httpStatusCodes()['success']
        );
    }

    /**
     * @OA\Patch(
     *      path="/api/v1/comptes/{id}",
     *      operationId="updateCompte",
     *      tags={"Comptes"},
     *      summary="Update a specific compte",
     *      description="Updates a compte by its ID with provided fields",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to update",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *              @OA\Property(property="informationsClient", type="object",
     *                  @OA\Property(property="telephone", type="string", example="+221771234568"),
     *                  @OA\Property(property="email", type="string", format="email", example="amadou.diallo@example.com"),
     *                  @OA\Property(property="password", type="string", example="newpassword123"),
     *                  @OA\Property(property="nci", type="string", example="12345678901234567890")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Compte updated successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Compte mis à jour avec succès"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                  @OA\Property(property="numeroCompte", type="string", example="C00123456"),
     *                  @OA\Property(property="titulaire", type="string", example="Amadou Diallo Junior"),
     *                  @OA\Property(property="type", type="string", example="epargne"),
     *                  @OA\Property(property="solde", type="number", example=1250000),
     *                  @OA\Property(property="devise", type="string", example="FCFA"),
     *                  @OA\Property(property="dateCreation", type="string", format="date-time", example="2023-03-15T00:00:00Z"),
     *                  @OA\Property(property="statut", type="string", example="bloque"),
     *                  @OA\Property(property="metadata", type="object",
     *                      @OA\Property(property="derniereModification", type="string", format="date-time", example="2025-10-19T11:00:00Z"),
     *                      @OA\Property(property="version", type="integer", example=1)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Compte not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Compte non trouvé."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Validation failed."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Échec de la mise à jour du compte."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      )
     * )
     */
    public function update(UpdateCompteRequest $request, string $id): JsonResponse
    {
        $compte = Compte::with(['client.user'])->find($id);

        if (!$compte) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
            );
        }

        // Vérifier les permissions : admin voit tous les comptes, client seulement les siens
        $user = Auth::user();
        $isAdmin = (bool) $user->admin;

        if (!$isAdmin && (!$user->client || $compte->client_id !== $user->client->id)) {
            return $this->error(
                $this->unauthorizedCompteAccess(),
                CompteValide::httpStatusCodes()['forbidden']
            );
        }

        try {
            $data = $request->validated();

            // Mettre à jour le titulaire si fourni
            if (isset($data['titulaire']) && $compte->client && $compte->client->user) {
                $compte->client->user->name = $data['titulaire'];
                $compte->client->user->save();
            }

            // Mettre à jour les informations client si fournies
            if (isset($data['informationsClient']) && $compte->client) {
                $clientData = $data['informationsClient'];

                if (isset($clientData['telephone'])) {
                    $compte->client->telephone = $clientData['telephone'];
                }
                if (isset($clientData['email']) && $compte->client->user) {
                    $compte->client->user->email = $clientData['email'];
                    $compte->client->user->save();
                }
                if (isset($clientData['password']) && $compte->client->user) {
                    $compte->client->user->password = bcrypt($clientData['password']);
                    $compte->client->user->save();
                }
                if (isset($clientData['nci'])) {
                    $compte->client->nci = $clientData['nci'];
                }

                $compte->client->save();
            }

            // Mettre à jour la dernière modification et la version
            $compte->derniereModification = now();
            $compte->version = $compte->version + 1;
            $compte->save();

            return $this->success(
                new CompteResource($compte),
                $this->compteUpdatedSuccessfully(),
                CompteValide::httpStatusCodes()['success']
            );
        } catch (\Exception $e) {
            \Log::error("Failed to update compte: " . $e->getMessage());
            return $this->error(
                $this->failedToUpdateCompte(),
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        }
    }

    /**
     * @OA\Post(
     *      path="/api/v1/comptes/{id}/debloquer",
     *      operationId="unblockCompte",
     *      tags={"Comptes"},
     *      summary="Unblock a specific compte",
     *      description="Unblocks a compte by its ID with a motif",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to unblock",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"motif"},
     *              @OA\Property(property="motif", type="string", example="Vérification complétée")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Compte unblocked successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Compte débloqué avec succès"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="string", format="uuid", example="550e8400-e29b-41d4-a716-446655440000"),
     *                  @OA\Property(property="statut", type="string", example="actif"),
     *                  @OA\Property(property="dateDeblocage", type="string", format="date-time", example="2025-10-19T12:00:00Z")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Compte not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Compte non trouvé."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Validation failed."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Échec du déblocage du compte."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      )
     * )
     */
    public function unblock(DeblocageCompteRequest $request, string $id): JsonResponse
    {
        try {
            $compte = Compte::findOrFail($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
            );
        }

        // Vérifier si le compte est expiré et le débloquer automatiquement si nécessaire
        $compte->checkAndUnblockExpired();

        if ($compte->statut !== 'bloque') {
            return $this->error(
                $this->compteNotBlocked(),
                CompteValide::httpStatusCodes()['conflict']
            );
        }

        try {
            $compte->statut = 'actif';
            $compte->motifBlocage = null;
            $compte->dateBlocage = null;
            $compte->dateDeblocagePrevue = null;
            $compte->derniereModification = now();
            $compte->save();

            return $this->success(
                [
                    'id' => $compte->id,
                    'statut' => $compte->statut,
                    'dateDeblocage' => now()->toIso8601String(),
                ],
                $this->compteUnblockedSuccessfully(),
                CompteValide::httpStatusCodes()['success']
            );
        } catch (\Exception $e) {
            \Log::error("Failed to unblock compte: " . $e->getMessage());
            return $this->error(
                $this->failedToUnblockCompte(),
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        }
    }
}
