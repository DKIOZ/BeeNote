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
        Schema::create('recoltes', function (Blueprint $table) {
            $table->id();
            
            // Relations obligatoires
            $table->foreignId('ruche_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Informations de base
            $table->date('date_recolte');
            
            // Type de miel selon origine florale
            $table->enum('type_miel', [
                'acacia',
                'tilleul', 
                'chataignier',
                'toutes_fleurs',
                'lavande',
                'autre'
            ]);
            
            // Quantités récoltées
            $table->decimal('quantite_kg', 6, 2); // 9999.99 kg max
            $table->integer('nombre_cadres');
            
            // Qualité du miel
            $table->enum('qualite', [
                'excellente',
                'bonne', 
                'moyenne',
                'mediocre'
            ]);
            
            $table->decimal('humidite_miel', 4, 1)->nullable(); // 0.0 à 999.9%
            $table->text('notes_qualite')->nullable();
            $table->text('notes_degustation')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recoltes');
    }
};