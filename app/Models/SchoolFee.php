<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description', 
        'amount',
        'school_class_id',
        'level_id',
        'cycle_id',
        'academic_year_id',
        'is_mandatory',
        'due_date',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_mandatory' => 'boolean',
        'due_date' => 'date'
    ];

    /**
     * Relation vers la classe
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Relation vers le niveau
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Relation vers le cycle
     */
    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    /**
     * Relation vers l'année académique
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relation vers les paiements
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Vérifie si les frais sont échus
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Calcule le montant total payé pour ces frais
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->where('status', 'confirmed')->sum('amount');
    }

    /**
     * Calcule le solde restant
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->amount - $this->total_paid;
    }

    /**
     * Vérifie si les frais sont entièrement payés
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_balance <= 0;
    }
}
