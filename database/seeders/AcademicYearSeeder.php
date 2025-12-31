<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des années académiques...');
        
        // Récupérer toutes les écoles
        $schools = School::all();
        
        if ($schools->isEmpty()) {
            $this->command->error('Aucune école trouvée ! Veuillez exécuter SchoolSeeder en premier.');
            return;
        }
        
        $academicYears = [
            [
                'name' => '2023-2024',
                'start_date' => Carbon::create(2023, 9, 1),
                'end_date' => Carbon::create(2024, 6, 30),
                'is_active' => false
            ],
            [
                'name' => '2024-2025',
                'start_date' => Carbon::create(2024, 9, 1),
                'end_date' => Carbon::create(2025, 6, 30),
                'is_active' => false
            ],
            [
                'name' => '2025-2026',
                'start_date' => Carbon::create(2025, 9, 1),
                'end_date' => Carbon::create(2026, 6, 30),
                'is_active' => true  // Année courante active
            ],
            [
                'name' => '2026-2027',
                'start_date' => Carbon::create(2026, 9, 1),
                'end_date' => Carbon::create(2027, 6, 30),
                'is_active' => false
            ]
        ];
        
        foreach ($schools as $school) {
            $this->command->line("Création des années académiques pour : {$school->name}");
            
            foreach ($academicYears as $yearData) {
                $academicYear = AcademicYear::create([
                    'school_id' => $school->id,
                    'name' => $yearData['name'],
                    'start_date' => $yearData['start_date'],
                    'end_date' => $yearData['end_date'],
                    'is_active' => $yearData['is_active'],
                ]);
                
                // Créer automatiquement les périodes par défaut
                if (method_exists($academicYear, 'createDefaultPeriods')) {
                    $academicYear->createDefaultPeriods();
                    $this->command->line("  └─ Année {$yearData['name']} créée avec périodes par défaut");
                } else {
                    $this->command->line("  └─ Année {$yearData['name']} créée");
                }
            }
        }
        
        $this->command->info('Années académiques créées avec succès !');
    }
}