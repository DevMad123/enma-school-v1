<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\GradePeriod;
use Carbon\Carbon;

class AcademicTestDataSeeder extends Seeder
{
    /**
     * Seeder pour créer des données de test supplémentaires
     * pour les années académiques avec différents statuts
     */
    public function run(): void
    {
        $this->command->info('Création de données de test supplémentaires...');
        
        $schools = School::all();
        
        if ($schools->isEmpty()) {
            $this->command->error('Aucune école trouvée !');
            return;
        }
        
        // Ajouter quelques années académiques avec différents statuts
        foreach ($schools as $school) {
            $this->command->line("Ajout de données de test pour : {$school->name}");
            
            // Année future planifiée
            $futureYear = AcademicYear::create([
                'school_id' => $school->id,
                'name' => '2027-2028',
                'start_date' => Carbon::create(2027, 9, 1),
                'end_date' => Carbon::create(2028, 6, 30),
                'is_active' => false,
            ]);
            
            // Année archivée
            $archivedYear = AcademicYear::create([
                'school_id' => $school->id,
                'name' => '2022-2023',
                'start_date' => Carbon::create(2022, 9, 1),
                'end_date' => Carbon::create(2023, 6, 30),
                'is_active' => false,
            ]);
            
            // Créer les périodes pour ces années
            $futureYear->createDefaultPeriods();
            $archivedYear->createDefaultPeriods();
            
            // Marquer toutes les périodes de l'année archivée comme terminées
            foreach ($archivedYear->academicPeriods as $period) {
                $period->update(['is_active' => false]);
            }
            
            $this->command->line("  ├─ Année future créée : {$futureYear->name}");
            $this->command->line("  └─ Année archivée créée : {$archivedYear->name}");
        }
        
        // Mise à jour de quelques périodes pour les tests
        $this->updatePeriodsForTesting();
        
        $this->command->info('Données de test supplémentaires créées avec succès !');
    }
    
    /**
     * Mettre à jour quelques périodes pour les tests
     */
    private function updatePeriodsForTesting(): void
    {
        // Récupérer l'année académique active
        $activeYears = AcademicYear::where('is_active', true)->get();
        
        foreach ($activeYears as $year) {
            $periods = $year->academicPeriods->sortBy('order');
            
            if ($periods->count() >= 2) {
                // Activer seulement la première période
                $firstPeriod = $periods->first();
                $firstPeriod->update(['is_active' => true]);
                
                // Désactiver les autres
                $periods->skip(1)->each(function($period) {
                    $period->update(['is_active' => false]);
                });
                
                $this->command->line("Période active définie : {$firstPeriod->name} pour {$year->school->name}");
            }
        }
    }
}