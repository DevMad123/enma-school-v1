<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\GradePeriod;
use App\Models\TeacherAssignment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des évaluations...');
        
        // Récupérer la période active
        $activePeriod = GradePeriod::where('is_active', true)->first();
        
        if (!$activePeriod) {
            $this->command->error('Aucune période active trouvée !');
            return;
        }
        
        // Récupérer l'année académique active
        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        
        if (!$activeAcademicYear) {
            $this->command->error('Aucune année académique active trouvée !');
            return;
        }
        
        // Récupérer toutes les affectations d'enseignants pour l'année active
        $assignments = TeacherAssignment::with(['teacher.user', 'schoolClass', 'subject', 'academicYear'])
            ->where('academic_year_id', $activeAcademicYear->id)
            ->whereHas('subject') // S'assurer que subject existe
            ->get();
            
        if ($assignments->isEmpty()) {
            $this->command->error('Aucune affectation d\'enseignant trouvée !');
            return;
        }
        
        $evaluationTypes = [
            ['type' => 'devoir', 'coefficient' => 1.0, 'prefix' => 'Devoir'],
            ['type' => 'controle', 'coefficient' => 2.0, 'prefix' => 'Contrôle'],
            ['type' => 'composition', 'coefficient' => 3.0, 'prefix' => 'Composition'],
        ];
        
        $evaluationCount = 0;
        
        foreach ($assignments as $assignment) {
            // Vérifier que l'affectation a bien un sujet
            if (!$assignment->subject || !$assignment->schoolClass) {
                $this->command->warn("Affectation #{$assignment->id} ignorée - sujet ou classe manquant");
                continue;
            }
            
            // Créer 2-4 évaluations par affectation
            $numEvaluations = rand(2, 4);
            
            for ($i = 1; $i <= $numEvaluations; $i++) {
                // Choisir un type d'évaluation aléatoire
                $evalType = $evaluationTypes[array_rand($evaluationTypes)];
                
                // Générer une date aléatoire dans la période
                $startDate = Carbon::parse($activePeriod->start_date);
                $endDate = Carbon::parse($activePeriod->end_date);
                $randomDate = $startDate->addDays(rand(0, $startDate->diffInDays($endDate)));
                
                // Déterminer le statut (80% completed, 20% scheduled)
                $status = rand(1, 100) <= 80 ? 'completed' : 'scheduled';
                
                $evaluation = Evaluation::create([
                    'name' => $evalType['prefix'] . ' ' . $i,
                    'type' => $evalType['type'],
                    'coefficient' => $evalType['coefficient'],
                    'max_grade' => 20.00,
                    'academic_year_id' => $activeAcademicYear->id, // Utiliser l'année académique active
                    'subject_id' => $assignment->subject_id,
                    'class_id' => $assignment->class_id,
                    'teacher_assignment_id' => $assignment->id,
                    'grade_period_id' => $activePeriod->id,
                    'evaluation_date' => $randomDate,
                    'status' => $status,
                    'description' => "Evaluation en {$assignment->subject->name} pour la classe {$assignment->schoolClass->name}",
                ]);
                
                $evaluationCount++;
                
                if ($evaluationCount % 20 == 0) {
                    $this->command->line("✓ {$evaluationCount} évaluations créées...");
                }
            }
        }
        
        $this->command->info("✓ {$evaluationCount} évaluations créées au total !");
        $this->command->line("Répartition par type :");
        
        foreach ($evaluationTypes as $type) {
            $count = Evaluation::where('type', $type['type'])->count();
            $this->command->line("  - {$type['prefix']} : {$count}");
        }
    }
}
