<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SchoolClass>
 */
class SchoolClassFactory extends Factory
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
            'level_id' => \App\Models\Level::factory(),
            'name' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'capacity' => $this->faker->numberBetween(20, 40),
            'is_active' => true,
        ];
    }
}
