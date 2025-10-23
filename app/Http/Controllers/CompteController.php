<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompteListRequest;
use App\Http\Resources\CompteResource;
use App\Models\Compte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // Import Request for store and update methods
use App\Rules\CompteValide; // Import the custom rule
use App\Models\Client; // Import Client model
use App\Traits\ApiResponseTrait;
use App\Traits\CompteMessages; // Import the new trait
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
 *     )
 * )
 */
class CompteController extends Controller
{
    use ApiResponseTrait, CompteMessages;
    /**
     * @OA\Get(
     *      path="/api/comptes",
     *      operationId="getComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of comptes",
     *      description="Returns list of comptes",
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
     *              @OA\Property(property="pagination", type="object"),
     *              @OA\Property(property="links", type="object")
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
     *      path="/api/comptes",
     *      operationId="getComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of comptes",
     *      description="Returns list of comptes",
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
     *              @OA\Property(property="pagination", type="object"),
     *              @OA\Property(property="links", type="object")
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
        $limit = $request->get('limit', 10);
        $comptes = Compte::applyFiltersAndPagination($request)->paginate($limit);

        return $this->success(
            $comptes, // Pass the paginator directly
            $this->comptesRetrievedSuccessfully(),
            Response::HTTP_OK
        );
    }

    /**
     * @OA\Get(
     *      path="/api/comptes/non-archives",
     *      operationId="getNonArchivedComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of non-archived comptes",
     *      description="Returns list of non-archived comptes",
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
     *              @OA\Property(property="pagination", type="object"),
     *              @OA\Property(property="links", type="object")
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
        $limit = $request->get('limit', 10);
        $comptes = Compte::where('archived', false)->applyFiltersAndPagination($request)->paginate($limit);

        return $this->success(
            $comptes, // Pass the paginator directly
            $this->nonArchivedComptesRetrievedSuccessfully(),
            Response::HTTP_OK
        );
    }

    /**
     * @OA\Get(
     *      path="/api/comptes/archives",
     *      operationId="getArchivedComptesList",
     *      tags={"Comptes"},
     *      summary="Get list of archived comptes",
     *      description="Returns list of archived comptes",
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
     *              @OA\Property(property="pagination", type="object"),
     *              @OA\Property(property="links", type="object")
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
        $limit = $request->get('limit', 10);
        $comptes = Compte::where('archived', true)->applyFiltersAndPagination($request)->paginate($limit);

        return $this->success(
            $comptes, // Pass the paginator directly
            $this->archivedComptesRetrievedSuccessfully(),
            Response::HTTP_OK
        );
    }
}
