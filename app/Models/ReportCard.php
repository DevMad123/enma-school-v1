<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    protected $fillable = [
        'student_id',
        'academic_year_id',
        'grade_period_id',
        'school_class_id',
        'general_average',
        'class_rank',
        'total_students_in_class',
        'total_subjects',
        'subjects_passed',
        'attendance_rate',
        'decision',
        'mention',
        'observations',
        'status',
        'generated_at',
        'generated_by',
        'is_final',
    ];

    protected $casts = [
        'general_average' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
        'generated_at' => 'datetime',
        'is_final' => 'boolean',
    ];

    /**
     * Relations
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function gradePeriod(): BelongsTo
    {
        return $this->belongsTo(GradePeriod::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    public function scopeForPeriod($query, $periodId)
    {
        return $query->where('grade_period_id', $periodId);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Méthodes utilitaires
     */
    public function getMentionAttribute($value)
    {
        if ($this->general_average >= 16) return 'Très Bien';
        if ($this->general_average >= 14) return 'Bien';
        if ($this->general_average >= 12) return 'Assez Bien';
        if ($this->general_average >= 10) return 'Passable';
        return 'Insuffisant';
    }

    public function getDecisionAttribute($value)
    {
        return $this->general_average >= 10 ? 'admis' : 'ajourné';
    }

    public function getPassingRateAttribute()
    {
        return $this->total_subjects > 0 
            ? round(($this->subjects_passed / $this->total_subjects) * 100, 2)
            : 0;
    }

    /**
     * Calculs des moyennes par matière pour ce bulletin
     */
    public function getSubjectAverages()
    {
        return $this->student->grades()
            ->whereHas('evaluation', function($query) {
                $query->where('grade_period_id', $this->grade_period_id);
            })
            ->with(['evaluation.subject'])
            ->get()
            ->groupBy('evaluation.subject.id')
            ->map(function ($grades, $subjectId) {
                $subject = $grades->first()->evaluation->subject;
                $totalWeightedGrades = 0;
                $totalCoefficients = 0;

                foreach ($grades as $grade) {
                    if ($grade->grade !== null && !$grade->absent) {
                        $coefficient = $grade->evaluation->coefficient;
                        $totalWeightedGrades += $grade->grade * $coefficient;
                        $totalCoefficients += $coefficient;
                    }
                }

                $average = $totalCoefficients > 0 ? $totalWeightedGrades / $totalCoefficients : 0;

                return [
                    'subject' => $subject,
                    'average' => round($average, 2),
                    'coefficient' => $subject->coefficient ?? 1,
                    'grades_count' => $grades->count(),
                    'is_passed' => $average >= 10,
                ];
            });
    }

    /**
     * Génère ou recalcule toutes les données du bulletin
     */
    public function calculate()
    {
        $subjectAverages = $this->getSubjectAverages();
        
        // Calcul moyenne générale
        $totalWeightedAverages = 0;
        $totalCoefficients = 0;
        $subjectsPassed = 0;

        foreach ($subjectAverages as $subjectData) {
            $coefficient = $subjectData['coefficient'];
            $totalWeightedAverages += $subjectData['average'] * $coefficient;
            $totalCoefficients += $coefficient;
            
            if ($subjectData['is_passed']) {
                $subjectsPassed++;
            }
        }

        $generalAverage = $totalCoefficients > 0 
            ? round($totalWeightedAverages / $totalCoefficients, 2) 
            : 0;

        // Calcul du classement
        $classRank = $this->calculateClassRank($generalAverage);
        $totalStudentsInClass = $this->calculateTotalStudentsInClass();

        // Mise à jour des données
        $this->update([
            'general_average' => $generalAverage,
            'total_subjects' => $subjectAverages->count(),
            'subjects_passed' => $subjectsPassed,
            'class_rank' => $classRank,
            'total_students_in_class' => $totalStudentsInClass,
            'decision' => $generalAverage >= 10 ? 'admis' : 'ajourné',
            'generated_at' => now(),
        ]);

        return $this;
    }

    private function calculateClassRank($studentAverage)
    {
        // Calculer le rang dans la classe
        return ReportCard::where('school_class_id', $this->school_class_id)
            ->where('grade_period_id', $this->grade_period_id)
            ->where('general_average', '>', $studentAverage)
            ->count() + 1;
    }

    private function calculateTotalStudentsInClass()
    {
        return ReportCard::where('school_class_id', $this->school_class_id)
            ->where('grade_period_id', $this->grade_period_id)
            ->count();
    }
}
