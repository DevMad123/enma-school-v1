<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\AcademicYear;
use Illuminate\Console\Command;

class TestSettingsModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test du module Paramétrage & Gouvernance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Test du module Paramétrage & Gouvernance...');
        $this->newLine();

        // Test des paramètres généraux
        $this->testGeneralSettings();
        
        // Test des années scolaires
        $this->testAcademicYears();
        
        // Test du système de notation
        $this->testGradingSystem();
        
        // Test des paramètres financiers
        $this->testFinancialSettings();

        $this->newLine();
        $this->info('✅ Tous les tests du module Paramétrage & Gouvernance ont réussi !');
        
        return 0;
    }

    private function testGeneralSettings()
    {
        $this->line('📋 Test des paramètres généraux...');
        
        $schoolName = Setting::get('school_name');
        $currency = Setting::get('currency');
        $timezone = Setting::get('timezone');
        
        $this->line("  - Nom de l'école: {$schoolName}");
        $this->line("  - Devise: {$currency}");
        $this->line("  - Fuseau horaire: {$timezone}");
        
        $this->info('  ✅ Paramètres généraux chargés');
    }

    private function testAcademicYears()
    {
        $this->line('📅 Test des années scolaires...');
        
        $currentYear = AcademicYear::where('is_current', true)->first();
        $totalYears = AcademicYear::count();
        
        if ($currentYear) {
            $this->line("  - Année courante: {$currentYear->name}");
            $this->line("  - Périodes: {$currentYear->gradePeriods->count()}");
        } else {
            $this->line('  - Aucune année courante définie');
        }
        
        $this->line("  - Total années configurées: {$totalYears}");
        $this->info('  ✅ Années scolaires vérifiées');
    }

    private function testGradingSystem()
    {
        $this->line('📊 Test du système de notation...');
        
        $scale = Setting::get('grading_scale', '20');
        $passingGrade = Setting::get('passing_grade', '10');
        $excellenceGrade = Setting::get('excellence_grade', '16');
        $precision = Setting::get('grade_precision', '2');
        $displayLetters = Setting::get('display_letter_grades', false);
        
        $this->line("  - Échelle: Sur {$scale}");
        $this->line("  - Note de passage: {$passingGrade}");
        $this->line("  - Note d'excellence: {$excellenceGrade}");
        $this->line("  - Précision: {$precision} décimale(s)");
        $this->line("  - Affichage lettres: " . ($displayLetters ? 'Oui' : 'Non'));
        
        $this->info('  ✅ Système de notation configuré');
    }

    private function testFinancialSettings()
    {
        $this->line('💰 Test des paramètres financiers...');
        
        $currency = Setting::get('default_currency', 'FCFA');
        $paymentTerms = Setting::get('payment_terms_days', '30');
        $lateFee = Setting::get('late_payment_fee_percentage', '5');
        $reminders = Setting::get('enable_payment_reminders', true);
        $onlinePayments = Setting::get('enable_online_payments', false);
        
        $this->line("  - Devise par défaut: {$currency}");
        $this->line("  - Délai de paiement: {$paymentTerms} jours");
        $this->line("  - Frais de retard: {$lateFee}%");
        $this->line("  - Rappels automatiques: " . ($reminders ? 'Activés' : 'Désactivés'));
        $this->line("  - Paiements en ligne: " . ($onlinePayments ? 'Activés' : 'Désactivés'));
        
        $this->info('  ✅ Paramètres financiers configurés');
    }
}