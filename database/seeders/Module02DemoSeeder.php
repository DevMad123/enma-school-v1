<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class Module02DemoSeeder extends Seeder
{
    /**
     * Seeder spécial pour créer toutes les données de démonstration
     * du MODULE A2 - Années académiques & Périodes
     */
    public function run(): void
    {
        $this->command->info('=== MODULE A2 - DÉMONSTRATION DES ANNÉES ACADÉMIQUES & PÉRIODES ===');
        $this->command->newLine();
        
        // Exécuter les seeders dans l'ordre optimal pour la démonstration
        $this->call([
            SchoolSeeder::class,              // 3 écoles avec systèmes différents
            AcademicYearSeeder::class,        // 4 années par école
            GradePeriodSeeder::class,         // Périodes automatiques
            AdminUsersSeeder::class,          // Utilisateurs de test
            AcademicTestDataSeeder::class,    // Données supplémentaires
        ]);
        
        $this->command->newLine();
        $this->command->info('=== RÉSUMÉ DES DONNÉES CRÉÉES ===');
        
        // Afficher un résumé des données créées
        $this->displaySummary();
        
        $this->command->newLine();
        $this->command->info('=== COMPTES DE TEST CRÉÉS ===');
        $this->displayTestAccounts();
        
        $this->command->newLine();
        $this->command->warn('🔐 Mot de passe par défaut pour tous les comptes : password123');
        $this->command->info('✅ Démonstration du MODULE A2 prête !');
        $this->command->info('🌐 Accédez à l\'interface admin via : /admin/academic-years');
    }
    
    /**
     * Afficher un résumé des données créées
     */
    private function displaySummary(): void
    {
        $schools = \App\Models\School::with('academicYears.academicPeriods')->get();
        
        foreach ($schools as $school) {
            $this->command->line("🏫 {$school->name} ({$school->short_name})");
            $this->command->line("   └─ Système : {$school->academic_system}");
            $this->command->line("   └─ Années académiques : {$school->academicYears->count()}");
            
            $totalPeriods = $school->academicYears->sum(function($year) {
                return $year->academicPeriods->count();
            });
            
            $this->command->line("   └─ Total périodes : {$totalPeriods}");
            
            // Année active
            $activeYear = $school->academicYears->where('is_active', true)->first();
            if ($activeYear) {
                $activePeriods = $activeYear->academicPeriods->where('is_active', true)->count();
                $this->command->line("   └─ Année active : {$activeYear->name} ({$activePeriods} période(s) active(s))");
            }
            
            $this->command->newLine();
        }
    }
    
    /**
     * Afficher les comptes de test
     */
    private function displayTestAccounts(): void
    {
        $accounts = [
            ['email' => 'superadmin@enmaschool.com', 'role' => 'Super Admin', 'access' => 'Toutes les écoles'],
            ['email' => 'admin.ees@enmaschool.com', 'role' => 'Admin', 'access' => 'École Enma School'],
            ['email' => 'admin.cma@enmaschool.com', 'role' => 'Admin', 'access' => 'Collège Moderne'],
            ['email' => 'admin.gsp@enmaschool.com', 'role' => 'Admin', 'access' => 'Groupe Palmiers'],
            ['email' => 'directeur.ees@enmaschool.com', 'role' => 'Directeur', 'access' => 'École Enma School'],
            ['email' => 'directeur.cma@enmaschool.com', 'role' => 'Directeur', 'access' => 'Collège Moderne'],
            ['email' => 'directeur.gsp@enmaschool.com', 'role' => 'Directeur', 'access' => 'Groupe Palmiers'],
        ];
        
        foreach ($accounts as $account) {
            $this->command->line("👤 {$account['email']} - {$account['role']} ({$account['access']})");
        }
    }
}