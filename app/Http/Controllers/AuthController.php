<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Passport\RefreshToken;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Authentifie un utilisateur (admin ou client) et retourne un token d'accès
     *
     * @OA\Post(
     *      path="/api/login",
     *      operationId="login",
     *      tags={"Authentification"},
     *      summary="Connexion utilisateur",
     *      description="Authentifie un utilisateur (admin ou client) et retourne un token d'accès",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Connexion réussie",
     *          @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Identifiants invalides",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Données de validation invalides",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="The email field is required."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     *
     * @OA\Tag(
     *     name="Authentification",
     *     description="Endpoints pour l'authentification des utilisateurs"
     * )
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'sometimes|required_without:code_authentification',
            'code_authentification' => 'sometimes|required_without:password',
        ], \App\Rules\ApiMessages::authValidationMessages());

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => \App\Rules\ApiMessages::authErrorMessages()['invalid_credentials']], 401);
        }

        // Vérifier si c'est un admin (utilise password)
        if ($user->admin()->exists()) {
            if (!$request->has('password') || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => \App\Rules\ApiMessages::authErrorMessages()['invalid_credentials']], 401);
            }
        } else {
            // C'est un client, vérifier le code_authentification
            $client = $user->client;
            if (!$client || $client->code_authentification !== $request->code_authentification) {
                return response()->json(['message' => \App\Rules\ApiMessages::authErrorMessages()['invalid_credentials']], 401);
            }
        }

        // Créer un token d'accès OAuth directement
        $oauthClient = \Laravel\Passport\Client::where('password_client', 1)->first();

        if (!$oauthClient) {
            return response()->json(['message' => 'OAuth client not configured'], 500);
        }

        // Créer le token avec le client password grant
        $token = $user->createToken('API Token', [], $oauthClient->id);

        // Générer un refresh token qui expire dans 30 jours
        $refreshToken = $user->createToken('Refresh Token', [], $oauthClient->id);
        $refreshToken->token->expires_at = Carbon::now()->addDays(30);
        $refreshToken->token->save();

        return response()->json([
            'access_token' => $token->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('passport.tokens.expire_in', 31536000),
            'refresh_token' => $refreshToken->accessToken,
        ])->cookie('api_token', $token->accessToken, 60*24*7, '/', null, false, true);
    }
}