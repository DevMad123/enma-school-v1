<?php

namespace Database\Seeders;

use App\Models\ReportCard;
use App\Models\Student;
use App\Models\GradePeriod;
use App\Models\AcademicYear;
use Illuminate\Database\Seeder;

class ReportCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_active', true)->first();
        $activePeriods = GradePeriod::where('is_active', true)->get();
        
        if (!$academicYear || $activePeriods->isEmpty()) {
            $this->command->warn('Pas d\'année académique active ou de périodes actives trouvées');
            return;
        }

        // Générer des bulletins pour tous les étudiants ayant des notes
        $studentsWithGrades = Student::whereHas('grades')->get();
        
        $this->command->info("Génération de bulletins pour {$studentsWithGrades->count()} étudiants...");
        
        foreach ($studentsWithGrades as $student) {
            $currentClass = $student->currentClass();
            
            if (!$currentClass) {
                continue;
            }
            
            foreach ($activePeriods as $period) {
                try {
                    $reportCard = $student->getOrCreateReportCard(
                        $period->id,
                        $academicYear->id,
                        $currentClass->id
                    );
                    
                    // Ajouter des observations aléatoires
                    $observations = [
                        'Élève sérieux et appliqué. Continuez vos efforts.',
                        'Des progrès sont à noter. Encouragez la régularité dans le travail.',
                        'Résultats satisfaisants. Peut mieux faire avec plus de concentration.',
                        'Très bon trimestre. Félicitations pour ces excellents résultats.',
                        'Quelques difficultés observées. Un soutien supplémentaire serait bénéfique.',
                        'Élève motivé avec de bons résultats. Continuez dans cette voie.',
                    ];
                    
                    $reportCard->update([
                        'observations' => $observations[array_rand($observations)],
                        'status' => 'published',
                        'generated_by' => 1, // Assumé ID administrateur
                        'generated_at' => now(),
                    ]);
                    
                    $this->command->line("✓ Bulletin généré pour {$student->full_name} - {$period->name}");
                    
                } catch (\Exception $e) {
                    $this->command->error("✗ Erreur pour {$student->full_name}: {$e->getMessage()}");
                }
            }
        }
        
        $totalGenerated = ReportCard::count();
        $this->command->info("\n{$totalGenerated} bulletins générés au total.");
    }
}
