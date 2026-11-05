<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Ruche extends Model
{
    /** @use HasFactory<\Database\Factories\RucheFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'couleur',
        'type',
        'statut',
        'annee_reine',
        'couleur_marquage_reine',
        'date_installation',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ruche $ruche) {
            $ruche->qr_token = Str::random(32);
        });
    }

    /**
     * Place la ruche dans un rucher. Ses tâches la suivent.
     */
    public function placerDans(int $rucherId): void
    {
        $this->forceFill(['rucher_id' => $rucherId])->save();
        Tache::where('ruche_id', $this->id)->update(['rucher_id' => $rucherId]);
    }

    public function rucher()
    {
        return $this->belongsTo(Rucher::class);
    }

    public function visites()
    {
        return $this->hasMany(Visite::class);
    }

    public function derniereVisite()
    {
        return $this->hasOne(Visite::class)->ofMany(['date_visite' => 'max', 'id' => 'max']);
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

    public function emplacements()
    {
        return $this->hasMany(RucheEmplacement::class);
    }
}
