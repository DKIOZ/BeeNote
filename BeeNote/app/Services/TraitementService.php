<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ruche;
use App\Models\Traitement;

class TraitementService
{
    /**
     * Créer un nouveau traitement pour une ruche
     */
    public static function createForRuche(array $validatedData, Ruche $ruche, User $user): Traitement
    {
        // Vérifier que la ruche appartient à la team
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas ajouter un traitement à cette ruche.');
        }

        // Créer le traitement
        $traitement = new Traitement($validatedData);
        $traitement->ruche_id = $ruche->id;
        $traitement->user_id = $user->id;
        $traitement->team_id = $user->current_team_id;
        $traitement->save();

        return $traitement;
    }

    /**
     * Récupérer tous les traitements d'une ruche
     */
    public static function getTraitementsForRuche(Ruche $ruche, User $user)
    {
        // Vérifier l'accès via la team
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette ruche.');
        }

        return $ruche->traitements()
            ->where('team_id', $user->current_team_id)
            ->orderBy('date_traitement', 'desc')
            ->get();
    }

    public static function prepareEditData(Traitement $traitement, User $user): array
    {
        if ($traitement->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à ce traitement.');
        }

        // Retourner toutes les données du traitement avec fallbacks
        return [
            'date_traitement' => $traitement->date_traitement,
            'type_traitement' => $traitement->type_traitement,
            'produit_utilise' => $traitement->produit_utilise,
            'dosage' => $traitement->dosage,
            'methode_application' => $traitement->methode_application,
            'temperature_application' => $traitement->temperature_application,
            'efficacite' => $traitement->efficacite,
            'effets_secondaires' => $traitement->effets_secondaires,
            'notes' => $traitement->notes,
        ];
    }

    /**
     * Préparer les données pour un nouveau traitement
     * (pré-remplissage intelligent basé sur le dernier traitement)
     */
    public static function prepareFormData(Ruche $ruche, User $user): array
    {
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette ruche.');
        }

        $baseData = [];


        // Pas de traitement précédente : valeurs par défaut
        $baseData = [
            'date_traitement' => now()->format('Y-m-d'),
            'type_traitement' => '',
            'produit_utilise' => '',
            'dosage' => '',
            'methode_application' => '',
            'temperature_application' => '',
            'efficacite' => '',
            'effets_secondaires' => '',
            'notes' => '',
        ];


        return $baseData;
    }

    public static function updateForRuche(Traitement $traitement, array $validatedData, User $user): Traitement
    {
        // Vérifier l'accès via la team
        if ($traitement->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas modifier ce traitement.');
        }

        $traitement->update($validatedData);
        return $traitement;
    }

    public static function deleteForRuche(Traitement $traitement, User $user): bool
    {
        // Vérifier l'accès via la team
        if ($traitement->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas supprimer ce traitement.');
        }

        // TODO: Gérer les relations liées si nécessaire
        // - Supprimer les photos liées
        // - Supprimer les traitements liés à ce traitement
        // - etc.

        $traitement->delete();
        return true;
    }
}
