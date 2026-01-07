<?php

namespace App\Traits;

use App\ValueObjects\EducationalContext;

trait HasEducationalSettings
{
    /**
     * Récupère une configuration éducative
     */
    public function getEducationalSetting(string $category, string $key, mixed $default = null): mixed
    {
        $context = app('educational.context');
        $settings = app('educational.settings');
        
        return $settings->getSetting("{$category}.{$key}", $default);
    }
    
    /**
     * Récupère toutes les configurations d'une catégorie
     */
    public function getEducationalSettingsCategory(string $category): array
    {
        $settings = app('educational.settings');
        return $settings->getSettingsByCategory($category);
    }
    
    /**
     * Vérifie si une configuration existe
     */
    public function hasEducationalSetting(string $category, string $key): bool
    {
        return $this->getEducationalSetting($category, $key) !== null;
    }
    
    /**
     * Récupère les seuils d'évaluation selon le contexte
     */
    public function getEvaluationThresholds(): array
    {
        $settings = app('educational.settings');
        return $settings->getEvaluationThresholds();
    }
    
    /**
     * Récupère la structure des frais selon le contexte
     */
    public function getFeeStructure(): array
    {
        $settings = app('educational.settings');
        return $settings->getFeeStructure();
    }
}