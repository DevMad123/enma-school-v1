<?php

namespace App\ValueObjects;

use App\Models\School;
use App\Models\AcademicYear;

class EducationalContext
{
    public readonly School $school;
    public readonly string $school_type;
    public readonly ?string $educational_level;
    public readonly AcademicYear $academic_year;
    public readonly ?string $user_role;
    public readonly array $permissions;
    public readonly array $cached_settings;

    public function __construct(array $data)
    {
        $this->school = $data['school'];
        $this->school_type = $data['school_type'];
        $this->educational_level = $data['educational_level'] ?? null;
        $this->academic_year = $data['academic_year'];
        $this->user_role = $data['user_role'] ?? null;
        $this->permissions = $data['permissions'] ?? [];
        $this->cached_settings = $data['cached_settings'] ?? [];
    }

    /**
     * Vérifie si l'utilisateur a une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }

    /**
     * Vérifie si c'est un contexte préuniversitaire
     */
    public function isPreuniversity(): bool
    {
        return $this->school_type === 'preuniversity';
    }

    /**
     * Vérifie si c'est un contexte universitaire
     */
    public function isUniversity(): bool
    {
        return $this->school_type === 'university';
    }

    /**
     * Génère une clé de cache pour les paramètres
     */
    public function getSettingsCacheKey(string $category = null): string
    {
        $key = "settings:{$this->school->id}:{$this->school_type}";
        if ($this->educational_level) {
            $key .= ":{$this->educational_level}";
        }
        if ($category) {
            $key .= ":{$category}";
        }
        return $key;
    }

    /**
     * Récupère un paramètre depuis le cache du contexte
     */
    public function getCachedSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->cached_settings, $key, $default);
    }

    /**
     * Vérifie si le contexte est valide pour une opération
     */
    public function canAccess(string $resource, ?string $action = null): bool
    {
        $permission = $action ? "{$resource}.{$action}" : $resource;
        return $this->hasPermission($permission) || $this->hasPermission('admin.all');
    }

    /**
     * Récupère les informations de debug du contexte
     */
    public function toDebugArray(): array
    {
        return [
            'school_id' => $this->school->id,
            'school_name' => $this->school->name,
            'school_type' => $this->school_type,
            'educational_level' => $this->educational_level,
            'academic_year_id' => $this->academic_year->id,
            'user_role' => $this->user_role,
            'permissions_count' => count($this->permissions),
            'cached_settings_count' => count($this->cached_settings),
        ];
    }
}