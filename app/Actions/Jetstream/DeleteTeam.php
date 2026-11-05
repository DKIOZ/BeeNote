<?php

namespace App\Actions\Jetstream;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Jetstream\Contracts\DeletesTeams;

class DeleteTeam implements DeletesTeams
{
    /**
     * Delete the given team.
     */
    public function delete(Team $team): void
    {
        $dossier = 'notes-audio/'.$team->id;

        $team->purge();

        // Après validation seulement : une annulation ne doit pas laisser des visites sans leur son.
        DB::afterCommit(fn () => Storage::disk('local')->deleteDirectory($dossier));
    }
}
