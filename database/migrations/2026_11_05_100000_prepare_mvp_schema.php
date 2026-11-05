<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['ruchers', 'ruches', 'recoltes'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->softDeletes());
        }

        Schema::table('visites', function (Blueprint $table) {
            $table->softDeletes();
            // Identifiant généré sur le téléphone : un envoi répété ne crée jamais de doublon.
            $table->uuid('client_uuid')->nullable();
            $table->string('note_audio_path')->nullable();
            $table->unsignedSmallInteger('note_audio_duree')->nullable();
            $table->unique(['team_id', 'client_uuid']);
        });

        Schema::table('traitements', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('numero_lot', 60)->nullable();
            $table->string('dosage', 50)->nullable()->change();
            $table->string('methode_application')->nullable()->change();
        });

        Schema::table('recoltes', function (Blueprint $table) {
            $table->integer('nombre_cadres')->nullable()->change();
            $table->string('qualite')->nullable()->change();
        });

        Schema::create('ruche_emplacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ruche_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('depuis_rucher_id')->nullable()->constrained('ruchers')->nullOnDelete();
            $table->foreignId('vers_rucher_id')->nullable()->constrained('ruchers')->nullOnDelete();
            $table->date('date_deplacement');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruche_emplacements');

        Schema::table('visites', function (Blueprint $table) {
            $table->dropUnique(['team_id', 'client_uuid']);
            $table->dropColumn(['client_uuid', 'note_audio_path', 'note_audio_duree', 'deleted_at']);
        });

        Schema::table('traitements', function (Blueprint $table) {
            $table->dropColumn(['numero_lot', 'deleted_at']);
        });

        foreach (['ruchers', 'ruches', 'recoltes'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropSoftDeletes());
        }
    }
};
