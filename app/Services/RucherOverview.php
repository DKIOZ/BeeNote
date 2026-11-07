<?php

namespace App\Services;

use App\Models\Rucher;
use App\Models\Tache;
use App\Models\User;
use App\Models\Visite;
use Illuminate\Support\Collection;

/**
 * État des ruchers d'une équipe : ce qui est fait, à faire ou en retard.
 * Mêmes données pour l'accueil, la liste des ruchers et le mode terrain.
 */
class RucherOverview
{
    public static function ruchers(User $user): Collection
    {
        $teamId = $user->current_team_id;
        $today = now()->toDateString();

        $taches = Tache::where('team_id', $teamId)
            ->where('statut', 'a_faire')
            ->orderBy('date_prevue')
            ->get();

        return Rucher::where('team_id', $teamId)
            ->with(['ruches' => fn ($q) => $q->orderBy('id')->with('derniereVisite')])
            ->orderBy('nom')
            ->get()
            ->map(function (Rucher $rucher) use ($taches, $today) {
                $numeros = self::numeros($rucher->ruches);
                $ruches = $rucher->ruches
                    ->map(fn ($ruche) => self::ruche($ruche, $numeros[$ruche->id], $taches->where('ruche_id', $ruche->id), $today))
                    ->sortBy('numero')
                    ->values();

                return [
                    'id' => $rucher->id,
                    'nom' => $rucher->nom,
                    'a_position' => $rucher->latitude !== null,
                    'ruches' => $ruches,
                    'a_visiter' => $ruches->whereIn('etat', ['todo', 'late'])->where('statut', '!=', 'morte')->where('visitee', false)->count(),
                    'taches' => self::taches($taches->where('rucher_id', $rucher->id)->whereNull('ruche_id'), $today),
                ];
            })
            ->values();
    }

    /**
     * Le numéro dit à voix haute suit le nom : « Ruche 3 » reste la 3 après un déplacement.
     * Une ruche au nom sans numéro prend le plus petit numéro libre.
     */
    public static function numeros(Collection $ruches): array
    {
        $numeros = [];
        $pris = [];

        foreach ($ruches as $ruche) {
            $numero = self::numeroDuNom($ruche->nom);
            if ($numero !== null && ! in_array($numero, $pris, true)) {
                $numeros[$ruche->id] = $numero;
                $pris[] = $numero;
            }
        }

        $libre = 1;
        foreach ($ruches as $ruche) {
            if (! isset($numeros[$ruche->id])) {
                while (in_array($libre, $pris, true)) {
                    $libre++;
                }
                $numeros[$ruche->id] = $libre;
                $pris[] = $libre;
            }
        }

        return $numeros;
    }

    /**
     * Numéro de la prochaine ruche, sur tout le compte : deux ruches ne partagent jamais un nom,
     * même après une transhumance.
     */
    public static function prochainNumero(int $teamId): int
    {
        $noms = \App\Models\Ruche::withTrashed()
            ->whereHas('rucher', fn ($q) => $q->withTrashed()->where('team_id', $teamId))
            ->pluck('nom');

        return $noms->map(fn ($nom) => self::numeroDuNom($nom) ?? 0)->push($noms->count())->max() + 1;
    }

    public static function numeroDuNom(string $nom): ?int
    {
        return preg_match('/(\d+)\s*$/', $nom, $trouve) && (int) $trouve[1] > 0 ? (int) $trouve[1] : null;
    }

    private static function ruche($ruche, int $numero, Collection $taches, string $today): array
    {
        $visite = $ruche->derniereVisite;
        $prochaine = $visite?->prochaine_visite_prevue?->toDateString();
        $premiere = $taches->first()?->date_prevue?->toDateString();

        // Une ruche jamais visitée est à faire : elle n'a pas de date à attendre.
        $etat = $visite ? 'next' : 'todo';
        $visitee = $visite?->date_visite?->toDateString() === $today;

        // Même règle que le téléphone : une ruche vue aujourd'hui est faite.
        // Une tâche en retard reste affichée à côté, elle appartient à la tâche, pas à la ruche.
        if ($visitee) {
            $etat = 'done';
        } elseif (($premiere && $premiere < $today) || ($prochaine && $prochaine < $today)) {
            $etat = 'late';
        } elseif ($prochaine === $today || $premiere === $today) {
            $etat = 'todo';
        }

        return [
            'id' => $ruche->id,
            'numero' => $numero,
            'nom' => $ruche->nom,
            'couleur' => $ruche->couleur,
            'statut' => $ruche->statut,
            'qr_token' => $ruche->qr_token,
            'etat' => $etat,
            'visitee' => $visitee,
            'prochaine_visite' => $prochaine,
            'derniere_visite' => $visite ? [
                'id' => $visite->id,
                'date' => $visite->date_visite->toDateString(),
                'resume' => $visite->resume(),
            ] : null,
            'taches' => self::taches($taches, $today),
        ];
    }

    private static function taches(Collection $taches, string $today): array
    {
        return $taches->map(fn (Tache $tache) => [
            'id' => $tache->id,
            'titre' => $tache->titre,
            'date_prevue' => $tache->date_prevue->toDateString(),
            'retard' => $tache->date_prevue->toDateString() < $today,
        ])->values()->all();
    }

    /**
     * Visites notées « Traité » sans produit ni dose : le registre reste incomplet.
     */
    public static function aCompleter(User $user): Collection
    {
        return Visite::where('team_id', $user->current_team_id)
            ->where('traitement_applique', true)
            // Un traitement noté le soir pour plusieurs ruches solde le rappel de chacune.
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('traitements')
                ->whereColumn('traitements.ruche_id', 'visites.ruche_id')
                ->whereColumn('traitements.date_traitement', 'visites.date_visite')
                ->whereNull('traitements.deleted_at'))
            ->whereHas('ruche')
            ->with('ruche:id,nom')
            ->latest('date_visite')
            ->limit(20)
            ->get()
            ->map(fn (Visite $visite) => [
                'visite_id' => $visite->id,
                'ruche_id' => $visite->ruche_id,
                'ruche' => $visite->ruche->nom,
                'date' => $visite->date_visite->toDateString(),
            ]);
    }
}
