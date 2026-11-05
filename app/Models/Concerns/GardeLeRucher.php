<?php

namespace App\Models\Concerns;

use App\Models\Ruche;
use App\Models\RucheEmplacement;
use App\Models\Rucher;

/**
 * Une saisie garde le rucher où se trouvait la ruche à sa date : le registre doit rester vrai
 * après un déplacement, y compris pour une saisie faite après coup.
 */
trait GardeLeRucher
{
    protected static function bootGardeLeRucher(): void
    {
        static::creating(function ($modele) {
            $modele->rucher_id ??= self::rucherALaDate($modele);
        });

        static::updating(function ($modele) {
            if ($modele->isDirty(['ruche_id', 'date_visite', 'date_traitement', 'date_recolte'])) {
                $modele->rucher_id = self::rucherALaDate($modele);
            }
        });
    }

    private static function rucherALaDate($modele): ?int
    {
        $date = $modele->date_visite ?? $modele->date_traitement ?? $modele->date_recolte;

        $suivant = $date
            ? RucheEmplacement::where('ruche_id', $modele->ruche_id)
                ->whereDate('date_deplacement', '>', $date)
                ->orderBy('date_deplacement')
                ->orderBy('id')
                ->first()
            : null;

        return $suivant?->depuis_rucher_id ?? Ruche::withTrashed()->whereKey($modele->ruche_id)->value('rucher_id');
    }

    public function rucher()
    {
        return $this->belongsTo(Rucher::class)->withTrashed();
    }
}
