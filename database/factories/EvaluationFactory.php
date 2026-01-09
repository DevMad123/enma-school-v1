<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject_id' => \App\Models\Subject::factory(),
            'school_class_id' => \App\Models\SchoolClass::factory(),
            'teacher_id' => \App\Models\Teacher::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'evaluation_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'coefficient' => $this->faker->numberBetween(1, 3),
            'max_score' => 20,
            'type' => $this->faker->randomElement(['devoir', 'composition', 'controle']),
        ];
    }
}
