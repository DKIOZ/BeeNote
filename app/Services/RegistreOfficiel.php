<?php

namespace App\Services;

use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\RucheEmplacement;
use App\Models\Rucher;
use App\Models\Traitement;
use App\Models\Visite;
use Illuminate\Support\Collection;

/**
 * Range les saisies dans les rubriques du registre d'élevage apicole
 * (arrêté du 5 juin 2000), sur le modèle des registres proposés par les GDS.
 * Une ligne par rucher et par jour, avec les ruches concernées : c'est ainsi que
 * le registre papier se tient.
 */
class RegistreOfficiel
{
    private const NOURRITURES = ['sirop' => 'Sirop', 'candi' => 'Candi', 'pate' => 'Pâte protéinée'];

    public static function pourEquipe(int $teamId, string $depuis, string $jusqu, ?int $rucherId = null): array
    {
        $periode = fn ($query, string $colonne) => $query
            ->where('team_id', $teamId)
            ->whereDate($colonne, '>=', $depuis)
            ->whereDate($colonne, '<=', $jusqu)
            ->orderBy($colonne);
        // Le rucher de l'époque, comme dans la chronologie.
        $duRucher = fn ($query) => $query->with('rucher:id,nom')->when($rucherId, fn ($q) => $q->where('rucher_id', $rucherId));
        // Traitements et déplacements restent au registre même quand leur ruche est à la corbeille.
        $avecCorbeille = fn ($query) => $query->whereHas('ruche', fn ($q) => $q->withTrashed())->with(['ruche' => fn ($q) => $q->withTrashed()]);
        $actives = fn ($query) => $query->whereHas('ruche')->with('ruche');

        $visites = $duRucher($actives($periode(Visite::query(), 'date_visite')))->get();
        $traitements = $duRucher($avecCorbeille($periode(Traitement::query(), 'date_traitement')))->get();
        $recoltes = $duRucher($actives($periode(Recolte::query(), 'date_recolte')))->get();
        $deplacements = $avecCorbeille($periode(RucheEmplacement::query(), 'date_deplacement'))
            ->with(['depuis:id,nom,localisation', 'vers:id,nom,localisation'])
            ->when($rucherId, fn ($q) => $q->where(fn ($ou) => $ou->where('depuis_rucher_id', $rucherId)->orWhere('vers_rucher_id', $rucherId)))
            ->get();

        $ruchers = Rucher::where('team_id', $teamId)
            ->when($rucherId, fn ($q) => $q->whereKey($rucherId))
            ->withCount(['ruches' => fn ($q) => $q->where('statut', '!=', 'morte')])
            ->orderBy('nom')
            ->get(['id', 'nom', 'localisation']);

        $traitesCeJour = $traitements->map(fn ($t) => $t->ruche_id.'|'.$t->date_traitement->toDateString())
            ->merge($traitements->whereNotNull('visite_id')->pluck('visite_id')->map(fn ($id) => 'v'.$id))
            ->flip();

        return [
            'ruchers' => $ruchers,
            'nb_ruches' => $ruchers->sum('ruches_count'),
            'production_miel' => $recoltes->isNotEmpty(),
            'varroa' => self::estimations($visites),
            'traitements_varroa' => self::traitements($traitements->where('type_traitement', 'varroa')),
            'sanitaires' => self::sanitaires($traitements->where('type_traitement', '!=', 'varroa'), $visites, $traitesCeJour),
            'nourrissements' => self::nourrissements($visites),
            'interventions' => self::interventions($visites, $recoltes),
            'transhumances' => self::transhumances($deplacements),
        ];
    }

    /**
     * Une ligne par rucher et par jour (et par clé donnée), avec la liste des ruches.
     */
    private static function parRucherEtJour(Collection $saisies, string $colonne, callable $cle, callable $ligne): array
    {
        return $saisies
            ->groupBy(fn ($s) => $s->{$colonne}->toDateString().'|'.$s->rucher_id.'|'.$cle($s))
            ->map(fn (Collection $groupe) => [
                'date' => $groupe->first()->{$colonne}->toDateString(),
                'rucher' => $groupe->first()->rucher?->nom ?? 'Rucher inconnu',
                'ruches' => self::ruches($groupe),
                ...$ligne($groupe),
            ])
            ->sortBy('date')
            ->values()
            ->all();
    }

    private static function ruches(Collection $groupe): string
    {
        $noms = $groupe->map(fn ($s) => $s->ruche?->nom)->filter()->unique()->values();

        return $noms->count().' '.($noms->count() > 1 ? 'ruches' : 'ruche').' : '.$noms->implode(', ');
    }

    private static function estimations(Collection $visites): array
    {
        $vues = $visites->filter(fn ($v) => in_array($v->varroas_observes, ['ok', 'non_ok'], true) || $v->nombre_varroas_estimes !== null);

        return self::parRucherEtJour($vues, 'date_visite', fn () => '', fn ($groupe) => [
            'methode' => $groupe->contains(fn ($v) => $v->nombre_varroas_estimes !== null) ? 'Comptage à la visite' : 'Observation à la visite',
            'resultats' => $groupe->map(fn ($v) => $v->ruche?->nom.' : '.match (true) {
                $v->nombre_varroas_estimes !== null => $v->nombre_varroas_estimes.' varroas',
                $v->varroas_observes === 'ok' => 'varroas vus',
                default => 'pas vu de varroas',
            })->implode(' ; '),
        ]);
    }

    private static function traitements(Collection $traitements): array
    {
        return self::parRucherEtJour($traitements, 'date_traitement', fn ($t) => $t->produit_utilise.'|'.$t->dosage.'|'.$t->numero_lot, fn ($groupe) => [
            'produit' => $groupe->first()->produit_utilise,
            'dosage' => $groupe->first()->dosage,
            'remarques' => self::remarques($groupe),
        ]);
    }

    private static function sanitaires(Collection $traitements, Collection $visites, Collection $traitesCeJour): array
    {
        $lignes = self::parRucherEtJour($traitements, 'date_traitement', fn ($t) => $t->type_traitement.'|'.$t->produit_utilise.'|'.$t->dosage, fn ($groupe) => [
            'objet' => ucfirst(RegistreService::CIBLES[$groupe->first()->type_traitement] ?? (string) $groupe->first()->type_traitement),
            'traitement' => trim($groupe->first()->produit_utilise.($groupe->first()->dosage ? ', '.$groupe->first()->dosage : '')),
            'remarques' => self::remarques($groupe),
        ]);

        // « Traité » coché à la visite sans traitement enregistré : le registre demande de compléter.
        $aCompleter = $visites->filter(fn ($v) => $v->traitement_applique
            && ! $traitesCeJour->has('v'.$v->id)
            && ! $traitesCeJour->has($v->ruche_id.'|'.$v->date_visite->toDateString()));

        $lignes = [...$lignes, ...self::parRucherEtJour($aCompleter, 'date_visite', fn () => '', fn () => [
            'objet' => 'Traitement noté à la visite',
            'traitement' => 'Produit à compléter',
            'remarques' => '',
        ])];

        usort($lignes, fn ($a, $b) => $a['date'] <=> $b['date']);

        return $lignes;
    }

    private static function remarques(Collection $groupe): string
    {
        $lots = $groupe->pluck('numero_lot')->filter()->unique();
        $notes = $groupe->pluck('notes')->filter()->unique();

        return collect([$lots->isEmpty() ? 'Lot non noté' : 'Lot '.$lots->implode(', '), ...$notes])->implode('. ');
    }

    private static function nourrissements(Collection $visites): array
    {
        $nourries = $visites->filter(fn ($v) => $v->nourrissement_effectue || $v->type_nourrissement);

        return self::parRucherEtJour($nourries, 'date_visite', fn ($v) => (string) $v->type_nourrissement, function ($groupe) {
            $kilos = $groupe->sum(fn ($v) => (float) $v->quantite_nourrissement);

            return [
                'cause' => $groupe->contains(fn ($v) => $v->reserves_miel === 'insuffisantes') ? 'Réserves faibles' : '',
                'produit' => (self::NOURRITURES[$groupe->first()->type_nourrissement] ?? 'Non précisé').($kilos > 0 ? ', '.self::kilos($kilos).' en tout' : ''),
                'remarques' => '',
            ];
        });
    }

    private static function interventions(Collection $visites, Collection $recoltes): array
    {
        $lignes = self::parRucherEtJour($recoltes, 'date_recolte', fn ($r) => (string) $r->type_miel, fn ($groupe) => [
            'cause' => 'Récolte',
            'action' => (RegistreService::MIELS[$groupe->first()->type_miel] ?? 'Miel').', '.self::kilos($groupe->sum(fn ($r) => (float) $r->quantite_kg)),
        ]);

        foreach (['hausse_ajoutee' => ['Miellée', 'Hausse posée'], 'hausse_retiree' => ['Fin de miellée', 'Hausse enlevée']] as $champ => [$cause, $action]) {
            $lignes = [...$lignes, ...self::parRucherEtJour($visites->where($champ, true), 'date_visite', fn () => '', fn () => ['cause' => $cause, 'action' => $action])];
        }

        usort($lignes, fn ($a, $b) => $a['date'] <=> $b['date']);

        return $lignes;
    }

    private static function transhumances(Collection $deplacements): array
    {
        return $deplacements
            ->groupBy(fn ($e) => $e->date_deplacement->toDateString().'|'.$e->depuis_rucher_id.'|'.$e->vers_rucher_id)
            ->map(function (Collection $groupe) {
                $e = $groupe->first();
                $lieu = fn (?Rucher $r) => $r ? trim($r->nom.($r->localisation ? ' ('.$r->localisation.')' : '')) : 'Hors BeeNote';

                return [
                    'date' => $e->date_deplacement->toDateString(),
                    'depuis' => $lieu($e->depuis),
                    'vers' => $lieu($e->vers),
                    'ruches' => self::ruches($groupe),
                ];
            })
            ->sortBy('date')
            ->values()
            ->all();
    }

    private static function kilos(float $quantite): string
    {
        return rtrim(rtrim(number_format($quantite, 2, ',', ''), '0'), ',').' kg';
    }
}
