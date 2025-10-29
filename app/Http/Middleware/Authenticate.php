<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

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

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $user = $this->auth->guard($guard)->user();
                \Log::info('Authenticate middleware: User authenticated. ID: ' . $user->id . ', Admin status: ' . ($user->admin()->exists() ? 'true' : 'false'));
                return $this->auth->shouldUse($guard);
            }
        }

        \Log::info('Authenticate middleware: User not authenticated for guards: ' . implode(', ', $guards));
        throw new AuthenticationException(
            'Unauthenticated.', $guards, $this->redirectTo($request)
        );
    }
}
