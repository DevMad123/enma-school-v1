<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
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
            'academic_year_id' => \App\Models\AcademicYear::factory(),
            'cycle_id' => \App\Models\Cycle::factory(),
            'name' => $this->faker->randomElement(['6ème', '5ème', '4ème', '3ème', '2nde', '1ère', 'Terminale']),
            'code' => $this->faker->randomElement(['6', '5', '4', '3', '2nd', '1ere', 'Tle']),
            'order' => $this->faker->numberBetween(1, 7),
            'is_active' => true,
        ];
    }
}
