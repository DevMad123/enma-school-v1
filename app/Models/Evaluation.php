<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Evaluation extends Model
{
    protected $fillable = [
        'name',
        'type',
        'coefficient',
        'max_grade',
        'academic_year_id',
        'subject_id',
        'class_id',
        'teacher_assignment_id',
        'grade_period_id',
        'evaluation_date',
        'status',
        'description',
    ];

    protected $casts = [
        'coefficient' => 'decimal:2',
        'max_grade' => 'decimal:2',
        'evaluation_date' => 'date',
    ];

    /**
     * Relations
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacherAssignment(): BelongsTo
    {
        return $this->belongsTo(TeacherAssignment::class);
    }

    public function gradePeriod(): BelongsTo
    {
        return $this->belongsTo(GradePeriod::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeForPeriod($query, $periodId)
    {
        return $query->where('grade_period_id', $periodId);
    }

    /**
     * Méthodes utilitaires
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getClassAverage(): float
    {
        return $this->grades()
            ->whereNotNull('grade')
            ->where('absent', false)
            ->avg('grade') ?? 0;
    }

    public function getParticipationRate(): float
    {
        $totalGrades = $this->grades()->count();
        if ($totalGrades === 0) return 0;
        
        $presentGrades = $this->grades()->where('absent', false)->count();
        return round(($presentGrades / $totalGrades) * 100, 2);
    }

    public function getGradeDistribution(): array
    {
        $grades = $this->grades()
            ->whereNotNull('grade')
            ->where('absent', false)
            ->pluck('grade');
            
        return [
            'excellent' => $grades->filter(fn($g) => $g >= 16)->count(),
            'good' => $grades->filter(fn($g) => $g >= 14 && $g < 16)->count(),
            'fair' => $grades->filter(fn($g) => $g >= 12 && $g < 14)->count(),
            'passing' => $grades->filter(fn($g) => $g >= 10 && $g < 12)->count(),
            'failing' => $grades->filter(fn($g) => $g < 10)->count(),
        ];
    }
}
