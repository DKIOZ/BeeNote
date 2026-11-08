<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une visite complétée dans la journée garde chaque note vocale, sans en écraser.
        Schema::table('visites', function (Blueprint $table) {
            $table->json('notes_audio_ajoutees')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('visites', fn (Blueprint $table) => $table->dropColumn('notes_audio_ajoutees'));
    }
};
