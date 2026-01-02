<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Middleware\CustomRateLimit;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\StoreSchoolFeeRequest;

class SecurityCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'security:check 
                            {--detailed : Afficher les détails des vérifications}
                            {--fix : Tenter de corriger les problèmes détectés}';

    /**
     * The console command description.
     */
    protected $description = 'Vérifier la configuration de sécurité du système';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Vérification de la sécurité du système ENMA School');
        $this->newLine();

        $issues = [];
        $warnings = [];
        $passed = [];

        // 1. Vérification des Form Requests
        $this->info('📝 Vérification des Form Requests...');
        if (class_exists(StoreUserRequest::class)) {
            $passed[] = 'Form Request StoreUserRequest existant';
        } else {
            $issues[] = 'Form Request StoreUserRequest manquant';
        }

        if (class_exists(UpdateUserRequest::class)) {
            $passed[] = 'Form Request UpdateUserRequest existant';
        } else {
            $issues[] = 'Form Request UpdateUserRequest manquant';
        }

        if (class_exists(StoreSchoolFeeRequest::class)) {
            $passed[] = 'Form Request StoreSchoolFeeRequest existant';
        } else {
            $issues[] = 'Form Request StoreSchoolFeeRequest manquant';
        }

        // 2. Vérification du middleware de Rate Limiting
        $this->info('⏱️  Vérification du Rate Limiting...');
        if (class_exists(CustomRateLimit::class)) {
            $passed[] = 'Middleware CustomRateLimit existant';
        } else {
            $issues[] = 'Middleware CustomRateLimit manquant';
        }

        // 3. Vérification de la configuration de sécurité
        $this->info('⚙️  Vérification de la configuration...');
        if (config('security.rate_limits')) {
            $passed[] = 'Configuration de sécurité présente';
        } else {
            $warnings[] = 'Configuration de sécurité manquante';
        }

        // 4. Vérification CSRF
        $this->info('🛡️  Vérification de la protection CSRF...');
        $bootstrapPath = resource_path('js/bootstrap.js');
        if (file_exists($bootstrapPath)) {
            $content = file_get_contents($bootstrapPath);
            if (strpos($content, 'X-CSRF-TOKEN') !== false) {
                $passed[] = 'Protection CSRF configurée dans bootstrap.js';
            } else {
                $issues[] = 'Protection CSRF manquante dans bootstrap.js';
            }
        } else {
            $warnings[] = 'Fichier bootstrap.js non trouvé';
        }

        // 5. Vérification des routes protégées
        $this->info('🚪 Vérification des routes...');
        $routePath = base_path('routes/web.php');
        if (file_exists($routePath)) {
            $content = file_get_contents($routePath);
            if (strpos($content, 'rate.limit.custom') !== false) {
                $passed[] = 'Routes avec rate limiting configurées';
            } else {
                $warnings[] = 'Certaines routes pourraient manquer de rate limiting';
            }
        }

        // 6. Vérification des middlewares enregistrés
        $this->info('🔗 Vérification de l\'enregistrement des middlewares...');
        $appPath = base_path('bootstrap/app.php');
        if (file_exists($appPath)) {
            $content = file_get_contents($appPath);
            if (strpos($content, 'CustomRateLimit') !== false) {
                $passed[] = 'Middleware CustomRateLimit enregistré';
            } else {
                $issues[] = 'Middleware CustomRateLimit non enregistré';
            }
        }

        // Affichage des résultats
        $this->newLine();
        $this->info('📊 Résultats de la vérification:');
        $this->newLine();

        if (!empty($passed)) {
            $this->info('✅ Tests réussis:');
            foreach ($passed as $test) {
                $this->line("   • $test");
            }
            $this->newLine();
        }

        if (!empty($warnings)) {
            $this->warn('⚠️  Avertissements:');
            foreach ($warnings as $warning) {
                $this->line("   • $warning");
            }
            $this->newLine();
        }

        if (!empty($issues)) {
            $this->error('❌ Problèmes détectés:');
            foreach ($issues as $issue) {
                $this->line("   • $issue");
            }
            $this->newLine();
        }

        // Score de sécurité
        $total = count($passed) + count($warnings) + count($issues);
        $score = round((count($passed) / $total) * 100);
        
        if ($score >= 90) {
            $this->info("🎯 Score de sécurité: {$score}/100 - Excellent!");
        } elseif ($score >= 70) {
            $this->warn("🎯 Score de sécurité: {$score}/100 - Bon");
        } else {
            $this->error("🎯 Score de sécurité: {$score}/100 - À améliorer");
        }

        // Recommandations
        if (!empty($issues) || !empty($warnings)) {
            $this->newLine();
            $this->info('💡 Recommandations:');
            
            if (in_array('Form Request StoreUserRequest manquant', $issues)) {
                $this->line('   • Créer StoreUserRequest avec validation robuste');
            }
            
            if (in_array('Middleware CustomRateLimit manquant', $issues)) {
                $this->line('   • Implémenter le middleware de rate limiting');
            }
            
            if (in_array('Protection CSRF manquante dans bootstrap.js', $issues)) {
                $this->line('   • Ajouter la protection CSRF automatique pour AJAX');
            }
            
            $this->line('   • Effectuer des tests de pénétration réguliers');
            $this->line('   • Mettre à jour les dépendances de sécurité');
            $this->line('   • Configurer un monitoring de sécurité');
        }

        return empty($issues) ? 0 : 1;
    }
}