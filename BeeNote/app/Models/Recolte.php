<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recolte extends Model
{
    /** @use HasFactory<\Database\Factories\RecolteFactory> */
    use HasFactory;

    protected $fillable = [
        'date_recolte',
        'type_miel',
        'quantite_kg',
        'nombre_cadres',
        'qualite',
        'humidite_miel',
        'notes_qualite',
        'notes_degustation',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ruche()
    {
        return $this->belongsTo(Ruche::class);
    }

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');  // photos du miel récolté
    }

    public function taches()
    {
        return $this->hasMany(Tache::class);  // tâches liées à cette récolte
    }
}
