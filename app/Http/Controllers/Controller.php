<?php

namespace App\Http\Controllers;

use App\Models\Ruche;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Une ruche hérite des droits de son rucher : c'est le seul point d'entrée pour la vérifier.
     */
    protected function authorizeRuche(Ruche $ruche, string $ability = 'view'): void
    {
        abort_unless($ruche->rucher, 404);
        $this->authorize($ability, $ruche->rucher);
    }

    /**
     * Ne renvoie que les ruches de l'équipe courante, pour les identifiants venus du client.
     */
    protected function teamRuches(array $ids)
    {
        return Ruche::whereIn('id', $ids)
            ->whereHas('rucher', fn ($q) => $q->where('team_id', request()->user()->current_team_id))
            ->with('rucher')
            ->get();
    }
}
