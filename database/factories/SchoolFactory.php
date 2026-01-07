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
            'code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{3}'),
            'type' => $this->faker->randomElement(['preuniversity', 'university']),
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'principal_name' => $this->faker->name,
            'status' => 'active',
            'established_at' => $this->faker->date,
        ];
    }

    /**
     * Indicate that the school is a preuniversity school.
     */
    public function preuniversity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'preuniversity',
            'name' => $this->faker->company . ' College',
        ]);
    }

    /**
     * Indicate that the school is a university.
     */
    public function university(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'university',
            'name' => 'Université ' . $this->faker->city,
        ]);
    }

    /**
     * Indicate that the school is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}