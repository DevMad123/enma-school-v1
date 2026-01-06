<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle pour les inscriptions préuniversitaires
 * 
 * Gère les inscriptions des élèves dans les classes
 * Remplace la relation many-to-many class_student
 */
class PreUniversityEnrollment extends Model
{
    protected $fillable = [
        'unified_student_id',
        'school_class_id',
        'academic_year_id',
        'academic_period_id',
        'enrollment_status',
        'enrollment_date',
        'effective_date',
        'completion_date',
        'is_repeater',
        'previous_class',
        'enrollment_notes',
        'enrollment_fees',
        'fees_paid',
        'payment_deadline',
        'admin_validated',
        'pedagogical_validated',
        'validated_by',
        'validated_at',
        'enrollment_metadata',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'effective_date' => 'date',
        'completion_date' => 'date',
        'is_repeater' => 'boolean',
        'enrollment_fees' => 'decimal:2',
        'fees_paid' => 'boolean',
        'payment_deadline' => 'date',
        'admin_validated' => 'boolean',
        'pedagogical_validated' => 'boolean',
        'validated_at' => 'datetime',
        'enrollment_metadata' => 'array',
    ];

    /**
     * Relation avec l'étudiant unifié
     */
    public function unifiedStudent(): BelongsTo
    {
        return $this->belongsTo(UnifiedStudent::class);
    }

    /**
     * Relation avec la classe
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * Relation avec l'année académique
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relation avec la période académique
     */
    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    /**
     * Relation avec le validateur
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Scope pour les inscriptions actives
     */
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'enrolled');
    }

    /**
     * Scope par statut
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('enrollment_status', $status);
    }

    /**
     * Scope pour l'année en cours
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereHas('academicYear', function($academicQuery) {
            $academicQuery->where('is_active', true);
        });
    }

    /**
     * Scope pour les redoublants
     */
    public function scopeRepeaters($query)
    {
        return $query->where('is_repeater', true);
    }

    /**
     * Scope pour les inscriptions validées
     */
    public function scopeValidated($query)
    {
        return $query->where('admin_validated', true)
                    ->where('pedagogical_validated', true);
    }

    /**
     * Scope pour les inscriptions en attente
     */
    public function scopePending($query)
    {
        return $query->where(function($q) {
            $q->where('admin_validated', false)
              ->orWhere('pedagogical_validated', false);
        });
    }

    /**
     * Vérifier si l'inscription est complètement validée
     */
    public function isFullyValidated(): bool
    {
        return $this->admin_validated && $this->pedagogical_validated;
    }

    /**
     * Vérifier si les frais sont payés
     */
    public function isFeePaid(): bool
    {
        return $this->fees_paid;
    }

    /**
     * Vérifier si l'inscription est active
     */
    public function isActive(): bool
    {
        return $this->enrollment_status === 'enrolled';
    }

    /**
     * Valider administrativement
     */
    public function validateAdmin(): void
    {
        $this->update([
            'admin_validated' => true,
            'validated_by' => auth()->id(),
            'validated_at' => now()
        ]);
    }

    /**
     * Valider pédagogiquement
     */
    public function validatePedagogical(): void
    {
        $this->update([
            'pedagogical_validated' => true,
            'validated_by' => auth()->id(),
            'validated_at' => now()
        ]);
    }

    /**
     * Activer l'inscription (après validation complète)
     */
    public function activate(): bool
    {
        if (!$this->isFullyValidated()) {
            return false;
        }

        $this->update([
            'enrollment_status' => 'enrolled',
            'effective_date' => now()
        ]);

        return true;
    }

    /**
     * Suspendre l'inscription
     */
    public function suspend(string $reason = ''): void
    {
        $metadata = $this->enrollment_metadata ?? [];
        $metadata['suspension'] = [
            'reason' => $reason,
            'suspended_at' => now()->toDateTimeString(),
            'suspended_by' => auth()->id()
        ];

        $this->update([
            'enrollment_status' => 'suspended',
            'enrollment_metadata' => $metadata
        ]);
    }

    /**
     * Transférer vers une autre classe
     */
    public function transferTo(int $newClassId, string $reason = ''): void
    {
        $metadata = $this->enrollment_metadata ?? [];
        $metadata['transfer'] = [
            'from_class' => $this->school_class_id,
            'to_class' => $newClassId,
            'reason' => $reason,
            'transferred_at' => now()->toDateTimeString(),
            'transferred_by' => auth()->id()
        ];

        $this->update([
            'school_class_id' => $newClassId,
            'enrollment_status' => 'transferred',
            'enrollment_metadata' => $metadata
        ]);
    }

    /**
     * Marquer les frais comme payés
     */
    public function markFeesAsPaid(string $paymentMethod = '', string $reference = ''): void
    {
        $metadata = $this->enrollment_metadata ?? [];
        $metadata['payment'] = [
            'method' => $paymentMethod,
            'reference' => $reference,
            'paid_at' => now()->toDateTimeString(),
            'processed_by' => auth()->id()
        ];

        $this->update([
            'fees_paid' => true,
            'enrollment_metadata' => $metadata
        ]);
    }

    /**
     * Calculer la durée d'inscription
     */
    public function getEnrollmentDurationAttribute(): int
    {
        $startDate = $this->effective_date ?? $this->enrollment_date;
        $endDate = $this->completion_date ?? now();
        
        return $startDate->diffInDays($endDate);
    }

    /**
     * Obtenir le statut financier
     */
    public function getFinancialStatusAttribute(): string
    {
        if ($this->enrollment_fees <= 0) {
            return 'gratuit';
        }

        if ($this->fees_paid) {
            return 'payé';
        }

        if ($this->payment_deadline && $this->payment_deadline->isPast()) {
            return 'en_retard';
        }

        return 'en_attente';
    }

    /**
     * Scope par classe
     */
    public function scopeByClass($query, int $classId)
    {
        return $query->where('school_class_id', $classId);
    }

    /**
     * Scope par année académique
     */
    public function scopeByAcademicYear($query, int $yearId)
    {
        return $query->where('academic_year_id', $yearId);
    }
}