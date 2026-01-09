<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $endDate = (clone $startDate)->modify('+1 year');

        return [
            'school_id' => \App\Models\School::factory(),
            'name' => '2025-2026',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'is_active' => true,
            'is_current' => true,
            'is_archived' => false,
        ];
    }

    /**
     * Indicate that the academic year is the current one.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the academic year is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
            'is_active' => false,
            'is_current' => false,
        ]);
    }
}
