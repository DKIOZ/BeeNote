<?php

namespace App\Models;

use App\Models\Concerns\GardeLeRucher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recolte extends Model
{
    /** @use HasFactory<\Database\Factories\RecolteFactory> */
    use GardeLeRucher, HasFactory, SoftDeletes;

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

    protected function casts(): array
    {
        return ['date_recolte' => 'date:Y-m-d'];
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

    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');  // photos du miel récolté
    }

    public function taches()
    {
        return $this->hasMany(Tache::class);  // tâches liées à cette récolte
    }
}
