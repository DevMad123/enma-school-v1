<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\Staff;
use App\Models\User;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * MODULE A4 - Seeder pour le personnel (enseignants et staff)
 */
class ModuleA4PersonnelSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer l'école par défaut
        $school = School::first();
        
        if (!$school) {
            $this->command->warn('Aucune école trouvée. Veuillez d\'abord exécuter les seeders des modules précédents.');
            return;
        }

        // S'assurer que les rôles existent
        $teacherRole = Role::firstOrCreate(['name' => 'enseignant']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $directorRole = Role::firstOrCreate(['name' => 'directeur']);
        $pedagogicalRole = Role::firstOrCreate(['name' => 'responsable_pedagogique']);

        $this->command->info('🧑‍🏫 Création des enseignants...');

        // Créer des enseignants
        $teachers = [
            [
                'first_name' => 'Marie',
                'last_name' => 'Dupont',
                'email' => 'marie.dupont@enmaschool.com',
                'specialization' => 'Mathématiques',
                'employee_id' => 'ENS001',
                'qualifications' => 'Master en Mathématiques, CAPES',
                'teaching_subjects' => ['Mathématiques', 'Physique'],
            ],
            [
                'first_name' => 'Jean',
                'last_name' => 'Martin',
                'email' => 'jean.martin@enmaschool.com',
                'specialization' => 'Français',
                'employee_id' => 'ENS002',
                'qualifications' => 'Master en Lettres Modernes, Agrégation',
                'teaching_subjects' => ['Français', 'Littérature'],
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Bernard',
                'email' => 'sophie.bernard@enmaschool.com',
                'specialization' => 'Sciences',
                'employee_id' => 'ENS003',
                'qualifications' => 'Master en Biologie, CAPES SVT',
                'teaching_subjects' => ['Biologie', 'Chimie'],
            ],
            [
                'first_name' => 'Pierre',
                'last_name' => 'Moreau',
                'email' => 'pierre.moreau@enmaschool.com',
                'specialization' => 'Histoire-Géographie',
                'employee_id' => 'ENS004',
                'qualifications' => 'Master en Histoire, CAPES',
                'teaching_subjects' => ['Histoire', 'Géographie'],
            ],
            [
                'first_name' => 'Amélie',
                'last_name' => 'Rousseau',
                'email' => 'amelie.rousseau@enmaschool.com',
                'specialization' => 'Langues',
                'employee_id' => 'ENS005',
                'qualifications' => 'Master en Langues Étrangères, CAPES Anglais',
                'teaching_subjects' => ['Anglais', 'Espagnol'],
            ],
        ];

        foreach ($teachers as $teacherData) {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $teacherData['first_name'] . ' ' . $teacherData['last_name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('password123'),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'date_of_birth' => fake()->dateTimeBetween('-50 years', '-25 years')->format('Y-m-d'),
                'is_active' => true,
            ]);

            // Assigner le rôle enseignant
            $user->assignRole($teacherRole);

            // Créer le profil enseignant
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'first_name' => $teacherData['first_name'],
                'last_name' => $teacherData['last_name'],
                'phone' => $user->phone,
                'specialization' => $teacherData['specialization'],
                'employee_id' => $teacherData['employee_id'],
                'hire_date' => fake()->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
                'qualifications' => $teacherData['qualifications'],
                'teaching_subjects' => $teacherData['teaching_subjects'],
                'status' => 'active',
            ]);

            $this->command->info("✓ Enseignant créé: {$teacher->full_name} ({$teacher->specialization})");
        }

        $this->command->info('👥 Création du personnel administratif...');

        // Créer du personnel administratif
        $staffMembers = [
            [
                'first_name' => 'Claude',
                'last_name' => 'Directeur',
                'email' => 'claude.directeur@enmaschool.com',
                'position' => 'Directeur',
                'employee_id' => 'DIR001',
                'department' => 'Direction',
                'responsibilities' => 'Gestion générale de l\'établissement, relations avec les autorités',
                'role' => 'directeur',
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Pedagogie',
                'email' => 'lisa.pedagogie@enmaschool.com',
                'position' => 'Responsable Pédagogique',
                'employee_id' => 'PED001',
                'department' => 'Pédagogie',
                'responsibilities' => 'Coordination pédagogique, suivi des enseignants, organisation des cours',
                'role' => 'responsable_pedagogique',
            ],
            [
                'first_name' => 'Michel',
                'last_name' => 'Secretaire',
                'email' => 'michel.secretaire@enmaschool.com',
                'position' => 'Secrétaire',
                'employee_id' => 'SEC001',
                'department' => 'Administration',
                'responsibilities' => 'Accueil, gestion administrative, correspondance',
                'role' => null,
            ],
            [
                'first_name' => 'Fabienne',
                'last_name' => 'Comptable',
                'email' => 'fabienne.comptable@enmaschool.com',
                'position' => 'Comptable',
                'employee_id' => 'CPT001',
                'department' => 'Finance',
                'responsibilities' => 'Gestion financière, facturation, suivi des paiements',
                'role' => null,
            ],
            [
                'first_name' => 'Thomas',
                'last_name' => 'Surveillance',
                'email' => 'thomas.surveillance@enmaschool.com',
                'position' => 'Surveillant',
                'employee_id' => 'SUR001',
                'department' => 'Vie scolaire',
                'responsibilities' => 'Surveillance des élèves, discipline, sécurité',
                'role' => null,
            ],
        ];

        foreach ($staffMembers as $staffData) {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $staffData['first_name'] . ' ' . $staffData['last_name'],
                'email' => $staffData['email'],
                'password' => Hash::make('password123'),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'date_of_birth' => fake()->dateTimeBetween('-55 years', '-25 years')->format('Y-m-d'),
                'is_active' => true,
            ]);

            // Assigner le rôle spécifique si défini
            if ($staffData['role']) {
                $role = Role::firstOrCreate(['name' => $staffData['role']]);
                $user->assignRole($role);
            }

            // Créer le profil staff
            $staff = Staff::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'first_name' => $staffData['first_name'],
                'last_name' => $staffData['last_name'],
                'position' => $staffData['position'],
                'phone' => $user->phone,
                'employee_id' => $staffData['employee_id'],
                'hire_date' => fake()->dateTimeBetween('-8 years', '-6 months')->format('Y-m-d'),
                'department' => $staffData['department'],
                'responsibilities' => $staffData['responsibilities'],
                'status' => 'active',
            ]);

            $this->command->info("✓ Staff créé: {$staff->full_name} ({$staff->position})");
        }

        $this->command->info('🎓 MODULE A4 - Personnel créé avec succès !');
        $this->command->info('');
        $this->command->info('📊 Résumé:');
        $this->command->info('   - ' . Teacher::count() . ' enseignants');
        $this->command->info('   - ' . Staff::count() . ' membres du personnel administratif');
        $this->command->info('');
        $this->command->info('🔑 Identifiants de test (mot de passe: password123):');
        $this->command->info('   - marie.dupont@enmaschool.com (Enseignant Mathématiques)');
        $this->command->info('   - claude.directeur@enmaschool.com (Directeur)');
        $this->command->info('   - lisa.pedagogie@enmaschool.com (Responsable Pédagogique)');
    }
}