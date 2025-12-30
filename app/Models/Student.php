<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name', 
        'gender',
        'date_of_birth',
        'phone',
        'address',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Relation avec le modèle User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation many-to-many avec les classes
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student', 'student_id', 'class_id')
                    ->withTimestamps()
                    ->withPivot('assigned_at');
    }

    /**
     * Relation avec la classe actuelle (année en cours)
     */
    public function currentClass()
    {
        return $this->classes()
                    ->whereHas('academicYear', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->first();
    }

    /**
     * Accessor pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope pour les étudiants actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Vérifier si l'étudiant est dans une classe pour l'année en cours
     */
    public function hasCurrentClass(): bool
    {
        return $this->currentClass() !== null;
    }

    /**
     * Relation avec les notes
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Relation avec les évaluations (via grades)
     */
    public function evaluations()
    {
        return $this->hasManyThrough(Evaluation::class, Grade::class, 'student_id', 'id', 'id', 'evaluation_id');
    }

    /**
     * Obtenir les notes pour une matière donnée
     */
    public function gradesForSubject($subjectId)
    {
        return $this->grades()->forSubject($subjectId);
    }

    /**
     * Obtenir les notes pour une période donnée
     */
    public function gradesForPeriod($periodId)
    {
        return $this->grades()->forPeriod($periodId);
    }

    /**
     * Calculer la moyenne générale pour une période
     */
    public function getAverageForPeriod($periodId = null): float
    {
        $query = $this->grades()->present()->graded();
        
        if ($periodId) {
            $query->forPeriod($periodId);
        }
        
        $grades = $query->with('evaluation')->get();
        
        if ($grades->isEmpty()) {
            return 0;
        }
        
        $totalWeightedGrades = 0;
        $totalCoefficients = 0;
        
        foreach ($grades as $grade) {
            $coefficient = $grade->evaluation->coefficient;
            $totalWeightedGrades += $grade->grade * $coefficient;
            $totalCoefficients += $coefficient;
        }
        
        return $totalCoefficients > 0 ? round($totalWeightedGrades / $totalCoefficients, 2) : 0;
    }

    /**
     * Calculer la moyenne pour une matière donnée
     */
    public function getAverageForSubject($subjectId, $periodId = null): float
    {
        $query = $this->gradesForSubject($subjectId)->present()->graded();
        
        if ($periodId) {
            $query->forPeriod($periodId);
        }
        
        $grades = $query->with('evaluation')->get();
        
        if ($grades->isEmpty()) {
            return 0;
        }
        
        $totalWeightedGrades = 0;
        $totalCoefficients = 0;
        
        foreach ($grades as $grade) {
            $coefficient = $grade->evaluation->coefficient;
            $totalWeightedGrades += $grade->grade * $coefficient;
            $totalCoefficients += $coefficient;
        }
        
        return $totalCoefficients > 0 ? round($totalWeightedGrades / $totalCoefficients, 2) : 0;
    }

    /**
     * Obtenir les statistiques de l'élève
     */
    public function getGradeStatistics($periodId = null): array
    {
        $query = $this->grades()->present()->graded();
        
        if ($periodId) {
            $query->forPeriod($periodId);
        }
        
        $grades = $query->pluck('grade');
        
        if ($grades->isEmpty()) {
            return [
                'count' => 0,
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'passing_rate' => 0,
            ];
        }
        
        return [
            'count' => $grades->count(),
            'average' => round($grades->avg(), 2),
            'min' => $grades->min(),
            'max' => $grades->max(),
            'passing_rate' => round(($grades->filter(fn($g) => $g >= 10)->count() / $grades->count()) * 100, 2),
        ];
    }

    /**
     * Relation avec les inscriptions
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Inscription active pour l'année en cours
     */
    public function currentEnrollment()
    {
        return $this->enrollments()
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', true);
            })
            ->where('status', 'active')
            ->first();
    }

    /**
     * Vérifier si l'étudiant est inscrit pour l'année en cours
     */
    public function isEnrolledCurrentYear(): bool
    {
        return $this->currentEnrollment() !== null;
    }

    /**
     * Obtenir toutes les classes via les inscriptions
     */
    public function enrolledClasses()
    {
        return $this->hasManyThrough(
            SchoolClass::class,
            Enrollment::class,
            'student_id',
            'id',
            'id',
            'class_id'
        );
    }

    /**
     * Relation avec les bulletins
     */
    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class);
    }

    /**
     * Bulletin pour une période donnée
     */
    public function reportCardForPeriod($periodId)
    {
        return $this->reportCards()->forPeriod($periodId)->first();
    }

    /**
     * Générer ou récupérer le bulletin pour une période
     */
    public function getOrCreateReportCard($periodId, $academicYearId = null, $classId = null)
    {
        $academicYearId = $academicYearId ?? AcademicYear::where('is_active', true)->first()?->id;
        $classId = $classId ?? $this->currentClass()?->id;

        if (!$academicYearId || !$classId) {
            throw new \Exception('Impossible de déterminer l\'année académique ou la classe');
        }

        $reportCard = $this->reportCards()
            ->where('grade_period_id', $periodId)
            ->where('academic_year_id', $academicYearId)
            ->first();

        if (!$reportCard) {
            $reportCard = $this->reportCards()->create([
                'grade_period_id' => $periodId,
                'academic_year_id' => $academicYearId,
                'school_class_id' => $classId,
                'status' => 'draft',
            ]);
        }

        return $reportCard->calculate();
    }
}
