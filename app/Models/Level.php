<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasSchoolContext;

class Level extends Model
{
    use HasFactory, HasSchoolContext;

    protected $fillable = [
        'school_id',
        'academic_year_id', 
        'cycle_id',
        'name',
        'code',
        'type',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

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
     * Relation avec les classes
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Relation avec les matières/UE (MODULE A3)
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Relation many-to-many avec les anciennes matières (legacy)
     */
    public function oldSubjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'level_subject');
    }

    /**
     * Scope pour les niveaux actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par école
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope par année académique
     */
    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope par type (secondaire/universitaire)
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Ordre pour affichage
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name');
    }

    /**
     * Nom complet avec le cycle
     */
    public function getFullNameAttribute(): string
    {
        return $this->cycle->name . ' - ' . $this->name;
    }

    /**
     * Compter le nombre de classes pour ce niveau
     */
    public function getClassesCountAttribute(): int
    {
        return $this->classes()->count();
    }

    /**
     * Nombre de matières enseignées à ce niveau (legacy)
     */
    public function getSubjectsCountAttribute(): int
    {
        return $this->oldSubjects()->count();
    }

    /**
     * Vérifier si une matière est enseignée à ce niveau (legacy)
     */
    public function hasSubject($subjectId): bool
    {
        return $this->oldSubjects()->where('subjects.id', $subjectId)->exists();
    }
}
