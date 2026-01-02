<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasSchoolContext;

class CourseUnit extends Model
{
    use HasSchoolContext;

    protected $fillable = [
        'school_id',
        'semester_id',
        'name',
        'code',
        'short_name',
        'type',
        'credits',
        'hours_cm',
        'hours_td',
        'hours_tp',
        'hours_total',
        'hours_personal',
        'description',
        'prerequisites',
        'learning_outcomes',
        'evaluation_method',
        'coefficient',
        'is_active',
        // Champs pour synchronisation automatique avec ECUE
        'sync_credits_with_elements',
        'sync_hours_with_elements',
        'auto_sync_enabled',
        'elements_count',
        'hours_distribution',
        'last_element_sync',
        'last_job_sync',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'prerequisites' => 'array',
        'learning_outcomes' => 'array',
        'coefficient' => 'decimal:2',
        // Nouveaux casts pour synchronisation
        'sync_credits_with_elements' => 'boolean',
        'sync_hours_with_elements' => 'boolean',
        'auto_sync_enabled' => 'boolean',
        'hours_distribution' => 'array',
        'last_element_sync' => 'datetime',
        'last_job_sync' => 'datetime',
    ];

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation avec le semestre
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relation avec les éléments constitutifs (ECUE)
     */
    public function elements()
    {
        return $this->hasMany(CourseUnitElement::class);
    }

    /**
     * Relation avec les ECUE actifs uniquement
     */
    public function activeElements()
    {
        return $this->elements()->where('status', 'active');
    }

    /**
     * Synchroniser les données de l'UE depuis ses ECUE
     */
    public function syncFromElements(): void
    {
        if ($this->elements()->count() == 0) {
            return;
        }

        $elements = $this->elements()->where('status', 'active')->get();
        
        $totalCredits = $elements->sum('credits');
        $totalHoursCm = $elements->sum('hours_cm');
        $totalHoursTd = $elements->sum('hours_td');
        $totalHoursTp = $elements->sum('hours_tp');
        
        // Mise à jour sans déclencher les events pour éviter la boucle
        $this->updateQuietly([
            'hours_cm' => $totalHoursCm,
            'hours_td' => $totalHoursTd,
            'hours_tp' => $totalHoursTp,
            'hours_total' => $totalHoursCm + $totalHoursTd + $totalHoursTp,
        ]);
    }

    /**
     * Valider la cohérence entre UE et ECUE
     */
    public function validateEcueConsistency(): array
    {
        $errors = [];
        $elements = $this->elements()->where('status', 'active')->get();
        
        if ($elements->isEmpty()) {
            return $errors;
        }
        
        $totalCreditsEcue = $elements->sum('credits');
        $totalHoursEcue = $elements->sum('hours_total');
        
        // Vérifier les crédits
        if ($totalCreditsEcue != $this->credits) {
            $errors[] = "Total crédits ECUE ({$totalCreditsEcue}) ≠ Crédits UE ({$this->credits})";
        }
        
        // Vérifier les heures (avec tolérance de 5%)
        $tolerance = $this->hours_total * 0.05;
        if (abs($totalHoursEcue - $this->hours_total) > $tolerance) {
            $errors[] = "Total heures ECUE ({$totalHoursEcue}h) ≠ Heures UE ({$this->hours_total}h)";
        }
        
        return $errors;
    }

    /**
     * Vérifier si l'UE peut être supprimée
     */
    public function canBeDeleted(): bool
    {
        return $this->elements()->count() == 0;
    }

    /**
     * Scope pour les UE actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par type d'UE
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Obtenir le total des heures d'enseignement
     */
    public function getTotalTeachingHoursAttribute(): int
    {
        return $this->hours_cm + $this->hours_td + $this->hours_tp;
    }

    /**
     * Obtenir le total des heures de travail (enseignement + personnel)
     */
    public function getTotalWorkHoursAttribute(): int
    {
        return $this->total_teaching_hours + $this->hours_personal;
    }

    /**
     * Obtenir le nombre d'ECUE
     */
    public function getElementsCountAttribute(): int
    {
        return $this->elements()->count();
    }

    /**
     * Obtenir le nombre d'ECUE actifs
     */
    public function getActiveElementsCountAttribute(): int
    {
        return $this->activeElements()->count();
    }

    /**
     * Vérifier si l'UE a des ECUE
     */
    public function hasElements(): bool
    {
        return $this->elements_count > 0;
    }

    /**
     * Obtenir le libellé du type d'UE
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'obligatoire' => 'Obligatoire',
            'optionnel' => 'Optionnel',
            'libre' => 'Libre',
            default => $this->type
        };
    }

    /**
     * Obtenir le libellé de la méthode d'évaluation
     */
    public function getEvaluationMethodLabelAttribute(): string
    {
        return match($this->evaluation_method) {
            'controle_continu' => 'Contrôle continu',
            'examen_final' => 'Examen final',
            'mixte' => 'Mixte (CC + Examen)',
            default => $this->evaluation_method
        };
    }
}
