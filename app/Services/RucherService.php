<?php

namespace App\Services;

use App\Models\Rucher;
use App\Models\User;

class RucherService
{
    /**
     * Créer un rucher pour la team d'un utilisateur
     */
    public static function createForTeam(array $validatedData, User $user): Rucher
    {
        // Vérifier que l'utilisateur a une team
        if (!$user->current_team_id) {
            throw new \Exception('L\'utilisateur doit appartenir à une équipe pour créer un rucher.');
        }

        // Créer le rucher
        $rucher = new Rucher($validatedData);
        // Manuel parce que le champ n'est pas dans $fillable
        $rucher->team_id = $user->current_team_id;
        $rucher->save();

        return $rucher;
    }

    /**
     * Récupérer tous les ruchers d'une team
     */
    public static function getRuchersForTeam(User $user)
    {
        if (!$user->current_team_id) {
            return collect();
        }

        return Rucher::where('team_id', $user->current_team_id)->get();
    }

    /**
     * Mettre à jour un rucher (sécurisé par team)
     */
    public static function updateForTeam(Rucher $rucher, array $validatedData, User $user): Rucher
    {
        // Vérifier que le rucher appartient à la team de l'utilisateur
        if ($rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas modifier ce rucher.');
        }

        $rucher->update($validatedData);
        return $rucher;
    }

    /**
     * Supprimer un rucher (sécurisé par team)
     */
    public static function deleteForTeam(Rucher $rucher, User $user): bool
    {
        if ($rucher->team_id !== $user->current_team_id) {
            throw new \Exception('Vous ne pouvez pas supprimer ce rucher.');
        }

        return $rucher->delete();
    }
}