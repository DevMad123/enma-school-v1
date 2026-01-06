<?php

namespace App\Domains\Evaluation;

use App\Domains\Evaluation\EvaluationSystemInterface;
use App\Models\CourseUnit;
use App\Models\Semester;
use Illuminate\Support\Collection;

/**
 * Service d'évaluation pour l'universitaire (LMD)
 * Implémente la logique ECTS avec crédits et compensations
 */
class UniversityEvaluationService implements EvaluationSystemInterface
{
    /**
     * Configuration LMD par défaut
     */
    protected const LMD_THRESHOLDS = [
        'pass' => 10.0,
        'good' => 12.0,
        'very_good' => 14.0,
        'excellent' => 16.0,
        'ects_pass' => 10.0, // Seuil ECTS pour validation crédits
    ];

    /**
     * Grades ECTS
     */
    protected const ECTS_GRADES = [
        'A' => 16.0, // Excellent
        'B' => 14.0, // Très bien  
        'C' => 12.0, // Bien
        'D' => 10.0, // Satisfaisant
        'E' => 8.0,  // Passable (avec compensation)
        'FX' => 6.0, // Échec (proche réussite)
        'F' => 0.0,  // Échec
    ];

    /**
     * Calculer une note sur 20
     *
     * @param float $rawScore
     * @param float $maxScore
     * @return float
     */
    public function calculateGrade(float $rawScore, float $maxScore): float
    {
        if ($maxScore <= 0) {
            return 0.0;
        }

        $grade = ($rawScore / $maxScore) * 20;
        return round($grade, 2);
    }

    /**
     * Calculer une moyenne pondérée par crédits ECTS
     *
     * @param Collection $grades Collection avec structure: ['grade' => float, 'credits' => int]
     * @return float
     */
    public function calculateAverage(Collection $grades): float
    {
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0.0;
        $totalCredits = 0;

        foreach ($grades as $gradeData) {
            $grade = $gradeData['grade'] ?? 0.0;
            $credits = $gradeData['credits'] ?? 0;

            $totalPoints += $grade * $credits;
            $totalCredits += $credits;
        }

        if ($totalCredits <= 0) {
            return 0.0;
        }

        return round($totalPoints / $totalCredits, 2);
    }

    /**
     * Déterminer le statut de réussite selon LMD
     *
     * @param float $average
     * @param array $context
     * @return string
     */
    public function determinePassingStatus(float $average, array $context = []): string
    {
        $thresholds = $this->getPassingThresholds();

        if ($average >= $thresholds['ects_pass']) {
            return 'pass';
        }

        // Vérification des compensations LMD
        if ($this->canCompensate($context)) {
            return 'compensation';
        }

        // Rattrapage possible selon LMD
        if ($average >= 7.0) {
            return 'rattrapage';
        }

        return 'fail';
    }

    /**
     * Valider une note selon les critères universitaires
     *
     * @param float $grade
     * @return array
     */
    public function validateGrade(float $grade): array
    {
        $errors = [];

        if ($grade < 0) {
            $errors[] = 'La note ne peut pas être négative';
        }

        if ($grade > 20) {
            $errors[] = 'La note ne peut pas dépasser 20';
        }

        // Vérifier la précision (max 2 décimales)
        if (round($grade, 2) !== $grade) {
            $errors[] = 'La note ne peut avoir plus de 2 décimales';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Obtenir les seuils de réussite LMD
     *
     * @return array
     */
    public function getPassingThresholds(): array
    {
        return self::LMD_THRESHOLDS;
    }

    /**
     * Calculer la moyenne d'une UE (Unité d'Enseignement)
     *
     * @param Collection $courseUnitGrades Notes des éléments constitutifs
     * @param CourseUnit $courseUnit
     * @return array
     */
    public function calculateCourseUnitAverage(Collection $courseUnitGrades, CourseUnit $courseUnit): array
    {
        $elementsData = $courseUnitGrades->map(function ($grade) {
            return [
                'grade' => $grade['grade'],
                'credits' => $grade['credits'] ?? 1,
            ];
        });

        $average = $this->calculateAverage($elementsData);
        $credits = $courseUnit->credits;
        
        return [
            'average' => $average,
            'credits' => $credits,
            'validated' => $average >= self::LMD_THRESHOLDS['ects_pass'],
            'ects_grade' => $this->calculateECTSGrade($average),
            'can_compensate' => $average >= 8.0 && $average < 10.0,
        ];
    }

    /**
     * Calculer la moyenne d'un semestre
     *
     * @param Collection $courseUnitsResults Résultats des UE
     * @param Semester $semester
     * @return array
     */
    public function calculateSemesterAverage(Collection $courseUnitsResults, Semester $semester): array
    {
        $totalCredits = $courseUnitsResults->sum('credits');
        $targetCredits = $semester->total_credits ?? 30;
        
        $average = $this->calculateAverage($courseUnitsResults);
        
        // Calculer les crédits acquis
        $acquiredCredits = $courseUnitsResults->where('validated', true)->sum('credits');
        
        // Calculer les crédits compensables
        $compensatableCredits = $courseUnitsResults
            ->where('can_compensate', true)
            ->filter(function ($ue) use ($average) {
                // Compensation possible si moyenne générale >= 10
                return $average >= 10.0;
            })
            ->sum('credits');

        return [
            'average' => $average,
            'total_credits' => $totalCredits,
            'acquired_credits' => $acquiredCredits,
            'compensated_credits' => $compensatableCredits,
            'final_credits' => $acquiredCredits + $compensatableCredits,
            'validated' => ($acquiredCredits + $compensatableCredits) >= $targetCredits && $average >= 10.0,
            'ects_grade' => $this->calculateECTSGrade($average),
        ];
    }

    /**
     * Calculer la moyenne d'une année universitaire
     *
     * @param Collection $semesterResults Résultats des semestres
     * @return array
     */
    public function calculateYearAverage(Collection $semesterResults): array
    {
        $yearAverage = $this->calculateAverage($semesterResults);
        
        $totalCredits = $semesterResults->sum('final_credits');
        $requiredCredits = 60; // Année complète = 60 crédits ECTS
        
        return [
            'average' => $yearAverage,
            'total_credits' => $totalCredits,
            'required_credits' => $requiredCredits,
            'validated' => $totalCredits >= $requiredCredits && $yearAverage >= 10.0,
            'ects_grade' => $this->calculateECTSGrade($yearAverage),
            'mention' => $this->calculateMention($yearAverage),
        ];
    }

    /**
     * Calculer le grade ECTS
     *
     * @param float $average
     * @return string
     */
    public function calculateECTSGrade(float $average): string
    {
        foreach (self::ECTS_GRADES as $grade => $threshold) {
            if ($average >= $threshold) {
                return $grade;
            }
        }
        
        return 'F'; // Échec par défaut
    }

    /**
     * Calculer la mention
     *
     * @param float $average
     * @return string|null
     */
    public function calculateMention(float $average): ?string
    {
        $thresholds = $this->getPassingThresholds();

        if ($average >= $thresholds['excellent']) {
            return 'Très bien';
        } elseif ($average >= $thresholds['very_good']) {
            return 'Bien';
        } elseif ($average >= $thresholds['good']) {
            return 'Assez bien';
        } elseif ($average >= $thresholds['pass']) {
            return 'Passable';
        }

        return null; // Pas de mention si échec
    }

    /**
     * Vérifier si une compensation est possible
     *
     * @param array $context
     * @return bool
     */
    protected function canCompensate(array $context): bool
    {
        // Compensation possible si moyenne générale >= 10
        $generalAverage = $context['general_average'] ?? 0.0;
        
        if ($generalAverage < 10.0) {
            return false;
        }

        // Vérifier les règles spécifiques de compensation
        $failingUnits = $context['failing_units'] ?? [];
        $totalFailingCredits = collect($failingUnits)->sum('credits');
        
        // Maximum 30% des crédits en compensation
        $maxCompensableCredits = ($context['total_credits'] ?? 30) * 0.3;
        
        return $totalFailingCredits <= $maxCompensableCredits;
    }

    /**
     * Déterminer les décisions de jury
     *
     * @param Collection $studentResults
     * @param array $juryRules
     * @return Collection
     */
    public function determineJuryDecisions(Collection $studentResults, array $juryRules = []): Collection
    {
        return $studentResults->map(function ($student) use ($juryRules) {
            $yearAverage = $student['year_average'];
            $totalCredits = $student['total_credits'];
            $requiredCredits = $student['required_credits'] ?? 60;

            $decision = $this->applyJuryRules($student, $juryRules);

            return [
                'student_id' => $student['student_id'],
                'year_average' => $yearAverage,
                'total_credits' => $totalCredits,
                'decision' => $decision,
                'mention' => $this->calculateMention($yearAverage),
                'ects_grade' => $this->calculateECTSGrade($yearAverage),
                'can_progress' => $this->canProgress($student),
            ];
        });
    }

    /**
     * Appliquer les règles de jury
     *
     * @param array $student
     * @param array $rules
     * @return string
     */
    protected function applyJuryRules(array $student, array $rules): string
    {
        $average = $student['year_average'];
        $credits = $student['total_credits'];
        $requiredCredits = $student['required_credits'] ?? 60;

        // Validation complète
        if ($credits >= $requiredCredits && $average >= 10.0) {
            return 'admitted'; // Admis
        }

        // Compensation par jury
        if ($credits >= ($requiredCredits * 0.7) && $average >= 9.0) {
            return 'jury_compensation'; // Admis par compensation du jury
        }

        // Progression conditionnelle (pour L1 vers L2, etc.)
        if ($this->canProgress($student)) {
            return 'conditional_progression'; // Progression avec dettes
        }

        // Redoublement
        if ($credits >= ($requiredCredits * 0.5) || $average >= 7.0) {
            return 'repeat'; // Redouble
        }

        return 'excluded'; // Exclusion/réorientation
    }

    /**
     * Vérifier si l'étudiant peut progresser avec dettes
     *
     * @param array $student
     * @return bool
     */
    protected function canProgress(array $student): bool
    {
        $credits = $student['total_credits'];
        $requiredCredits = $student['required_credits'] ?? 60;
        $level = $student['level'] ?? 'L1';

        // Règles de progression avec dettes selon le niveau
        $progressionRules = [
            'L1' => 0.6, // 60% des crédits pour passer en L2
            'L2' => 0.7, // 70% des crédits pour passer en L3
            'L3' => 0.8, // 80% des crédits pour valider la licence
            'M1' => 0.8, // 80% des crédits pour passer en M2
            'M2' => 1.0, // 100% des crédits pour valider le master
        ];

        $requiredRatio = $progressionRules[$level] ?? 1.0;
        
        return ($credits / $requiredCredits) >= $requiredRatio;
    }

    /**
     * Générer un bulletin LMD complet
     *
     * @param array $studentData
     * @return array
     */
    public function generateLMDBulletin(array $studentData): array
    {
        $semesterResults = collect($studentData['semester_results']);
        $yearResult = $this->calculateYearAverage($semesterResults);
        
        return [
            'student_info' => $studentData['student_info'],
            'academic_year' => $studentData['academic_year'],
            'level' => $studentData['level'],
            'semester_results' => $semesterResults->toArray(),
            'year_result' => $yearResult,
            'progression_status' => $this->canProgress($yearResult),
            'jury_decision' => $studentData['jury_decision'] ?? null,
            'generated_at' => now(),
        ];
    }

    /**
     * Calculer les statistiques d'une promotion
     *
     * @param Collection $promotionResults
     * @return array
     */
    public function calculatePromotionStatistics(Collection $promotionResults): array
    {
        $averages = $promotionResults->pluck('year_average');
        
        return [
            'promotion_size' => $promotionResults->count(),
            'statistics' => [
                'average' => $averages->avg(),
                'median' => $averages->median(),
                'max' => $averages->max(),
                'min' => $averages->min(),
                'std_deviation' => $this->calculateStandardDeviation($averages),
            ],
            'ects_distribution' => [
                'A' => $promotionResults->where('ects_grade', 'A')->count(),
                'B' => $promotionResults->where('ects_grade', 'B')->count(),
                'C' => $promotionResults->where('ects_grade', 'C')->count(),
                'D' => $promotionResults->where('ects_grade', 'D')->count(),
                'E' => $promotionResults->where('ects_grade', 'E')->count(),
                'F' => $promotionResults->where('ects_grade', 'F')->count(),
            ],
            'success_rate' => $promotionResults->count() > 0 
                ? ($promotionResults->where('decision', 'admitted')->count() / $promotionResults->count()) * 100
                : 0,
        ];
    }

    /**
     * Calculer l'écart-type
     *
     * @param Collection $values
     * @return float
     */
    protected function calculateStandardDeviation(Collection $values): float
    {
        if ($values->isEmpty()) {
            return 0.0;
        }

        $mean = $values->avg();
        $squaredDifferences = $values->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        });

        $variance = $squaredDifferences->avg();
        return round(sqrt($variance), 2);
    }
}