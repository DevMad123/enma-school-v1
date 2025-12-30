<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'coefficient',
    ];

    /**
     * Relation many-to-many avec les niveaux
     */
    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'level_subject');
    }

    /**
     * Relation avec les affectations d'enseignants
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * Enseignants qui enseignent cette matière
     */
    public function teachers()
    {
        return $this->hasManyThrough(
            Teacher::class,
            TeacherAssignment::class,
            'subject_id',
            'id',
            'id',
            'teacher_id'
        );
    }

    /**
     * Classes où cette matière est enseignée
     */
    public function classes()
    {
        return $this->hasManyThrough(
            SchoolClass::class,
            TeacherAssignment::class,
            'subject_id',
            'id',
            'id',
            'class_id'
        );
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
     * Obtenir les évaluations pour une classe donnée
     */
    public function evaluationsForClass($classId)
    {
        return $this->evaluations()->forClass($classId);
    }

    /**
     * Obtenir les notes pour une classe et une période
     */
    public function gradesForClassAndPeriod($classId, $periodId)
    {
        return $this->grades()
            ->whereHas('evaluation', function($query) use ($classId, $periodId) {
                $query->where('class_id', $classId)
                      ->where('grade_period_id', $periodId);
            });
    }

    /**
     * Calculer la moyenne de classe pour cette matière
     */
    public function getClassAverageForPeriod($classId, $periodId): float
    {
        $grades = $this->gradesForClassAndPeriod($classId, $periodId)
            ->present()
            ->graded()
            ->with('evaluation')
            ->get();
            
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
     * Obtenir les meilleurs élèves dans cette matière
     */
    public function getBestStudentsForClass($classId, $periodId, $limit = 5)
    {
        return Student::whereHas('enrollments.schoolClass', function($query) use ($classId) {
                $query->where('id', $classId);
            })
            ->with('grades.evaluation')
            ->get()
            ->map(function($student) use ($periodId) {
                return [
                    'student' => $student,
                    'average' => $student->getAverageForSubject($this->id, $periodId)
                ];
            })
            ->sortByDesc('average')
            ->take($limit)
            ->values();
    }

    /**
     * Obtenir les statistiques de la matière pour une classe
     */
    public function getClassStatistics($classId, $periodId): array
    {
        $grades = $this->gradesForClassAndPeriod($classId, $periodId)
            ->present()
            ->graded()
            ->pluck('grade');
            
        if ($grades->isEmpty()) {
            return [
                'count' => 0,
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'passing_rate' => 0,
                'distribution' => [
                    'excellent' => 0,
                    'good' => 0,
                    'fair' => 0,
                    'passing' => 0,
                    'failing' => 0,
                ]
            ];
        }
        
        return [
            'count' => $grades->count(),
            'average' => round($grades->avg(), 2),
            'min' => $grades->min(),
            'max' => $grades->max(),
            'passing_rate' => round(($grades->filter(fn($g) => $g >= 10)->count() / $grades->count()) * 100, 2),
            'distribution' => [
                'excellent' => $grades->filter(fn($g) => $g >= 16)->count(),
                'good' => $grades->filter(fn($g) => $g >= 14 && $g < 16)->count(),
                'fair' => $grades->filter(fn($g) => $g >= 12 && $g < 14)->count(),
                'passing' => $grades->filter(fn($g) => $g >= 10 && $g < 12)->count(),
                'failing' => $grades->filter(fn($g) => $g < 10)->count(),
            ]
        ];
    }

    /**
     * Scope pour rechercher par code
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'LIKE', '%' . $name . '%');
    }

    /**
     * Scope pour les matières d'un niveau donné
     */
    public function scopeForLevel($query, $levelId)
    {
        return $query->whereHas('levels', function ($q) use ($levelId) {
            $q->where('levels.id', $levelId);
        });
    }

    /**
     * Nombre total d'affectations pour cette matière
     */
    public function getTotalAssignmentsAttribute(): int
    {
        return $this->teacherAssignments()->count();
    }

    /**
     * Nombre de niveaux où cette matière est enseignée
     */
    public function getTotalLevelsAttribute(): int
    {
        return $this->levels()->count();
    }

    /**
     * Vérifier si cette matière est enseignée dans un niveau donné
     */
    public function isTaughtInLevel($levelId): bool
    {
        return $this->levels()->where('levels.id', $levelId)->exists();
    }
}
