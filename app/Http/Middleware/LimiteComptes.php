<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fortify ne limite que la connexion : inscription et mots de passe passent ici.
 */
class LimiteComptes
{
    private const ESSAIS_PAR_MINUTE = 10;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('get') && $request->is('register', 'forgot-password', 'reset-password', 'user/confirm-password', 'user/password', 'user/profile-information')) {
            $cle = 'comptes:'.$request->path().'|'.$request->ip();

            abort_if(RateLimiter::tooManyAttempts($cle, self::ESSAIS_PAR_MINUTE), 429);
            RateLimiter::hit($cle);
        }

        return $next($request);
    }
}
