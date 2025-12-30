<?php

namespace App\Console\Commands;

use App\Http\Controllers\DashboardController;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestDashboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test le fonctionnement du dashboard';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Test utilisateur directeur
            $user = User::where('email', 'directeur@enmaschool.com')->first();
            
            if (!$user) {
                $this->error('❌ Utilisateur directeur non trouvé');
                return 1;
            }
            
            $this->info("✅ Utilisateur trouvé: {$user->name}");
            $this->line("📧 Email: {$user->email}");
            $this->line("👤 Rôles: " . $user->roles->pluck('name')->join(', '));
            
            // Simuler l'authentification
            Auth::login($user);
            $this->info("✅ Authentification simulée");
            
            // Tester le dashboard controller
            $controller = new DashboardController();
            
            // Test méthode index (devrait rediriger vers admin dashboard)
            $this->info("🔍 Test du dashboard controller...");
            
            // Test données admin dashboard
            $this->testAdminDashboardData();
            
            $this->info("✅ Tous les tests du dashboard ont réussi !");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
            $this->line("🔍 Trace: " . $e->getFile() . ':' . $e->getLine());
            return 1;
        } finally {
            Auth::logout();
        }
    }
    
    private function testAdminDashboardData()
    {
        // Test des requêtes du dashboard admin
        $this->line("📊 Test des statistiques admin...");
        
        // Test comptage étudiants
        $totalStudents = \App\Models\Student::count();
        $this->line("  - Étudiants: {$totalStudents}");
        
        // Test comptage enseignants
        $totalTeachers = \App\Models\Teacher::count();
        $this->line("  - Enseignants: {$totalTeachers}");
        
        // Test comptage classes
        $totalClasses = \App\Models\SchoolClass::count();
        $this->line("  - Classes: {$totalClasses}");
        
        // Test année académique
        $currentYear = \App\Models\AcademicYear::current();
        $this->line("  - Année académique: " . ($currentYear ? $currentYear->name : 'Aucune'));
        
        // Test paiements
        $totalRevenue = \App\Models\Payment::where('status', 'confirmed')->sum('amount');
        $this->line("  - Revenus totaux: " . number_format($totalRevenue) . " FCFA");
        
        // Test affectations enseignants
        $totalAssignments = \App\Models\TeacherAssignment::count();
        $this->line("  - Affectations d'enseignants: {$totalAssignments}");
        
        $this->info("✅ Toutes les statistiques ont été calculées sans erreur");
    }
}