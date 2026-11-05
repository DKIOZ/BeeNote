<?php

namespace App\Services;

use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\RucheEmplacement;
use App\Models\Traitement;
use App\Models\Visite;
use Illuminate\Support\Collection;

/**
 * Réunit visites, traitements, récoltes et déplacements en une seule chronologie.
 */
class RegistreService
{
    public const LIMITE = 500;

    public static function pourEquipe(int $teamId, ?string $depuis = null, ?string $jusqu = null, ?int $rucherId = null, ?int $limite = self::LIMITE): Collection
    {
        $base = fn ($query) => $query->where('team_id', $teamId)->whereHas('ruche')->with('ruche.rucher:id,nom');
        // Le filtre porte sur le rucher de l'époque, pas sur celui où se trouve la ruche aujourd'hui.
        $saisie = fn ($query) => $base($query)->with('rucher:id,nom')->when($rucherId, fn ($q) => $q->where('rucher_id', $rucherId));

        return self::assembler(
            $saisie(Visite::query()),
            $saisie(Traitement::query()),
            $saisie(Recolte::query()),
            $base(RucheEmplacement::query())
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

        foreach ($periode($visites, 'date_visite') as $v) {
            $lignes->push(self::ligne('visite', $v, $v->date_visite, $v->resume()));
        }

        foreach ($periode($traitements, 'date_traitement') as $t) {
            $lignes->push(self::ligne('traitement', $t, $t->date_traitement, trim($t->produit_utilise.($t->dosage ? ', '.$t->dosage : ''))));
        }

        foreach ($periode($recoltes, 'date_recolte') as $r) {
            $lignes->push(self::ligne('recolte', $r, $r->date_recolte, self::kilos($r->quantite_kg)));
        }

        foreach ($periode($emplacements, 'date_deplacement') as $e) {
            $lignes->push(self::ligne('deplacement', $e, $e->date_deplacement, ($e->depuis?->nom ?? '?').' vers '.($e->vers?->nom ?? '?')));
        }

        $lignes = $lignes->sortByDesc('date')->values();

        return $limite ? $lignes->take($limite)->values() : $lignes;
    }

    private static function kilos($quantite): string
    {
        return rtrim(rtrim(number_format((float) $quantite, 2, ',', ''), '0'), ',').' kg';
    }

    private static function ligne(string $type, $modele, $date, string $detail): array
    {
        return [
            'type' => $type,
            'id' => $modele->id,
            'date' => $date->toDateString(),
            'ruche_id' => $modele->ruche_id,
            'ruche' => $modele->ruche?->nom,
            'rucher' => $modele instanceof RucheEmplacement ? $modele->vers?->nom : ($modele->rucher?->nom ?? $modele->ruche?->rucher?->nom),
            'detail' => $detail,
        ];
    }
}
