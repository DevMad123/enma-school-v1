<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle unifié pour les matières/UE du système éducatif
 * 
 * Centralise la gestion des matières préuniversitaires et UE universitaires
 * Utilise le polymorphisme pour les niveaux éducatifs
 */
class EducationalSubject extends Model
{
    protected $fillable = [
        'school_id',
        'educational_level_type',
        'educational_level_id',
        'name',
        'code',
        'description',
        'coefficient',
        'ects_credits',
        'volume_hours',
        'td_hours',
        'tp_hours',
        'subject_type',
        'evaluation_mode',
        'educational_context',
        'academic_domain',
        'prerequisites',
        'assigned_teachers',
        'coordinator_id',
        'is_active',
        'is_mandatory',
        'display_order',
        'semester_period',
        'subject_metadata',
    ];

    protected $casts = [
        'coefficient' => 'decimal:2',
        'ects_credits' => 'integer',
        'volume_hours' => 'integer',
        'td_hours' => 'integer',
        'tp_hours' => 'integer',
        'prerequisites' => 'array',
        'assigned_teachers' => 'array',
        'is_active' => 'boolean',
        'is_mandatory' => 'boolean',
        'display_order' => 'integer',
        'subject_metadata' => 'array',
    ];

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation polymorphique avec le niveau éducatif (Level ou Semester)
     */
    public function educationalLevel(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation avec le coordinateur
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'coordinator_id');
    }

    /**
     * Relation avec les évaluations
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(UnifiedEvaluation::class);
    }

    /**
     * Scope pour les matières actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Scope par type de matière
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('subject_type', $type);
    }

    /**
     * Scope pour les matières obligatoires
     */
    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Scope avec ordre d'affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Générer un code unique pour la matière/UE
     */
    public static function generateCode(string $context, string $baseName, int $levelId): string
    {
        $prefix = match($context) {
            'preuniversity' => 'MAT',
            'university' => 'UE',
            default => 'SUB'
        };

        $sanitizedName = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $baseName), 0, 3));
        $year = now()->year;
        
        return "{$prefix}_{$sanitizedName}_{$levelId}_{$year}";
    }

    /**
     * Vérifier si c'est une matière préuniversitaire
     */
    public function isPreUniversity(): bool
    {
        return $this->educational_context === 'preuniversity';
    }

    /**
     * Vérifier si c'est une UE universitaire
     */
    public function isUniversity(): bool
    {
        return $this->educational_context === 'university';
    }

    /**
     * Obtenir les enseignants assignés
     */
    public function getAssignedTeachersAttribute($value)
    {
        $teacherIds = json_decode($value, true) ?? [];
        return Teacher::whereIn('id', $teacherIds)->get();
    }

    /**
     * Assigner un enseignant
     */
    public function assignTeacher(int $teacherId): void
    {
        $assignedTeachers = $this->assigned_teachers ?? [];
        if (!in_array($teacherId, $assignedTeachers)) {
            $assignedTeachers[] = $teacherId;
            $this->update(['assigned_teachers' => $assignedTeachers]);
        }
    }

    /**
     * Retirer un enseignant
     */
    public function removeTeacher(int $teacherId): void
    {
        $assignedTeachers = array_diff($this->assigned_teachers ?? [], [$teacherId]);
        $this->update(['assigned_teachers' => array_values($assignedTeachers)]);
    }

    /**
     * Vérifier les prérequis
     */
    public function checkPrerequisites(UnifiedStudent $student): bool
    {
        $prerequisites = $this->prerequisites ?? [];
        
        if (empty($prerequisites)) {
            return true; // Pas de prérequis
        }

        // Vérifier si l'étudiant a validé toutes les matières prérequises
        $validatedSubjects = $student->grades()
                                   ->whereHas('unifiedEvaluation', function($query) use ($prerequisites) {
                                       $query->whereIn('educational_subject_id', $prerequisites)
                                             ->where('is_published', true);
                                   })
                                   ->where('grade_status', 'present')
                                   ->where('score', '>=', 10) // Note de passage
                                   ->distinct('unified_evaluation.educational_subject_id')
                                   ->count();

        return $validatedSubjects >= count($prerequisites);
    }

    /**
     * Calculer la charge horaire totale
     */
    public function getTotalHoursAttribute(): int
    {
        return ($this->volume_hours ?? 0) + ($this->td_hours ?? 0) + ($this->tp_hours ?? 0);
    }

    /**
     * Obtenir les statistiques de réussite
     */
    public function getSuccessStats(): array
    {
        $evaluations = $this->evaluations()
                          ->where('is_published', true)
                          ->with('unifiedGrades')
                          ->get();

        $totalGrades = 0;
        $passedGrades = 0;
        $totalScore = 0;

        foreach ($evaluations as $evaluation) {
            foreach ($evaluation->unifiedGrades as $grade) {
                if ($grade->grade_status === 'present' && $grade->score !== null) {
                    $totalGrades++;
                    $totalScore += $grade->score;
                    
                    if ($grade->score >= $evaluation->min_passing_score) {
                        $passedGrades++;
                    }
                }
            }
        }

        return [
            'total_evaluations' => $evaluations->count(),
            'total_grades' => $totalGrades,
            'passed_grades' => $passedGrades,
            'success_rate' => $totalGrades > 0 ? round(($passedGrades / $totalGrades) * 100, 2) : 0,
            'average_score' => $totalGrades > 0 ? round($totalScore / $totalGrades, 2) : 0,
        ];
    }

    /**
     * Scope pour recherche
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope par période semestrielle
     */
    public function scopeBySemesterPeriod($query, string $period)
    {
        return $query->where('semester_period', $period);
    }

    /**
     * Scope par domaine académique
     */
    public function scopeByAcademicDomain($query, string $domain)
    {
        return $query->where('academic_domain', $domain);
    }

    /**
     * Obtenir les matières du même domaine
     */
    public function getRelatedSubjects()
    {
        return static::where('academic_domain', $this->academic_domain)
                    ->where('id', '!=', $this->id)
                    ->where('educational_context', $this->educational_context)
                    ->active()
                    ->get();
    }

    /**
     * Vérifier si la matière peut être supprimée
     */
    public function canBeDeleted(): bool
    {
        // Ne peut pas être supprimée s'il y a des évaluations ou des notes
        return !$this->evaluations()->exists();
    }

    /**
     * Cloner la matière pour une nouvelle année
     */
    public function cloneForNewYear(int $newLevelId): self
    {
        $clone = $this->replicate();
        $clone->educational_level_id = $newLevelId;
        $clone->code = self::generateCode(
            $this->educational_context,
            $this->name,
            $newLevelId
        );
        $clone->save();

        return $clone;
    }
}