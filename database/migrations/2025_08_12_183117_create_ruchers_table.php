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
        Schema::create('ruchers', function (Blueprint $table) {
            $table->id();
            
            // Relation propriétaire - OBLIGATOIRE pour sécurité
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
            
            // Informations de base
            $table->string('nom', 100);
            $table->string('localisation')->nullable(); // Adresse textuelle
            
            // Géolocalisation - DECIMAL pour précision GPS
            $table->decimal('latitude', 10, 8)->nullable();  // ex: 47.12345678
            $table->decimal('longitude', 11, 8)->nullable(); // ex: -1.23456789
            
            // Informations complémentaires
            $table->text('description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruchers');
    }
};