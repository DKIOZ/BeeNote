<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Une visite Inertia garde la politique de la page d'origine : un écran vocal
        // exige un vrai chargement pour recevoir la sienne.
        if ($this->utiliseLaVoix($request) && $request->isMethod('get') && $request->header('X-Inertia')) {
            return Inertia::location($request->fullUrl());
        }

        $nonce = Vite::useCspNonce();

        /** @var Response $response */
        $response = $next($request);

        $headers = $response->headers;
        $headers->remove('X-Powered-By');
        header_remove('X-Powered-By');
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(self), microphone=(self), geolocation=(self), screen-wake-lock=(self), payment=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        // Après déconnexion, le bouton retour ne doit pas réafficher des données du compte.
        if ($request->user() && $request->isMethod('get') && (str_contains((string) $headers->get('Content-Type'), 'html') || $request->header('X-Inertia'))) {
            $headers->set('Cache-Control', 'private, no-store');
        }

        if ($request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Le serveur de développement Vite injecte ses propres scripts.
        if (! Vite::isRunningHot()) {
            $headers->set('Content-Security-Policy', $this->policy($nonce, $this->utiliseLaVoix($request)));
        }

        return $response;
    }

    private function utiliseLaVoix(Request $request): bool
    {
        return $request->is('terrain', 'scan/*', 'demarrage', 'mots');
    }

    private function policy(string $nonce, bool $voix): string
    {
        // Le moteur vocal compilé en WebAssembly évalue du code à son démarrage :
        // cette souplesse est limitée aux seuls écrans qui s'en servent.
        $evaluation = $voix ? " 'unsafe-eval'" : '';

        $parts = parse_url((string) config('beenote.voice_model_url'));
        $modelOrigin = isset($parts['host']) ? ($parts['scheme'] ?? 'https').'://'.$parts['host'] : '';

        return implode('; ', [
            "default-src 'self'",
            // wasm-unsafe-eval et blob: servent uniquement au moteur vocal, qui tourne dans un worker.
            "script-src 'self' 'nonce-{$nonce}' 'wasm-unsafe-eval'{$evaluation}",
            "worker-src 'self' blob:",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "media-src 'self' blob:",
            "font-src 'self' data:",
            trim("connect-src 'self' {$modelOrigin}"),
            "manifest-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);
    }
}
