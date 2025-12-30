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

class UserProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un administrateur
        $admin = User::firstOrCreate([
            'email' => 'admin@enmaschool.com',
        ], [
            'name' => 'Administrateur',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Créer des directeurs
        foreach (['directeur@enmaschool.com', 'directeur.adjoint@enmaschool.com'] as $index => $email) {
            $user = User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => $index == 0 ? 'Directeur Principal' : 'Directeur Adjoint',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('director');

            Staff::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => explode(' ', $user->name)[0],
                'last_name' => explode(' ', $user->name)[1] ?? '',
                'position' => $index == 0 ? 'Directeur' : 'Directeur Adjoint',
                'phone' => '0123456789',
                'status' => 'active',
            ]);
        }

        // Créer des enseignants
        $teacherNames = [
            'Marie Dubois' => 'marie.dubois@enmaschool.com',
            'Jean Martin' => 'jean.martin@enmaschool.com',
            'Sophie Laurent' => 'sophie.laurent@enmaschool.com',
            'Pierre Bernard' => 'pierre.bernard@enmaschool.com',
            'Emma Moreau' => 'emma.moreau@enmaschool.com',
            'Lucas Simon' => 'lucas.simon@enmaschool.com',
            'Claire Leroy' => 'claire.leroy@enmaschool.com',
            'Thomas Roux' => 'thomas.roux@enmaschool.com',
        ];

        foreach ($teacherNames as $name => $email) {
            $user = User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => $name,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('teacher');

            $nameParts = explode(' ', $name);
            Teacher::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1],
                'specialization' => $this->getRandomSpecialization(),
                'phone' => '01234567' . str_pad(rand(10, 99), 2, '0'),
                'hire_date' => now()->subMonths(rand(6, 36)),
                'status' => 'active',
            ]);
        }

        // Créer des parents
        $parentNames = [
            'Mohammed Ahmed' => 'mohammed.ahmed@email.com',
            'Fatima Benali' => 'fatima.benali@email.com',
            'Amadou Diallo' => 'amadou.diallo@email.com',
            'Aïcha Traoré' => 'aicha.traore@email.com',
            'Ibrahim Kone' => 'ibrahim.kone@email.com',
            'Mariam Sylla' => 'mariam.sylla@email.com',
            'Oumar Cissé' => 'oumar.cisse@email.com',
            'Aminata Dembélé' => 'aminata.dembele@email.com',
            'Moussa Coulibaly' => 'moussa.coulibaly@email.com',
            'Rokiatou Bah' => 'rokiatou.bah@email.com',
        ];

        foreach ($parentNames as $name => $email) {
            $user = User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => $name,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('parent');

            $nameParts = explode(' ', $name);
            ParentProfile::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1],
                'phone' => '07654321' . str_pad(rand(10, 99), 2, '0'),
                'profession' => $this->getRandomProfession(),
                'address' => $this->getRandomAddress(),
                'status' => 'active',
            ]);
        }

        // Créer des étudiants
        $studentNames = [
            'Aminata Ahmed' => 'female',
            'Ibrahim Benali' => 'male',
            'Fatoumata Diallo' => 'female',
            'Moussa Traoré' => 'male',
            'Mariame Kone' => 'female',
            'Seydou Sylla' => 'male',
            'Kadiatou Cissé' => 'female',
            'Bakary Dembélé' => 'male',
            'Awa Coulibaly' => 'female',
            'Mamadou Bah' => 'male',
            'Oumou Sanogo' => 'female',
            'Adama Keita' => 'male',
            'Djénéba Camara' => 'female',
            'Souleymane Touré' => 'male',
            'Aissatou Sidibé' => 'female',
            'Boubacar Fofana' => 'male',
            'Salamata Sissoko' => 'female',
            'Modibo Doumbia' => 'male',
            'Ramata Maiga' => 'female',
            'Youssoupha Ndiaye' => 'male',
        ];

        foreach ($studentNames as $name => $gender) {
            $nameParts = explode(' ', $name);
            $email = strtolower($nameParts[0] . '.' . $nameParts[1]) . '@student.enmaschool.com';
            
            $user = User::firstOrCreate([
                'email' => $email,
            ], [
                'name' => $name,
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('student');

            Student::firstOrCreate([
                'user_id' => $user->id,
            ], [
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1],
                'gender' => $gender,
                'date_of_birth' => now()->subYears(rand(6, 18))->subDays(rand(1, 365)),
                'phone' => '0654321' . str_pad(rand(100, 999), 3, '0'),
                'address' => $this->getRandomAddress(),
                'status' => 'active',
            ]);
        }

        $this->command->info('Utilisateurs et profils créés avec succès !');
        $this->command->info('- ' . User::count() . ' utilisateurs');
        $this->command->info('- ' . Student::count() . ' étudiants');
        $this->command->info('- ' . Teacher::count() . ' enseignants');
        $this->command->info('- ' . ParentProfile::count() . ' parents');
        $this->command->info('- ' . Staff::count() . ' membres du personnel');
    }

    private function getRandomSpecialization(): string
    {
        $specializations = [
            'Mathématiques',
            'Français',
            'Sciences',
            'Histoire-Géographie',
            'Anglais',
            'Éducation Physique',
            'Arts Plastiques',
            'Musique',
            'Informatique',
            'Philosophie'
        ];

        return $specializations[array_rand($specializations)];
    }

    private function getRandomProfession(): string
    {
        $professions = [
            'Commerçant',
            'Enseignant',
            'Médecin',
            'Ingénieur',
            'Agriculteur',
            'Artisan',
            'Fonctionnaire',
            'Chauffeur',
            'Mécanicien',
            'Infirmier'
        ];

        return $professions[array_rand($professions)];
    }

    private function getRandomAddress(): string
    {
        $addresses = [
            'Quartier Banconi, Bamako',
            'Quartier Magnambougou, Bamako',
            'Quartier Lafiabougou, Bamako',
            'Quartier Kalaban Coura, Bamako',
            'Quartier Sabalibougou, Bamako',
            'Quartier Djelibougou, Bamako',
            'Quartier Badalabougou, Bamako',
            'Quartier Quinzambougou, Bamako',
            'Quartier Sogoniko, Bamako',
            'Quartier Yirimadio, Bamako'
        ];

        return $addresses[array_rand($addresses)];
    }
}
