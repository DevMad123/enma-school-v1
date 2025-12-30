<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class GradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des notes...');
        
        // Récupérer toutes les évaluations complétées
        $evaluations = Evaluation::with(['schoolClass.enrollments.student.user', 'teacherAssignment.teacher.user'])
            ->where('status', 'completed')
            ->get();
            
        if ($evaluations->isEmpty()) {
            $this->command->error('Aucune évaluation complétée trouvée !');
            return;
        }
        
        $gradeCount = 0;
        $totalStudents = 0;
        
        foreach ($evaluations as $evaluation) {
            // Récupérer les élèves inscrits dans cette classe
            $students = Student::whereHas('enrollments', function($query) use ($evaluation) {
                $query->where('class_id', $evaluation->class_id)
                      ->where('academic_year_id', $evaluation->academic_year_id)
                      ->where('status', 'active');
            })->get();
            
            if ($students->isEmpty()) {
                continue;
            }
            
            $totalStudents += $students->count();
            
            foreach ($students as $student) {
                // 5% de chance d'être absent
                $isAbsent = rand(1, 100) <= 5;
                
                $gradeData = [
                    'evaluation_id' => $evaluation->id,
                    'student_id' => $student->id,
                    'absent' => $isAbsent,
                    'graded_by' => $evaluation->teacherAssignment->teacher->user->id ?? 1,
                    'graded_at' => Carbon::now(),
                ];
                
                if ($isAbsent) {
                    $gradeData['justification'] = 'Absence non justifiée';
                } else {
                    // Générer une note réaliste avec distribution normale
                    $grade = $this->generateRealisticGrade();
                    $gradeData['grade'] = $grade;
                }
                
                Grade::create($gradeData);
                $gradeCount++;
                
                if ($gradeCount % 100 == 0) {
                    $this->command->line("✓ {$gradeCount} notes créées...");
                }
            }
        }
        
        $this->command->info("✓ {$gradeCount} notes créées au total !");
        $this->command->line("Pour {$totalStudents} élèves dans {$evaluations->count()} évaluations");
        
        // Afficher les statistiques
        $this->showGradeStatistics();
    }
    
    /**
     * Générer une note réaliste avec distribution normale
     */
    private function generateRealisticGrade(): float
    {
        // Distribution réaliste des notes :
        // 10% excellentes (16-20)
        // 25% bonnes (14-16)
        // 35% moyennes (10-14)
        // 20% passables (8-10)
        // 10% faibles (0-8)
        
        $rand = rand(1, 100);
        
        if ($rand <= 10) {
            // Excellentes : 16-20
            return round(16 + (rand(0, 400) / 100), 2);
        } elseif ($rand <= 35) {
            // Bonnes : 14-16
            return round(14 + (rand(0, 200) / 100), 2);
        } elseif ($rand <= 70) {
            // Moyennes : 10-14
            return round(10 + (rand(0, 400) / 100), 2);
        } elseif ($rand <= 90) {
            // Passables : 8-10
            return round(8 + (rand(0, 200) / 100), 2);
        } else {
            // Faibles : 0-8
            return round(rand(0, 800) / 100, 2);
        }
    }
    
    /**
     * Afficher les statistiques des notes générées
     */
    private function showGradeStatistics(): void
    {
        $this->command->info('\nSTATISTIQUES DES NOTES :');
        
        $totalGrades = Grade::whereNotNull('grade')->where('absent', false)->count();
        $averageGrade = Grade::whereNotNull('grade')->where('absent', false)->avg('grade');
        $absentCount = Grade::where('absent', true)->count();
        
        $this->command->line("Moyenne générale : " . number_format($averageGrade, 2));
        $this->command->line("Total absences : {$absentCount}");
        
        // Distribution par tranche
        $excellent = Grade::whereNotNull('grade')->where('absent', false)->where('grade', '>=', 16)->count();
        $good = Grade::whereNotNull('grade')->where('absent', false)->whereBetween('grade', [14, 16])->count();
        $fair = Grade::whereNotNull('grade')->where('absent', false)->whereBetween('grade', [12, 14])->count();
        $passing = Grade::whereNotNull('grade')->where('absent', false)->whereBetween('grade', [10, 12])->count();
        $failing = Grade::whereNotNull('grade')->where('absent', false)->where('grade', '<', 10)->count();
        
        $this->command->line('Distribution :');
        $this->command->line("  • Excellent (16-20) : {$excellent} (" . round(($excellent/$totalGrades)*100, 1) . '%)');
        $this->command->line("  • Bien (14-16) : {$good} (" . round(($good/$totalGrades)*100, 1) . '%)');
        $this->command->line("  • Assez bien (12-14) : {$fair} (" . round(($fair/$totalGrades)*100, 1) . '%)');
        $this->command->line("  • Passable (10-12) : {$passing} (" . round(($passing/$totalGrades)*100, 1) . '%)');
        $this->command->line("  • Insuffisant (<10) : {$failing} (" . round(($failing/$totalGrades)*100, 1) . '%)');
    }
}
