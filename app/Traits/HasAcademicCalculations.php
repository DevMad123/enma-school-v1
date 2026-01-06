<?php

namespace App\Traits;

use App\Models\UnifiedGrade;
use App\Models\UnifiedEvaluation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Trait pour les calculs académiques unifiés
 * 
 * Fournit des méthodes communes pour calculer les moyennes,
 * GPA et autres métriques académiques selon le contexte
 */
trait HasAcademicCalculations
{
    /**
     * Calculer la moyenne générale pour une période donnée
     */
    public function calculateGeneralAverage(string $period = null, string $context = null): float
    {
        $grades = $this->getValidGrades($period, $context);
        
        if ($grades->isEmpty()) {
            return 0.0;
        }

        return $this->isUniversityContext($context) 
            ? $this->calculateUniversityGPA($grades)
            : $this->calculatePreUniversityAverage($grades);
    }

    /**
     * Calculer la moyenne préuniversitaire (pondérée par coefficient)
     */
    protected function calculatePreUniversityAverage(Collection $grades): float
    {
        $totalWeighted = 0;
        $totalCoefficients = 0;

        foreach ($grades as $grade) {
            $coefficient = $grade->unifiedEvaluation->coefficient ?? 1;
            $totalWeighted += $grade->score * $coefficient;
            $totalCoefficients += $coefficient;
        }

        return $totalCoefficients > 0 ? round($totalWeighted / $totalCoefficients, 2) : 0.0;
    }

    /**
     * Calculer le GPA universitaire (pondéré par crédits ECTS)
     */
    protected function calculateUniversityGPA(Collection $grades): float
    {
        $totalPoints = 0;
        $totalCredits = 0;

        foreach ($grades as $grade) {
            $credits = $grade->ects_points ?? 1;
            $totalPoints += $grade->score * $credits;
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
    }

    /**
     * Obtenir les notes valides pour les calculs
     */
    protected function getValidGrades(string $period = null, string $context = null): Collection
    {
        $query = $this->unifiedGrades()
                     ->with(['unifiedEvaluation.educationalSubject'])
                     ->whereHas('unifiedEvaluation', function($evalQuery) {
                         $evalQuery->where('is_published', true);
                     })
                     ->where('grade_status', 'present')
                     ->whereNotNull('score');

        if ($period) {
            $query->whereHas('unifiedEvaluation.evaluationContext', function($contextQuery) use ($period) {
                $contextQuery->where('name', $period);
            });
        }

        if ($context) {
            $query->where('educational_context', $context);
        }

        return $query->get();
    }

    /**
     * Vérifier si c'est un contexte universitaire
     */
    protected function isUniversityContext(?string $context = null): bool
    {
        if ($context) {
            return $context === 'university';
        }

        // Détecter automatiquement selon le type d'étudiant
        if (method_exists($this, 'studentable')) {
            return $this->isUniversity();
        }

        return false;
    }

    /**
     * Calculer les statistiques académiques complètes
     */
    public function getAcademicStats(string $period = null): array
    {
        $grades = $this->getValidGrades($period);
        
        if ($grades->isEmpty()) {
            return [
                'average' => 0.0,
                'total_grades' => 0,
                'passed_grades' => 0,
                'failed_grades' => 0,
                'success_rate' => 0.0,
                'subjects_count' => 0
            ];
        }

        $passed = $grades->filter(function($grade) {
            return $grade->score >= $grade->unifiedEvaluation->min_passing_score;
        });

        $uniqueSubjects = $grades->pluck('unifiedEvaluation.educational_subject_id')->unique();

        return [
            'average' => $this->calculateGeneralAverage($period),
            'total_grades' => $grades->count(),
            'passed_grades' => $passed->count(),
            'failed_grades' => $grades->count() - $passed->count(),
            'success_rate' => round(($passed->count() / $grades->count()) * 100, 2),
            'subjects_count' => $uniqueSubjects->count(),
            'highest_score' => $grades->max('score'),
            'lowest_score' => $grades->min('score')
        ];
    }

    /**
     * Calculer la progression académique
     */
    public function getAcademicProgression(): array
    {
        // Obtenir les moyennes par période
        $periods = $this->getAcademicPeriods();
        $progression = [];

        foreach ($periods as $period) {
            $average = $this->calculateGeneralAverage($period);
            $progression[$period] = $average;
        }

        return $progression;
    }

    /**
     * Obtenir les périodes académiques disponibles
     */
    protected function getAcademicPeriods(): array
    {
        return ['trimestre_1', 'trimestre_2', 'trimestre_3', 'semestre_1', 'semestre_2'];
    }

    /**
     * Vérifier l'éligibilité au passage
     */
    public function isEligibleForPromotion(): bool
    {
        $average = $this->calculateGeneralAverage();
        
        // Règles de base (à adapter selon les besoins)
        return $average >= 10.0;
    }

    /**
     * Calculer les moyennes par matière
     */
    public function getSubjectAverages(string $period = null): array
    {
        $grades = $this->getValidGrades($period);
        $subjectAverages = [];

        $gradesBySubject = $grades->groupBy('unifiedEvaluation.educational_subject_id');

        foreach ($gradesBySubject as $subjectId => $subjectGrades) {
            $subject = $subjectGrades->first()->unifiedEvaluation->educationalSubject;
            
            if ($this->isUniversityContext()) {
                $average = $this->calculateUniversityGPA($subjectGrades);
            } else {
                $average = $this->calculatePreUniversityAverage($subjectGrades);
            }

            $subjectAverages[] = [
                'subject_id' => $subjectId,
                'subject_name' => $subject->name,
                'average' => $average,
                'grades_count' => $subjectGrades->count(),
                'coefficient' => $subject->coefficient ?? 1,
                'credits' => $subject->ects_credits ?? 0
            ];
        }

        return $subjectAverages;
    }

    /**
     * Obtenir le rang dans la classe/promotion
     */
    public function getClassRank(string $period = null): array
    {
        // Cette méthode nécessiterait une logique plus complexe
        // pour identifier la "classe" ou la cohorte de comparaison
        
        return [
            'rank' => 1,
            'total_students' => 1,
            'percentile' => 100.0
        ];
    }
}