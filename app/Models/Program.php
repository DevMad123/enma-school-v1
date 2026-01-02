<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasSchoolContext;

class Program extends Model
{
    use HasSchoolContext;

    protected $fillable = [
        'school_id',
        'department_id',
        'name',
        'code',
        'short_name',
        'level',
        'duration_semesters',
        'total_credits',
        'description',
        'objectives',
        'diploma_title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'objectives' => 'array',
    ];

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation avec le département
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relation avec les semestres
     */
    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class);
    }

    /**
     * Relation avec les semestres actifs uniquement
     */
    public function activeSemesters(): HasMany
    {
        return $this->semesters()->active();
    }

    /**
     * Relation avec le semestre actuel (en cours)
     */
    public function currentSemester()
    {
        return $this->semesters()->where('is_current', true)->first();
    }

    /**
     * Unités d'enseignement via les semestres
     */
    public function courseUnits()
    {
        return $this->hasManyThrough(
            CourseUnit::class,
            Semester::class,
            'program_id',    // Foreign key sur semesters table
            'semester_id',   // Foreign key sur course_units table
            'id',           // Local key sur programs table
            'id'            // Local key sur semesters table
        );
    }

    /**
     * Scope pour les programmes actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par niveau (licence, master, etc.)
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Obtenir le libellé du niveau
     */
    public function getLevelLabelAttribute(): string
    {
        return match($this->level) {
            'licence' => 'Licence',
            'master' => 'Master',
            'doctorat' => 'Doctorat',
            'dut' => 'DUT',
            'bts' => 'BTS',
            default => $this->level
        };
    }

    /**
     * Obtenir la durée en années
     */
    public function getDurationYearsAttribute(): float
    {
        return $this->duration_semesters / 2;
    }
}
