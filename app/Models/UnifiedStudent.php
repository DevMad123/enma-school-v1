<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasAcademicCalculations;

/**
 * Modèle unifié pour tous les étudiants
 * 
 * Utilise le polymorphisme pour supporter les profils PreUniversity et University
 * Centralise les données académiques communes
 */
class UnifiedStudent extends Model
{
    use HasAcademicCalculations;

    protected $fillable = [
        'person_id',
        'studentable_type',
        'studentable_id',
        'student_number',
        'academic_entry_date',
        'student_status',
        'financial_clearance',
        'last_payment_date',
        'academic_history',
        'overall_gpa',
        'total_credits_earned',
        'student_metadata',
    ];

    protected $casts = [
        'academic_entry_date' => 'date',
        'last_payment_date' => 'date',
        'academic_history' => 'array',
        'overall_gpa' => 'decimal:2',
        'total_credits_earned' => 'integer',
        'student_metadata' => 'array',
        'financial_clearance' => 'boolean',
    ];

    /**
     * Relation polymorphique vers le profil spécialisé
     */
    public function studentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation avec la personne de base
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Relation avec les notes unifiées
     */
    public function unifiedGrades(): HasMany
    {
        return $this->hasMany(UnifiedGrade::class);
    }

    /**
     * Relation avec les inscriptions préuniversitaires
     */
    public function preUniversityEnrollments(): HasMany
    {
        return $this->hasMany(PreUniversityEnrollment::class);
    }

    /**
     * Relation avec les inscriptions universitaires
     */
    public function universityEnrollments(): HasMany
    {
        return $this->hasMany(UniversityEnrollment::class);
    }

    /**
     * Relation avec l'école via Person
     */
    public function school()
    {
        return $this->person->school();
    }

    /**
     * Accessor pour les données personnelles
     */
    public function getPersonalDataAttribute()
    {
        return $this->person;
    }

    /**
     * Générer un numéro d'étudiant unique
     */
    public static function generateStudentNumber(string $context, int $schoolId): string
    {
        $prefix = match($context) {
            'preuniversity' => 'ELV',
            'university' => 'ETU',
            default => 'STU'
        };
        
        $year = now()->year;
        $lastNumber = static::whereHas('person', function($query) use ($schoolId) {
                               $query->where('school_id', $schoolId);
                           })
                           ->where('student_number', 'like', "{$prefix}{$year}%")
                           ->count() + 1;
        
        return sprintf('%s%d%05d', $prefix, $year, $lastNumber);
    }

    /**
     * Scope pour les étudiants actifs
     */
    public function scopeActive($query)
    {
        return $query->where('student_status', 'enrolled');
    }

    /**
     * Scope par contexte éducatif
     */
    public function scopeByEducationalContext($query, string $context)
    {
        return $query->where('studentable_type', match($context) {
            'preuniversity' => PreUniversityStudent::class,
            'university' => UniversityStudent::class,
            default => null
        });
    }

    /**
     * Scope par école
     */
    public function scopeBySchool($query, int $schoolId)
    {
        return $query->whereHas('person', function($q) use ($schoolId) {
            $q->where('school_id', $schoolId);
        });
    }

    /**
     * Vérifier le contexte éducatif
     */
    public function isPreUniversity(): bool
    {
        return $this->studentable_type === PreUniversityStudent::class;
    }

    /**
     * Vérifier le contexte universitaire
     */
    public function isUniversity(): bool
    {
        return $this->studentable_type === UniversityStudent::class;
    }

    /**
     * Calculer la moyenne générale
     */
    public function calculateOverallGpa(): float
    {
        $grades = $this->grades()
                      ->whereHas('unifiedEvaluation', function($query) {
                          $query->where('is_published', true);
                      })
                      ->where('grade_status', 'present')
                      ->whereNotNull('score')
                      ->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        if ($this->isUniversity()) {
            // Calcul GPA universitaire (moyenne pondérée par crédits ECTS)
            $totalPoints = 0;
            $totalCredits = 0;

            foreach ($grades as $grade) {
                $credits = $grade->ects_points ?? 1;
                $totalPoints += $grade->score * $credits;
                $totalCredits += $credits;
            }

            return $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0.0;
        } else {
            // Calcul moyenne préuniversitaire (pondérée par coefficient)
            $totalWeighted = 0;
            $totalCoefficients = 0;

            foreach ($grades as $grade) {
                $coefficient = $grade->unifiedEvaluation->coefficient ?? 1;
                $totalWeighted += $grade->score * $coefficient;
                $totalCoefficients += $coefficient;
            }

            return $totalCoefficients > 0 ? round($totalWeighted / $totalCoefficients, 2) : 0.0;
        }
    }

    /**
     * Mettre à jour la GPA globale
     */
    public function updateOverallGpa(): void
    {
        $this->update([
            'overall_gpa' => $this->calculateOverallGpa()
        ]);
    }

    /**
     * Vérifier la situation financière
     */
    public function isFinanciallyCleared(): bool
    {
        return $this->financial_clearance;
    }

    /**
     * Obtenir le profil spécialisé avec ses données
     */
    public function getSpecializedProfile()
    {
        return $this->studentable;
    }

    /**
     * Scope pour recherche
     */
    public function scopeSearch($query, string $search)
    {
        return $query->whereHas('person', function($q) use ($search) {
            $q->searchByName($search);
        })->orWhere('student_number', 'like', "%{$search}%");
    }
}