<?php

namespace Database\Factories;

use App\Models\Rucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ruche>
 */
class RucheFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nom' => 'Ruche '.fake()->unique()->numberBetween(1, 9999),
            'rucher_id' => Rucher::factory(),
        ];
    }
}
