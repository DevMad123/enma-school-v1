<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company . ' School',
            'short_name' => $this->faker->optional()->lexify('??'),
            'type' => $this->faker->randomElement(['pre_university', 'university']),
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'country' => 'Côte d\'Ivoire',
            'academic_system' => $this->faker->randomElement(['trimestre', 'semestre']),
            'grading_system' => $this->faker->randomElement(['20', '100', 'custom']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the school is a pre-university school.
     */
    public function preUniversity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pre_university',
            'educational_levels' => ['primary', 'secondary'],
        ]);
    }

    /**
     * Indicate that the school is a university.
     */
    public function university(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'university',
            'educational_levels' => ['undergraduate', 'graduate'],
        ]);
    }

    /**
     * Indicate that the school is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}