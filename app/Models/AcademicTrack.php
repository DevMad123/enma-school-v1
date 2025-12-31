<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasSchoolContext;

class AcademicTrack extends Model
{
    use HasSchoolContext;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
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
     * Relation avec les classes
     */
    public function academicClasses(): HasMany
    {
        return $this->hasMany(SchoolClass::class, 'academic_track_id');
    }

    /**
     * Relation avec les matières/UE
     */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class, 'academic_track_id');
    }

    /**
     * Scope pour les filières actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope par école
     */
    public function scopeForSchool($query, $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }
}
