<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'specialization',
        'status',
    ];

    /**
     * Relation avec le modèle User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
