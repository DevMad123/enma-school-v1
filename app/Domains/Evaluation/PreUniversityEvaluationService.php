<?php

namespace App\Domains\Evaluation;

use App\Domains\Evaluation\EvaluationSystemInterface;
use Illuminate\Support\Collection;

/**
 * Service d'évaluation pour le préuniversitaire
 * Implémente la logique des moyennes pondérées par coefficient
 */
class PreUniversityEvaluationService implements EvaluationSystemInterface
{
    /**
     * Configuration des seuils par défaut
     */
    protected const DEFAULT_THRESHOLDS = [
        'pass' => 10.0,
        'good' => 12.0,
        'very_good' => 14.0,
        'excellent' => 16.0,
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
     * Calculer une moyenne pondérée par coefficient
     *
     * @param Collection $grades Collection avec structure: ['grade' => float, 'coefficient' => float]
     * @return float
     */
    public function calculateAverage(Collection $grades): float
    {
        if ($grades->isEmpty()) {
            return 0.0;
        }

        $totalPoints = 0.0;
        $totalCoefficients = 0.0;

        foreach ($grades as $gradeData) {
            $grade = $gradeData['grade'] ?? 0.0;
            $coefficient = $gradeData['coefficient'] ?? 1.0;

            $totalPoints += $grade * $coefficient;
            $totalCoefficients += $coefficient;
        }

        if ($totalCoefficients <= 0) {
            return 0.0;
        }

        return round($totalPoints / $totalCoefficients, 2);
    }

    /**
     * Déterminer le statut de réussite
     *
     * @param float $average
     * @param array $context
     * @return string
     */
    public function determinePassingStatus(float $average, array $context = []): string
    {
        $thresholds = $this->getPassingThresholds();

        if ($average >= $thresholds['pass']) {
            return 'pass';
        }

        // Vérifier les possibilités de compensation si applicable
        if (isset($context['allow_compensation']) && $context['allow_compensation']) {
            if ($average >= ($thresholds['pass'] - 2)) { // Compensation possible si < 2 points de la moyenne
                return 'compensation_possible';
            }
        }

        return 'fail';
    }

    /**
     * Valider une note
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
     * Obtenir les seuils de réussite
     *
     * @return array
     */
    public function getPassingThresholds(): array
    {
        // TODO: Récupérer depuis la configuration de l'école
        return self::DEFAULT_THRESHOLDS;
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
            return 'Excellent';
        } elseif ($average >= $thresholds['very_good']) {
            return 'Très bien';
        } elseif ($average >= $thresholds['good']) {
            return 'Bien';
        } elseif ($average >= $thresholds['pass']) {
            return 'Assez bien';
        }

        return null; // Pas de mention si échec
    }

    /**
     * Calculer les moyennes par matière
     *
     * @param Collection $grades Grades avec subject_id, grade, coefficient
     * @return Collection
     */
    public function calculateSubjectAverages(Collection $grades): Collection
    {
        return $grades->groupBy('subject_id')->map(function ($subjectGrades) {
            $gradeData = $subjectGrades->map(function ($grade) {
                return [
                    'grade' => $grade['grade'],
                    'coefficient' => $grade['coefficient'] ?? 1.0,
                ];
            });

            return $this->calculateAverage($gradeData);
        });
    }

    /**
     * Calculer la moyenne générale d'un élève
     *
     * @param Collection $subjectAverages Moyennes par matière avec coefficient matière
     * @return float
     */
    public function calculateGeneralAverage(Collection $subjectAverages): float
    {
        if ($subjectAverages->isEmpty()) {
            return 0.0;
        }

        $gradeData = $subjectAverages->map(function ($average, $subjectId) use ($subjectAverages) {
            return [
                'grade' => $average,
                'coefficient' => $subjectAverages[$subjectId]['coefficient'] ?? 1.0,
            ];
        });

        return $this->calculateAverage($gradeData);
    }

    /**
     * Calculer le classement d'une classe
     *
     * @param Collection $studentAverages Collection avec student_id => average
     * @return Collection
     */
    public function calculateClassRanking(Collection $studentAverages): Collection
    {
        return $studentAverages->sortByDesc(function ($average) {
            return $average;
        })->values()->map(function ($average, $index) {
            return [
                'average' => $average,
                'rank' => $index + 1,
            ];
        });
    }

    /**
     * Déterminer les décisions de passage
     *
     * @param Collection $studentResults Résultats avec moyennes et contexte
     * @param array $passageRules Règles de passage spécifiques
     * @return Collection
     */
    public function determinePassageDecisions(Collection $studentResults, array $passageRules = []): Collection
    {
        return $studentResults->map(function ($student) use ($passageRules) {
            $generalAverage = $student['general_average'];
            $subjectAverages = $student['subject_averages'] ?? collect();

            // Appliquer les règles de passage
            $decision = $this->applyPassageRules($generalAverage, $subjectAverages, $passageRules);

            return [
                'student_id' => $student['student_id'],
                'general_average' => $generalAverage,
                'decision' => $decision,
                'mention' => $this->calculateMention($generalAverage),
                'details' => $student,
            ];
        });
    }

    /**
     * Appliquer les règles de passage
     *
     * @param float $generalAverage
     * @param Collection $subjectAverages
     * @param array $rules
     * @return string
     */
    protected function applyPassageRules(float $generalAverage, Collection $subjectAverages, array $rules): string
    {
        $thresholds = $this->getPassingThresholds();
        
        // Règle de base : moyenne générale
        if ($generalAverage >= $thresholds['pass']) {
            return 'admitted'; // Admis
        }

        // Règles de compensation si configurées
        if (isset($rules['allow_compensation']) && $rules['allow_compensation']) {
            $failingSubjects = $subjectAverages->filter(function ($avg) use ($thresholds) {
                return $avg < $thresholds['pass'];
            });

            // Compensation possible si pas plus de 2 matières en échec
            if ($failingSubjects->count() <= 2 && $generalAverage >= ($thresholds['pass'] - 2)) {
                return 'compensation'; // Admis par compensation
            }
        }

        // Règle de redoublement vs exclusion
        if ($generalAverage >= ($thresholds['pass'] - 4)) {
            return 'repeat'; // Redouble
        }

        return 'excluded'; // Exclu
    }

    /**
     * Générer un rapport d'évaluation complet
     *
     * @param Collection $classData Données de la classe
     * @return array
     */
    public function generateEvaluationReport(Collection $classData): array
    {
        $averages = $classData->pluck('general_average');
        
        return [
            'class_statistics' => [
                'students_count' => $classData->count(),
                'class_average' => $averages->avg(),
                'highest_average' => $averages->max(),
                'lowest_average' => $averages->min(),
                'median_average' => $averages->median(),
            ],
            'distribution' => [
                'excellent' => $averages->filter(fn($avg) => $avg >= 16)->count(),
                'very_good' => $averages->filter(fn($avg) => $avg >= 14 && $avg < 16)->count(),
                'good' => $averages->filter(fn($avg) => $avg >= 12 && $avg < 14)->count(),
                'pass' => $averages->filter(fn($avg) => $avg >= 10 && $avg < 12)->count(),
                'fail' => $averages->filter(fn($avg) => $avg < 10)->count(),
            ],
            'success_rate' => $classData->count() > 0 
                ? ($averages->filter(fn($avg) => $avg >= 10)->count() / $classData->count()) * 100
                : 0,
        ];
    }
}