<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Teacher>
 */
class TeacherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'school_id' => \App\Models\School::factory(),
            'employee_id' => $this->faker->unique()->numerify('TCH####'),
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->phoneNumber,
            'specialization' => $this->faker->randomElement(['Mathématiques', 'Français', 'Anglais', 'Sciences', 'Histoire']),
            'hire_date' => $this->faker->dateTimeBetween('-10 years', 'now'),
            'status' => 'active',
        ];
    }
}
