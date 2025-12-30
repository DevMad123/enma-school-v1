<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les permissions de base
        $permissions = [
            // Gestion des utilisateurs
            'manage_users',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Gestion des rôles et permissions
            'manage_roles',
            'view_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            
            // Gestion de la sécurité
            'manage_security',
            'view_security_logs',
            'manage_system_settings',
            
            // Dashboard et navigation
            'access_admin_dashboard',
            'access_teacher_dashboard',
            'access_student_dashboard',
            
            // Gestion des étudiants
            'manage_students',
            'view_students',
            'create_students',
            'edit_students',
            'delete_students',
            
            // Gestion des enseignants
            'manage_teachers',
            'view_teachers',
            'create_teachers',
            'edit_teachers',
            'delete_teachers',
            
            // Gestion des matières
            'manage_subjects',
            'view_subjects',
            'create_subjects',
            'edit_subjects',
            'delete_subjects',
            
            // Gestion des classes
            'manage_classes',
            'view_classes',
            'create_classes',
            'edit_classes',
            'delete_classes',
            
            // Gestion des notes et évaluations
            'manage_grades',
            'view_grades',
            'create_grades',
            'edit_grades',
            'delete_grades',
            
            // Gestion des inscriptions
            'manage_enrollments',
            'view_enrollments',
            'create_enrollments',
            'edit_enrollments',
            'delete_enrollments',
            
            // Rapports et statistiques
            'view_reports',
            'generate_reports',
            'export_data',
            
            // Paramètres de l'école
            'manage_school_settings',
            'manage_academic_year',
            'manage_school_info',
            'manage_academic_settings',
            'manage_system_settings',
            'manage_notification_settings',
            'manage_backup_settings',
            'manage_maintenance_mode',
        ];

        // Créer les permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Créer les rôles de base
        $adminRole = Role::firstOrCreate(['name' => 'Administrateur']);
        $teacherRole = Role::firstOrCreate(['name' => 'Enseignant']);
        $studentRole = Role::firstOrCreate(['name' => 'Élève']);
        $parentRole = Role::firstOrCreate(['name' => 'Parent']);

        // Assigner toutes les permissions à l'administrateur
        $adminRole->givePermissionTo(Permission::all());

        // Assigner des permissions spécifiques aux enseignants
        $teacherRole->givePermissionTo([
            'access_teacher_dashboard',
            'view_students',
            'view_classes',
            'view_subjects',
            'manage_grades',
            'view_grades',
            'create_grades',
            'edit_grades',
            'view_reports',
            'view_enrollments',
        ]);

        // Assigner des permissions limitées aux étudiants
        $studentRole->givePermissionTo([
            'access_student_dashboard',
            'view_grades',
        ]);

        // Assigner des permissions aux parents
        $parentRole->givePermissionTo([
            'view_grades',
            'view_reports',
        ]);

        // Assigner le rôle administrateur à l'utilisateur admin
        $adminUser = User::where('email', 'admin@enmaschool.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('Administrateur');
            $this->command->info('Rôle Administrateur assigné à admin@enmaschool.com');
        } else {
            $this->command->info('Utilisateur admin@enmaschool.com non trouvé');
        }

        $this->command->info('Permissions et rôles créés avec succès!');
    }
}
