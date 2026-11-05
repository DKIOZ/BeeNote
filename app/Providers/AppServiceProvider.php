<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $proxies = config('beenote.trusted_proxies');
        if ($proxies) {
            TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }

        // Une limite par usage : lire ses données ne doit pas bloquer l'envoi ou l'export.
        foreach (['qr' => 60, 'terrain-donnees' => 60, 'terrain-envoi' => 60, 'terrain-notes' => 60, 'export' => 20] as $usage => $parMinute) {
            RateLimiter::for($usage, fn (Request $request) => Limit::perMinute($parMinute)->by($request->user()?->id ?: $request->ip()));
        }

        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->letters()->numbers()->uncompromised()
            : Password::min(8));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            URL::forceRootUrl(config('app.url'));

            // Force HTTPS pour les proxies
            $this->app['request']->server->set('HTTPS', 'on');

            config([
                'session.secure' => true,
                'session.http_only' => true,
                'session.same_site' => 'lax',
            ]);
        }
    }
}
