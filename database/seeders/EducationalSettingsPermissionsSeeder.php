<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EducationalSettingsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les permissions pour la configuration éducative
        $permissions = [
            'manage_educational_settings' => 'Gérer les paramètres éducatifs',
            'view_educational_settings' => 'Consulter les paramètres éducatifs',
            'export_educational_settings' => 'Exporter les paramètres éducatifs',
            'import_educational_settings' => 'Importer les paramètres éducatifs',
            'reset_educational_settings' => 'Remettre les paramètres par défaut',
        ];

        foreach ($permissions as $permission => $description) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // Attribuer les permissions aux rôles appropriés
        $this->assignPermissionsToRoles();
    }

    /**
     * Attribuer les permissions aux rôles
     */
    private function assignPermissionsToRoles(): void
    {
        // Super Admin : toutes les permissions
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $superAdmin->givePermissionTo([
                'manage_educational_settings',
                'view_educational_settings',
                'export_educational_settings',
                'import_educational_settings',
                'reset_educational_settings',
            ]);
        }

        // Admin : gestion complète
        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $admin->givePermissionTo([
                'manage_educational_settings',
                'view_educational_settings',
                'export_educational_settings',
                'import_educational_settings',
                'reset_educational_settings',
            ]);
        }

        // Directeur : gestion limitée
        $directeur = Role::where('name', 'directeur')->first();
        if ($directeur) {
            $directeur->givePermissionTo([
                'manage_educational_settings',
                'view_educational_settings',
                'export_educational_settings',
            ]);
        }

        // Staff : consultation seulement
        $staff = Role::where('name', 'staff')->first();
        if ($staff) {
            $staff->givePermissionTo([
                'view_educational_settings',
            ]);
        }

        // Enseignant : consultation seulement
        $teacher = Role::where('name', 'teacher')->first();
        if ($teacher) {
            $teacher->givePermissionTo([
                'view_educational_settings',
            ]);
        }

        $this->command->info('✅ Permissions de configuration éducative assignées');
    }
}