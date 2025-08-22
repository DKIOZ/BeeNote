<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ruche;
use App\Models\Rucher;

class RucheService
{
    /**
     * Créer une ruche pour un rucher
     */
    public static function createForRucher(array $validatedData, Rucher $rucher, User $user): Ruche
    {
        // Vérifier que le rucher appartient à la team de l'utilisateur
        if ($rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas ajouter une ruche à ce rucher.');
        }

        // Créer la ruche
        $ruche = new Ruche($validatedData);
        $ruche->rucher_id = $rucher->id;
        $ruche->save();

        return $ruche;
    }

    /**
     * Récupérer toutes les ruches d'un rucher
     */
    public static function getRuchesForRucher(Rucher $rucher, User $user)
    {
        if ($rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Accès refusé à ce rucher.');
        }

        return $rucher->ruches;
    }

    /**
     * Mettre à jour une ruche
     */
    public static function updateForRucher(Ruche $ruche, array $validatedData, User $user): Ruche
    {
        // Vérifier via le rucher parent
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas modifier cette ruche.');
        }

        $ruche->update($validatedData);
        return $ruche;
    }

    /**
     * Supprimer une ruche
     */
    public static function deleteForRucher(Ruche $ruche, User $user): bool
    {
        if ($ruche->rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas supprimer cette ruche.');
        }

        return $ruche->delete();
    }

    /**
     * Déplacer une ruche vers un autre rucher (transhumance)
     */
    public static function moveToRucher(Ruche $ruche, Rucher $newRucher, User $user): Ruche
    {
        // Vérifier que l'utilisateur a accès aux deux ruchers
        if (
            $ruche->rucher->team_id !== $user->current_team_id ||
            $newRucher->team_id !== $user->current_team_id
        ) {
            throw new \Exception('Accès refusé pour déplacer cette ruche.');
        }

        // Éviter le déplacement inutile
        if ($ruche->rucher_id === $newRucher->id) {
            throw new \Exception('La ruche est déjà dans ce rucher.');
        }

        $ruche->rucher_id = $newRucher->id;
        $ruche->save();

        return $ruche;
    }
}
