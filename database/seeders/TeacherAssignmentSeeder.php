<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TeacherAssignment;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\SchoolClass;

class TeacherAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenir les enseignants, l'année académique active et les classes
        $teachers = Teacher::where('status', 'active')->get();
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        $classes = SchoolClass::where('academic_year_id', $activeAcademicYear->id)->get();

        if (!$activeAcademicYear || $classes->isEmpty() || $teachers->isEmpty()) {
            $this->command->info('Impossible de créer des affectations : données manquantes.');
            return;
        }

        $this->command->info('Création des affectations d\'enseignants...');

        // Stratégie d'affectation : chaque enseignant sera affecté à 2-4 classes
        foreach ($teachers as $teacher) {
            // Nombre aléatoire de classes pour cet enseignant
            $numberOfClasses = rand(2, 4);
            
            // Sélectionner des classes aléatoirement pour cet enseignant
            $selectedClasses = $classes->random(min($numberOfClasses, $classes->count()));

            foreach ($selectedClasses as $class) {
                // Vérifier qu'il n'y a pas déjà une affectation pour cet enseignant dans cette classe
                $existingAssignment = TeacherAssignment::where('teacher_id', $teacher->id)
                    ->where('academic_year_id', $activeAcademicYear->id)
                    ->where('class_id', $class->id)
                    ->whereNull('subject_id')
                    ->first();

                if (!$existingAssignment) {
                    TeacherAssignment::create([
                        'teacher_id' => $teacher->id,
                        'academic_year_id' => $activeAcademicYear->id,
                        'class_id' => $class->id,
                        'subject_id' => null, // Sera géré dans le module suivant
                    ]);

                    $this->command->info("Affectation créée : {$teacher->full_name} → {$class->level->name} {$class->name}");
                }
            }
        }

        // Ajoutons quelques affectations spécifiques pour s'assurer que chaque classe a au moins un enseignant
        $this->ensureAllClassesHaveTeachers($teachers, $activeAcademicYear, $classes);

        $this->command->info('Affectations d\'enseignants créées avec succès !');
        $this->command->info('- ' . TeacherAssignment::count() . ' affectations au total');
    }

    private function ensureAllClassesHaveTeachers($teachers, $academicYear, $classes)
    {
        foreach ($classes as $class) {
            // Vérifier si cette classe a au moins un enseignant affecté
            $assignedTeachers = TeacherAssignment::where('class_id', $class->id)
                ->where('academic_year_id', $academicYear->id)
                ->count();

            if ($assignedTeachers === 0) {
                // Affecter un enseignant aléatoire à cette classe
                $randomTeacher = $teachers->random();

                TeacherAssignment::create([
                    'teacher_id' => $randomTeacher->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $class->id,
                    'subject_id' => null,
                ]);

                $this->command->info("Affectation de sécurité : {$randomTeacher->full_name} → {$class->level->name} {$class->name}");
            }
        }
    }
}
