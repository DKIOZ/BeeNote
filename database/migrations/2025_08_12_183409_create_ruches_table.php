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
        Schema::create('ruches', function (Blueprint $table) {
            $table->id();
            
            // Relations - Cascade important pour cohérence données
            $table->foreignId('rucher_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Informations de base
            $table->string('nom', 50); // "Ruche A", "Bleue", etc.
            $table->string('couleur', 30)->nullable(); // "Bleu", "Rouge", etc.
            
            // Type de ruche - ENUM Laravel 12
            $table->enum('type', [
                'dadant', 
                'warre', 
                'langstroth', 
                'ktbh',      // Kenyan Top Bar Hive
                'autre'
            ])->default('dadant'); // Dadant = plus courant en France
            
            // Statut de la colonie
            $table->enum('statut', [
                'active',
                'inactive', 
                'morte'
            ])->default('active');
            
            // Informations sur la reine
            $table->year('annee_reine')->nullable(); // Année de naissance
            $table->enum('couleur_marquage_reine', [
                'blanc',
                'jaune', 
                'rouge',
                'vert',
                'bleu'
            ])->nullable(); // Code couleur international
            
            // Dates importantes
            $table->date('date_installation')->nullable(); // Quand la colonie a été installée
            
            // Notes libres
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruches');
    }
};