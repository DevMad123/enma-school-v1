<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Carbon\Carbon;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenir les étudiants, l'année académique active et les classes
        $students = Student::take(10)->get(); // Prendre 10 étudiants
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::where('academic_year_id', $activeAcademicYear->id)->get();

        if (!$activeAcademicYear || $classes->isEmpty() || $students->isEmpty()) {
            $this->command->info('Impossible de créer des inscriptions : données manquantes.');
            return;
        }

        $this->command->info('Création des inscriptions...');

        foreach ($students as $index => $student) {
            // Choisir une classe pour l'étudiant
            $class = $classes->random();

            // Vérifier qu'il n'y a pas déjà une inscription active pour cet étudiant
            $existingEnrollment = Enrollment::where('student_id', $student->id)
                ->where('academic_year_id', $activeAcademicYear->id)
                ->where('status', 'active')
                ->first();

            if ($existingEnrollment) {
                $this->command->info("Étudiant {$student->full_name} déjà inscrit, ignoré.");
                continue;
            }

            // Définir des statuts variés
            $statuses = ['active', 'completed', 'cancelled'];
            $weights = [70, 20, 10]; // 70% actif, 20% terminé, 10% annulé
            
            $rand = mt_rand(1, 100);
            if ($rand <= 70) {
                $status = 'active';
            } elseif ($rand <= 90) {
                $status = 'completed';
            } else {
                $status = 'cancelled';
            }

            // Dates d'inscription variées
            $enrollmentDate = Carbon::now()->subDays(mt_rand(1, 60));

            $enrollment = Enrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $activeAcademicYear->id,
                'class_id' => $class->id,
                'enrollment_date' => $enrollmentDate,
                'status' => $status,
            ]);

            // Si l'inscription est active, ajouter l'étudiant à la table pivot
            if ($status === 'active') {
                $student->classes()->syncWithoutDetaching([
                    $class->id => ['assigned_at' => $enrollmentDate]
                ]);
            }

            $this->command->info("Inscription créée : {$student->full_name} -> {$class->name} ({$status})");
        }

        // Créer quelques inscriptions supplémentaires avec différents statuts
        $this->createAdditionalEnrollments($students, $activeAcademicYear, $classes);

        $this->command->info('Inscriptions créées avec succès !');
    }

    private function createAdditionalEnrollments($students, $academicYear, $classes)
    {
        // Créer des inscriptions pour l'année précédente (simulées)
        for ($i = 0; $i < 5; $i++) {
            $student = $students->random();
            $class = $classes->random();

            // Vérifier qu'il n'y a pas de doublon
            $existing = Enrollment::where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->exists();

            if (!$existing) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $class->id,
                    'enrollment_date' => Carbon::now()->subDays(mt_rand(90, 180)),
                    'status' => 'completed',
                ]);
            }
        }

        // Créer quelques inscriptions annulées
        for ($i = 0; $i < 3; $i++) {
            $student = $students->random();
            $class = $classes->random();

            $existing = Enrollment::where('student_id', $student->id)
                ->where('academic_year_id', $academicYear->id)
                ->exists();

            if (!$existing) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $class->id,
                    'enrollment_date' => Carbon::now()->subDays(mt_rand(10, 30)),
                    'status' => 'cancelled',
                ]);
            }
        }
    }
}
