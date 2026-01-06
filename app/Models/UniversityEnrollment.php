<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle pour les inscriptions universitaires
 * 
 * Gère les inscriptions des étudiants par programme/semestre
 * Support pour le processus de validation multi-niveaux LMD
 */
class UniversityEnrollment extends Model
{
    protected $fillable = [
        'unified_student_id',
        'program_id',
        'semester_id',
        'academic_year_id',
        'enrollment_status',
        'enrollment_type',
        'application_date',
        'admission_date',
        'enrollment_date',
        'semester_start_date',
        'semester_end_date',
        'credits_enrolled',
        'min_credits',
        'max_credits',
        'tuition_fees',
        'administrative_fees',
        'total_fees',
        'fees_paid',
        'payment_deadline',
        'payment_method',
        'academic_validation',
        'administrative_validation',
        'financial_validation',
        'final_validation',
        'academic_validator_id',
        'admin_validator_id',
        'financial_validator_id',
        'academic_validated_at',
        'admin_validated_at',
        'financial_validated_at',
        'final_validated_at',
        'prerequisites_checked',
        'prerequisites_met',
        'prerequisites_notes',
        'expected_gpa',
        'semester_gpa',
        'honor_roll',
        'academic_warning',
        'required_documents',
        'submitted_documents',
        'documents_complete',
        'enrollment_metadata',
        'special_conditions',
        'notes',
    ];

    protected $casts = [
        'application_date' => 'date',
        'admission_date' => 'date',
        'enrollment_date' => 'date',
        'semester_start_date' => 'date',
        'semester_end_date' => 'date',
        'credits_enrolled' => 'integer',
        'min_credits' => 'integer',
        'max_credits' => 'integer',
        'tuition_fees' => 'decimal:2',
        'administrative_fees' => 'decimal:2',
        'total_fees' => 'decimal:2',
        'fees_paid' => 'boolean',
        'payment_deadline' => 'date',
        'academic_validation' => 'boolean',
        'administrative_validation' => 'boolean',
        'financial_validation' => 'boolean',
        'final_validation' => 'boolean',
        'academic_validated_at' => 'datetime',
        'admin_validated_at' => 'datetime',
        'financial_validated_at' => 'datetime',
        'final_validated_at' => 'datetime',
        'prerequisites_checked' => 'array',
        'prerequisites_met' => 'boolean',
        'expected_gpa' => 'decimal:2',
        'semester_gpa' => 'decimal:2',
        'honor_roll' => 'boolean',
        'academic_warning' => 'boolean',
        'required_documents' => 'array',
        'submitted_documents' => 'array',
        'documents_complete' => 'boolean',
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
     * Relation avec le programme
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Relation avec le semestre
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Relation avec l'année académique
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relations avec les validateurs
     */
    public function academicValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'academic_validator_id');
    }

    public function adminValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_validator_id');
    }

    public function financialValidator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financial_validator_id');
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
     * Scope pour les inscriptions validées complètement
     */
    public function scopeFullyValidated($query)
    {
        return $query->where('final_validation', true);
    }

    /**
     * Scope pour les inscriptions en attente
     */
    public function scopePendingValidation($query)
    {
        return $query->where('final_validation', false);
    }

    /**
     * Scope par type d'inscription
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('enrollment_type', $type);
    }

    /**
     * Vérifier si l'inscription est complètement validée
     */
    public function isFullyValidated(): bool
    {
        return $this->academic_validation && 
               $this->administrative_validation && 
               $this->financial_validation &&
               $this->final_validation;
    }

    /**
     * Vérifier si les prérequis sont satisfaits
     */
    public function hasPrerequisitesMet(): bool
    {
        return $this->prerequisites_met;
    }

    /**
     * Vérifier si les documents sont complets
     */
    public function hasCompleteDocuments(): bool
    {
        return $this->documents_complete;
    }

    /**
     * Vérifier la charge académique
     */
    public function hasValidCredits(): bool
    {
        return $this->credits_enrolled >= $this->min_credits && 
               $this->credits_enrolled <= $this->max_credits;
    }

    /**
     * Validation académique
     */
    public function validateAcademic(): bool
    {
        if (!$this->hasPrerequisitesMet() || !$this->hasValidCredits()) {
            return false;
        }

        $this->update([
            'academic_validation' => true,
            'academic_validator_id' => auth()->id(),
            'academic_validated_at' => now()
        ]);

        $this->checkFinalValidation();
        return true;
    }

    /**
     * Validation administrative
     */
    public function validateAdministrative(): bool
    {
        if (!$this->hasCompleteDocuments()) {
            return false;
        }

        $this->update([
            'administrative_validation' => true,
            'admin_validator_id' => auth()->id(),
            'admin_validated_at' => now()
        ]);

        $this->checkFinalValidation();
        return true;
    }

    /**
     * Validation financière
     */
    public function validateFinancial(): bool
    {
        if (!$this->fees_paid) {
            return false;
        }

        $this->update([
            'financial_validation' => true,
            'financial_validator_id' => auth()->id(),
            'financial_validated_at' => now()
        ]);

        $this->checkFinalValidation();
        return true;
    }

    /**
     * Vérifier et marquer la validation finale
     */
    protected function checkFinalValidation(): void
    {
        if ($this->academic_validation && 
            $this->administrative_validation && 
            $this->financial_validation) {
            
            $this->update([
                'final_validation' => true,
                'final_validated_at' => now(),
                'enrollment_status' => 'enrolled'
            ]);
        }
    }

    /**
     * Calculer les frais totaux
     */
    public function calculateTotalFees(): void
    {
        $total = $this->tuition_fees + $this->administrative_fees;
        $this->update(['total_fees' => $total]);
    }

    /**
     * Vérifier les prérequis
     */
    public function checkPrerequisites(): bool
    {
        $student = $this->unifiedStudent;
        $program = $this->program;
        
        // Logique de vérification des prérequis selon le programme
        // Cette logique doit être adaptée selon les règles métier
        
        $met = true; // Simplifié pour l'exemple
        
        $this->update([
            'prerequisites_met' => $met,
            'prerequisites_checked' => [
                'checked_at' => now()->toDateTimeString(),
                'checked_by' => auth()->id()
            ]
        ]);

        return $met;
    }

    /**
     * Soumettre un document
     */
    public function submitDocument(string $documentType, string $filePath): void
    {
        $submitted = $this->submitted_documents ?? [];
        $submitted[$documentType] = [
            'file_path' => $filePath,
            'submitted_at' => now()->toDateTimeString(),
            'submitted_by' => auth()->id()
        ];

        $this->update(['submitted_documents' => $submitted]);
        $this->checkDocumentCompleteness();
    }

    /**
     * Vérifier la complétude des documents
     */
    protected function checkDocumentCompleteness(): void
    {
        $required = $this->required_documents ?? [];
        $submitted = array_keys($this->submitted_documents ?? []);

        $complete = empty(array_diff($required, $submitted));
        $this->update(['documents_complete' => $complete]);
    }

    /**
     * Mettre à jour le GPA semestriel
     */
    public function updateSemesterGpa(float $gpa): void
    {
        $this->update([
            'semester_gpa' => $gpa,
            'honor_roll' => $gpa >= 15.0, // Tableau d'honneur
            'academic_warning' => $gpa < 10.0 // Avertissement académique
        ]);
    }

    /**
     * Différer l'inscription
     */
    public function defer(string $reason, string $newDate): void
    {
        $metadata = $this->enrollment_metadata ?? [];
        $metadata['deferral'] = [
            'reason' => $reason,
            'new_date' => $newDate,
            'deferred_at' => now()->toDateTimeString(),
            'deferred_by' => auth()->id()
        ];

        $this->update([
            'enrollment_status' => 'deferred',
            'enrollment_metadata' => $metadata
        ]);
    }

    /**
     * Transférer vers un autre programme
     */
    public function transferTo(int $newProgramId, int $newSemesterId): void
    {
        $metadata = $this->enrollment_metadata ?? [];
        $metadata['transfer'] = [
            'from_program' => $this->program_id,
            'from_semester' => $this->semester_id,
            'to_program' => $newProgramId,
            'to_semester' => $newSemesterId,
            'transferred_at' => now()->toDateTimeString(),
            'transferred_by' => auth()->id()
        ];

        $this->update([
            'program_id' => $newProgramId,
            'semester_id' => $newSemesterId,
            'enrollment_status' => 'transferred',
            'enrollment_metadata' => $metadata
        ]);
    }

    /**
     * Obtenir le statut de progression
     */
    public function getProgressStatusAttribute(): string
    {
        $validations = [
            'academic' => $this->academic_validation,
            'administrative' => $this->administrative_validation,
            'financial' => $this->financial_validation
        ];

        $completed = array_sum($validations);
        $total = count($validations);

        return match($completed) {
            0 => 'non_commencé',
            $total => 'terminé',
            default => 'en_cours'
        };
    }

    /**
     * Scope par programme
     */
    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope par semestre
     */
    public function scopeBySemester($query, int $semesterId)
    {
        return $query->where('semester_id', $semesterId);
    }
}