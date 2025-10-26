<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Vérifier si c'est un admin (utilise password)
        if ($user->admin()->exists()) {
            if (!$request->has('password') || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            // Authentifier manuellement l'admin
            Auth::login($user);
        } else {
            // C'est un client, vérifier le code_authentification
            $client = $user->client;
            if (!$client || $client->code_authentification !== $request->code_authentification) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }
            // Authentifier manuellement le client
            Auth::login($user);
        }

        $token = $user->createToken('API Token')->accessToken;
        return response()->json(['token' => $token])->cookie('api_token', $token, 60*24*7, '/', null, false, true);
    }
}