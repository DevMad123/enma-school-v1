<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle unifié pour toutes les notes
 * 
 * Gère les notes préuniversitaires et universitaires de manière unifiée
 * Support pour les différents systèmes d'évaluation
 */
class UnifiedGrade extends Model
{
    protected $fillable = [
        'school_id',
        'unified_student_id',
        'unified_evaluation_id',
        'score',
        'max_score',
        'grade_status',
        'educational_context',
        'comments',
        'detailed_scores',
        'is_validated',
        'graded_by',
        'validated_by',
        'graded_at',
        'validated_at',
        'ects_points',
        'lmd_grade',
        'ue_validated',
        'can_compensate',
        'weighted_score',
        'appreciation',
        'improvement_percentage',
        'teacher_feedback',
        'remedial_actions',
        'grade_metadata',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'detailed_scores' => 'array',
        'is_validated' => 'boolean',
        'graded_at' => 'datetime',
        'validated_at' => 'datetime',
        'ects_points' => 'decimal:2',
        'ue_validated' => 'boolean',
        'can_compensate' => 'boolean',
        'weighted_score' => 'decimal:2',
        'improvement_percentage' => 'integer',
        'grade_metadata' => 'array',
    ];

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation avec l'étudiant unifié
     */
    public function unifiedStudent(): BelongsTo
    {
        return $this->belongsTo(UnifiedStudent::class);
    }

    /**
     * Relation avec l'évaluation unifiée
     */
    public function unifiedEvaluation(): BelongsTo
    {
        return $this->belongsTo(UnifiedEvaluation::class);
    }

    /**
     * Relation avec l'enseignant qui a noté
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Relation avec l'enseignant qui a validé
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scope pour les notes présentes
     */
    public function scopePresent($query)
    {
        return $query->where('grade_status', 'present');
    }

    /**
     * Scope pour les notes validées
     */
    public function scopeValidated($query)
    {
        return $query->where('is_validated', true);
    }

    /**
     * Scope par contexte éducatif
     */
    public function scopeByEducationalContext($query, string $context)
    {
        return $query->where('educational_context', $context);
    }

    /**
     * Scope par école
     */
    public function scopeBySchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope pour les notes réussies
     */
    public function scopePassed($query)
    {
        return $query->whereColumn('score', '>=', function($query) {
            $query->select('min_passing_score')
                  ->from('unified_evaluations')
                  ->whereColumn('unified_evaluations.id', 'unified_grades.unified_evaluation_id');
        });
    }

    /**
     * Scope pour les notes échouées
     */
    public function scopeFailed($query)
    {
        return $query->whereColumn('score', '<', function($query) {
            $query->select('min_passing_score')
                  ->from('unified_evaluations')
                  ->whereColumn('unified_evaluations.id', 'unified_grades.unified_evaluation_id');
        });
    }

    /**
     * Vérifier si c'est une note préuniversitaire
     */
    public function isPreUniversity(): bool
    {
        return $this->educational_context === 'preuniversity';
    }

    /**
     * Vérifier si c'est une note universitaire
     */
    public function isUniversity(): bool
    {
        return $this->educational_context === 'university';
    }

    /**
     * Calculer la note pondérée
     */
    public function calculateWeightedScore(): float
    {
        if (!$this->score || !$this->unifiedEvaluation) {
            return 0.0;
        }

        $coefficient = $this->unifiedEvaluation->coefficient ?? 1;
        return round($this->score * $coefficient, 2);
    }

    /**
     * Calculer les points ECTS (pour universitaire)
     */
    public function calculateEctsPoints(): float
    {
        if (!$this->isUniversity() || !$this->score) {
            return 0.0;
        }

        $subject = $this->unifiedEvaluation->educationalSubject;
        $credits = $subject->ects_credits ?? 0;

        // Points ECTS = (note/20) * crédits
        return round(($this->score / 20) * $credits, 2);
    }

    /**
     * Déterminer le grade LMD (pour universitaire)
     */
    public function determineLmdGrade(): ?string
    {
        if (!$this->isUniversity() || !$this->score) {
            return null;
        }

        return UniversityStudent::calculateLmdGrade($this->score);
    }

    /**
     * Déterminer l'appréciation (pour préuniversitaire)
     */
    public function determineAppreciation(): ?string
    {
        if (!$this->isPreUniversity() || !$this->score) {
            return null;
        }

        return match(true) {
            $this->score >= 18 => 'excellent',
            $this->score >= 16 => 'tres_bien',
            $this->score >= 14 => 'bien',
            $this->score >= 12 => 'assez_bien',
            $this->score >= 10 => 'passable',
            $this->score >= 8 => 'insuffisant',
            default => 'mediocre'
        };
    }

    /**
     * Vérifier si la note est réussie
     */
    public function isPassed(): bool
    {
        if (!$this->score || !$this->unifiedEvaluation) {
            return false;
        }

        return $this->score >= $this->unifiedEvaluation->min_passing_score;
    }

    /**
     * Vérifier si l'UE est validée (pour universitaire)
     */
    public function isUeValidated(): bool
    {
        if (!$this->isUniversity()) {
            return false;
        }

        return $this->ue_validated === true;
    }

    /**
     * Vérifier si compensation possible (pour universitaire)
     */
    public function canCompensate(): bool
    {
        if (!$this->isUniversity()) {
            return false;
        }

        return $this->can_compensate === true && $this->score >= 8; // Seuil de compensation
    }

    /**
     * Mettre à jour automatiquement les champs calculés
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($grade) {
            // Calculer la note pondérée
            $grade->weighted_score = $grade->calculateWeightedScore();

            // Pour les notes universitaires
            if ($grade->isUniversity()) {
                $grade->ects_points = $grade->calculateEctsPoints();
                $grade->lmd_grade = $grade->determineLmdGrade();
                $grade->ue_validated = $grade->isPassed();
                $grade->can_compensate = $grade->score >= 8 && $grade->score < 10;
            }

            // Pour les notes préuniversitaires
            if ($grade->isPreUniversity()) {
                $grade->appreciation = $grade->determineAppreciation();
            }
        });
    }

    /**
     * Valider la note
     */
    public function validate(): void
    {
        $this->update([
            'is_validated' => true,
            'validated_by' => auth()->id(),
            'validated_at' => now()
        ]);
    }

    /**
     * Invalider la note
     */
    public function invalidate(string $reason = ''): void
    {
        $metadata = $this->grade_metadata ?? [];
        $metadata['invalidation'] = [
            'reason' => $reason,
            'invalidated_at' => now()->toDateTimeString(),
            'invalidated_by' => auth()->id()
        ];

        $this->update([
            'is_validated' => false,
            'validated_by' => null,
            'validated_at' => null,
            'grade_metadata' => $metadata
        ]);
    }

    /**
     * Ajouter un feedback enseignant
     */
    public function addTeacherFeedback(string $feedback): void
    {
        $this->update([
            'teacher_feedback' => $feedback
        ]);
    }

    /**
     * Ajouter des actions correctives
     */
    public function addRemedialActions(string $actions): void
    {
        $this->update([
            'remedial_actions' => $actions
        ]);
    }

    /**
     * Calculer le pourcentage d'amélioration
     */
    public function calculateImprovement(): ?int
    {
        // Logique pour calculer l'amélioration par rapport aux notes précédentes
        $previousGrade = static::where('unified_student_id', $this->unified_student_id)
                              ->whereHas('unifiedEvaluation', function($query) {
                                  $query->where('educational_subject_id', 
                                      $this->unifiedEvaluation->educational_subject_id);
                              })
                              ->where('id', '<', $this->id)
                              ->where('grade_status', 'present')
                              ->whereNotNull('score')
                              ->orderBy('id', 'desc')
                              ->first();

        if (!$previousGrade || !$this->score || !$previousGrade->score) {
            return null;
        }

        $improvement = (($this->score - $previousGrade->score) / $previousGrade->score) * 100;
        return round($improvement);
    }

    /**
     * Obtenir les statistiques de position dans la classe
     */
    public function getClassRankingStats(): array
    {
        $allGrades = static::where('unified_evaluation_id', $this->unified_evaluation_id)
                          ->where('grade_status', 'present')
                          ->whereNotNull('score')
                          ->orderBy('score', 'desc')
                          ->get();

        $rank = $allGrades->search(function($grade) {
            return $grade->id === $this->id;
        }) + 1;

        return [
            'rank' => $rank,
            'total_students' => $allGrades->count(),
            'percentile' => $allGrades->count() > 0 ? round((($allGrades->count() - $rank + 1) / $allGrades->count()) * 100, 2) : 0
        ];
    }

    /**
     * Scope pour recherche par étudiant
     */
    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('unified_student_id', $studentId);
    }

    /**
     * Scope pour recherche par évaluation
     */
    public function scopeForEvaluation($query, int $evaluationId)
    {
        return $query->where('unified_evaluation_id', $evaluationId);
    }

    /**
     * Scope par période
     */
    public function scopeByPeriod($query, string $period)
    {
        return $query->whereHas('unifiedEvaluation.evaluationContext', function($contextQuery) use ($period) {
            $contextQuery->where('name', $period);
        });
    }

    /**
     * Scope par matière
     */
    public function scopeBySubject($query, int $subjectId)
    {
        return $query->whereHas('unifiedEvaluation', function($evalQuery) use ($subjectId) {
            $evalQuery->where('educational_subject_id', $subjectId);
        });
    }
}