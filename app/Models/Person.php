<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modèle de base pour toutes les personnes du système
 * 
 * Implémente l'architecture unifiée pour les données personnelles communes
 * Sert de base pour l'extension polymorphique vers Student, Staff, etc.
 */
class Person extends Model
{
    protected $fillable = [
        'user_id',
        'school_id',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'phone',
        'address',
        'email',
        'emergency_contact_name',
        'emergency_contact_phone',
        'matricule',
        'status',
        'registration_date',
        'metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * Relation avec le modèle User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'école
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Relation avec le profil étudiant (si applicable)
     */
    public function studentProfile(): HasOne
    {
        return $this->hasOne(UnifiedStudent::class);
    }

    /**
     * Accessor pour le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Générer un matricule unique
     */
    public static function generateMatricule(int $schoolId, string $prefix = 'PER'): string
    {
        $year = now()->year;
        $lastNumber = static::where('school_id', $schoolId)
                           ->where('matricule', 'like', "{$prefix}{$year}%")
                           ->count() + 1;
        
        return sprintf('%s%d%04d', $prefix, $year, $lastNumber);
    }

    /**
     * Scope pour les personnes actives
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope par école
     */
    public function scopeBySchool($query, int $schoolId)
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Vérifier si la personne est un étudiant
     */
    public function isStudent(): bool
    {
        return $this->studentProfile()->exists();
    }

    /**
     * Obtenir l'âge
     */
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    /**
     * Scope pour recherche par nom
     */
    public function scopeSearchByName($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"]);
        });
    }

    /**
     * Scope par statut
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}