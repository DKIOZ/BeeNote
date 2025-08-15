<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ruche extends Model
{
    /** @use HasFactory<\Database\Factories\RucheFactory> */
    use HasFactory;

    protected $fillable = [
        'nom',           // ✅ L'utilisateur peut le définir
        'couleur',  // ✅ L'utilisateur peut le définir  
        'type',      // ✅ L'utilisateur peut le définir
        'statut',     // ✅ L'utilisateur peut le définir
        'annee_reine',   // ✅ L'utilisateur peut le définir
        'couleur_marquage_reine',   // ✅ L'utilisateur peut le définir
        'date_installation',   // ✅ L'utilisateur peut le définir
        'notes',   // ✅ L'utilisateur peut le définir
    ];

    public function user()
    {
        $this->hasOneThrough(
            User::class,         // 1. Modèle final qu'on veut atteindre
            Rucher::class,       // 2. Modèle intermédiaire par lequel on passe
            'id',               // 3. Clé primaire du modèle intermédiaire (ruchers.id)
            'id',              // 4. Clé primaire du modèle final (users.id)
            'rucher_id',        // 5. Foreign key dans la table courante (ruches.rucher_id)
            'user_id'     // 6. Foreign key dans le modèle intermédiaire (ruchers.user_id)
        );

        // Ou plus simple et Laravel devine tout avec les conventions
        // return $this->hasOneThrough(User::class, Rucher::class);
    }

    public function rucher()
    {
        return $this->belongsTo(Rucher::class);
    }

    public function visites()
    {
        return $this->hasMany(Visite::class);
    }

    public function recoltes()
    {
        return $this->hasMany(Recolte::class);
    }

    public function traitements()
    {
        return $this->hasMany(Traitement::class);
    }

    public function taches()
    {
        return $this->hasMany(Tache::class);
    }
}
