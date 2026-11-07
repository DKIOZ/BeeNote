<?php

namespace Database\Factories;

use App\Models\Rucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ruche>
 */
class RucheFactory extends Factory
{
    private static int $numero = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Numéros croissants : l'ordre de création suit l'ordre d'affichage, trié par numéro.
            'nom' => 'Ruche '.self::$numero++,
            'rucher_id' => Rucher::factory(),
        ];
    }
}
