<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enrollment>
 */
class EnrollmentFactory extends Factory
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
            'class_id' => \App\Models\SchoolClass::factory(),
            'academic_year_id' => \App\Models\AcademicYear::factory(),
            'enrollment_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'status' => 'active',
        ];
    }
}
