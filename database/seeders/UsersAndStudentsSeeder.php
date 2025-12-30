<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentProfile;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class UsersAndStudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des utilisateurs et profils...');

        // Créer des étudiants
        $studentNames = [
            ['Jean', 'Dupont', 'male'],
            ['Marie', 'Martin', 'female'],
            ['Pierre', 'Durand', 'male'],
            ['Sophie', 'Lefebvre', 'female'],
            ['Lucas', 'Moreau', 'male'],
            ['Emma', 'Laurent', 'female'],
            ['Thomas', 'Simon', 'male'],
            ['Chloe', 'Michel', 'female'],
            ['Alexandre', 'Leroy', 'male'],
            ['Léa', 'Roux', 'female'],
            ['Antoine', 'Fournier', 'male'],
            ['Camille', 'Girard', 'female'],
            ['Julien', 'Bonnet', 'male'],
            ['Manon', 'Dupuis', 'female'],
            ['Nicolas', 'Lambert', 'male'],
        ];

        foreach ($studentNames as $index => $studentData) {
            $user = User::create([
                'name' => $studentData[0] . ' ' . $studentData[1],
                'email' => strtolower($studentData[0]) . '.' . strtolower($studentData[1]) . '@enmaschool.com',
                'password' => Hash::make('password123'),
            ]);

            $user->assignRole('eleve');

            Student::create([
                'user_id' => $user->id,
                'first_name' => $studentData[0],
                'last_name' => $studentData[1],
                'gender' => $studentData[2],
                'date_of_birth' => now()->subYears(rand(6, 18))->subDays(rand(1, 365)),
                'phone' => '06' . rand(10000000, 99999999),
                'address' => rand(1, 99) . ' Rue de la ' . ['Paix', 'République', 'Liberté', 'Fraternité', 'École'][rand(0, 4)],
                'status' => 'active',
            ]);

            $this->command->info("Étudiant créé : {$studentData[0]} {$studentData[1]}");
        }

        // Créer des enseignants
        $teacherNames = [
            ['Paul', 'Durand', 'Mathématiques'],
            ['Claire', 'Bernard', 'Français'],
            ['Michel', 'Petit', 'Histoire-Géographie'],
            ['Anne', 'Robert', 'Sciences'],
            ['David', 'Richard', 'Anglais'],
        ];

        foreach ($teacherNames as $teacherData) {
            $user = User::create([
                'name' => $teacherData[0] . ' ' . $teacherData[1],
                'email' => strtolower($teacherData[0]) . '.' . strtolower($teacherData[1]) . '.prof@enmaschool.com',
                'password' => Hash::make('password123'),
            ]);

            $user->assignRole('enseignant');

            Teacher::create([
                'user_id' => $user->id,
                'first_name' => $teacherData[0],
                'last_name' => $teacherData[1],
                'specialization' => $teacherData[2],
                'phone' => '06' . rand(10000000, 99999999),
                'status' => 'active',
            ]);

            $this->command->info("Enseignant créé : {$teacherData[0]} {$teacherData[1]} ({$teacherData[2]})");
        }

        // Créer des parents
        $parentNames = [
            ['Robert', 'Dupont'],
            ['Sylvie', 'Martin'],
            ['François', 'Durand'],
        ];

        foreach ($parentNames as $parentData) {
            $user = User::create([
                'name' => $parentData[0] . ' ' . $parentData[1],
                'email' => strtolower($parentData[0]) . '.' . strtolower($parentData[1]) . '.parent@enmaschool.com',
                'password' => Hash::make('password123'),
            ]);

            $user->assignRole('parent');

            ParentProfile::create([
                'user_id' => $user->id,
                'first_name' => $parentData[0],
                'last_name' => $parentData[1],
                'phone' => '06' . rand(10000000, 99999999),
                'address' => rand(1, 99) . ' Avenue de la ' . ['Paix', 'République', 'Liberté'][rand(0, 2)],
            ]);

            $this->command->info("Parent créé : {$parentData[0]} {$parentData[1]}");
        }

        // Créer du personnel administratif
        $staffNames = [
            ['Christine', 'Directeur', 'Directrice'],
            ['Marc', 'Secretaire', 'Secrétaire'],
            ['Julie', 'Comptable', 'Comptable'],
        ];

        foreach ($staffNames as $staffData) {
            $user = User::create([
                'name' => $staffData[0] . ' ' . $staffData[1],
                'email' => strtolower($staffData[0]) . '.' . strtolower($staffData[1]) . '@enmaschool.com',
                'password' => Hash::make('password123'),
            ]);

            $user->assignRole('surveillant');

            Staff::create([
                'user_id' => $user->id,
                'first_name' => $staffData[0],
                'last_name' => $staffData[1],
                'position' => $staffData[2],
                'phone' => '06' . rand(10000000, 99999999),
                'status' => 'active',
            ]);

            $this->command->info("Personnel créé : {$staffData[0]} {$staffData[1]} ({$staffData[2]})");
        }

        $this->command->info('Utilisateurs et profils créés avec succès !');
        $this->command->info('- ' . User::count() . ' utilisateurs au total');
        $this->command->info('- ' . Student::count() . ' étudiants');
        $this->command->info('- ' . Teacher::count() . ' enseignants');
        $this->command->info('- ' . ParentProfile::count() . ' parents');
        $this->command->info('- ' . Staff::count() . ' personnels');
    }
}
