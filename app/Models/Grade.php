<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Grade extends Model
{
    use HasFactory;
    protected $fillable = [
        'evaluation_id',
        'student_id',
        'grade',
        'absent',
        'justification',
        'graded_by',
        'graded_at',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
        'absent' => 'boolean',
        'graded_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function gradedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Relations indirectes
     */
    public function subject()
    {
        return $this->hasOneThrough(Subject::class, Evaluation::class, 'id', 'id', 'evaluation_id', 'subject_id');
    }

    public function schoolClass()
    {
        return $this->hasOneThrough(SchoolClass::class, Evaluation::class, 'id', 'id', 'evaluation_id', 'class_id');
    }

    public function gradePeriod()
    {
        return $this->hasOneThrough(GradePeriod::class, Evaluation::class, 'id', 'id', 'evaluation_id', 'grade_period_id');
    }

    /**
     * Scopes
     */
    public function scopePresent($query)
    {
        return $query->where('absent', false);
    }

    public function scopeAbsent($query)
    {
        return $query->where('absent', true);
    }

    public function scopeGraded($query)
    {
        return $query->whereNotNull('grade');
    }

    public function scopePassing($query, $passingGrade = 10)
    {
        return $query->where('absent', false)
                    ->whereNotNull('grade')
                    ->where('grade', '>=', $passingGrade);
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->whereHas('evaluation', function($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        });
    }

    public function scopeForPeriod($query, $periodId)
    {
        return $query->whereHas('evaluation', function($q) use ($periodId) {
            $q->where('grade_period_id', $periodId);
        });
    }

    /**
     * Méthodes utilitaires
     */
    public function getWeightedValue(): float
    {
        if ($this->absent || !$this->grade) {
            return 0;
        }
        return $this->grade * $this->evaluation->coefficient;
    }

    public function getPercentage(): float
    {
        if ($this->absent || !$this->grade) {
            return 0;
        }
        return ($this->grade / $this->evaluation->max_grade) * 100;
    }

    public function isPassing($passingGrade = 10): bool
    {
        return !$this->absent && $this->grade && $this->grade >= $passingGrade;
    }

    public function isExcellent($excellentGrade = 16): bool
    {
        return !$this->absent && $this->grade && $this->grade >= $excellentGrade;
    }

    public function getGradeWithStatus(): string
    {
        if ($this->absent) {
            return 'ABS';
        }
        return $this->grade ? number_format($this->grade, 2) : 'N/A';
    }

    /**
     * Boot method pour enregistrer automatiquement les timestamps de notation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($grade) {
            if ($grade->grade !== null && !$grade->absent && !$grade->graded_at) {
                $grade->graded_at = Carbon::now();
            }
        });
    }
}
