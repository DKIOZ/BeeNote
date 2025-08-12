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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            
            // Relation polymorphique - Une photo peut appartenir à n'importe quel modèle
            $table->morphs('photoable'); // Crée photoable_type + photoable_id
            
            // Informations fichier
            $table->string('chemin', 500); // Chemin vers le fichier stocké
            $table->string('titre')->nullable(); // Titre optionnel
            $table->text('description')->nullable(); // Description libre
            
            // Métadonnées fichier
            $table->unsignedBigInteger('taille_fichier')->nullable(); // Taille en bytes
            $table->string('mime_type', 50); // image/jpeg, image/png, etc.
            
            // Ordre d'affichage
            $table->unsignedInteger('ordre')->default(0); // Pour ordonner les photos
            
            $table->timestamps();
            
            // Index pour tri par ordre d'affichage
            $table->index('ordre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};