<?php

namespace App\Models;

use App\Models\Concerns\GardeLeRucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Traitement extends Model
{
    /** @use HasFactory<\Database\Factories\TraitementFactory> */
    use GardeLeRucher, HasFactory, SoftDeletes;

    protected $fillable = [
        'date_traitement',
        'type_traitement',
        'produit_utilise',
        'dosage',
        'numero_lot',
        'methode_application',
        'temperature_application',
        'efficacite',
        'effets_secondaires',
        'notes',
    ];

    protected function casts(): array
    {
        return ['date_traitement' => 'date:Y-m-d'];
    }

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
