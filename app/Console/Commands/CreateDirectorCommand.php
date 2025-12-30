<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Staff;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateDirectorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:director {email=directeur@enmaschool.com} {password=password123}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un utilisateur directeur pour tester l\'application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Vérifier si l'utilisateur existe déjà
        if (User::where('email', $email)->exists()) {
            $this->error("L'utilisateur avec l'email {$email} existe déjà !");
            return 1;
        }

        // Créer l'utilisateur
        $user = User::create([
            'name' => 'Directeur Test',
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        // Créer le profil staff
        $staff = Staff::create([
            'user_id' => $user->id,
            'staff_id' => 'DIR' . str_pad(1, 3, '0', STR_PAD_LEFT),
            'first_name' => 'Directeur',
            'last_name' => 'Test',
            'position' => 'Directeur',
            'department' => 'Direction',
            'hire_date' => now(),
            'phone' => '0123456789',
        ]);

        // Assigner le rôle admin (ou créer le rôle directeur s'il n'existe pas)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $directorRole = Role::firstOrCreate(['name' => 'directeur']);
        
        $user->assignRole([$adminRole, $directorRole]);

        $this->info("✅ Utilisateur directeur créé avec succès !");
        $this->line("📧 Email: {$email}");
        $this->line("🔑 Password: {$password}");
        $this->line("👤 Rôles: " . $user->roles->pluck('name')->join(', '));

        return 0;
    }
}