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
        'hours_personal',
        'description',
        'prerequisites',
        'learning_outcomes',
        'evaluation_method',
        'coefficient',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'prerequisites' => 'array',
        'learning_outcomes' => 'array',
        'coefficient' => 'decimal:2',
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
