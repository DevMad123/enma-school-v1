<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UniversityPermissionsSeeder extends Seeder
{
    /**
     * Ajouter les permissions spécifiques au module universitaire
     */
    public function run(): void
    {
        // Réinitialisation du cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions spécifiques au module universitaire
        $universityPermissions = [
            // Gestion universitaire générale
            'manage_university_system',
            'view_university_dashboard',
            'configure_university_settings',
            
            // Gestion des UFR
            'manage_ufrs',
            'create_ufrs',
            'edit_ufrs',
            'delete_ufrs',
            'view_ufrs',
            
            // Gestion des départements
            'manage_departments',
            'create_departments',
            'edit_departments',
            'delete_departments',
            'view_departments',
            
            // Gestion des programmes
            'manage_programs',
            'create_programs',
            'edit_programs',
            'delete_programs',
            'view_programs',
            
            // Gestion des semestres
            'manage_semesters',
            'create_semesters',
            'edit_semesters',
            'delete_semesters',
            'view_semesters',
            
            // Gestion des unités d'enseignement
            'manage_course_units',
            'create_course_units',
            'edit_course_units',
            'delete_course_units',
            'view_course_units',
        ];

        // Créer les permissions si elles n'existent pas
        foreach ($universityPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assigner toutes les permissions universitaires aux rôles appropriés
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
            $this->command->info('Toutes les permissions assignées au super_admin');
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            // Admin a aussi accès au module universitaire
            $admin->givePermissionTo($universityPermissions);
            $this->command->info('Permissions universitaires assignées à admin');
        }

        $directeur = Role::where('name', 'directeur')->first();
        if ($directeur) {
            // Directeur a accès limité au module universitaire
            $directeurPermissions = [
                'view_university_dashboard',
                'view_ufrs', 'view_departments', 'view_programs', 
                'view_semesters', 'view_course_units'
            ];
            $directeur->givePermissionTo($directeurPermissions);
            $this->command->info('Permissions universitaires (lecture) assignées à directeur');
        }

        $this->command->info('Permissions universitaires créées avec succès !');
    }
}