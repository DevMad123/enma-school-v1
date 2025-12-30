<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Level extends Model
{
    protected $fillable = [
        'cycle_id',
        'name',
    ];

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
     * Relation many-to-many avec les matières
     */
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'level_subject');
    }

    /**
     * Nombre de matières enseignées à ce niveau
     */
    public function getSubjectsCountAttribute(): int
    {
        return $this->subjects()->count();
    }

    /**
     * Vérifier si une matière est enseignée à ce niveau
     */
    public function hasSubject($subjectId): bool
    {
        return $this->subjects()->where('subjects.id', $subjectId)->exists();
    }
}
