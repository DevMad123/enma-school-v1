<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Modèle unifié pour toutes les évaluations
 * 
 * Gère les évaluations préuniversitaires et universitaires de manière unifiée
 * Support polymorphique pour Grade Period et Academic Period
 */
class UnifiedEvaluation extends Model
{
    protected $fillable = [
        'school_id',
        'educational_subject_id',
        'evaluation_context_type',
        'evaluation_context_id',
        'title',
        'description',
        'evaluation_type',
        'coefficient',
        'max_score',
        'min_passing_score',
        'evaluation_date',
        'duration',
        'location',
        'status',
        'is_published',
        'created_by',
        'validated_by',
        'validated_at',
        'educational_context',
        'evaluation_criteria',
        'instructions',
        'class_average',
        'highest_score',
        'lowest_score',
        'participants_count',
        'evaluation_metadata',
    ];

    protected $casts = [
        'coefficient' => 'decimal:2',
        'max_score' => 'decimal:2',
        'min_passing_score' => 'decimal:2',
        'evaluation_date' => 'datetime',
        'duration' => 'datetime:H:i',
        'is_published' => 'boolean',
        'validated_at' => 'datetime',
        'evaluation_criteria' => 'array',
        'class_average' => 'decimal:2',
        'highest_score' => 'decimal:2',
        'lowest_score' => 'decimal:2',
        'participants_count' => 'integer',
        'evaluation_metadata' => 'array',
    ];

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation avec la matière/UE
     */
    public function educationalSubject(): BelongsTo
    {
        return $this->belongsTo(EducationalSubject::class);
    }

    /**
     * Relation polymorphique avec le contexte d'évaluation (GradePeriod ou AcademicPeriod)
     */
    public function evaluationContext(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation avec le créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec le validateur
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Relation avec les notes
     */
    public function unifiedGrades(): HasMany
    {
        return $this->hasMany(UnifiedGrade::class);
    }

    /**
     * Scope pour les évaluations actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
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
     * Scope pour les évaluations publiées
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope par statut
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope par type d'évaluation
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('evaluation_type', $type);
    }

    /**
     * Scope pour les évaluations à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->where('evaluation_date', '>', now())
                    ->where('status', 'planned');
    }

    /**
     * Scope pour les évaluations en cours
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing')
                    ->whereDate('evaluation_date', today());
    }

    /**
     * Vérifier si c'est une évaluation préuniversitaire
     */
    public function isPreUniversity(): bool
    {
        return $this->educational_context === 'preuniversity';
    }

    /**
     * Vérifier si c'est une évaluation universitaire
     */
    public function isUniversity(): bool
    {
        return $this->educational_context === 'university';
    }

    /**
     * Calculer les statistiques de l'évaluation
     */
    public function calculateStatistics(): void
    {
        $grades = $this->unifiedGrades()
                      ->where('grade_status', 'present')
                      ->whereNotNull('score')
                      ->get();

        if ($grades->isEmpty()) {
            return;
        }

        $scores = $grades->pluck('score');
        
        $this->update([
            'class_average' => $scores->avg(),
            'highest_score' => $scores->max(),
            'lowest_score' => $scores->min(),
            'participants_count' => $grades->count()
        ]);
    }

    /**
     * Publier l'évaluation et ses notes
     */
    public function publish(): bool
    {
        if ($this->status !== 'graded') {
            return false;
        }

        $this->calculateStatistics();
        
        $this->update([
            'is_published' => true,
            'status' => 'validated'
        ]);

        return true;
    }

    /**
     * Annuler l'évaluation
     */
    public function cancel(string $reason = ''): void
    {
        $metadata = $this->evaluation_metadata ?? [];
        $metadata['cancellation'] = [
            'reason' => $reason,
            'cancelled_at' => now()->toDateTimeString(),
            'cancelled_by' => auth()->id()
        ];

        $this->update([
            'status' => 'cancelled',
            'evaluation_metadata' => $metadata
        ]);
    }

    /**
     * Démarrer l'évaluation
     */
    public function start(): bool
    {
        if ($this->status !== 'planned') {
            return false;
        }

        $this->update([
            'status' => 'ongoing'
        ]);

        return true;
    }

    /**
     * Terminer l'évaluation
     */
    public function complete(): bool
    {
        if ($this->status !== 'ongoing') {
            return false;
        }

        $this->update([
            'status' => 'completed'
        ]);

        return true;
    }

    /**
     * Marquer comme notée
     */
    public function markAsGraded(): void
    {
        $this->update(['status' => 'graded']);
    }

    /**
     * Obtenir le taux de participation
     */
    public function getParticipationRateAttribute(): float
    {
        $totalStudents = $this->getEligibleStudentsCount();
        $participants = $this->participants_count ?? 0;

        return $totalStudents > 0 ? round(($participants / $totalStudents) * 100, 2) : 0;
    }

    /**
     * Obtenir le taux de réussite
     */
    public function getSuccessRateAttribute(): float
    {
        $passedGrades = $this->unifiedGrades()
                           ->where('grade_status', 'present')
                           ->where('score', '>=', $this->min_passing_score)
                           ->count();

        $totalGrades = $this->participants_count ?? 0;

        return $totalGrades > 0 ? round(($passedGrades / $totalGrades) * 100, 2) : 0;
    }

    /**
     * Obtenir le nombre d'étudiants éligibles
     */
    protected function getEligibleStudentsCount(): int
    {
        // Cette méthode devrait être implémentée selon la logique métier
        // pour déterminer combien d'étudiants sont éligibles à cette évaluation
        return $this->educationalSubject
                   ->school
                   ->students()
                   ->active()
                   ->count();
    }

    /**
     * Vérifier si l'évaluation peut être modifiée
     */
    public function canBeModified(): bool
    {
        return in_array($this->status, ['planned']) && !$this->is_published;
    }

    /**
     * Vérifier si l'évaluation peut être supprimée
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'planned' && !$this->unifiedGrades()->exists();
    }

    /**
     * Dupliquer l'évaluation pour une autre période
     */
    public function duplicate(array $newData = []): self
    {
        $duplicate = $this->replicate();
        $duplicate->fill(array_merge([
            'status' => 'planned',
            'is_published' => false,
            'validated_by' => null,
            'validated_at' => null,
            'class_average' => null,
            'highest_score' => null,
            'lowest_score' => null,
            'participants_count' => 0
        ], $newData));
        $duplicate->save();

        return $duplicate;
    }

    /**
     * Scope pour recherche
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('educationalSubject', function($subQuery) use ($search) {
                  $subQuery->where('name', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope par date
     */
    public function scopeByDate($query, Carbon $date)
    {
        return $query->whereDate('evaluation_date', $date);
    }

    /**
     * Scope par période
     */
    public function scopeByDateRange($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('evaluation_date', [$start, $end]);
    }
}