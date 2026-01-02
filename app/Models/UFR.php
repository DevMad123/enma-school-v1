<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasSchoolContext;

class UFR extends Model
{
    use HasSchoolContext;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'short_name',
        'description',
        'dean_name',
        'contact_email',
        'contact_phone',
        'building',
        'address',
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
     * Relation avec les départements avec eager loading optimisé
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'ufr_id')
                    ->with(['programs:id,department_id,name,is_active'])
                    ->withCount(['programs', 'activePrograms']);
    }

    /**
     * Programmes via les départements (optimisé)
     */
    public function programs()
    {
        return $this->hasManyThrough(
            Program::class,
            Department::class,
            'ufr_id',      // Foreign key sur departments table
            'department_id', // Foreign key sur programs table
            'id',           // Local key sur ufrs table
            'id'            // Local key sur departments table
        );
    }

    /**
     * Programmes actifs uniquement
     */
    public function activePrograms()
    {
        return $this->programs()->active();
    }

    /**
     * Scope pour les UFR actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir le nombre total de programmes dans cette UFR
     */
    public function getTotalProgramsAttribute(): int
    {
        return $this->departments()->withCount('programs')->get()->sum('programs_count');
    }

    /**
     * Obtenir le nombre total d'étudiants dans cette UFR
     */
    public function getTotalStudentsAttribute(): int
    {
        // TODO: Implémenter après création du système d'inscription universitaire
        return 0;
    }
}
