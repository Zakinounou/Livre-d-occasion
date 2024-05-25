<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\auteur>
 */
class auteurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            
            'nom'=> fake()->firstName(),
            'prenom'=>fake()->lastName(),
            'Nationalite'=>fake()->country(), 
        ];
    }
}
