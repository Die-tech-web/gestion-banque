<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = now();

        $response = $next($request);

        $endTime = now();

        // Logger les informations de la requête
        \Log::info('API Request Log', [
            'timestamp' => $startTime->toIso8601String(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'operation' => $this->getOperationName($request),
            'host' => $request->getHost(),
            'resource' => $request->path(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $endTime->diffInMilliseconds($startTime),
            'cookies' => $request->cookies->all(), // Log des tokens dans les cookies
        ]);

        return $response;
    }

    private function getOperationName(Request $request): string
    {
        $method = $request->method();
        $path = $request->path();

        // Déterminer le nom de l'opération basé sur la méthode et le chemin
        if (str_contains($path, 'comptes')) {
            if ($method === 'POST') {
                return 'Création de compte';
            } elseif ($method === 'GET') {
                return 'Consultation de comptes';
            } elseif ($method === 'PUT' || $method === 'PATCH') {
                return 'Modification de compte';
            } elseif ($method === 'DELETE') {
                return 'Suppression de compte';
            }
        }

        return 'Opération inconnue';
    }
}
