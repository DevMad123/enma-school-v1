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
            AcademicYearSeeder::class,  // Ajout du seeder pour les années académiques
            AcademicStructureSeeder::class,
            SubjectSeeder::class,
            UsersAndStudentsSeeder::class,
            EnrollmentSeeder::class,
            TeacherAssignmentSeeder::class,
            // MODULE 8 — NOTES & ÉVALUATIONS
            GradePeriodSeeder::class,
            EvaluationSeeder::class,
            GradeSeeder::class,
        ]);

        // Créer l'utilisateur administrateur principal
        $superAdmin = User::create([
            'name' => 'Super Administrateur',
            'email' => 'admin@enmaschool.com',
            'password' => bcrypt('password123'),
        ]);
        // Assigner le rôle super_admin à l'utilisateur
        $superAdmin->assignRole('super_admin');
        
        // Créer le profil staff pour l'admin
        \App\Models\Staff::create([
            'user_id' => $superAdmin->id,
            'first_name' => 'Admin',
            'last_name' => 'Système',
            'position' => 'Directeur Général',
            'phone' => '123-456-7890',
            'status' => 'active',
        ]);
    }
}
