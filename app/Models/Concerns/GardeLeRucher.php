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

    /**
     * Un déplacement ajouté, corrigé ou annulé change le rucher des saisies de la ruche.
     * La date de modification de chaque saisie ne bouge pas : rien n'a été corrigé à la main.
     */
    public static function recalculerPourRuche(int $rucheId): void
    {
        static::withTrashed()->where('ruche_id', $rucheId)->each(function ($modele) {
            $rucher = self::rucherALaDate($modele);

            if ($modele->rucher_id !== $rucher) {
                $modele->newQueryWithoutScopes()->whereKey($modele->getKey())->toBase()->update(['rucher_id' => $rucher]);
            }
        });
    }

    private static function rucherALaDate($modele): ?int
    {
        $date = $modele->date_visite ?? $modele->date_traitement ?? $modele->date_recolte;

        $suivant = $date
            ? RucheEmplacement::where('ruche_id', $modele->ruche_id)
                // Le même jour, l'ordre de saisie départage : une visite notée avant le départ reste au rucher de départ.
                ->where(fn ($q) => $q->whereDate('date_deplacement', '>', $date)
                    ->orWhere(fn ($meme) => $meme->whereDate('date_deplacement', $date)->where('created_at', '>=', $modele->created_at ?? now())))
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
