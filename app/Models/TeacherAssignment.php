<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherAssignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'academic_year_id',
        'class_id',
        'subject_id',
    ];

    /**
     * Relation avec l'enseignant
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Relation avec l'année académique
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relation avec la classe
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relation avec la matière
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relation avec les évaluations créées par cet enseignant
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
     * Obtenir les évaluations pour une période donnée
     */
    public function evaluationsForPeriod($periodId)
    {
        return $this->evaluations()->forPeriod($periodId);
    }

    /**
     * Obtenir les notes pour une période donnée
     */
    public function gradesForPeriod($periodId)
    {
        return $this->grades()
            ->whereHas('evaluation', function($query) use ($periodId) {
                $query->where('grade_period_id', $periodId);
            });
    }

    /**
     * Calculer la moyenne de la classe pour cet enseignant
     */
    public function getClassAverageForPeriod($periodId): float
    {
        $grades = $this->gradesForPeriod($periodId)
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
     * Obtenir le nombre d'évaluations créées
     */
    public function getEvaluationCount($periodId = null): int
    {
        $query = $this->evaluations();
        
        if ($periodId) {
            $query->forPeriod($periodId);
        }
        
        return $query->count();
    }

    /**
     * Vérifier si l'enseignant a des évaluations en attente
     */
    public function hasPendingEvaluations(): bool
    {
        return $this->evaluations()->scheduled()->count() > 0;
    }

    /**
     * Scope pour l'année académique en cours
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereHas('academicYear', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope pour un enseignant donné
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope pour une classe donnée
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope pour une année académique donnée
     */
    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Obtenir le nom complet de l'affectation
     */
    public function getFullNameAttribute(): string
    {
        $teacher = $this->teacher;
        $class = $this->schoolClass;
        $year = $this->academicYear;
        $subject = $this->subject;
        
        $subjectText = $subject ? " - {$subject->name}" : "";
        
        return "{$teacher->first_name} {$teacher->last_name} → {$class->level->name} {$class->name}{$subjectText} ({$year->name})";
    }
}
