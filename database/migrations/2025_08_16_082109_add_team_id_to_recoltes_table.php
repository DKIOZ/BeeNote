<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recoltes', function (Blueprint $table) {
            // Ajouter team_id pour l'appartenance à l'équipe
            // On garde user_id pour la traçabilité (qui a fait la récolte)
            $table->foreignId('team_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('recoltes', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }
};