<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlocageCompteRequest;
use App\Http\Requests\DeblocageCompteRequest;
use App\Http\Requests\CompteListRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // Import Request for store and update methods
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
 *         @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *         @OA\Property(property="password", type="string", example="password", description="Requis pour les admins"),
 *         @OA\Property(property="code_authentification", type="string", example="123456", description="Requis pour les clients")
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
     *      path="/api/v1/{api_name}/comptes",
     *      operationId="getComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of comptes",
     *      description="Returns list of comptes",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Filter by account type (courant, epargne)",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="statut",
     *          in="query",
     *          description="Filter by account status (actif, ferme, suspendu)",
     *          required=false,
     *          @OA\Schema(type="string")
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
     *          @OA\Schema(type="string", default="dateCreation")
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          @OA\Schema(type="string", default="desc")
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
     *      path="/api/v1/{api_name}/comptes",
     *      operationId="getComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of comptes",
     *      description="Returns list of comptes",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Filter by account type (courant, epargne)",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="statut",
     *          in="query",
     *          description="Filter by account status (actif, ferme, suspendu)",
     *          required=false,
     *          @OA\Schema(type="string")
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
     *          @OA\Schema(type="string", default="dateCreation")
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          @OA\Schema(type="string", default="desc")
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

        $comptes = Compte::applyUserPermissions($user)
            ->applyFiltersAndPagination($request)
            ->paginate($limit);

        return $this->success(
            $comptes, // Pass the paginator directly
            $this->comptesRetrievedSuccessfully(),
            CompteValide::httpStatusCodes()['success']
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/{api_name}/comptes/non-archives",
     *      operationId="getNonArchivedComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of non-archived comptes",
     *      description="Returns list of non-archived comptes",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Filter by account type (courant, epargne)",
     *          required=false,
     *          @OA\Schema(type="string")
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
     *          @OA\Schema(type="string", default="dateCreation")
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          @OA\Schema(type="string", default="desc")
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
     *                  @OA\Property(property="self", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/non-archives?page=1"),
     *                  @OA\Property(property="next", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/non-archives?page=2"),
     *                  @OA\Property(property="first", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/non-archives?page=1"),
     *                  @OA\Property(property="last", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/non-archives?page=5")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      )
     * )
     */
    public function getNonArchivedComptes(CompteListRequest $request): JsonResponse
    {
        // Vérifier et débloquer automatiquement les comptes expirés avant de récupérer la liste
        Compte::checkExpiredBlocks();

        $user = Auth::user();
        $limit = $request->get('limit', 10);

        $comptes = Compte::applyUserPermissions($user)
            ->byArchivedStatus(false)
            ->applyFiltersAndPagination($request)
            ->paginate($limit);

        return $this->success(
            $comptes, // Pass the paginator directly
            $this->nonArchivedComptesRetrievedSuccessfully(),
            CompteValide::httpStatusCodes()['success']
        );
    }

    /**
     * @OA\Get(
     *      path="/api/v1/{api_name}/comptes/archives",
     *      operationId="getArchivedComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of archived comptes",
     *      description="Returns list of archived comptes",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Filter by account type (courant, epargne)",
     *          required=false,
     *          @OA\Schema(type="string")
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
     *          @OA\Schema(type="string", default="dateCreation")
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sort order (asc, desc)",
     *          required=false,
     *          @OA\Schema(type="string", default="desc")
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
     *                  @OA\Property(property="self", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/archives?page=1"),
     *                  @OA\Property(property="next", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/archives?page=2"),
     *                  @OA\Property(property="first", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/archives?page=1"),
     *                  @OA\Property(property="last", type="string", format="url", example="http://127.0.0.1:8000/api/v1/die.niang/comptes/archives?page=5")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      )
     * )
     */
    
    public function getArchivedComptes(CompteListRequest $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);

        $comptes = Compte::applyUserPermissions($user)
            ->byArchivedStatus(true)
            ->applyFiltersAndPagination($request)
            ->paginate($limit);

        return $this->success(
            $comptes, // Pass the paginator directly
            $this->archivedComptesRetrievedSuccessfully(),
            CompteValide::httpStatusCodes()['success']
        );
    }

    /**
     * @OA\Post(
     *      path="/api/v1/{api_name}/comptes/{id}/archiver",
     *      operationId="archiveCompte",
     *      tags={"Comptes"},
     *      summary="Archive a specific compte",
     *      description="Archives a compte by its ID",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to archive",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Compte archived successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Compte archived successfully."),
     *              @OA\Property(property="success", type="boolean", example=true)
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Compte not found",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Compte not found."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Failed to archive compte."),
     *              @OA\Property(property="success", type="boolean", example=false)
     *          )
     *      )
     * )
     */
    public function archiveCompte(int $id): JsonResponse
    {
        $compte = Compte::find($id);

        if (!$compte) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
            );
        }

        try {
            $compte->archived = true;
            $compte->save();
            return $this->success(
                null,
                $this->compteArchivedSuccessfully(),
                CompteValide::httpStatusCodes()['success']
            );
        } catch (\Exception $e) {
            return $this->error(
                $this->failedToArchiveCompte(),
                CompteValide::httpStatusCodes()['internal_server_error']
            );
        }
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/{api_name}/comptes/{id}",
     *      operationId="deleteCompte",
     *      tags={"Comptes"},
     *      summary="Soft delete a specific compte",
     *      description="Soft deletes a compte by its ID, marking its status as 'ferme' and setting dateFermeture.",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to soft delete",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
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
    public function destroy(int $id): JsonResponse
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
     *      path="/api/v1/{api_name}/comptes/{id}/bloquer",
     *      operationId="blockCompte",
     *      tags={"Comptes"},
     *      summary="Block a specific compte",
     *      description="Blocks a compte by its ID with a motif and duration",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to block",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"motif", "duree", "unite"},
     *              @OA\Property(property="motif", type="string", example="Activité suspecte détectée"),
     *              @OA\Property(property="duree", type="integer", example=30),
     *              @OA\Property(property="unite", type="string", enum={"jour", "jours", "semaine", "semaines", "mois", "annee", "annees"}, example="mois")
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
    public function block(BlocageCompteRequest $request, int $id): JsonResponse
    {
        $compte = Compte::find($id);

        if (!$compte) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
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
            $duree = $request->input('duree');
            $unite = $request->input('unite');

            // Calculer la date de déblocage prévue
            $dateDeblocagePrevue = now();
            switch ($unite) {
                case 'jour':
                case 'jours':
                    $dateDeblocagePrevue = $dateDeblocagePrevue->addDays($duree);
                    break;
                case 'semaine':
                case 'semaines':
                    $dateDeblocagePrevue = $dateDeblocagePrevue->addWeeks($duree);
                    break;
                case 'mois':
                    $dateDeblocagePrevue = $dateDeblocagePrevue->addMonths($duree);
                    break;
                case 'annee':
                case 'annees':
                    $dateDeblocagePrevue = $dateDeblocagePrevue->addYears($duree);
                    break;
            }

            $compte->statut = 'bloque';
            $compte->motifBlocage = $motif;
            $compte->dateBlocage = now();
            $compte->dateDeblocagePrevue = $dateDeblocagePrevue;
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
     *      path="/api/v1/{api_name}/comptes/{id}",
     *      operationId="getCompte",
     *      tags={"Comptes"},
     *      summary="Get a specific compte",
     *      description="Returns details of a specific compte by its ID",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to retrieve",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
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
    public function show($id): JsonResponse
    {
        $compte = Compte::with(['client.user', 'transactions'])->find($id);

        if (!$compte) {
            return $this->error(
                $this->compteNotFound(),
                CompteValide::httpStatusCodes()['not_found']
            );
        }

        $user = Auth::user();
        $isAdmin = $user->admin()->exists();

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
     * @OA\Post(
     *      path="/api/v1/{api_name}/comptes/{id}/debloquer",
     *      operationId="unblockCompte",
     *      tags={"Comptes"},
     *      summary="Unblock a specific compte",
     *      description="Unblocks a compte by its ID with a motif",
     *      @OA\Parameter(
     *          name="api_name",
     *          in="path",
     *          description="Dynamic API name from config",
     *          required=true,
     *          @OA\Schema(type="string", default="die.niang")
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the compte to unblock",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
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
    public function unblock(DeblocageCompteRequest $request, int $id): JsonResponse
    {
        $compte = Compte::find($id);

        if (!$compte) {
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
