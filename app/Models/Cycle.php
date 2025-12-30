<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cycle extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Relation avec les niveaux
     */
    public function levels(): HasMany
    {
        return $this->hasMany(Level::class);
    }

    /**
     * Relation avec les classes (via les niveaux)
     */
    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }

    /**
     * Compter le nombre de niveaux dans ce cycle
     */
    public function getLevelsCountAttribute(): int
    {
        return $this->levels()->count();
    }
}
