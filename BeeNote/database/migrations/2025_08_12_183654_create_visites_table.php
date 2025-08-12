<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visites', function (Blueprint $table) {
            $table->id();
            
            // Relations obligatoires
            $table->foreignId('ruche_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Informations temporelles
            $table->date('date_visite');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();
            $table->integer('duree_visite')->nullable(); // en minutes, calculée
            
            // MÉTÉO & CONDITIONS
            $table->enum('meteo', ['ensoleille', 'nuageux', 'pluvieux', 'orageux'])->nullable();
            $table->decimal('temperature', 4, 1)->nullable(); // -99.9 à 999.9°C
            $table->integer('humidite')->nullable(); // 0-100%
            $table->enum('force_vent', ['nul', 'faible', 'modere', 'fort'])->nullable();
            
            // ÉTAT GÉNÉRAL DE LA COLONIE
            $table->enum('humeur_colonie', ['non_observe', 'calme', 'agitee', 'agressive', 'tres_agressive'])->default('non_observe');
            $table->enum('activite_entree', ['non_observe', 'normale', 'faible', 'intense'])->default('non_observe');
            $table->enum('estimation_population', ['non_observe', 'normale', 'faible', 'forte'])->default('non_observe');
            $table->enum('bruit_colonie', ['non_observe', 'normal', 'anormal'])->default('non_observe');
            
            // OBSERVATIONS REINE
            $table->enum('reine_vue', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('reine_marquee', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('ponte_observee', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('qualite_ponte', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('cellules_royales', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->integer('nombre_cellules_royales')->nullable();
            
            // COUVAIN
            $table->enum('oeufs_vus', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('larves_vues', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('couvain_opercule', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('surface_couvain', ['non_observe', 'normale', 'faible', 'importante'])->default('non_observe');
            $table->enum('regularite_couvain', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            
            // RÉSERVES
            $table->enum('reserves_miel', ['non_observe', 'suffisantes', 'insuffisantes'])->default('non_observe');
            $table->enum('reserves_pollen', ['non_observe', 'suffisantes', 'insuffisantes'])->default('non_observe');
            $table->integer('cadres_miel_opercule')->nullable();
            
            // SANTÉ & PARASITES
            $table->enum('varroas_observes', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->integer('nombre_varroas_estimes')->nullable();
            $table->enum('fausse_teigne', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('mortalite', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->enum('abeilles_mortes_quantite', ['quelques_unes', 'beaucoup', 'enorme'])->nullable();
            
            // PRÉDATEURS & NUISIBLES (dans la section Santé)
            $table->enum('frelons_asiatiques', ['non_observe', 'absents', 'quelques_uns', 'nombreux'])->default('non_observe');
            $table->enum('impact_frelons', ['non_observe', 'aucun', 'faible', 'modere', 'severe'])->default('non_observe');

            // STRUCTURE DE LA RUCHE
            $table->enum('cadres_etat', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            $table->integer('nombre_cadres_total')->nullable();
            $table->integer('nombre_cadres_occupes')->nullable();
            $table->enum('cire_construite', ['non_observe', 'ok', 'non_ok'])->default('non_observe');
            
            // ACTIONS RÉALISÉES PENDANT LA VISITE
            $table->boolean('nourrissement_effectue')->default(false);
            $table->enum('type_nourrissement', ['sirop', 'candi', 'pate'])->nullable();
            $table->decimal('quantite_nourrissement', 5, 2)->nullable(); // 999.99 kg max
            $table->boolean('traitement_applique')->default(false);
            $table->boolean('hausse_ajoutee')->default(false);
            $table->boolean('hausse_retiree')->default(false);
            
            // NOTES & OBSERVATIONS
            $table->text('notes_generales')->nullable();
            $table->text('actions_prevues')->nullable();
            $table->date('prochaine_visite_prevue')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visites');
    }
};