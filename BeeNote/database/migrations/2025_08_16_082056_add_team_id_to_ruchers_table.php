<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ruchers', function (Blueprint $table) {
            // Supprimer l'ancienne relation user_id
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            
            // Ajouter la nouvelle relation team_id
            $table->foreignId('team_id')
                  ->constrained()
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ruchers', function (Blueprint $table) {
            // Annuler les modifications
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
            
            // Remettre user_id
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');
        });
    }
};