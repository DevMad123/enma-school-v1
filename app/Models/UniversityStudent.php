<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle spécialisé pour les étudiants universitaires
 * 
 * Gère les données spécifiques au système LMD universitaire
 * Relation polymorphique avec UnifiedStudent
 */
class UniversityStudent extends Model
{
    protected $fillable = [
        'university_matricule',
        'admission_type',
        'ufr_id',
        'department_id',
        'program_id',
        'current_level',
        'current_semester',
        'cumulative_gpa',
        'total_ects_credits',
        'required_credits',
        'semester_history',
        'ue_validations',
        'compensation_history',
        'bac_series',
        'bac_average',
        'bac_year',
        'previous_institution',
        'tuition_fees',
        'scholarship_recipient',
        'scholarship_type',
        'scholarship_amount',
        'academic_advisor',
        'on_probation',
        'probation_semesters',
        'remedial_actions',
        'research_area',
        'thesis_title',
        'supervisor',
        'thesis_defense_date',
        'thesis_status',
    ];

    protected $casts = [
        'cumulative_gpa' => 'decimal:2',
        'total_ects_credits' => 'integer',
        'required_credits' => 'integer',
        'semester_history' => 'array',
        'ue_validations' => 'array',
        'compensation_history' => 'array',
        'bac_average' => 'decimal:2',
        'bac_year' => 'integer',
        'tuition_fees' => 'decimal:2',
        'scholarship_recipient' => 'boolean',
        'scholarship_amount' => 'decimal:2',
        'on_probation' => 'boolean',
        'probation_semesters' => 'integer',
        'thesis_defense_date' => 'date',
    ];

    /**
     * Relation polymorphique inverse vers UnifiedStudent
     */
    public function student(): MorphOne
    {
        return $this->morphOne(UnifiedStudent::class, 'studentable');
    }

    /**
     * Relation avec l'UFR
     */
    public function ufr(): BelongsTo
    {
        return $this->belongsTo(UFR::class);
    }

    /**
     * Relation avec le département
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relation avec le programme
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Accessor pour les données personnelles via student
     */
    public function getPersonalDataAttribute()
    {
        return $this->student?->person;
    }

    /**
     * Relation avec les inscriptions universitaires
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(UniversityEnrollment::class, 'unified_student_id', 'student_id')
                   ->whereHas('unifiedStudent', function($query) {
                       $query->where('studentable_type', static::class);
                   });
    }

    /**
     * Générer un matricule universitaire
     */
    public static function generateMatricule(int $schoolId): string
    {
        $year = now()->year;
        $lastNumber = static::whereHas('student.person', function($query) use ($schoolId) {
                               $query->where('school_id', $schoolId);
                           })
                           ->where('university_matricule', 'like', "UNI{$year}%")
                           ->count() + 1;
        
        return sprintf('UNI%d%05d', $year, $lastNumber);
    }

    /**
     * Scope par niveau LMD
     */
    public function scopeByLevel($query, string $level)
    {
        return $query->where('current_level', $level);
    }

    /**
     * Scope par programme
     */
    public function scopeByProgram($query, int $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope pour les boursiers
     */
    public function scopeScholarshipRecipients($query)
    {
        return $query->where('scholarship_recipient', true);
    }

    /**
     * Scope pour les étudiants en probation
     */
    public function scopeOnProbation($query)
    {
        return $query->where('on_probation', true);
    }

    /**
     * Calculer le pourcentage de progression
     */
    public function getProgressPercentageAttribute(): float
    {
        return $this->required_credits > 0 
            ? round(($this->total_ects_credits / $this->required_credits) * 100, 2) 
            : 0;
    }

    /**
     * Vérifier l'éligibilité pour la prochaine année
     */
    public function isEligibleForNextLevel(): bool
    {
        $requiredCreditsForLevel = match($this->current_level) {
            'L1' => 60,  // Pour passer en L2
            'L2' => 120, // Pour passer en L3
            'L3' => 180, // Pour diplôme Licence
            'M1' => 60,  // Pour passer en M2 (Master 1 = 60 crédits)
            'M2' => 120, // Pour diplôme Master
            default => 0
        };

        return $this->total_ects_credits >= $requiredCreditsForLevel;
    }

    /**
     * Calculer le grade LMD basé sur la moyenne
     */
    public static function calculateLmdGrade(float $average): string
    {
        return match(true) {
            $average >= 18 => 'A+',
            $average >= 16 => 'A',
            $average >= 14 => 'B+',
            $average >= 12 => 'B',
            $average >= 11 => 'C+',
            $average >= 10 => 'C',
            $average >= 9 => 'D+',
            $average >= 8 => 'D',
            default => 'F'
        };
    }

    /**
     * Ajouter une validation d'UE
     */
    public function validateUE(int $ueId, float $grade, int $credits, string $semester): void
    {
        $validations = $this->ue_validations ?? [];
        
        $validations[$ueId] = [
            'grade' => $grade,
            'credits' => $credits,
            'semester' => $semester,
            'lmd_grade' => self::calculateLmdGrade($grade),
            'validated_at' => now()->toDateTimeString()
        ];

        $this->update([
            'ue_validations' => $validations,
            'total_ects_credits' => $this->total_ects_credits + $credits,
            'cumulative_gpa' => $this->recalculateGPA()
        ]);
    }

    /**
     * Recalculer le GPA cumulé
     */
    public function recalculateGPA(): float
    {
        $validations = $this->ue_validations ?? [];
        
        if (empty($validations)) {
            return 0.0;
        }

        $totalWeightedGrades = 0;
        $totalCredits = 0;

        foreach ($validations as $validation) {
            $totalWeightedGrades += $validation['grade'] * $validation['credits'];
            $totalCredits += $validation['credits'];
        }

        return $totalCredits > 0 ? round($totalWeightedGrades / $totalCredits, 2) : 0.0;
    }

    /**
     * Ajouter un semestre à l'historique
     */
    public function addSemesterToHistory(string $semester, array $results): void
    {
        $history = $this->semester_history ?? [];
        
        $history[$semester] = [
            'gpa' => $results['gpa'],
            'credits_earned' => $results['credits'],
            'ues_validated' => $results['ues'],
            'status' => $results['status'], // 'passed', 'failed', 'probation'
            'completed_at' => now()->toDateTimeString()
        ];

        $this->update(['semester_history' => $history]);
    }

    /**
     * Mettre en probation académique
     */
    public function putOnProbation(string $reason): void
    {
        $this->update([
            'on_probation' => true,
            'probation_semesters' => $this->probation_semesters + 1,
            'remedial_actions' => $this->remedial_actions . "\n" . now()->format('Y-m-d') . ": Probation - {$reason}"
        ]);
    }

    /**
     * Lever la probation
     */
    public function liftProbation(): void
    {
        $this->update([
            'on_probation' => false,
            'remedial_actions' => $this->remedial_actions . "\n" . now()->format('Y-m-d') . ": Probation levée - performance satisfaisante"
        ]);
    }

    /**
     * Vérifier l'éligibilité à la bourse
     */
    public function isEligibleForScholarship(): bool
    {
        // Critères basiques : GPA > 12 et pas en probation
        return $this->cumulative_gpa >= 12.0 && !$this->on_probation;
    }

    /**
     * Calculer les frais de scolarité effectifs (après bourse)
     */
    public function getEffectiveTuitionFeesAttribute(): float
    {
        if (!$this->scholarship_recipient || !$this->scholarship_amount) {
            return $this->tuition_fees;
        }

        return max(0, $this->tuition_fees - $this->scholarship_amount);
    }

    /**
     * Obtenir le niveau suivant
     */
    public function getNextLevel(): ?string
    {
        return match($this->current_level) {
            'L1' => 'L2',
            'L2' => 'L3',
            'L3' => 'M1',
            'M1' => 'M2',
            'M2' => 'D1',
            'D1' => 'D2',
            'D2' => 'D3',
            default => null
        };
    }

    /**
     * Promouvoir à l'année suivante
     */
    public function promoteToNextLevel(): bool
    {
        if (!$this->isEligibleForNextLevel()) {
            return false;
        }

        $nextLevel = $this->getNextLevel();
        if (!$nextLevel) {
            return false; // Déjà au niveau maximal
        }

        $this->update([
            'current_level' => $nextLevel,
            'current_semester' => 1 // Retour au semestre 1 du nouveau niveau
        ]);

        return true;
    }

    /**
     * Obtenir les statistiques de performance
     */
    public function getPerformanceStats(): array
    {
        return [
            'gpa' => $this->cumulative_gpa,
            'credits_progress' => $this->progress_percentage,
            'total_credits' => $this->total_ects_credits,
            'required_credits' => $this->required_credits,
            'current_level' => $this->current_level,
            'semester' => $this->current_semester,
            'on_probation' => $this->on_probation,
            'eligible_next_level' => $this->isEligibleForNextLevel(),
            'scholarship_status' => $this->scholarship_recipient,
            'financial_obligation' => $this->effective_tuition_fees
        ];
    }

    /**
     * Initier une procédure de thèse
     */
    public function initiateThesis(string $title, string $supervisor, string $researchArea): void
    {
        $this->update([
            'thesis_title' => $title,
            'supervisor' => $supervisor,
            'research_area' => $researchArea,
            'thesis_status' => 'in_progress'
        ]);
    }

    /**
     * Programmer la soutenance
     */
    public function scheduleThesisDefense(string $date): void
    {
        $this->update([
            'thesis_defense_date' => $date,
            'thesis_status' => 'submitted'
        ]);
    }

    /**
     * Valider la thèse
     */
    public function validateThesis(): void
    {
        $this->update([
            'thesis_status' => 'validated'
        ]);
    }
}