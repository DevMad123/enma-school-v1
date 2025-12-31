<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasSchoolContext;
use Carbon\Carbon;

class Semester extends Model
{
    use HasSchoolContext;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'program_id',
        'name',
        'semester_number',
        'academic_level',
        'start_date',
        'end_date',
        'required_credits',
        'description',
        'is_current',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
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
     * Relation avec le programme
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Relation avec les unités d'enseignement
     */
    public function courseUnits(): HasMany
    {
        return $this->hasMany(CourseUnit::class);
    }

    /**
     * Scope pour les semestres actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour le semestre actuel
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Obtenir le nom du niveau académique (L1, L2, M1, etc.)
     */
    public function getAcademicLevelNameAttribute(): string
    {
        $levelMap = [
            1 => 'L1',
            2 => 'L2', 
            3 => 'L3',
            4 => 'M1',
            5 => 'M2',
            6 => 'D1',
            7 => 'D2',
            8 => 'D3'
        ];
        
        return $levelMap[$this->academic_level] ?? "Niveau {$this->academic_level}";
    }

    /**
     * Vérifier si le semestre est en cours
     */
    public function isOngoing(): bool
    {
        $now = Carbon::now()->startOfDay();
        return $now->between($this->start_date, $this->end_date);
    }
}
