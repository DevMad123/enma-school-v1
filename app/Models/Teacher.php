<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'specialization',
        'status',
        // MODULE A4 - Nouveaux champs
        'school_id',
        'employee_id',
        'hire_date',
        'qualifications',
        'teaching_subjects',
        // Champs universitaires
        'ufr_id',
        'department_id',
        'academic_rank',
        'research_interests',
        'office_location',
        'salary',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'teaching_subjects' => 'array',
    ];

    /**
     * Relation avec le modèle User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'école (MODULE A4)
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation avec UFR (universitaire)
     */
    public function ufr(): BelongsTo
    {
        return $this->belongsTo(UFR::class);
    }

    /**
     * Relation avec le département (universitaire)
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relation avec les programmes (universitaire)
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'teacher_program_assignments')
                    ->withPivot(['semester_id', 'course_unit_id', 'weekly_hours', 'is_active', 'assignment_type', 'start_date', 'end_date', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Accessor pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope pour les enseignants actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Relation avec les affectations
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * Affectations pour l'année en cours
     */
    public function currentAssignments()
    {
        return $this->assignments()->currentYear();
    }

    /**
     * Classes assignées pour l'année en cours
     */
    public function currentClasses()
    {
        return $this->hasManyThrough(
            SchoolClass::class,
            TeacherAssignment::class,
            'teacher_id',
            'id',
            'id',
            'class_id'
        )->whereHas('assignments.academicYear', function ($query) {
            $query->where('is_active', true);
        });
    }

    /**
     * Vérifier si l'enseignant a des affectations pour l'année en cours
     */
    public function hasCurrentAssignments(): bool
    {
        return $this->currentAssignments()->exists();
    }

    /**
     * Affectations actives (MODULE A4)
     */
    public function activeAssignments()
    {
        return $this->assignments()->where('is_active', true);
    }

    /**
     * Obtenir les matières enseignées par l'enseignant
     */
    public function getSubjectsAttribute()
    {
        return $this->assignments()
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->filter();
    }

    /**
     * Obtenir les classes enseignées par l'enseignant
     */
    public function getClassesAttribute()
    {
        return $this->assignments()
            ->with('schoolClass')
            ->get()
            ->pluck('schoolClass')
            ->unique('id')
            ->filter();
    }

    /**
     * Calculer la charge horaire totale
     */
    public function getTotalWeeklyHours(): int
    {
        return $this->activeAssignments()->sum('weekly_hours') ?? 0;
    }

    /**
     * Vérifier si l'enseignant peut être assigné à une nouvelle classe
     */
    public function canBeAssignedTo($classId, $subjectId, $academicYearId): bool
    {
        return !$this->assignments()
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Scope pour les enseignants d'une école donnée
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope pour les enseignants avec une spécialisation donnée
     */
    public function scopeWithSpecialization($query, $specialization)
    {
        return $query->where('specialization', $specialization);
    }
}
