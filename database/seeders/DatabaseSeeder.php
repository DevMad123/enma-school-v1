<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Exécuter les seeders dans l'ordre logique
        $this->call([
            RolesAndPermissionsSeeder::class,
            SchoolSeeder::class,              // D'abord créer les écoles
            AcademicYearSeeder::class,        // Puis les années académiques liées aux écoles
            GradePeriodSeeder::class,         // Ensuite les périodes (trimestres/semestres)
            AdminUsersSeeder::class,          // Créer les utilisateurs administrateurs
            AcademicStructureSeeder::class,
            SubjectSeeder::class,
            UsersAndStudentsSeeder::class,
            // MODULE A4 — PERSONNEL & AFFECTATIONS PÉDAGOGIQUES
            ModuleA4PersonnelSeeder::class,   // Créer les enseignants et le staff
            ModuleA4AssignmentSeeder::class,  // Puis leurs affectations
            EnrollmentSeeder::class,
            TeacherAssignmentSeeder::class,
            // MODULE 8 — NOTES & ÉVALUATIONS
            EvaluationSeeder::class,
            GradeSeeder::class,
        ]);
    }
}
