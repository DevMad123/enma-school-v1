<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\DB;

/**
 * Seeder de transition pour le contexte école
 * 
 * Ce seeder associe tous les utilisateurs existants à l'école active
 * pour assurer la transition vers le nouveau système de contexte école.
 */
class SchoolContextTransitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'école active (première école active)
        $activeSchool = School::where('is_active', true)->first();
        
        if (!$activeSchool) {
            $this->command->error('Aucune école active trouvée. Veuillez activer une école avant d\'exécuter ce seeder.');
            return;
        }

        $this->command->info("École active trouvée : {$activeSchool->name}");

        // Compter les utilisateurs à migrer
        $usersToMigrate = User::whereNull('school_id')->count();
        
        if ($usersToMigrate === 0) {
            $this->command->info('Tous les utilisateurs ont déjà un contexte école assigné.');
            return;
        }

        $this->command->info("Migration de {$usersToMigrate} utilisateur(s) vers l'école active...");

        // Mise à jour en lot pour de meilleures performances
        $updatedUsers = User::whereNull('school_id')
            ->update(['school_id' => $activeSchool->id]);

        $this->command->info("✅ {$updatedUsers} utilisateur(s) associé(s) à l'école '{$activeSchool->name}'");

        // Vérification post-migration
        $remainingUsers = User::whereNull('school_id')->count();
        
        if ($remainingUsers === 0) {
            $this->command->info('✅ Migration terminée avec succès. Tous les utilisateurs ont maintenant un contexte école.');
        } else {
            $this->command->warn("⚠️ {$remainingUsers} utilisateur(s) n'ont pas pu être migrés. Vérifiez manuellement.");
        }
    }
}
