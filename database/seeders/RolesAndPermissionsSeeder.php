<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Réinitialisation du cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Création des permissions de base
        $permissions = [
            // Gestion des utilisateurs
            'manage_users',
            'create_users',
            'edit_users', 
            'delete_users',
            'view_users',
            
            // Gestion des rôles et permissions
            'manage_roles',
            'create_roles',
            'edit_roles',
            'delete_roles',
            'view_roles',
            'assign_permissions',
            
            // Gestion des paramètres système
            'manage_settings',
            'manage_security_settings',
            'view_activity_logs',
            'manage_academic_settings',
            
            // Gestion des étudiants
            'manage_students', 
            'create_students',
            'edit_students',
            'delete_students',
            'view_students',
            
            // Gestion des enseignants
            'manage_teachers',
            'create_teachers',
            'edit_teachers',
            'delete_teachers',
            'view_teachers',
            
            // Gestion des classes
            'manage_classes',
            'create_classes',
            'edit_classes',
            'delete_classes',
            'view_classes',
            
            // Gestion des notes
            'manage_grades',
            'create_grades',
            'edit_grades',
            'delete_grades',
            'view_grades',
            'manage_evaluations',
            
            // Gestion des paiements
            'manage_payments',
            'create_payments',
            'edit_payments',
            'delete_payments',
            'view_payments',
            'manage_school_fees',
            
            // Rapports et analytics
            'view_reports',
            'create_reports',
            'export_reports',
            'view_dashboard',
            'view_analytics',
            
            // Données personnelles
            'view_own_data',
            'edit_own_profile',
            
            // Gestion des inscriptions
            'manage_enrollments',
            'create_enrollments',
            'edit_enrollments',
            'delete_enrollments',
            'view_enrollments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Création des rôles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $enseignant = Role::firstOrCreate(['name' => 'teacher']);
        $comptable = Role::firstOrCreate(['name' => 'accountant']);
        $surveillant = Role::firstOrCreate(['name' => 'supervisor']);
        $eleve = Role::firstOrCreate(['name' => 'student']);
        $parent = Role::firstOrCreate(['name' => 'parent']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // Attribution des permissions aux rôles

        // Super Admin : toutes les permissions
        $superAdmin->syncPermissions(Permission::all());

        // Admin : toutes les permissions de gestion sauf super admin spécifiques
        $admin->syncPermissions([
            'manage_users', 'create_users', 'edit_users', 'delete_users', 'view_users',
            'manage_roles', 'create_roles', 'edit_roles', 'view_roles', 'assign_permissions',
            'manage_settings', 'manage_security_settings', 'view_activity_logs', 'manage_academic_settings',
            'manage_students', 'create_students', 'edit_students', 'delete_students', 'view_students',
            'manage_teachers', 'create_teachers', 'edit_teachers', 'delete_teachers', 'view_teachers',
            'manage_classes', 'create_classes', 'edit_classes', 'delete_classes', 'view_classes',
            'manage_grades', 'create_grades', 'edit_grades', 'view_grades', 'manage_evaluations',
            'manage_payments', 'create_payments', 'edit_payments', 'view_payments', 'manage_school_fees',
            'view_reports', 'create_reports', 'export_reports', 'view_dashboard', 'view_analytics',
            'manage_enrollments', 'create_enrollments', 'edit_enrollments', 'delete_enrollments', 'view_enrollments',
            'view_own_data', 'edit_own_profile',
        ]);

        // Staff (Personnel administratif) : gestion limitée
        $staff->syncPermissions([
            'view_users', 'manage_students', 'create_students', 'edit_students', 'view_students',
            'manage_teachers', 'view_teachers', 'view_classes',
            'manage_enrollments', 'create_enrollments', 'edit_enrollments', 'view_enrollments',
            'view_reports', 'view_dashboard',
            'view_own_data', 'edit_own_profile',
        ]);

        // Enseignant : gestion des notes et classes
        $enseignant->syncPermissions([
            'view_students', 'view_classes',
            'manage_grades', 'create_grades', 'edit_grades', 'view_grades', 'manage_evaluations',
            'view_reports', 'view_dashboard',
            'view_own_data', 'edit_own_profile',
        ]);

        // Comptable : gestion des paiements et rapports financiers
        $comptable->syncPermissions([
            'view_students', 'view_classes',
            'manage_payments', 'create_payments', 'edit_payments', 'view_payments', 'manage_school_fees',
            'view_reports', 'create_reports', 'export_reports', 'view_dashboard',
            'view_own_data', 'edit_own_profile',
        ]);

        // Surveillant : consultation des données
        $surveillant->syncPermissions([
            'view_students', 'view_teachers', 'view_classes', 'view_grades',
            'view_reports', 'view_dashboard',
            'view_own_data', 'edit_own_profile',
        ]);

        // Élève : consultation de ses propres données
        $eleve->syncPermissions([
            'view_own_data', 'edit_own_profile',
        ]);

        // Parent : consultation de ses propres données
        $parent->syncPermissions([
            'view_own_data', 'edit_own_profile',
        ]);
    }
}
