<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => \App\Models\Student::factory(),
            'evaluation_id' => \App\Models\Evaluation::factory(),
            'score' => $this->faker->randomFloat(2, 0, 20),
            'is_absent' => false,
            'is_excused' => false,
            'comments' => $this->faker->optional()->sentence(),
        ];
    }
}
