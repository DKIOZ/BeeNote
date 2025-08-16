<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traitement extends Model
{
    /** @use HasFactory<\Database\Factories\TraitementFactory> */
    use HasFactory;

    protected $fillable = [
        'date_traitement',
        'type_traitement',
        'produit_utilise',
        'dosage',
        'methode_application',
        'temperature_application',
        'efficacite',
        'effets_secondaires',
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

    public function ruche()
    {
        return $this->belongsTo(Ruche::class);
    }

    public function visite()
    {
        return $this->belongsTo(Visite::class);  // nullable - traitement peut être hors visite
    }

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');  // photos avant/après traitement
    }
}
