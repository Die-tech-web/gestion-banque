<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;

class ApiCookieAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Si pas d'Authorization header mais cookie présent, définir l'header
        \Log::info('ApiCookieAuth middleware: Start processing.');

        if (!$request->header('Authorization') && $request->cookie('api_token')) {
            $token = $request->cookie('api_token');
            $request->headers->set('Authorization', 'Bearer ' . $token);
            \Log::info('ApiCookieAuth middleware: api_token cookie found and Authorization header set.');
        } else if ($request->header('Authorization')) {
            \Log::info('ApiCookieAuth middleware: Authorization header already present.');
        } else {
            \Log::info('ApiCookieAuth middleware: No Authorization header and no api_token cookie.');
        }

        return $next($request);
    }
}
