<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SAISIES = ['visites', 'traitements', 'recoltes'];

    public function up(): void
    {
        foreach (self::SAISIES as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->foreignId('rucher_id')->nullable()->constrained('ruchers')->nullOnDelete();
            });

            DB::table($nom)->update([
                'rucher_id' => DB::raw("(select ruches.rucher_id from ruches where ruches.id = {$nom}.ruche_id)"),
            ]);
        }

        // Le départ d'un membre ne doit pas effacer ce qu'il a noté dans le registre.
        foreach ([...self::SAISIES, 'taches', 'ruche_emplacements'] as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            Schema::table($nom, function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->change();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    /**
     * user_id reste nullable au retour : des lignes peuvent avoir perdu leur auteur.
     */
    public function down(): void
    {
        foreach ([...self::SAISIES, 'taches', 'ruche_emplacements'] as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            Schema::table($nom, function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        foreach (self::SAISIES as $nom) {
            Schema::table($nom, function (Blueprint $table) {
                $table->dropConstrainedForeignId('rucher_id');
            });
        }
    }
};
