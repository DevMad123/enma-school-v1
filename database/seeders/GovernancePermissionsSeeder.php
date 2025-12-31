<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GovernancePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les permissions pour la gouvernance de l'établissement
        $permissions = [
            'manage_school_governance',
            'manage_school_info',
            'view_school_settings',
            'edit_school_settings',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Assigner les permissions au super_admin et admin
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $directeurRole = Role::where('name', 'directeur')->first();

        if ($superAdminRole) {
            $superAdminRole->syncPermissions(Permission::all());
            echo "Permissions assignées au super_admin\n";
        }

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
            echo "Permissions de gouvernance assignées à admin\n";
        }

        if ($directeurRole) {
            $directeurRole->givePermissionTo($permissions);
            echo "Permissions de gouvernance assignées à directeur\n";
        }

        echo "Permissions de gouvernance créées avec succès!\n";
    }
}
