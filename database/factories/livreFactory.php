<?php

namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\livre>
 */
class livreFactory extends Factory
{
        /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   $minValue = 1000000000000; // 10^12
        $maxValue = 9999999999999; // 10^13 - 1
        return [
                'id'=>fake()->numberBetween($minValue,$maxValue),
                'titre'=> fake()->sentence(5),
                'Description'=> fake()->paragraph(),
                'anneePublication'=> fake()->year(),
                'category'=>fake()->word(),
                'nbex'=>0,
                
        ];
    }
}
