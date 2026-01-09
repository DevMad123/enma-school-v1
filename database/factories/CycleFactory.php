<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cycle>
 */
class CycleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => \App\Models\School::factory(),
            'name' => $this->faker->randomElement(['Préscolaire', 'Primaire', 'Collège', 'Lycée']),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the cycle is for primary education.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Primaire',
        ]);
    }

    /**
     * Indicate that the cycle is for secondary education.
     */
    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Secondaire',
        ]);
    }
}
