<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'classes';
    
    protected $fillable = [
        'academic_year_id',
        'cycle_id',
        'level_id',
        'name',
        'capacity',
    ];

    /**
     * Relation avec l'année académique
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Relation avec le cycle
     */
    public function cycle(): BelongsTo
    {
        return $this->belongsTo(Cycle::class);
    }

    /**
     * Relation avec le niveau
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * Relation many-to-many avec les étudiants
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'class_student', 'class_id', 'student_id')
                    ->withTimestamps()
                    ->withPivot('assigned_at');
    }

    /**
     * Nom complet de la classe
     */
    public function getFullNameAttribute(): string
    {
        return $this->level->name . ' ' . $this->name . ' (' . $this->academicYear->name . ')';
    }

    /**
     * Nombre d'étudiants actuellement inscrits
     */
    public function getStudentsCountAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Places disponibles
     */
    public function getAvailableSpotsAttribute(): int
    {
        return $this->capacity - $this->students_count;
    }

    /**
     * Vérifier si la classe est pleine
     */
    public function isFull(): bool
    {
        return $this->students_count >= $this->capacity;
    }

    /**
     * Scope pour les classes de l'année en cours
     */
    public function scopeCurrentYear($query)
    {
        return $query->whereHas('academicYear', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope pour un cycle donné
     */
    public function scopeByCycle($query, $cycleId)
    {
        return $query->where('cycle_id', $cycleId);
    }

    /**
     * Relation avec les inscriptions
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'class_id');
    }

    /**
     * Inscriptions actives dans cette classe
     */
    public function activeEnrollments()
    {
        return $this->enrollments()->where('status', 'active');
    }

    /**
     * Étudiants inscrits via les inscriptions (plus précis que la relation many-to-many)
     */
    public function enrolledStudents()
    {
        return $this->hasManyThrough(
            Student::class,
            Enrollment::class,
            'class_id',
            'id',
            'id',
            'student_id'
        )->where('enrollments.status', 'active');
    }

    /**
     * Nombre d'étudiants inscrits via le système d'inscription
     */
    public function getEnrolledStudentsCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }

    /**
     * Relation avec les affectations d'enseignants
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class, 'class_id');
    }

    /**
     * Enseignants assignés à cette classe
     */
    public function assignedTeachers()
    {
        return $this->hasManyThrough(
            Teacher::class,
            TeacherAssignment::class,
            'class_id',
            'id',
            'id',
            'teacher_id'
        );
    }

    /**
     * Nombre d'enseignants assignés
     */
    public function getAssignedTeachersCountAttribute(): int
    {
        return $this->teacherAssignments()->count();
    }
}
