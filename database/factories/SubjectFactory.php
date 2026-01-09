<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
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
            'name' => $this->faker->randomElement(['Mathématiques', 'Français', 'Anglais', 'Sciences Physiques', 'Histoire-Géographie', 'SVT']),
            'code' => $this->faker->unique()->regexify('[A-Z]{2,4}[0-9]{1,2}'),
            'coefficient' => $this->faker->numberBetween(1, 4),
            'is_active' => true,
        ];
    }
}
