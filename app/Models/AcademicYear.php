<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = [
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
     * Relation avec les classes
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Relation avec les périodes de notation
     */
    public function gradePeriods(): HasMany
    {
        return $this->hasMany(GradePeriod::class);
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
        // Désactiver toutes les autres années
        self::query()->update(['is_active' => false]);
        
        // Activer celle-ci
        $this->update(['is_active' => true]);
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
