<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\GradePeriod;
use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class GradePeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Vérification des périodes académiques...');
        
        // Ce seeder vérifie si les périodes ont été créées automatiquement
        // Sinon, il les crée manuellement
        
        $academicYears = AcademicYear::with('school', 'academicPeriods')->get();
        
        if ($academicYears->isEmpty()) {
            $this->command->error('Aucune année académique trouvée ! Veuillez exécuter AcademicYearSeeder en premier.');
            return;
        }
        
        $periodsCreated = 0;
        
        foreach ($academicYears as $academicYear) {
            $school = $academicYear->school;
            
            // Vérifier si les périodes existent déjà
            if ($academicYear->academicPeriods->count() > 0) {
                $this->command->line("Périodes déjà créées pour {$school->name} - {$academicYear->name}");
                continue;
            }
            
            $this->command->line("Création des périodes pour {$school->name} - {$academicYear->name} (Système: {$school->academic_system})");
            
            if ($school->academic_system === 'trimestre') {
                $periods = [
                    [
                        'name' => '1er Trimestre',
                        'type' => 'trimestre',
                        'order' => 1,
                        'start_date' => $academicYear->start_date,
                        'end_date' => Carbon::parse($academicYear->start_date)->addMonths(3)->subDay(),
                        'is_active' => true,
                    ],
                    [
                        'name' => '2ème Trimestre',
                        'type' => 'trimestre', 
                        'order' => 2,
                        'start_date' => Carbon::parse($academicYear->start_date)->addMonths(3),
                        'end_date' => Carbon::parse($academicYear->start_date)->addMonths(6)->subDay(),
                        'is_active' => false,
                    ],
                    [
                        'name' => '3ème Trimestre',
                        'type' => 'trimestre',
                        'order' => 3,
                        'start_date' => Carbon::parse($academicYear->start_date)->addMonths(6),
                        'end_date' => $academicYear->end_date,
                        'is_active' => false,
                    ]
                ];
            } else {
                $periods = [
                    [
                        'name' => '1er Semestre',
                        'type' => 'semestre',
                        'order' => 1,
                        'start_date' => $academicYear->start_date,
                        'end_date' => Carbon::parse($academicYear->start_date)->addMonths(4)->subDay(),
                        'is_active' => true,
                    ],
                    [
                        'name' => '2ème Semestre',
                        'type' => 'semestre',
                        'order' => 2,
                        'start_date' => Carbon::parse($academicYear->start_date)->addMonths(4),
                        'end_date' => $academicYear->end_date,
                        'is_active' => false,
                    ]
                ];
            }
            
            foreach ($periods as $periodData) {
                $period = GradePeriod::create([
                    'academic_year_id' => $academicYear->id,
                    'name' => $periodData['name'],
                    'type' => $periodData['type'],
                    'order' => $periodData['order'],
                    'start_date' => $periodData['start_date'],
                    'end_date' => $periodData['end_date'],
                    'is_active' => $periodData['is_active'],
                ]);
                
                $periodsCreated++;
                $this->command->line("  └─ {$period->name} ({$period->start_date->format('d/m/Y')} - {$period->end_date->format('d/m/Y')})");
            }
        }
        
        $this->command->info("$periodsCreated nouvelles périodes créées !");
    }
}
