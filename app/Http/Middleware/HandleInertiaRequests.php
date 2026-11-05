<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // L'historique du navigateur reste illisible une fois la clé effacée à la déconnexion.
        Inertia::encryptHistory();

        // Lire l'équipe courante la fixe pour un compte neuf, avant tout contrôle de droits.
        $request->user()?->currentTeam;

        return [
            ...parent::share($request),
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'erreur' => fn () => $request->session()->get('erreur'),
            ],
            'voiceModelUrl' => config('beenote.voice_model_url'),
        ];
    }
}
