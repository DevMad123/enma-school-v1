<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AcademicYear extends Model
{
    use HasFactory;
    protected $fillable = [
        'school_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_current',
        'is_archived',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
        'is_archived' => 'boolean',
    ];

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation avec les périodes académiques
     */
    public function academicPeriods(): HasMany
    {
        return $this->hasMany(AcademicPeriod::class);
    }
    
    /**
     * Alias pour la rétrocompatibilité avec les grade periods
     */
    // public function gradePeriods(): HasMany
    // {
    //     return $this->hasMany(GradePeriod::class);
    // }

    /**
     * Relation avec les classes
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Relations MODULE A3 - Structure académique
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    /**
     * Relation avec les périodes de notation
     */
    public function gradePeriods(): HasMany
    {
        return $this->hasMany(GradePeriod::class);
    }

    /**
     * Scope pour l'année académique active d'une école
     */
    public function scopeActiveForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId)->where('is_active', true);
    }

    /**
     * Scope pour l'année académique courante d'une école
     */
    public function scopeCurrentForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId)->where('is_current', true);
    }

    /**
     * Scope pour l'année académique active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Méthode pour activer cette année académique
     */
    public function activate()
    {
        // Désactiver toutes les autres années de la même école
        if ($this->school_id) {
            self::query()
                ->where('school_id', $this->school_id)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);
        } else {
            // Fallback pour compatibilité avec l'ancien système
            self::query()->where('id', '!=', $this->id)->update(['is_active' => false]);
        }
        
        // Activer celle-ci
        $this->update(['is_active' => true]);
    }

    /**
     * Créer les périodes académiques selon le système de l'école
     */
    public function createDefaultPeriods()
    {
        if (!$this->school) {
            return;
        }

        $academicSystem = $this->school->academic_system;
        $periods = [];

        if ($academicSystem === 'trimestre') {
            $periods = [
                ['name' => 'Trimestre 1', 'order' => 1],
                ['name' => 'Trimestre 2', 'order' => 2],
                ['name' => 'Trimestre 3', 'order' => 3],
            ];
        } elseif ($academicSystem === 'semestre') {
            $periods = [
                ['name' => 'Semestre 1', 'order' => 1],
                ['name' => 'Semestre 2', 'order' => 2],
            ];
        }

        $startDate = $this->start_date;
        $endDate = $this->end_date;
        $totalDays = $startDate->diffInDays($endDate);
        
        foreach ($periods as $index => $period) {
            $periodDuration = intval($totalDays / count($periods));
            $periodStartDate = $startDate->copy()->addDays($index * $periodDuration);
            $periodEndDate = ($index === count($periods) - 1) 
                ? $endDate 
                : $periodStartDate->copy()->addDays($periodDuration - 1);

            $this->academicPeriods()->create([
                'name' => $period['name'],
                'short_name' => $academicSystem === 'trimestre' ? 'T' . $period['order'] : 'S' . $period['order'],
                'order' => $period['order'],
                'start_date' => $periodStartDate,
                'end_date' => $periodEndDate,
                'is_active' => false,
            ]);
        }
    }

    /**
     * Vérifier si l'année est en cours
     */
    public function isCurrent(): bool
    {
        $today = now()->toDateString();
        return $this->start_date <= $today && $this->end_date >= $today;
    }

    /**
     * Méthode statique pour récupérer l'année académique courante
     */
    public static function current()
    {
        return self::where('is_current', true)->first();
    }

    /**
     * Scope pour l'année académique courante
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope pour les années archivées
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope pour les années non archivées
     */
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Relation avec les inscriptions
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Inscriptions actives pour cette année
     */
    public function activeEnrollments()
    {
        return $this->enrollments()->where('status', 'active');
    }

    /**
     * Nombre total d'étudiants inscrits
     */
    public function getTotalEnrollmentsAttribute(): int
    {
        return $this->enrollments()->count();
    }

    /**
     * Nombre d'étudiants actifs
     */
    public function getActiveEnrollmentsCountAttribute(): int
    {
        return $this->activeEnrollments()->count();
    }

    /**
     * Relation avec les affectations d'enseignants
     */
    public function teacherAssignments(): HasMany
    {
        return $this->hasMany(TeacherAssignment::class);
    }

    /**
     * Nombre total d'affectations d'enseignants
     */
    public function getTotalAssignmentsAttribute(): int
    {
        return $this->teacherAssignments()->count();
    }
}
