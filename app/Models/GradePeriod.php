<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class GradePeriod extends Model
{
    protected $fillable = [
        'academic_year_id',
        'name',
        'type',
        'order',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec l'année académique
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relation avec les évaluations
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Relation avec les notes (via evaluations)
     */
    public function grades()
    {
        return $this->hasManyThrough(Grade::class, Evaluation::class);
    }

    /**
     * Scope pour la période active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les périodes par type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour les périodes par ordre
     */
    public function scopeByOrder($query, $order)
    {
        return $query->where('order', $order);
    }

    /**
     * Scope pour une année donnée
     */
    public function scopeForYear($query, $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }

    /**
     * Vérifie si la période est en cours
     */
    public function isCurrent(): bool
    {
        $now = Carbon::now();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    /**
     * Vérifie si la période est terminée
     */
    public function isCompleted(): bool
    {
        return $this->end_date < Carbon::now();
    }

    /**
     * Obtient la durée en jours
     */
    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
