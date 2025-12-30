<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\GradePeriod;
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
        $this->command->info('Création des périodes de notation...');
        
        // Récupérer l'année académique active
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        if (!$academicYear) {
            $this->command->error('Aucune année académique active trouvée !');
            return;
        }
        
        // Définir les périodes (trimestres) avec leurs dates
        $periods = [
            [
                'name' => '1er Trimestre',
                'start_date' => $academicYear->start_date,
                'end_date' => Carbon::parse($academicYear->start_date)->addMonths(3)->subDay(),
                'is_active' => true, // Activer le 1er trimestre par défaut
            ],
            [
                'name' => '2ème Trimestre',
                'start_date' => Carbon::parse($academicYear->start_date)->addMonths(3),
                'end_date' => Carbon::parse($academicYear->start_date)->addMonths(6)->subDay(),
                'is_active' => false,
            ],
            [
                'name' => '3ème Trimestre',
                'start_date' => Carbon::parse($academicYear->start_date)->addMonths(6),
                'end_date' => $academicYear->end_date,
                'is_active' => false,
            ]
        ];
        
        foreach ($periods as $periodData) {
            $period = GradePeriod::create([
                'academic_year_id' => $academicYear->id,
                'name' => $periodData['name'],
                'start_date' => $periodData['start_date'],
                'end_date' => $periodData['end_date'],
                'is_active' => $periodData['is_active'],
            ]);
            
            $this->command->line("Période créée : {$period->name} ({$period->start_date->format('d/m/Y')} - {$period->end_date->format('d/m/Y')})");
        }
        
        $this->command->info('Périodes de notation créées avec succès !');
    }
}
