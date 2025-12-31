<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un utilisateur admin de test
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@enmaschool.com'],
            [
                'name' => 'Administrateur ENMA',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'email_verified_at' => now(),
                'is_active' => true,
                'phone' => '+221 77 123 45 67',
                'address' => 'Dakar, Sénégal',
                'date_of_birth' => '1980-01-01',
                'last_login_at' => now(),
            ]
        );

        // S'assurer que l'utilisateur a le rôle Administrateur
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Vérifier que le rôle a toutes les permissions
        $adminRole = \Spatie\Permission\Models\Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermissions = \Spatie\Permission\Models\Permission::all();
            $adminRole->syncPermissions($allPermissions);
        }

        $this->command->info('Utilisateur admin créé/mis à jour: admin@enmaschool.com / password123');
        $this->command->info('Permissions vérifiées et synchronisées');
    }
}
