<?php

namespace App\Services;

use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\RucheEmplacement;
use App\Models\Traitement;
use App\Models\Visite;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Réunit visites, traitements, récoltes et déplacements en une seule chronologie.
 */
class RegistreService
{
    public const LIMITE = 500;

    public const MIELS = ['acacia' => 'Acacia', 'tilleul' => 'Tilleul', 'chataignier' => 'Châtaignier', 'toutes_fleurs' => 'Toutes fleurs', 'lavande' => 'Lavande', 'autre' => 'Autre miel'];

    public const CIBLES = ['varroa' => 'varroa', 'nosema' => 'nosémose', 'loque' => 'loque', 'preventif' => 'préventif', 'frelon_asiatique' => 'frelon asiatique', 'autre' => 'autre'];

    public static function pourEquipe(int $teamId, ?string $depuis = null, ?string $jusqu = null, ?int $rucherId = null, ?int $limite = self::LIMITE): Collection
    {
        $base = fn ($query) => $query->where('team_id', $teamId)->whereHas('ruche')->with('ruche.rucher:id,nom');
        // Traitements et déplacements restent au registre même quand leur ruche est à la corbeille.
        $legal = fn ($query) => $query->where('team_id', $teamId)
            ->whereHas('ruche', fn ($q) => $q->withTrashed())
            ->with(['ruche' => fn ($q) => $q->withTrashed()->with('rucher:id,nom')]);
        // Le filtre porte sur le rucher de l'époque, pas sur celui où se trouve la ruche aujourd'hui.
        $parRucher = fn ($query) => $query->with('rucher:id,nom')->when($rucherId, fn ($q) => $q->where('rucher_id', $rucherId));

        return self::assembler(
            $parRucher($base(Visite::query())),
            $parRucher($legal(Traitement::query())),
            $parRucher($base(Recolte::query())),
            $legal(RucheEmplacement::query())
                ->with(['depuis:id,nom', 'vers:id,nom'])
                ->when($rucherId, fn ($q) => $q->where(fn ($ou) => $ou->where('depuis_rucher_id', $rucherId)->orWhere('vers_rucher_id', $rucherId))),
            $depuis,
            $jusqu,
            $limite,
        );
    }

    public static function pourRuche(Ruche $ruche): Collection
    {
        $scope = fn ($query) => $query->where('ruche_id', $ruche->id)->with('ruche.rucher:id,nom');

        return self::assembler(
            $scope(Visite::query())->with('rucher:id,nom'),
            $scope(Traitement::query())->with('rucher:id,nom'),
            $scope(Recolte::query())->with('rucher:id,nom'),
            $scope(RucheEmplacement::query())->with(['depuis:id,nom', 'vers:id,nom']),
        );
    }

    private static function assembler($visites, $traitements, $recoltes, $emplacements, ?string $depuis = null, ?string $jusqu = null, ?int $limite = self::LIMITE): Collection
    {
        $periode = function ($query, string $colonne) use ($depuis, $jusqu, $limite) {
            return $query
                ->when($depuis, fn ($q) => $q->whereDate($colonne, '>=', $depuis))
                ->when($jusqu, fn ($q) => $q->whereDate($colonne, '<=', $jusqu))
                ->orderByDesc($colonne)
                ->when($limite, fn ($q) => $q->limit($limite))
                ->get();
        };

        $lignes = collect();

        $visitesTrouvees = $periode($visites, 'date_visite');
        $traites = self::joursTraites($visitesTrouvees->where('traitement_applique', true));

        foreach ($visitesTrouvees as $v) {
            $resume = $v->resume();
            // « Traité » sans traitement enregistré ce jour-là : le registre le dit.
            if ($v->traitement_applique && ! isset($traites[$v->ruche_id.'|'.$v->date_visite->toDateString()])) {
                $resume = str_replace('Traité', 'Traité (à compléter)', $resume);
            }
            $lignes->push(self::ligne('visite', $v, $v->date_visite, $resume));
        }

        foreach ($periode($traitements, 'date_traitement') as $t) {
            $lignes->push(self::ligne('traitement', $t, $t->date_traitement, self::traitement($t), [
                'produit' => $t->produit_utilise,
                'cible' => self::CIBLES[$t->type_traitement] ?? $t->type_traitement,
                'dose' => $t->dosage,
                'lot' => $t->numero_lot,
            ]));
        }

        foreach ($periode($recoltes, 'date_recolte') as $r) {
            $lignes->push(self::ligne('recolte', $r, $r->date_recolte, (self::MIELS[$r->type_miel] ?? 'Miel').', '.self::kilos($r->quantite_kg), [
                'miel' => self::MIELS[$r->type_miel] ?? $r->type_miel,
                'poids_kg' => (float) $r->quantite_kg,
            ]));
        }

        foreach ($periode($emplacements, 'date_deplacement') as $e) {
            $lignes->push(self::ligne('deplacement', $e, $e->date_deplacement, ($e->depuis?->nom ?? '?').' vers '.($e->vers?->nom ?? '?'), [
                'depuis' => $e->depuis?->nom,
                'vers' => $e->vers?->nom,
            ]));
        }

        // Dans une même journée, l'ordre de saisie : un déplacement fait le matin reste avant les visites.
        $lignes = $lignes->sortBy([['date', 'desc'], ['cree', 'desc']])->values();

        return $limite ? $lignes->take($limite)->values() : $lignes;
    }

    /**
     * Ruches et jours pour lesquels un traitement existe vraiment.
     */
    private static function joursTraites(Collection $visites): array
    {
        if ($visites->isEmpty()) {
            return [];
        }

        return Traitement::whereIn('ruche_id', $visites->pluck('ruche_id')->unique())
            ->get(['ruche_id', 'date_traitement'])
            ->mapWithKeys(fn ($t) => [$t->ruche_id.'|'.$t->date_traitement->toDateString() => true])
            ->all();
    }

    private static function traitement(Traitement $t): string
    {
        $morceaux = [$t->produit_utilise.(isset(self::CIBLES[$t->type_traitement]) ? ' ('.self::CIBLES[$t->type_traitement].')' : '')];

        if ($t->dosage) {
            $morceaux[] = $t->dosage;
        }

        $morceaux[] = $t->numero_lot ? 'lot '.$t->numero_lot : 'lot non noté';

        return implode(', ', $morceaux);
    }

    private static function kilos($quantite): string
    {
        return rtrim(rtrim(number_format((float) $quantite, 2, ',', ''), '0'), ',').' kg';
    }

    private static function ligne(string $type, $modele, $date, string $detail, array $champs = []): array
    {
        return [
            'type' => $type,
            'id' => $modele->id,
            'date' => $date->toDateString(),
            'ruche_id' => $modele->ruche_id,
            'ruche' => $modele->ruche?->nom,
            'rucher' => $modele instanceof RucheEmplacement ? $modele->vers?->nom : ($modele->rucher?->nom ?? $modele->ruche?->rucher?->nom),
            'detail' => $detail,
            'cree' => $modele->created_at?->getTimestamp() ?? 0,
            'champs' => $champs,
        ];
    }
}
