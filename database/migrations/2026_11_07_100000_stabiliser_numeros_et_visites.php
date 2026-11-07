<?php

use App\Services\RucherOverview;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Le numéro dit à voix haute est attribué une fois pour toutes : il ne bouge plus avec les autres ruches.
        Schema::table('ruches', function (Blueprint $table) {
            $table->unsignedSmallInteger('numero')->nullable();
        });

        // Les envois du téléphone fusionnés dans une visite déjà reçue le même jour.
        Schema::table('visites', function (Blueprint $table) {
            $table->text('uuids_fusionnes')->nullable();
        });

        $parEquipe = DB::table('ruches')
            ->join('ruchers', 'ruchers.id', '=', 'ruches.rucher_id')
            ->orderBy('ruches.id')
            ->get(['ruches.id', 'ruches.nom', 'ruchers.team_id'])
            ->groupBy('team_id');

        foreach ($parEquipe as $ruches) {
            foreach (RucherOverview::numeros($ruches) as $id => $numero) {
                DB::table('ruches')->where('id', $id)->update(['numero' => $numero]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('visites', fn (Blueprint $table) => $table->dropColumn('uuids_fusionnes'));
        Schema::table('ruches', fn (Blueprint $table) => $table->dropColumn('numero'));
    }
};
