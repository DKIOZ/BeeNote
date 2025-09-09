<?php

namespace App\Services;

use App\Models\User;
use App\Services\RucherService;

class DashboardService
{
    /**
     * Récupérer toutes les statistiques du dashboard
     */
    public static function getStats(User $user): array
    {
        // Récupérer les ruchers de la team
        $ruchers = RucherService::getRuchersForTeam($user);

        // Calculer les statistiques
        $stats = [
            'total_ruchers' => $ruchers->count(),
            'total_ruches' => $ruchers->sum(function ($rucher) {
                return $rucher->ruches->count();
            }),
            'ruches_actives' => $ruchers->sum(function ($rucher) {
                return $rucher->ruches->where('statut', 'active')->count();
            }),
            'ruches_inactives' => $ruchers->sum(function ($rucher) {
                return $rucher->ruches->where('statut', 'inactive')->count();
            }),
            'ruches_mortes' => $ruchers->sum(function ($rucher) {
                return $rucher->ruches->where('statut', 'morte')->count();
            }),
        ];

        return $stats;
    }

    /**
     * Récupérer les ruchers récents
     */
    public static function getRecentRuchers(User $user, int $limit = 3)
    {
        $ruchers = RucherService::getRuchersForTeam($user);

        return $ruchers->map(function ($rucher) {
            // Charger les ruches pour chaque rucher
            $rucher->load('ruches');
            return $rucher;
        })->sortByDesc('created_at')->take($limit)->values();
    }

    /**
     * Récupérer les ruches récentes
     */
    public static function getRecentRuches(User $user, int $limit = 5)
    {
        $ruchers = RucherService::getRuchersForTeam($user);

        $ruchesRecentes = $ruchers->flatMap(function ($rucher) {
            return $rucher->ruches->map(function ($ruche) use ($rucher) {
                $ruche->rucher_nom = $rucher->nom;
                $ruche->rucher_id = $rucher->id;  // ✅ Ajouter rucher_id pour les liens
                return $ruche;
            });
        })->sortByDesc('created_at')->take($limit);

        return $ruchesRecentes->values();
    }
}
