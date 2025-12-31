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
     * Relation avec les programmes
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
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
