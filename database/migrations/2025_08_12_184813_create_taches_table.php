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
        Schema::create('taches', function (Blueprint $table) {
            $table->id();
            
            // Relation utilisateur obligatoire
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Relations optionnelles - Flexibilité niveau application
            $table->foreignId('rucher_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade'); // Tâches générales du rucher
            
            $table->foreignId('ruche_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade'); // Tâches spécifiques à une ruche
            
            // Informations de base
            $table->string('titre', 200);
            $table->text('description')->nullable();
            
            // Catégorisation
            $table->enum('type', [
                'visite',
                'traitement', 
                'recolte',
                'nourrissement',
                'maintenance',
                'preparation_hivernage',
                'pose_hausse',
                'autre'
            ]);
            
            $table->enum('priorite', [
                'basse',
                'normale', 
                'haute',
                'urgente'
            ])->default('normale');
            
            $table->enum('statut', [
                'a_faire',
                'en_cours',
                'terminee', 
                'reportee',
                'annulee'
            ])->default('a_faire');
            
            // Planification temporelle
            $table->date('date_prevue');
            $table->date('date_limite')->nullable();
            $table->date('date_realisee')->nullable();
            $table->integer('temps_estime')->nullable(); // en minutes
            
            // RAPPELS & RÉCURRENCE
            $table->boolean('rappel_active')->default(false);
            $table->integer('rappel_jours_avant')->nullable(); // Nb jours avant pour rappel
            $table->boolean('recurrente')->default(false);
            $table->enum('periode_recurrence', [
                'hebdomadaire',
                'mensuelle',
                'trimestrielle', 
                'saisonniere',
                'annuelle'
            ])->nullable();
            $table->date('prochaine_occurrence')->nullable();
            
            // LIENS AVEC AUTRES ENTITÉS (traçabilité)
            $table->foreignId('visite_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null'); // Si tâche réalisée lors d'une visite
            
            $table->foreignId('traitement_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null'); // Si tâche liée à un traitement
            
            $table->foreignId('recolte_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null'); // Si tâche liée à une récolte
            
            // Notes libres
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['user_id', 'statut']); // Filtrage fréquent
            $table->index('date_prevue'); // Tri par date
            $table->index(['recurrente', 'prochaine_occurrence']); // Gestion récurrence
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taches');
    }
};