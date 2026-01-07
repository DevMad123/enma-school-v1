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
            RolesAndPermissionsSeeder::class,  // D'abord créer les rôles et permissions
            SchoolSeeder::class,               // Créer les écoles
            AcademicYearSeeder::class,         // Années académiques liées aux écoles
            GradePeriodSeeder::class,          // Périodes (trimestres/semestres)
            AcademicStructureSeeder::class,    // Structure académique
            // MODULE UNIVERSITAIRE — STRUCTURE UNIVERSITAIRE
            UniversityUFRSeeder::class,        // UFR (Unités de Formation et de Recherche)
            UniversityDepartmentSeeder::class, // Départements universitaires
            UniversityProgramSeeder::class,    // Programmes d'études universitaires
            UniversitySemesterSeeder::class,   // Semestres universitaires
            SubjectSeeder::class,              // Matières
            DefaultEducationalSettingsSeeder::class, // Paramètres éducatifs par défaut
            AdminUsersSeeder::class,           // Utilisateurs administrateurs
            UsersAndStudentsSeeder::class,     // Étudiants et personnel de base
            // MODULE A4 — PERSONNEL & AFFECTATIONS PÉDAGOGIQUES
            ModuleA4PersonnelSeeder::class,    // Enseignants et staff supplémentaire
            ModuleA4AssignmentSeeder::class,   // Affectations pédagogiques
            EnrollmentSeeder::class,           // Inscriptions
            TeacherAssignmentSeeder::class,    // Affectations d'enseignants
            // MODULE 8 — NOTES & ÉVALUATIONS
            EvaluationSeeder::class,           // Évaluations
            GradeSeeder::class,                // Notes
        ]);
    }
}
