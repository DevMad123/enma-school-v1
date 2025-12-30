<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\Grade;
use App\Models\GradePeriod;
use App\Models\AcademicYear;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Console\Command;

class TestReportCardsModule extends Command
{
    protected $signature = 'test:report-cards';
    protected $description = 'Test complet du module Bulletins & Résultats';

    public function handle()
    {
        $this->info('=== VÉRIFICATION MODULE 8 — BULLETINS & RÉSULTATS ===');
        $this->newLine();

        // 1. Vérification des modèles et relations
        $this->checkModelsAndRelations();

        // 2. Vérification des données de test
        $this->checkTestData();

        // 3. Test des calculs de moyennes
        $this->testAverageCalculations();

        // 4. Vérification des fonctionnalités manquantes
        $this->checkMissingFeatures();

        // 5. Recommandations
        $this->showRecommendations();
    }

    private function checkModelsAndRelations()
    {
        $this->info('1. VÉRIFICATION DES MODÈLES ET RELATIONS');
        $this->line('──────────────────────────────────────────');

        // Vérifier modèle ReportCard
        $reportCardExists = class_exists('App\Models\ReportCard');
        if ($reportCardExists) {
            $this->info('✅ Modèle ReportCard trouvé');
        } else {
            $this->error('❌ Modèle ReportCard MANQUANT');
        }

        // Vérifier les relations Student
        $this->info('🔍 Relations Student:');
        $student = new Student();
        $relations = ['grades', 'enrollments'];
        foreach ($relations as $relation) {
            if (method_exists($student, $relation)) {
                $this->info("   ✅ Relation {$relation}() existe");
            } else {
                $this->error("   ❌ Relation {$relation}() MANQUANTE");
            }
        }

        // Vérifier méthodes de calcul
        $methods = ['getAverageForPeriod', 'getAverageForSubject', 'getGradeStatistics'];
        $this->info('🔍 Méthodes de calcul:');
        foreach ($methods as $method) {
            if (method_exists($student, $method)) {
                $this->info("   ✅ Méthode {$method}() existe");
            } else {
                $this->error("   ❌ Méthode {$method}() MANQUANTE");
            }
        }

        $this->newLine();
    }

    private function checkTestData()
    {
        $this->info('2. VÉRIFICATION DES DONNÉES DE TEST');
        $this->line('────────────────────────────────────────');

        $studentCount = Student::count();
        $gradeCount = Grade::count();
        $periodCount = GradePeriod::count();
        $subjectCount = Subject::count();

        $this->info("📊 Étudiants: {$studentCount}");
        $this->info("📊 Notes: {$gradeCount}");
        $this->info("📊 Périodes: {$periodCount}");
        $this->info("📊 Matières: {$subjectCount}");

        if ($studentCount > 0 && $gradeCount > 0) {
            $this->info('✅ Données suffisantes pour tester les bulletins');
        } else {
            $this->warn('⚠️  Données insuffisantes - Ajoutez des étudiants et notes');
        }

        $this->newLine();
    }

    private function testAverageCalculations()
    {
        $this->info('3. TEST DES CALCULS DE MOYENNES');
        $this->line('─────────────────────────────────────');

        // Prendre le premier étudiant avec des notes
        $student = Student::whereHas('grades')->first();

        if ($student) {
            $this->info("🎓 Test avec l'étudiant: {$student->full_name}");

            // Test moyenne générale
            $generalAverage = $student->getAverageForPeriod();
            $this->info("   Moyenne générale: {$generalAverage}/20");

            // Test par période
            $periods = GradePeriod::where('is_active', true)->get();
            foreach ($periods as $period) {
                $periodAverage = $student->getAverageForPeriod($period->id);
                $this->info("   Moyenne {$period->name}: {$periodAverage}/20");
            }

            // Test par matière
            $subjects = Subject::whereHas('evaluations.grades', function($query) use ($student) {
                $query->where('student_id', $student->id);
            })->get();

            foreach ($subjects->take(3) as $subject) {
                $subjectAverage = $student->getAverageForSubject($subject->id);
                $this->info("   {$subject->name}: {$subjectAverage}/20");
            }

            // Statistiques
            $stats = $student->getGradeStatistics();
            $this->info("   Statistiques: {$stats['count']} notes, moyenne {$stats['average']}, taux de réussite {$stats['passing_rate']}%");

        } else {
            $this->warn('⚠️  Aucun étudiant avec notes trouvé');
        }

        $this->newLine();
    }

    private function checkMissingFeatures()
    {
        $this->info('4. FONCTIONNALITÉS MANQUANTES');
        $this->line('───────────────────────────────────');

        // Contrôleur ReportCard
        $controllerExists = file_exists(app_path('Http/Controllers/ReportCardController.php'));
        if (!$controllerExists) {
            $this->error('❌ ReportCardController MANQUANT');
        }

        // Routes bulletins
        $webRoutes = file_get_contents(base_path('routes/web.php'));
        $apiRoutes = file_get_contents(base_path('routes/api.php'));
        
        if (!str_contains($webRoutes, 'report') && !str_contains($apiRoutes, 'report')) {
            $this->error('❌ Routes bulletins MANQUANTES');
        }

        // Package PDF
        $composerJson = json_decode(file_get_contents(base_path('composer.json')), true);
        $hasPdfPackage = false;
        foreach (['dompdf/dompdf', 'barryvdh/laravel-dompdf', 'tcpdf/tcpdf'] as $package) {
            if (isset($composerJson['require'][$package])) {
                $hasPdfPackage = true;
                break;
            }
        }

        if (!$hasPdfPackage) {
            $this->error('❌ Package PDF MANQUANT');
        }

        // Vues bulletins
        $bulletinViews = glob(resource_path('views/**/report*'));
        if (empty($bulletinViews)) {
            $this->error('❌ Vues bulletins MANQUANTES');
        }

        // Tests
        $bulletinTests = glob(base_path('tests/**/*Report*'));
        if (empty($bulletinTests)) {
            $this->error('❌ Tests bulletins MANQUANTS');
        }

        $this->newLine();
    }

    private function showRecommendations()
    {
        $this->info('5. RECOMMANDATIONS POUR L\'IMPLÉMENTATION');
        $this->line('────────────────────────────────────────────────');

        $recommendations = [
            'Créer le modèle ReportCard avec migration',
            'Implémenter ReportCardController avec méthodes CRUD',
            'Ajouter routes web/api pour les bulletins',
            'Installer un package PDF (barryvdh/laravel-dompdf)',
            'Créer les vues Blade pour affichage des bulletins',
            'Implémenter template PDF pour export',
            'Ajouter tests unitaires et fonctionnels',
            'Créer seeder pour données de test',
            'Ajouter gestion des permissions',
            'Implémenter cache pour optimisation'
        ];

        foreach ($recommendations as $index => $rec) {
            $this->line(sprintf('%d. %s', $index + 1, $rec));
        }

        $this->newLine();
        $this->info('💡 Exécutez: php artisan make:model ReportCard -mcr pour commencer');
    }
}
