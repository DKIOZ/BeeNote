<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ruche;
use App\Models\Visite;

class VisiteService
{
    /**
     * Créer une nouvelle visite pour une ruche
     */
    public static function createForRuche(array $validatedData, Ruche $ruche, User $user): Visite
    {
        // Vérifier que la ruche appartient à la team
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas ajouter une visite à cette ruche.');
        }

        // Calculer la durée si les heures sont fournies
        if (!empty($validatedData['heure_debut']) && !empty($validatedData['heure_fin'])) {
            $debut = \Carbon\Carbon::createFromFormat('H:i', $validatedData['heure_debut']);
            $fin = \Carbon\Carbon::createFromFormat('H:i', $validatedData['heure_fin']);
            $validatedData['duree_visite'] = $fin->diffInMinutes($debut);
        }

        // Créer la visite
        $visite = new Visite($validatedData);
        $visite->ruche_id = $ruche->id;
        $visite->user_id = $user->id;
        $visite->team_id = $user->current_team_id;
        $visite->save();

        return $visite;
    }

    /**
     * Récupérer toutes les visites d'une ruche
     */
    public static function getVisitesForRuche(Ruche $ruche, User $user)
    {
        // Vérifier l'accès via la team
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette ruche.');
        }

        return $ruche->visites()
            ->where('team_id', $user->current_team_id)
            ->orderBy('date_visite', 'desc')
            ->orderBy('heure_debut', 'desc')
            ->get();
    }

    // Dans VisiteService.php, ajoutez cette méthode :
    public static function prepareEditData(Visite $visite, User $user): array
    {
        if ($visite->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette visite.');
        }

        // Retourner toutes les données de la visite avec fallbacks
        return [
            'date_visite' => $visite->date_visite,
            'heure_debut' => $visite->heure_debut ?? '',
            'heure_fin' => $visite->heure_fin ?? '',
            'meteo' => $visite->meteo ?? '',
            'temperature' => $visite->temperature ?? '',
            'humidite' => $visite->humidite ?? '',
            'force_vent' => $visite->force_vent ?? '',
            'humeur_colonie' => $visite->humeur_colonie ?? 'non_observe',
            'activite_entree' => $visite->activite_entree ?? 'non_observe',
            'estimation_population' => $visite->estimation_population ?? 'non_observe',
            'bruit_colonie' => $visite->bruit_colonie ?? 'non_observe',
            'reine_vue' => $visite->reine_vue ?? 'non_observe',
            'reine_marquee' => $visite->reine_marquee ?? 'non_observe',
            'ponte_observee' => $visite->ponte_observee ?? 'non_observe',
            'qualite_ponte' => $visite->qualite_ponte ?? 'non_observe',
            'cellules_royales' => $visite->cellules_royales ?? 'non_observe',
            'nombre_cellules_royales' => $visite->nombre_cellules_royales ?? '',
            'oeufs_vus' => $visite->oeufs_vus ?? 'non_observe',
            'larves_vues' => $visite->larves_vues ?? 'non_observe',
            'couvain_opercule' => $visite->couvain_opercule ?? 'non_observe',
            'surface_couvain' => $visite->surface_couvain ?? 'non_observe',
            'regularite_couvain' => $visite->regularite_couvain ?? 'non_observe',
            'reserves_miel' => $visite->reserves_miel ?? 'non_observe',
            'reserves_pollen' => $visite->reserves_pollen ?? 'non_observe',
            'cadres_miel_opercule' => $visite->cadres_miel_opercule ?? '',
            'varroas_observes' => $visite->varroas_observes ?? 'non_observe',
            'nombre_varroas_estimes' => $visite->nombre_varroas_estimes ?? '',
            'fausse_teigne' => $visite->fausse_teigne ?? 'non_observe',
            'mortalite' => $visite->mortalite ?? 'non_observe',
            'abeilles_mortes_quantite' => $visite->abeilles_mortes_quantite ?? '',
            'cadres_etat' => $visite->cadres_etat ?? 'non_observe',
            'nombre_cadres_total' => $visite->nombre_cadres_total ?? '',
            'nombre_cadres_occupes' => $visite->nombre_cadres_occupes ?? '',
            'cire_construite' => $visite->cire_construite ?? 'non_observe',
            'nourrissement_effectue' => $visite->nourrissement_effectue ?? false,
            'type_nourrissement' => $visite->type_nourrissement ?? '',
            'quantite_nourrissement' => $visite->quantite_nourrissement ?? '',
            'traitement_applique' => $visite->traitement_applique ?? false,
            'hausse_ajoutee' => $visite->hausse_ajoutee ?? false,
            'hausse_retiree' => $visite->hausse_retiree ?? false,
            'notes_generales' => $visite->notes_generales ?? '',
            'actions_prevues' => $visite->actions_prevues ?? '',
            'prochaine_visite_prevue' => $visite->prochaine_visite_prevue ?? '',
        ];
    }

    /**
     * Préparer les données pour une nouvelle visite
     * (pré-remplissage intelligent basé sur la dernière visite)
     */
    public static function prepareFormData(Ruche $ruche, User $user): array
    {
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette ruche.');
        }

        // Récupérer la dernière visite
        $derniereVisite = $ruche->visites()
            ->where('team_id', $user->current_team_id)
            ->orderBy('date_visite', 'desc')
            ->orderBy('heure_debut', 'desc')
            ->first();

        $baseData = [];

        if ($derniereVisite) {
            // Utiliser la logique de pré-remplissage du modèle
            $baseData = $derniereVisite->prepareDataForNewVisit();
        } else {
            // Pas de visite précédente : valeurs par défaut
            $baseData = [
                'date_visite' => now()->format('Y-m-d'),
                'heure_debut' => null,
                'heure_fin' => null,
                'meteo' => null,
                'temperature' => null,
                'humidite' => null,
                'force_vent' => null,

                // Tous les champs d'observation à "non_observe"
                'humeur_colonie' => 'non_observe',
                'activite_entree' => 'non_observe',
                'estimation_population' => 'non_observe',
                'bruit_colonie' => 'non_observe',
                'reine_vue' => 'non_observe',
                'reine_marquee' => 'non_observe',
                'ponte_observee' => 'non_observe',
                'qualite_ponte' => 'non_observe',
                'cellules_royales' => 'non_observe',
                'oeufs_vus' => 'non_observe',
                'larves_vues' => 'non_observe',
                'couvain_opercule' => 'non_observe',
                'surface_couvain' => 'non_observe',
                'regularite_couvain' => 'non_observe',
                'reserves_miel' => 'non_observe',
                'reserves_pollen' => 'non_observe',
                'varroas_observes' => 'non_observe',
                'fausse_teigne' => 'non_observe',
                'mortalite' => 'non_observe',
                'cadres_etat' => 'non_observe',
                'cire_construite' => 'non_observe',

                // Actions par défaut
                'nourrissement_effectue' => false,
                'traitement_applique' => false,
                'hausse_ajoutee' => false,
                'hausse_retiree' => false,
            ];
        }

        // 🌤️ RÉCUPÉRER LA MÉTÉO ACTUELLE
        $weather = \App\Services\WeatherService::getWeatherForRucher($ruche->rucher);

        if ($weather) {
            $baseData['meteo'] = $weather['meteo'];
            $baseData['temperature'] = $weather['temperature'];
            $baseData['humidite'] = $weather['humidity'];
            $baseData['force_vent'] = $weather['force_vent'];
        }

        return $baseData;
    }

    public static function updateForRuche(Visite $visite, array $validatedData, User $user): Visite
    {
        // Vérifier l'accès via la team
        if ($visite->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas modifier cette visite.');
        }

        // Calculer la durée si les heures sont fournies
        if (!empty($validatedData['heure_debut']) && !empty($validatedData['heure_fin'])) {
            $debut = \Carbon\Carbon::createFromFormat('H:i', $validatedData['heure_debut']);
            $fin = \Carbon\Carbon::createFromFormat('H:i', $validatedData['heure_fin']);
            $validatedData['duree_visite'] = $fin->diffInMinutes($debut);
        }

        $visite->update($validatedData);
        return $visite;
    }

    public static function deleteForRuche(Visite $visite, User $user): bool
    {
        // Vérifier l'accès via la team
        if ($visite->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas supprimer cette visite.');
        }

        // TODO: Gérer les relations liées si nécessaire
        // - Supprimer les photos liées
        // - Supprimer les traitements liés à cette visite
        // - etc.

        $visite->delete();
        return true;
    }
}
