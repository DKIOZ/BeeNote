<?php

use App\Models\Recolte;
use App\Models\Ruche;
use App\Models\RucheEmplacement;
use App\Models\Rucher;
use App\Models\Traitement;
use App\Models\Visite;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('beenote:vider-corbeille', function () {
    $limite = now()->subDays(config('beenote.corbeille_jours'));

    $effacerNote = function (Visite $visite) {
        foreach ($visite->cheminsAudio() as $chemin) {
            Storage::disk('local')->delete($chemin);
        }
    };

    Visite::onlyTrashed()->where('deleted_at', '<', $limite)->each(function (Visite $visite) use ($effacerNote) {
        $effacerNote($visite);
        $visite->forceDelete();
    });

    // Traitements et déplacements relèvent du registre d'élevage : ils ne sont jamais effacés.
    // Une ruche qui en porte reste en base, à la corbeille, pour qu'ils gardent leur ruche.
    $purgerRuches = function ($ruches) use ($effacerNote) {
        foreach ($ruches as $ruche) {
            Visite::withTrashed()->where('ruche_id', $ruche->id)->each(function (Visite $visite) use ($effacerNote) {
                $effacerNote($visite);
                $visite->forceDelete();
            });
            Recolte::withTrashed()->where('ruche_id', $ruche->id)->forceDelete();

            $legal = Traitement::withTrashed()->where('ruche_id', $ruche->id)->exists()
                || RucheEmplacement::withTrashed()->where('ruche_id', $ruche->id)->exists();

            if (! $legal) {
                $ruche->forceDelete();
            }
        }
    };

    Recolte::onlyTrashed()->where('deleted_at', '<', $limite)->forceDelete();

    $purgerRuches(Ruche::onlyTrashed()->where('deleted_at', '<', $limite)->get());

    // Un rucher encore cité par le registre d'une ruche partie ailleurs reste en base, pour son nom.
    foreach (Rucher::onlyTrashed()->where('deleted_at', '<', $limite)->get() as $rucher) {
        $siennes = Ruche::withTrashed()->where('rucher_id', $rucher->id)->pluck('id');
        $saisies = collect([Visite::class, Traitement::class, Recolte::class])
            ->contains(fn ($modele) => $modele::withTrashed()->where('rucher_id', $rucher->id)->whereNotIn('ruche_id', $siennes)->exists());
        $deplacements = RucheEmplacement::withTrashed()
            ->where(fn ($q) => $q->where('depuis_rucher_id', $rucher->id)->orWhere('vers_rucher_id', $rucher->id))
            ->whereNotIn('ruche_id', $siennes)
            ->exists();

        if (! $saisies && ! $deplacements) {
            $purgerRuches(Ruche::withTrashed()->where('rucher_id', $rucher->id)->get());

            if (! Ruche::withTrashed()->where('rucher_id', $rucher->id)->exists()) {
                $rucher->forceDelete();
            }
        }
    }

    $this->info('Corbeille vidée des éléments de plus de '.config('beenote.corbeille_jours').' jours.');
})->purpose('Supprime définitivement les éléments anciens de la corbeille');

Schedule::command('beenote:vider-corbeille')->daily();
