<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tache extends Model
{
    /** @use HasFactory<\Database\Factories\TacheFactory> */
    use HasFactory;

    protected $fillable = [
        // Informations de base
        'titre',
        'description',
        'type',
        'priorite',
        'statut',

        // Planification temporelle
        'date_prevue',
        'date_limite',
        'date_realisee',
        'temps_estime',

        // Rappels & récurrence
        'rappel_active',
        'rappel_jours_avant',
        'recurrente',
        'periode_recurrence',
        'prochaine_occurrence',

        // Notes
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function rucher()
    {
        return $this->belongsTo(Rucher::class);  // nullable - tâche générale du rucher
    }

    public function ruche()
    {
        return $this->belongsTo(Ruche::class);   // nullable - tâche spécifique à une ruche
    }

    // Liens de traçabilité (une fois la tâche réalisée)
    public function visite()
    {
        return $this->belongsTo(Visite::class);     // nullable
    }

    public function traitement()
    {
        return $this->belongsTo(Traitement::class); // nullable
    }

    public function recolte()
    {
        return $this->belongsTo(Recolte::class);    // nullable
    }
}
