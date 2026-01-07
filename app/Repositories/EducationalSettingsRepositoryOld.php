<?php

namespace App\Repositories;

use App\Models\EducationalSetting;
use App\Models\DefaultEducationalSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class EducationalSettingsRepository
{
    /**
     * Récupère une valeur de configuration avec priorité hiérarchique
     */
    public function getValue(
        ?int $schoolId,
        string $schoolType,
        string $category,
        string $key,
        ?string $educationalLevel = null,
        mixed $default = null
    ): mixed {
        // Priorité : École spécifique > Niveau éducatif global > Type d'école global > Défaut
        
        // 1. Recherche spécifique à l'école + niveau éducatif
        if ($schoolId && $educationalLevel) {
            $setting = EducationalSetting::forSchool($schoolId)
                ->forSchoolType($schoolType)
                ->forEducationalLevel($educationalLevel)
                ->byCategory($category)
                ->where('setting_key', $key)
                ->active()
                ->first();
                
            if ($setting) {
                return $setting->setting_value;
            }
        }

        // 2. Recherche spécifique à l'école (tout niveau)
        if ($schoolId) {
            $setting = EducationalSetting::forSchool($schoolId)
                ->forSchoolType($schoolType)
                ->whereNull('edu_level')
                ->byCategory($category)
                ->where('key', $key)
                ->first();
                
            if ($setting) {
                return $setting->setting_value;
            }
        }

        // 3. Recherche globale par niveau éducatif
        if ($educationalLevel) {
            $setting = EducationalSetting::whereNull('school_id')
                ->forSchoolType($schoolType)
                ->forEducationalLevel($educationalLevel)
                ->byCategory($category)
                ->where('setting_key', $key)
                ->active()
                ->first();
                
            if ($setting) {
                return $setting->setting_value;
            }
        }

        // 4. Recherche globale par type d'école
        $setting = EducationalSetting::whereNull('school_id')
            ->forSchoolType($schoolType)
            ->whereNull('educational_level')
            ->byCategory($category)
            ->where('setting_key', $key)
            ->active()
            ->first();
            
        if ($setting) {
            return $setting->setting_value;
        }

        // 5. Valeur par défaut du template
        $defaultSetting = DefaultEducationalSetting::forSchoolType($schoolType)
            ->forEducationalLevel($educationalLevel)
            ->byCategory($category)
            ->where('setting_key', $key)
            ->first();
            
        return $defaultSetting ? $defaultSetting->setting_value : $default;
    }

    /**
     * Met à jour ou crée un paramètre
     */
    public function setValue(
        ?int $schoolId,
        string $schoolType,
        string $category,
        string $key,
        mixed $value,
        ?string $educationalLevel = null,
        int $userId
    ): EducationalSetting {
        return EducationalSetting::updateOrCreate(
            [
                'school_id' => $schoolId,
                'school_type' => $schoolType,
                'educational_level' => $educationalLevel,
                'setting_category' => $category,
                'setting_key' => $key,
            ],
            [
                'setting_value' => $value,
                'is_active' => true,
                'updated_by' => $userId,
                'created_by' => $userId,
            ]
        );
    }

    /**
     * Récupère tous les paramètres d'une catégorie
     */
    public function getSettingsByCategory(
        ?int $schoolId,
        string $schoolType,
        string $category,
        ?string $educationalLevel = null
    ): array {
        $cacheKey = $this->getCacheKey($schoolId, $schoolType, $category, $educationalLevel);
        
        return Cache::remember($cacheKey, 3600, function () use ($schoolId, $schoolType, $category, $educationalLevel) {
            $settings = [];
            
            // Récupère d'abord les defaults
            $defaults = DefaultEducationalSetting::forSchoolType($schoolType)
                ->forEducationalLevel($educationalLevel)
                ->byCategory($category)
                ->get();
                
            foreach ($defaults as $default) {
                $settings[$default->setting_key] = $this->getValue(
                    $schoolId,
                    $schoolType,
                    $category,
                    $default->setting_key,
                    $educationalLevel,
                    $default->setting_value
                );
            }
            
            return $settings;
        });
    }

    /**
     * Récupère les paramètres globaux pour un type d'école
     */
    public function getGlobalSettings(string $schoolType, string $category): array
    {
        return $this->getSettingsByCategory(null, $schoolType, $category);
    }

    /**
     * Récupère les paramètres par défaut
     */
    public function getDefaultSettings(string $schoolType, string $category): array
    {
        return DefaultEducationalSetting::forSchoolType($schoolType)
            ->byCategory($category)
            ->get()
            ->pluck('setting_value', 'setting_key')
            ->toArray();
    }

    /**
     * Initialise les paramètres par défaut pour une école
     */
    public function initializeSchoolSettings(int $schoolId, string $schoolType): void
    {
        $defaults = DefaultEducationalSetting::forSchoolType($schoolType)->get();
        
        foreach ($defaults as $default) {
            EducationalSetting::firstOrCreate(
                [
                    'school_id' => $schoolId,
                    'school_type' => $schoolType,
                    'educational_level' => $default->educational_level,
                    'setting_category' => $default->setting_category,
                    'setting_key' => $default->setting_key,
                ],
                [
                    'setting_value' => $default->setting_value,
                    'is_active' => true,
                    'created_by' => auth()->id(),
                ]
            );
        }
    }

    /**
     * Valide un ensemble de paramètres
     */
    public function validateSettings(array $settings, string $schoolType, ?string $educationalLevel = null): array
    {
        $errors = [];
        
        foreach ($settings as $category => $categorySettings) {
            foreach ($categorySettings as $key => $value) {
                $defaultSetting = DefaultEducationalSetting::forSchoolType($schoolType)
                    ->forEducationalLevel($educationalLevel)
                    ->byCategory($category)
                    ->where('setting_key', $key)
                    ->first();
                    
                if ($defaultSetting) {
                    $validationErrors = $defaultSetting->validateValue($value);
                    if (!empty($validationErrors)) {
                        $errors["{$category}.{$key}"] = $validationErrors;
                    }
                }
            }
        }
        
        return $errors;
    }

    /**
     * Invalide le cache pour un paramètre
     */
    public function invalidateCache(?int $schoolId, string $schoolType, ?string $category = null): void
    {
        $pattern = $this->getCacheKey($schoolId, $schoolType, $category ?? '*');
        Cache::flush(); // Simplifié pour l'instant, peut être optimisé avec tags
    }

    /**
     * Génère une clé de cache
     */
    private function getCacheKey(?int $schoolId, string $schoolType, string $category, ?string $educationalLevel = null): string
    {
        $key = "educational_settings:" . 
               ($schoolId ?? 'global') . ":" . 
               $schoolType . ":" . 
               $category . ":" . 
               ($educationalLevel ?? 'all');
               
        return $key;
    }

    /**
     * Export des paramètres pour une école
     */
    public function exportSettings(?int $schoolId, string $schoolType): array
    {
        $query = EducationalSetting::forSchool($schoolId)
            ->forSchoolType($schoolType)
            ->active();
            
        return $query->get()
            ->groupBy('setting_category')
            ->map(function ($settings) {
                return $settings->pluck('setting_value', 'setting_key');
            })
            ->toArray();
    }

    /**
     * Import des paramètres pour une école
     */
    public function importSettings(?int $schoolId, string $schoolType, array $settings, int $userId): array
    {
        $imported = [];
        $errors = [];
        
        foreach ($settings as $category => $categorySettings) {
            foreach ($categorySettings as $key => $value) {
                try {
                    $setting = $this->setValue($schoolId, $schoolType, $category, $key, $value, null, $userId);
                    $imported[] = "{$category}.{$key}";
                } catch (\Exception $e) {
                    $errors["{$category}.{$key}"] = $e->getMessage();
                }
            }
        }
        
        return compact('imported', 'errors');
    }
}