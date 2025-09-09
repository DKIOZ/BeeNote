<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ruche;
use App\Models\Recolte;

class RecolteService
{
    /**
     * Créer un nouveau récolte pour une ruche
     */
    public static function createForRuche(array $validatedData, Ruche $ruche, User $user): Recolte
    {
        // Vérifier que la ruche appartient à la team
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas ajouter une récolte à cette ruche.');
        }

        // Créer la récolte
        $recolte = new Recolte($validatedData);
        $recolte->ruche_id = $ruche->id;
        $recolte->user_id = $user->id;
        $recolte->team_id = $user->current_team_id;
        $recolte->save();

        return $recolte;
    }

    /**
     * Récupérer tous les récoltes d'une ruche
     */
    public static function getRecoltesForRuche(Ruche $ruche, User $user)
    {
        // Vérifier l'accès via la team
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette ruche.');
        }

        return $ruche->recoltes()
            ->where('team_id', $user->current_team_id)
            ->orderBy('date_recolte', 'desc')
            ->get();
    }

    public static function prepareEditData(Recolte $recolte, User $user): array
    {
        if ($recolte->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette récolte.');
        }

        // Retourner toutes les données de la récolte avec fallbacks
        return [
            'date_recolte' => $recolte->date_recolte,
            'type_miel' => $recolte->type_miel,
            'quantite_kg' => $recolte->quantite_kg,
            'nombre_cadres' => $recolte->nombre_cadres,
            'qualite' => $recolte->qualite,
            'humidite_miel' => $recolte->humidite_miel,
            'notes_qualite' => $recolte->notes_qualite,
            'notes_degustation' => $recolte->notes_degustation,
        ];
    }

    /**
     * Préparer les données pour un nouveau récolte
     * (pré-remplissage intelligent basé sur le dernier récolte)
     */
    public static function prepareFormData(Ruche $ruche, User $user): array
    {
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à cette ruche.');
        }

        // Pas de récolte précédente : valeurs par défaut
        $baseData = [
            'date_recolte' => now()->format('Y-m-d'),
            'type_miel' => '',
            'quantite_kg' => '',
            'nombre_cadres' => '',
            'qualite' => '',
            'humidite_miel' => '',
            'notes_qualite' => '',
            'notes_degustation' => '',
        ];


        return $baseData;
    }

    public static function updateForRuche(Recolte $recolte, array $validatedData, User $user): Recolte
    {
        // Vérifier l'accès via la team
        if ($recolte->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas modifier cette récolte.');
        }

        $recolte->update($validatedData);
        return $recolte;
    }

    public static function deleteForRuche(Recolte $recolte, User $user): bool
    {
        // Vérifier l'accès via la team
        if ($recolte->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas supprimer cette récolte.');
        }

        // TODO: Gérer les relations liées si nécessaire
        // - Supprimer les photos liées
        // - Supprimer les récoltes liés à ce récolte
        // - etc.

        $recolte->delete();
        return true;
    }
}
