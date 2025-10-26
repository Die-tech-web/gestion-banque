<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        \Log::info('Authenticate middleware called for: ' . $request->path());
        if ($request->is('api/*')) {
            \Log::info('API request, returning null');
            return null;
        }

        return '/login';
    }
}
