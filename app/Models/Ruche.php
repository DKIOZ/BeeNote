<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\RucherOverview;
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
            $ruche->numero ??= $ruche->numeroLibre();
        });

        // Un nom qui porte un numéro le donne à la ruche : « Ruche 7 » se dit « ruche sept ».
        static::updating(function (Ruche $ruche) {
            if ($ruche->isDirty('nom') && ($numero = RucherOverview::numeroDuNom($ruche->nom))) {
                $ruche->numero = $numero;
            }
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

    /**
     * Après un changement de déplacement, chaque saisie retrouve le rucher de sa date.
     */
    public function recalculerRegistre(): int
    {
        $changees = 0;
        foreach ([Visite::class, Traitement::class, Recolte::class] as $modele) {
            $changees += $modele::recalculerPourRuche($this->id);
        }

        return $changees;
    }

    /**
     * Le numéro du nom s'il est libre sur le compte, sinon le suivant.
     */
    public function numeroLibre(): int
    {
        $teamId = Rucher::withTrashed()->whereKey($this->rucher_id)->value('team_id');
        $numero = RucherOverview::numeroDuNom((string) $this->nom);

        if ($numero && ! self::withTrashed()->where('numero', $numero)->whereHas('rucher', fn ($q) => $q->withTrashed()->where('team_id', $teamId))->exists()) {
            return $numero;
        }

        return RucherOverview::prochainNumero($teamId);
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
