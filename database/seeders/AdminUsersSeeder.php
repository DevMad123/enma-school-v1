<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Staff;
use App\Models\School;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des utilisateurs administrateurs...');
        
        // Récupérer les écoles
        $schools = School::all();
        
        if ($schools->isEmpty()) {
            $this->command->error('Aucune école trouvée ! Veuillez exécuter SchoolSeeder en premier.');
            return;
        }
        
        // Super Administrateur (accès à toutes les écoles)
        $superAdminEmail = 'superadmin@enmaschool.com';
        $superAdmin = User::where('email', $superAdminEmail)->first();
        
        if (!$superAdmin) {
            $superAdmin = User::create([
                'name' => 'Super Administrateur',
                'email' => $superAdminEmail,
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]);
            
            $superAdmin->assignRole('super_admin');
            
            Staff::create([
                'user_id' => $superAdmin->id,
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'position' => 'Directeur Général',
                'phone' => '+225 01 01 01 01',
                'status' => 'active',
            ]);
            
            $this->command->line("Super Admin créé : {$superAdmin->email}");
        } else {
            $this->command->warn("Super Admin existe déjà : {$superAdminEmail}");
        }
        
        // Créer un administrateur pour chaque école
        foreach ($schools as $index => $school) {
            $adminEmails = [
                'admin.ees@enmaschool.com',
                'admin.cma@enmaschool.com', 
                'admin.gsp@enmaschool.com'
            ];
            
            $adminNames = [
                'Admin École Enma',
                'Admin Collège Moderne',
                'Admin Groupe Palmiers'
            ];
            
            $email = $adminEmails[$index] ?? "admin.school{$index}@enmaschool.com";
            $name = $adminNames[$index] ?? "Admin École {$index}";
            
            $admin = User::where('email', $email)->first();
            
            if (!$admin) {
                $admin = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt('password123'),
                    'email_verified_at' => now(),
                ]);
                
                $admin->assignRole('admin');
                
                Staff::create([
                    'user_id' => $admin->id,
                    'first_name' => 'Admin',
                    'last_name' => $school->short_name,
                    'position' => 'Administrateur',
                    'phone' => '+225 01 01 01 0' . ($index + 2),
                    'status' => 'active',
                ]);
                
                $this->command->line("Admin créé pour {$school->name} : {$admin->email}");
            } else {
                $this->command->warn("Admin existe déjà pour {$school->name} : {$email}");
            }
        }
        
        // Créer quelques directeurs d'école
        foreach ($schools as $index => $school) {
            $directorEmails = [
                'directeur.ees@enmaschool.com',
                'directeur.cma@enmaschool.com',
                'directeur.gsp@enmaschool.com'
            ];
            
            $directorNames = [
                'Directeur École Enma',
                'Directeur Collège Moderne', 
                'Directeur Groupe Palmiers'
            ];
            
            $email = $directorEmails[$index] ?? "directeur.school{$index}@enmaschool.com";
            $name = $directorNames[$index] ?? "Directeur École {$index}";
            
            $director = User::where('email', $email)->first();
            
            if (!$director) {
                $director = User::create([
                    'name' => $name,
                    'email' => $email,
                    'password' => bcrypt('password123'),
                    'email_verified_at' => now(),
                ]);
                
                $director->assignRole('directeur');
                
                Staff::create([
                    'user_id' => $director->id,
                    'first_name' => 'Directeur',
                    'last_name' => $school->short_name,
                    'position' => 'Directeur d\'École',
                    'phone' => '+225 01 01 01 1' . ($index + 1),
                    'status' => 'active',
                ]);
                
                $this->command->line("Directeur créé pour {$school->name} : {$director->email}");
            } else {
                $this->command->warn("Directeur existe déjà pour {$school->name} : {$email}");
            }
        }
        
        $this->command->info('Utilisateurs administrateurs créés avec succès !');
        $this->command->warn('Mots de passe par défaut : password123');
    }
}