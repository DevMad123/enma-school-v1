<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'position',
        'phone',
        'status',
        // MODULE A4 - Nouveaux champs
        'school_id',
        'employee_id',
        'hire_date',
        'department',
        'responsibilities',
    ];

    protected $casts = [
        'hire_date' => 'date',
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
     * Accessor pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Scope pour le staff actif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour le staff d'une école donnée
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Scope pour le staff d'un département donné
     */
    public function scopeInDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope pour le staff avec une position donnée
     */
    public function scopeWithPosition($query, $position)
    {
        return $query->where('position', $position);
    }

    /**
     * Obtenir l'ancienneté en années
     */
    public function getYearsOfServiceAttribute(): int
    {
        if (!$this->hire_date) return 0;
        return \Carbon\Carbon::parse($this->hire_date)->diffInYears(now());
    }

    /**
     * Vérifier si le membre du staff est dans un département donné
     */
    public function isInDepartment(string $department): bool
    {
        return $this->department === $department;
    }
}
