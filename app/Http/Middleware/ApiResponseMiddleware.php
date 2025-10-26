<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Forcer les réponses JSON pour les routes API
        if ($request->is('api/*')) {
            \Log::info('API Response Status: ' . $response->getStatusCode());

            if ($response->getStatusCode() === 302) {
                // Convertir les redirections en erreurs JSON
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'AUTHENTICATION_FAILED',
                        'message' => 'Authentification requise'
                    ]
                ], 401);
            }

            // Assurer que le content-type est JSON
            $response->headers->set('Content-Type', 'application/json');

            // Si la réponse est HTML (comme la page Laravel par défaut), convertir en JSON
            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300 && str_contains($response->headers->get('Content-Type'), 'text/html')) {
                return response()->json([
                    'success' => true,
                    'message' => 'Requête traitée avec succès',
                    'data' => null
                ], $response->getStatusCode());
            }
        }

        return $response;
    }
}
