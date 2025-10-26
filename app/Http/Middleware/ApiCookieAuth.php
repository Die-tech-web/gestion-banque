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
        if (!$request->header('Authorization') && $request->cookie('api_token')) {
            $token = $request->cookie('api_token');
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        return $next($request);
    }
}