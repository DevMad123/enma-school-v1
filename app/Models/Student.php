<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'school_id', // Lien direct avec l'école
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
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation many-to-many avec les classes (optimisée)
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_student', 'student_id', 'class_id')
                    ->withTimestamps()
                    ->withPivot('assigned_at')
                    ->with(['level:id,name,cycle_id', 'academicYear:id,name,is_active'])
                    ->orderBy('academic_year_id', 'desc');
    }

    /**
     * Relation avec la classe actuelle (année en cours) - optimisée
     */
    public function currentClass()
    {
        return $this->classes()
                    ->whereHas('academicYear', function ($query) {
                        $query->where('is_active', true);
                    })
                    ->with(['level.cycle', 'academicYear'])
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
     * Relation avec les notes (optimisée avec eager loading)
     */
    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class)
                    ->with([
                        'evaluation:id,subject_id,coefficient,grade_period_id,title', 
                        'evaluation.subject:id,name,coefficient',
                        'evaluation.gradePeriod:id,name,type'
                    ])
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Relation avec les évaluations (via grades) - optimisée
     */
    public function evaluations()
    {
        return $this->hasManyThrough(
            Evaluation::class, 
            Grade::class, 
            'student_id',     // Foreign key sur grades table
            'id',             // Foreign key sur evaluations table
            'id',             // Local key sur students table
            'evaluation_id'   // Local key sur grades table
        )->with(['subject:id,name,coefficient', 'gradePeriod:id,name,type']);
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
     * Calculer la moyenne générale pour une période (optimisée)
     */
    public function getAverageForPeriod($periodId = null): float
    {
        // Utilisation d'une requête optimisée avec eager loading
        $query = $this->grades()
                      ->present()
                      ->graded()
                      ->with(['evaluation:id,coefficient,grade_period_id']);
        
        if ($periodId) {
            $query->forPeriod($periodId);
        }
        
        $grades = $query->get();
        
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
     * Calculer la moyenne pour une matière donnée (optimisée)
     */
    public function getAverageForSubject($subjectId, $periodId = null): float
    {
        // Utilisation d'une requête optimisée avec eager loading
        $query = $this->gradesForSubject($subjectId)
                      ->present()
                      ->graded()
                      ->with(['evaluation:id,coefficient,subject_id,grade_period_id']);
        
        if ($periodId) {
            $query->forPeriod($periodId);
        }
        
        $grades = $query->get();
        
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
     * Obtenir les statistiques de l'élève (optimisée)
     */
    public function getGradeStatistics($periodId = null): array
    {
        // Requête optimisée - on récupère seulement les notes, pas besoin des evaluations
        $query = $this->grades()->present()->graded()->select('grade');
        
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
     * Relation avec les inscriptions (optimisée)
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class)
                    ->with(['academicYear:id,name,is_active', 'schoolClass:id,name,level_id'])
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Inscription active pour l'année en cours (optimisée)
     */
    public function currentEnrollment()
    {
        return $this->enrollments()
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', true);
            })
            ->where('status', 'active')
            ->with(['academicYear', 'schoolClass.level.cycle'])
            ->first();
    }

    /**
     * Vérifier si l'étudiant est inscrit pour l'année en cours (corrigé)
     */
    public function isEnrolledCurrentYear(): bool
    {
        return $this->enrollments()
            ->whereHas('academicYear', function ($query) {
                $query->where('is_active', true);
            })
            ->where('status', 'active')
            ->exists();
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
     * Relation avec les bulletins (optimisée)
     */
    public function reportCards(): HasMany
    {
        return $this->hasMany(ReportCard::class)
                    ->with([
                        'academicYear:id,name,is_active',
                        'gradePeriod:id,name,type',
                        'schoolClass:id,name,level_id'
                    ])
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Bulletin pour une période donnée (optimisé)
     */
    public function reportCardForPeriod($periodId)
    {
        return $this->reportCards()
                    ->forPeriod($periodId)
                    ->with(['academicYear', 'gradePeriod', 'schoolClass.level'])
                    ->first();
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

        // S'assurer que $reportCard est une instance de ReportCard
        if ($reportCard instanceof ReportCard) {
            return $reportCard->calculate();
        }
        
        throw new \Exception('Erreur lors de la création ou récupération du bulletin');
    }

    /**
     * ================================
     * MÉTHODES OPTIMISÉES POUR PERFORMANCE
     * ================================
     */

    /**
     * Obtenir toutes les notes avec relations préchargées (pour éviter N+1)
     */
    public function gradesWithRelations()
    {
        return $this->grades()
                    ->with([
                        'evaluation' => function ($query) {
                            $query->select('id', 'subject_id', 'grade_period_id', 'coefficient', 'title');
                        },
                        'evaluation.subject' => function ($query) {
                            $query->select('id', 'name', 'coefficient');
                        },
                        'evaluation.gradePeriod' => function ($query) {
                            $query->select('id', 'name', 'type');
                        }
                    ])
                    ->select('id', 'student_id', 'evaluation_id', 'grade', 'present', 'created_at');
    }

    /**
     * Obtenir le résumé rapide des performances (1 seule requête)
     */
    public function getPerformanceSummary($periodId = null): array
    {
        $query = $this->grades()
                      ->selectRaw('
                          COUNT(*) as total_grades,
                          AVG(CASE WHEN present = 1 AND grade IS NOT NULL THEN grade END) as average_grade,
                          MIN(CASE WHEN present = 1 AND grade IS NOT NULL THEN grade END) as min_grade,
                          MAX(CASE WHEN present = 1 AND grade IS NOT NULL THEN grade END) as max_grade,
                          COUNT(CASE WHEN present = 1 AND grade IS NOT NULL AND grade >= 10 THEN 1 END) as passed_count,
                          COUNT(CASE WHEN present = 1 AND grade IS NOT NULL THEN 1 END) as graded_count
                      ');

        if ($periodId) {
            $query->whereHas('evaluation', function ($q) use ($periodId) {
                $q->where('grade_period_id', $periodId);
            });
        }

        $result = $query->first();

        return [
            'total_grades' => $result->total_grades ?? 0,
            'average_grade' => $result->average_grade ? round($result->average_grade, 2) : 0,
            'min_grade' => $result->min_grade ?? 0,
            'max_grade' => $result->max_grade ?? 0,
            'passing_rate' => $result->graded_count > 0 
                ? round(($result->passed_count / $result->graded_count) * 100, 2) 
                : 0,
        ];
    }

    /**
     * Vérification rapide de statut (optimisée)
     */
    public function isActiveStudent(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Obtenir le nombre de classes historiques (optimisé)
     */
    public function getTotalClassesCount(): int
    {
        return $this->classes()->count();
    }
}
