<?php

namespace App\Repositories;

use App\Models\EducationalSetting;
use App\Models\DefaultEducationalSetting;
use App\Models\EducationalSettingAudit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EducationalSettingsRepository
{
    /**
     * Récupère une valeur de configuration avec résolution hiérarchique
     */
    public function getValue(
        ?int $schoolId,
        string $schoolType,
        string $category,
        string $key,
        ?string $educationalLevel = null,
        mixed $default = null
    ): mixed {
        $cacheKey = "edu_setting_{$schoolId}_{$schoolType}_{$category}_{$key}_{$educationalLevel}";
        
        return Cache::remember($cacheKey, 3600, function () use ($schoolId, $schoolType, $category, $key, $educationalLevel, $default) {
            // 1. Recherche spécifique à l'école
            if ($schoolId) {
                $setting = EducationalSetting::where('school_id', $schoolId)
                    ->where('school_type', $schoolType)
                    ->where('category', $category)
                    ->where('key', $key)
                    ->where('edu_level', $educationalLevel)
                    ->first();
                    
                if ($setting) {
                    return $setting->value;
                }
            }

            // 2. Recherche dans les paramètres par défaut
            $defaultSetting = DB::table('default_edu_settings')
                ->where('school_type', $schoolType)
                ->where('category', $category)
                ->where('key', $key)
                ->where('edu_level', $educationalLevel)
                ->first();
                
            if ($defaultSetting) {
                return json_decode($defaultSetting->value, true);
            }

            return $default;
        });
    }

    /**
     * Définit une valeur de configuration
     */
    public function setValue(
        ?int $schoolId,
        string $schoolType,
        string $category,
        string $key,
        mixed $value,
        ?string $educationalLevel = null,
        ?int $changedBy = null
    ): EducationalSetting {
        $data = [
            'school_id' => $schoolId,
            'school_type' => $schoolType,
            'edu_level' => $educationalLevel,
            'category' => $category,
            'key' => $key,
            'value' => $value,
            'changed_by' => $changedBy,
        ];

        $setting = EducationalSetting::updateOrCreate(
            [
                'school_id' => $schoolId,
                'school_type' => $schoolType,
                'edu_level' => $educationalLevel,
                'category' => $category,
                'key' => $key,
            ],
            $data
        );

        $this->clearCache($schoolId, $schoolType, $category, $key, $educationalLevel);
        
        return $setting;
    }

    /**
     * Récupère toutes les valeurs pour une école
     */
    public function getSchoolSettings(int $schoolId, string $schoolType): array
    {
        $settings = EducationalSetting::where('school_id', $schoolId)
            ->where('school_type', $schoolType)
            ->get()
            ->groupBy('category')
            ->map(function ($categorySettings) {
                return $categorySettings->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->value];
                })->toArray();
            })
            ->toArray();

        return $settings;
    }

    /**
     * Récupère les paramètres par défaut
     */
    public function getDefaultSettings(string $schoolType, ?string $educationalLevel = null): array
    {
        $query = DefaultEducationalSetting::where('school_type', $schoolType);
        
        if ($educationalLevel) {
            $query->where('edu_level', $educationalLevel);
        }

        return $query->get()
            ->groupBy('category')
            ->map(function ($categorySettings) {
                return $categorySettings->mapWithKeys(function ($setting) {
                    return [$setting->key => $setting->value];
                })->toArray();
            })
            ->toArray();
    }

    /**
     * Supprime un paramètre
     */
    public function deleteSetting(
        ?int $schoolId,
        string $schoolType,
        string $category,
        string $key,
        ?string $educationalLevel = null
    ): bool {
        $deleted = EducationalSetting::where('school_id', $schoolId)
            ->where('school_type', $schoolType)
            ->where('edu_level', $educationalLevel)
            ->where('category', $category)
            ->where('key', $key)
            ->delete();

        if ($deleted) {
            $this->clearCache($schoolId, $schoolType, $category, $key, $educationalLevel);
        }

        return $deleted > 0;
    }

    /**
     * Valide une valeur contre les règles par défaut
     */
    public function validateValue(
        string $schoolType,
        string $category,
        string $key,
        mixed $value,
        ?string $educationalLevel = null
    ): array {
        $defaultSetting = DefaultEducationalSetting::where('school_type', $schoolType)
            ->where('category', $category)
            ->where('key', $key)
            ->where('edu_level', $educationalLevel)
            ->first();

        if (!$defaultSetting || !$defaultSetting->validation_rules) {
            return [];
        }

        $errors = [];
        $rules = $defaultSetting->validation_rules;

        // Validation basique
        if (isset($rules['required']) && $rules['required'] && empty($value)) {
            $errors[] = 'This setting is required';
        }

        if (is_numeric($value)) {
            if (isset($rules['min']) && $value < $rules['min']) {
                $errors[] = "Value must be at least {$rules['min']}";
            }
            if (isset($rules['max']) && $value > $rules['max']) {
                $errors[] = "Value must not exceed {$rules['max']}";
            }
        }

        return $errors;
    }

    /**
     * Nettoie le cache
     */
    private function clearCache(?int $schoolId, string $schoolType, string $category, string $key, ?string $educationalLevel): void
    {
        $cacheKey = "edu_setting_{$schoolId}_{$schoolType}_{$category}_{$key}_{$educationalLevel}";
        Cache::forget($cacheKey);
    }

    /**
     * Exporte les paramètres d'une école
     */
    public function exportSettings(int $schoolId, string $schoolType): array
    {
        return $this->getSchoolSettings($schoolId, $schoolType);
    }

    /**
     * Importe les paramètres pour une école
     */
    public function importSettings(int $schoolId, string $schoolType, array $settings, ?int $changedBy = null): array
    {
        $imported = [];
        $errors = [];

        DB::transaction(function () use ($schoolId, $schoolType, $settings, $changedBy, &$imported, &$errors) {
            foreach ($settings as $category => $categorySettings) {
                foreach ($categorySettings as $key => $value) {
                    try {
                        $setting = $this->setValue($schoolId, $schoolType, $category, $key, $value, null, $changedBy);
                        $imported[] = "{$category}.{$key}";
                    } catch (\Exception $e) {
                        $errors[] = "Failed to import {$category}.{$key}: " . $e->getMessage();
                    }
                }
            }
        });

        return ['imported' => $imported, 'errors' => $errors];
    }

    /**
     * Valide un ensemble de paramètres
     */
    public function validateSettings(array $settings, string $schoolType, ?string $educationalLevel = null): array
    {
        // Pour l'instant, validation basique
        $errors = [];
        
        foreach ($settings as $category => $categorySettings) {
            if (!is_array($categorySettings)) {
                $errors[] = "Category {$category} must be an array";
                continue;
            }
            
            foreach ($categorySettings as $key => $value) {
                $validationErrors = $this->validateValue($schoolType, $category, $key, $value, $educationalLevel);
                $errors = array_merge($errors, $validationErrors);
            }
        }
        
        return $errors;
    }

    /**
     * Récupère les paramètres d'une école par catégorie
     */
    public function getSchoolSettingsByCategory(?int $schoolId, string $schoolType, string $category): array
    {
        $settings = [];
        
        if ($schoolId) {
            $results = EducationalSetting::where('school_id', $schoolId)
                ->where('school_type', $schoolType)
                ->where('category', $category)
                ->get();
                
            foreach ($results as $setting) {
                $settings[$setting->key] = $setting->value;
            }
        }
        
        return $settings;
    }

    /**
     * Récupère tous les paramètres d'une école
     */
    public function getAllSchoolSettings(?int $schoolId, string $schoolType): array
    {
        $settings = [];
        
        if ($schoolId) {
            $results = EducationalSetting::where('school_id', $schoolId)
                ->where('school_type', $schoolType)
                ->get();
                
            foreach ($results as $setting) {
                $settings[$setting->category][$setting->key] = $setting->value;
            }
        }
        
        return $settings;
    }

    /**
     * Supprime les paramètres personnalisés d'une école pour une catégorie
     */
    public function deleteSchoolSettings(int $schoolId, string $schoolType, string $category): bool
    {
        return EducationalSetting::where('school_id', $schoolId)
            ->where('school_type', $schoolType)
            ->where('category', $category)
            ->delete() > 0;
    }

    /**
     * Récupère les paramètres par défaut par catégorie
     */
    public function getDefaultSettingsByCategory(string $schoolType, string $category): array
    {
        $settings = [];
        
        $results = DB::table('default_edu_settings')
            ->where('school_type', $schoolType)
            ->where('category', $category)
            ->get();
            
        foreach ($results as $setting) {
            $settings[$setting->key] = json_decode($setting->value, true);
        }
        
        return $settings;
    }

    /**
     * Récupère les paramètres globaux par catégorie
     */
    public function getGlobalSettings(string $schoolType, string $category): array
    {
        return $this->getDefaultSettingsByCategory($schoolType, $category);
    }
}