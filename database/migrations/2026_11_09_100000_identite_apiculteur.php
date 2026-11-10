<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ce que le registre d'élevage demande sur l'apiculteur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('adresse')->nullable();
            $table->string('telephone', 30)->nullable();
            $table->string('napi', 20)->nullable();
            $table->string('siret', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['adresse', 'telephone', 'napi', 'siret']);
        });
    }
};
