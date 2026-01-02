<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Semester;

class UniversitySemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier qu'il y a une école universitaire avec des programmes
        $school = School::where('type', 'university')->first();
        
        if (!$school) {
            $this->command->error('Aucune école universitaire trouvée.');
            return;
        }

        // Vérifier qu'il y a une année académique courante
        $currentAcademicYear = AcademicYear::currentForSchool($school->id)->first();
        
        if (!$currentAcademicYear) {
            $this->command->error('Aucune année académique courante trouvée pour l\'école universitaire.');
            return;
        }

        $programs = Program::where('school_id', $school->id)->get();
        if ($programs->isEmpty()) {
            $this->command->error('Aucun programme trouvé. Exécutez d\'abord UniversityProgramSeeder.');
            return;
        }

        $this->command->info("Création des semestres pour l'école universitaire : {$school->name}");
        $this->command->info("Année académique courante : {$currentAcademicYear->name}");

        $totalCreated = 0;

        foreach ($programs as $program) {
            $this->command->info("📅 Création des semestres pour : {$program->name} ({$program->level})");

            // Créer les semestres selon la durée du programme
            for ($semesterNumber = 1; $semesterNumber <= $program->duration_semesters; $semesterNumber++) {
                
                // Vérifier si le semestre existe déjà
                $existingSemester = Semester::where('program_id', $program->id)
                    ->where('academic_year_id', $currentAcademicYear->id)
                    ->where('semester_number', $semesterNumber)
                    ->first();
                
                if ($existingSemester) {
                    $this->command->warn("  ⚠️ Semestre {$semesterNumber} existe déjà pour {$program->name}");
                    continue;
                }

                // Déterminer les crédits requis selon le niveau
                $requiredCredits = $this->getCreditsForLevel($program->level, $semesterNumber);
                
                // Déterminer le niveau académique (1-8)
                $academicLevel = $this->getAcademicLevel($program->level, $semesterNumber);
                
                // Calculer les dates de début et fin du semestre
                $dates = $this->getSemesterDates($currentAcademicYear, $semesterNumber, $program->duration_semesters);
                
                // Noms de semestres selon le niveau
                $semesterName = $this->getSemesterName($program->level, $semesterNumber);
                
                // Description selon le niveau et le numéro
                $description = $this->getSemesterDescription($program->level, $semesterNumber, $program->duration_semesters);

                $semesterData = [
                    'school_id' => $school->id,
                    'academic_year_id' => $currentAcademicYear->id,
                    'program_id' => $program->id,
                    'name' => $semesterName,
                    'semester_number' => $semesterNumber,
                    'academic_level' => $academicLevel,
                    'start_date' => $dates['start_date'],
                    'end_date' => $dates['end_date'],
                    'required_credits' => $requiredCredits,
                    'description' => $description,
                    'is_current' => false,
                    'is_active' => true,
                ];

                $semester = Semester::create($semesterData);
                
                $this->command->info("  ✅ Semestre créé : {$semester->name} ({$requiredCredits} crédits)");
                $totalCreated++;
            }
        }

        $this->command->info("📚 Total des semestres : {$totalCreated} semestres créés avec succès.");
        
        // Affichage des statistiques finales
        $licenceSemesters = Semester::whereHas('program', function($query) use ($school) {
            $query->where('school_id', $school->id)->where('level', 'licence');
        })->count();
        
        $masterSemesters = Semester::whereHas('program', function($query) use ($school) {
            $query->where('school_id', $school->id)->where('level', 'master');
        })->count();
        
        $doctoratSemesters = Semester::whereHas('program', function($query) use ($school) {
            $query->where('school_id', $school->id)->where('level', 'doctorat');
        })->count();
        
        $this->command->info("📊 Répartition par niveau :");
        $this->command->info("   - Semestres Licence : {$licenceSemesters}");
        $this->command->info("   - Semestres Master : {$masterSemesters}");
        $this->command->info("   - Semestres Doctorat : {$doctoratSemesters}");
    }

    /**
     * Obtenir le nombre de crédits requis selon le niveau
     */
    private function getCreditsForLevel(string $level, int $semesterNumber): int
    {
        switch ($level) {
            case 'licence':
                return 30; // 30 crédits par semestre pour une licence
            case 'master':
                return 30; // 30 crédits par semestre pour un master
            case 'doctorat':
                // Pour le doctorat, crédits variables selon le semestre
                if ($semesterNumber <= 4) {
                    return 30; // Cours et séminaires
                } else {
                    return 60; // Recherche et thèse
                }
            case 'dut':
            case 'bts':
                return 30; // 30 crédits par semestre
            default:
                return 30;
        }
    }

    /**
     * Obtenir le niveau académique (1-8) selon le niveau et le numéro de semestre
     */
    private function getAcademicLevel(string $level, int $semesterNumber): int
    {
        switch ($level) {
            case 'licence':
                // L1: 1-2, L2: 3-4, L3: 5-6
                return ceil($semesterNumber / 2);
                
            case 'master':
                // M1: 1-2 (niveau 4-5), M2: 3-4 (niveau 5-6)
                return 3 + ceil($semesterNumber / 2);
                
            case 'doctorat':
                // D1: 1-2 (niveau 6-7), D2: 3-4 (niveau 7-8), etc.
                return 5 + ceil($semesterNumber / 2);
                
            case 'dut':
            case 'bts':
                // Niveau post-bac : 1-2
                return ceil($semesterNumber / 2);
                
            default:
                return $semesterNumber;
        }
    }

    /**
     * Calculer les dates de début et fin d'un semestre
     */
    private function getSemesterDates($academicYear, int $semesterNumber, int $totalSemesters): array
    {
        $startDate = $academicYear->start_date;
        $endDate = $academicYear->end_date;
        
        // Calculer la durée totale de l'année académique en jours
        $totalDays = $startDate->diffInDays($endDate);
        
        // Pour les programmes multi-années, on divise l'année académique en semestres
        // En supposant 2 semestres par année académique
        $semestersPerYear = min($totalSemesters, 2);
        $semesterDurationDays = intval($totalDays / $semestersPerYear);
        
        // Calculer les dates pour ce semestre
        // Les semestres impairs commencent plus tôt, les pairs plus tard dans l'année
        $semesterIndex = (($semesterNumber - 1) % $semestersPerYear);
        
        $semesterStartDate = $startDate->copy()->addDays($semesterIndex * $semesterDurationDays);
        
        // La date de fin est soit le début du semestre suivant - 1 jour, soit la fin de l'année académique
        if ($semesterIndex < $semestersPerYear - 1) {
            $semesterEndDate = $semesterStartDate->copy()->addDays($semesterDurationDays - 1);
        } else {
            $semesterEndDate = $endDate->copy();
        }
        
        return [
            'start_date' => $semesterStartDate,
            'end_date' => $semesterEndDate,
        ];
    }

    /**
     * Obtenir le nom du semestre selon le niveau
     */
    private function getSemesterName(string $level, int $semesterNumber): string
    {
        switch ($level) {
            case 'licence':
                $year = ceil($semesterNumber / 2);
                $sem = ($semesterNumber % 2 === 0) ? 2 : 1;
                return "L{$year} - Semestre {$sem}";
                
            case 'master':
                $year = ceil($semesterNumber / 2);
                $sem = ($semesterNumber % 2 === 0) ? 2 : 1;
                return "M{$year} - Semestre {$sem}";
                
            case 'doctorat':
                if ($semesterNumber <= 4) {
                    $year = ceil($semesterNumber / 2);
                    $sem = ($semesterNumber % 2 === 0) ? 2 : 1;
                    return "D{$year} - Semestre {$sem}";
                } else {
                    $thesisYear = $semesterNumber - 4;
                    return "Thèse - Année {$thesisYear}";
                }
                
            case 'dut':
                $year = ceil($semesterNumber / 2);
                $sem = ($semesterNumber % 2 === 0) ? 2 : 1;
                return "DUT {$year} - Semestre {$sem}";
                
            case 'bts':
                $year = ceil($semesterNumber / 2);
                $sem = ($semesterNumber % 2 === 0) ? 2 : 1;
                return "BTS {$year} - Semestre {$sem}";
                
            default:
                return "Semestre {$semesterNumber}";
        }
    }

    /**
     * Obtenir la description du semestre
     */
    private function getSemesterDescription(string $level, int $semesterNumber, int $totalSemesters): string
    {
        switch ($level) {
            case 'licence':
                if ($semesterNumber <= 2) {
                    return "Semestre de découverte et d'initiation aux concepts fondamentaux de la discipline.";
                } elseif ($semesterNumber <= 4) {
                    return "Semestre d'approfondissement des connaissances et de spécialisation progressive.";
                } else {
                    return "Semestre de spécialisation avancée avec projet de fin d'études ou stage professionnel.";
                }
                
            case 'master':
                if ($semesterNumber <= 2) {
                    return "Semestre d'acquisition des connaissances avancées et de méthodologies de recherche.";
                } elseif ($semesterNumber <= 3) {
                    return "Semestre de spécialisation avec séminaires avancés et début du mémoire de recherche.";
                } else {
                    return "Semestre dédié au mémoire de recherche, stage en entreprise ou en laboratoire.";
                }
                
            case 'doctorat':
                if ($semesterNumber <= 2) {
                    return "Formation doctorale : cours spécialisés, séminaires et définition du sujet de thèse.";
                } elseif ($semesterNumber <= 4) {
                    return "Approfondissement méthodologique et début des travaux de recherche.";
                } else {
                    return "Recherche doctorale intensive et rédaction de la thèse.";
                }
                
            default:
                if ($semesterNumber === 1) {
                    return "Semestre d'introduction et d'acquisition des bases fondamentales.";
                } elseif ($semesterNumber === $totalSemesters) {
                    return "Semestre final avec projet de synthèse ou stage professionnel.";
                } else {
                    return "Semestre d'approfondissement et de consolidation des acquis.";
                }
        }
    }
}