<?php

namespace App\Http\Controllers;

use App\Models\Tache;
use App\Models\Visite;
use App\Services\RucherOverview;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $taches = Tache::where('team_id', $user->current_team_id)
            ->where('statut', 'a_faire')
            // Une ruche à la corbeille n'a plus de page : sa tâche attend avec elle.
            ->whereHas('rucher')
            // Une ruche morte ou inactive n'a plus de travail à faire.
            ->where(fn ($q) => $q->whereNull('ruche_id')->orWhereHas('ruche', fn ($r) => $r->where(fn ($s) => $s->whereNull('statut')->orWhere('statut', 'active'))))
            ->with(['ruche:id,nom', 'rucher:id,nom'])
            ->orderBy('date_prevue')
            ->limit(20)
            ->get()
            ->map(fn (Tache $tache) => [
                'id' => $tache->id,
                'titre' => $tache->titre,
                'ou' => $tache->ruche?->nom ?? $tache->rucher?->nom,
                'ruche_id' => $tache->ruche_id,
                'date_prevue' => $tache->date_prevue->toDateString(),
                'retard' => $tache->date_prevue->toDateString() < $today,
            ]);

        return Inertia::render('Dashboard', [
            'ruchers' => RucherOverview::ruchers($user),
            'taches' => $taches,
            'aCompleter' => RucherOverview::aCompleter($user),
            'saison' => Visite::where('team_id', $user->current_team_id)
                ->whereYear('date_visite', now()->year)
                ->whereHas('ruche')
                ->pluck('date_visite')
                ->countBy(fn ($date) => $date->month),
            'aujourdhui' => $today,
        ]);
    }
}
