<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasSchoolContext;

class Department extends Model
{
    use HasSchoolContext;

    protected $fillable = [
        'school_id',
        'ufr_id',
        'name',
        'code',
        'short_name',
        'description',
        'head_of_department',
        'contact_email',
        'contact_phone',
        'office_location',
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
     * Relation avec l'UFR
     */
    public function ufr(): BelongsTo
    {
        return $this->belongsTo(UFR::class);
    }

    /**
     * Relation avec les programmes optimisée
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class)
                    ->with(['semesters:id,program_id,name,is_active'])
                    ->withCount(['semesters', 'activeSemesters']);
    }

    /**
     * Programmes actifs uniquement
     */
    public function activePrograms()
    {
        return $this->programs()->active();
    }

    /**
     * Semestres via les programmes
     */
    public function semesters()
    {
        return $this->hasManyThrough(
            Semester::class,
            Program::class,
            'department_id', // Foreign key sur programs table
            'program_id',    // Foreign key sur semesters table
            'id',           // Local key sur departments table
            'id'            // Local key sur programs table
        );
    }

    /**
     * Semestres actifs via les programmes
     */
    public function activeSemesters()
    {
        return $this->semesters()->active();
    }

    /**
     * Unités d'enseignement via programmes et semestres
     */
    public function courseUnits()
    {
        return $this->hasManyThrough(
            CourseUnit::class,
            Semester::class,
            'program_id',    // Foreign key depuis programs vers semesters
            'semester_id',   // Foreign key depuis semesters vers course_units
            'id',           // Local key sur departments table
            'id'            // Local key sur semesters table
        )->whereHas('semester.program', function($query) {
            $query->where('department_id', $this->id);
        });
    }

    /**
     * Scope pour les départements actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir le nom complet (UFR + Département)
     */
    public function getFullNameAttribute(): string
    {
        return $this->ufr->short_name . ' - ' . $this->name;
    }
}
