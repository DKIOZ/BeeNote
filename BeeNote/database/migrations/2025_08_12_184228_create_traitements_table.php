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
        Schema::create('traitements', function (Blueprint $table) {
            $table->id();
            
            // Relations obligatoires
            $table->foreignId('ruche_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Relation optionnelle - traitement peut être fait hors visite
            $table->foreignId('visite_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('set null'); // Si visite supprimée, traitement reste mais perd le lien
            
            // Informations de base
            $table->date('date_traitement');
            
            // Type de traitement selon pathologie
            $table->enum('type_traitement', [
                'varroa',
                'nosema', 
                'loque',
                'preventif',
                'frelon_asiatique',
                'autre'
            ]);
            
            // Produit et application
            $table->string('produit_utilise', 100); // Apivar, Apiguard, etc.
            $table->string('dosage', 50); // "2 lanières", "5ml/ruche", etc.
            
            $table->enum('methode_application', [
                'vaporisation',
                'goutte_a_goutte',
                'bandes',
                'poudrage',
                'autre'
            ]);
            
            $table->decimal('temperature_application', 4, 1)->nullable(); // Température lors application
            
            // Suivi efficacité
            $table->enum('efficacite', [
                'non_evaluee',
                'excellente',
                'bonne', 
                'moyenne',
                'faible'
            ])->default('non_evaluee');
            
            $table->text('effets_secondaires')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traitements');
    }
};