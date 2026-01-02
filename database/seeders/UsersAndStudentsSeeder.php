<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentProfile;
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

            $user->assignRole('student');

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

        // Créer des parents
        $parentNames = [
            ['Robert', 'Dupont'],
            ['Sylvie', 'Martin'],
            ['François', 'Durand'],
            ['Catherine', 'Lefebvre'],
            ['Patrick', 'Moreau'],
            ['Nicole', 'Laurent'],
            ['Bernard', 'Simon'],
            ['Monique', 'Michel'],
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

        $this->command->info('Utilisateurs et profils créés avec succès !');
        $this->command->info('- ' . User::count() . ' utilisateurs au total');
        $this->command->info('- ' . Student::count() . ' étudiants');
        $this->command->info('- ' . ParentProfile::count() . ' parents');
    }
}
