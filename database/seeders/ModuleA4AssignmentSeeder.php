<?php

namespace Database\Seeders;

use App\Models\TeacherAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

/**
 * MODULE A4 - Seeder pour les affectations pédagogiques
 */
class ModuleA4AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer l'année académique active
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        if (!$academicYear) {
            $this->command->warn('Aucune année académique active trouvée. Veuillez d\'abord exécuter les seeders des modules précédents.');
            return;
        }

        // Récupérer les enseignants
        $teachers = Teacher::with('user')->get();
        
        if ($teachers->isEmpty()) {
            $this->command->warn('Aucun enseignant trouvé. Veuillez d\'abord exécuter le seeder du personnel.');
            return;
        }

        // Récupérer les classes et matières
        $classes = SchoolClass::with('level')->get();
        $subjects = Subject::all();

        if ($classes->isEmpty() || $subjects->isEmpty()) {
            $this->command->warn('Classes ou matières manquantes. Veuillez vérifier les données du MODULE A3.');
            return;
        }

        $this->command->info('📚 Création des affectations pédagogiques...');

        // Définir les affectations par spécialisation
        $assignmentRules = [
            'Mathématiques' => ['Mathématiques', 'Physique'],
            'Français' => ['Français', 'Littérature'],
            'Sciences' => ['Biologie', 'Chimie', 'Sciences'],
            'Histoire-Géographie' => ['Histoire', 'Géographie'],
            'Langues' => ['Anglais', 'Espagnol', 'Allemand'],
        ];

        $assignmentsCreated = 0;

        foreach ($teachers as $teacher) {
            $teacherSpecialization = $teacher->specialization;
            $possibleSubjects = $assignmentRules[$teacherSpecialization] ?? [];
            
            if (empty($possibleSubjects)) {
                $this->command->warn("Aucune matière définie pour la spécialisation: {$teacherSpecialization}");
                continue;
            }

            // Trouver les matières correspondantes dans la base
            $teacherSubjects = $subjects->whereIn('name', $possibleSubjects);
            
            if ($teacherSubjects->isEmpty()) {
                $this->command->warn("Aucune matière trouvée pour: " . implode(', ', $possibleSubjects));
                continue;
            }

            // Affecter l'enseignant à 2-4 classes aléatoirement
            $assignedClasses = $classes->random(rand(2, min(4, $classes->count())));

            foreach ($assignedClasses as $class) {
                // Choisir une matière compatible avec la spécialisation
                $subject = $teacherSubjects->random();
                
                // Vérifier que l'affectation n'existe pas déjà
                $existingAssignment = TeacherAssignment::where([
                    'teacher_id' => $teacher->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                ])->first();

                if ($existingAssignment) {
                    continue; // Éviter les doublons
                }

                // Créer l'affectation
                $assignment = TeacherAssignment::create([
                    'teacher_id' => $teacher->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'assignment_type' => 'regular',
                    'start_date' => $academicYear->start_date,
                    'end_date' => $academicYear->end_date,
                    'weekly_hours' => rand(2, 6), // 2-6 heures par semaine
                    'notes' => "Affectation automatique - {$teacherSpecialization}",
                    'is_active' => true,
                ]);

                $assignmentsCreated++;
                
                $this->command->info("✓ {$teacher->full_name} → {$class->level->name} {$class->name} ({$subject->name})");
            }
        }

        // Créer quelques affectations temporaires pour démonstration
        $this->command->info('⏰ Création d\'affectations temporaires...');
        
        // Prendre 2 enseignants pour des affectations de remplacement
        $substituteTeachers = $teachers->random(min(2, $teachers->count()));
        
        foreach ($substituteTeachers as $teacher) {
            $randomClass = $classes->random();
            $randomSubject = $subjects->random();
            
            // Vérifier que l'affectation n'existe pas déjà
            $existingAssignment = TeacherAssignment::where([
                'teacher_id' => $teacher->id,
                'academic_year_id' => $academicYear->id,
                'class_id' => $randomClass->id,
                'subject_id' => $randomSubject->id,
            ])->first();

            if (!$existingAssignment) {
                $startDate = now()->addDays(rand(30, 60));
                $endDate = $startDate->copy()->addDays(rand(15, 45));
                
                $assignment = TeacherAssignment::create([
                    'teacher_id' => $teacher->id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $randomClass->id,
                    'subject_id' => $randomSubject->id,
                    'assignment_type' => 'temporary',
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'weekly_hours' => rand(3, 5),
                    'notes' => 'Affectation temporaire - Remplacement',
                    'is_active' => true,
                ]);

                $assignmentsCreated++;
                
                $this->command->info("✓ {$teacher->full_name} → {$randomClass->level->name} {$randomClass->name} (TEMP: {$randomSubject->name})");
            }
        }

        $this->command->info('🎓 MODULE A4 - Affectations pédagogiques créées avec succès !');
        $this->command->info('');
        $this->command->info('📊 Résumé:');
        $this->command->info("   - {$assignmentsCreated} affectations créées");
        $this->command->info('   - ' . TeacherAssignment::where('assignment_type', 'regular')->count() . ' affectations régulières');
        $this->command->info('   - ' . TeacherAssignment::where('assignment_type', 'temporary')->count() . ' affectations temporaires');
        $this->command->info('   - ' . TeacherAssignment::active()->count() . ' affectations actives');
    }
}