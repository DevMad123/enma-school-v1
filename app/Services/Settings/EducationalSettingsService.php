<?php

namespace App\Services\Settings;

use App\Repositories\EducationalSettingsRepository;
use App\Models\School;

abstract class EducationalSettingsService
{
    protected string $schoolType;
    protected ?School $school = null;
    protected ?string $educationalLevel = null;

    public function __construct(
        protected EducationalSettingsRepository $repository,
        ?School $school = null,
        ?string $educationalLevel = null
    ) {
        $this->school = $school;
        $this->educationalLevel = $educationalLevel;
    }

    /**
     * Méthodes abstraites à implémenter par les services spécialisés
     */
    abstract public function getAgeLimits(): array;
    abstract public function getRequiredDocuments(): array;
    abstract public function getEvaluationThresholds(): array;
    abstract public function getFeeStructure(): array;

    /**
     * Récupère un paramètre configuré
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        [$category, $settingKey] = $this->parseKey($key);
        
        return $this->repository->getValue(
            $this->school?->id,
            $this->schoolType,
            $category,
            $settingKey,
            $this->educationalLevel,
            $default
        );
    }

    /**
     * Met à jour un paramètre
     */
    public function setSetting(string $key, mixed $value): void
    {
        [$category, $settingKey] = $this->parseKey($key);
        
        $this->repository->setValue(
            $this->school?->id,
            $this->schoolType,
            $category,
            $settingKey,
            $value,
            $this->educationalLevel,
            auth()->id() ?? 1
        );
        
        // Invalide le cache
        $this->repository->invalidateCache($this->school?->id, $this->schoolType, $category);
    }

    /**
     * Récupère tous les paramètres d'une catégorie
     */
    public function getSettingsByCategory(string $category): array
    {
        return $this->repository->getSettingsByCategory(
            $this->school?->id,
            $this->schoolType,
            $category,
            $this->educationalLevel
        );
    }

    /**
     * Valide l'âge selon les limites configurées
     */
    public function validateAge(\Carbon\Carbon $birthDate, string $level): bool
    {
        $ageLimits = $this->getAgeLimits();
        
        if (!isset($ageLimits[$level])) {
            return true; // Pas de limite définie
        }
        
        $age = $birthDate->age;
        $limits = $ageLimits[$level];
        
        $minAge = $limits['min'] ?? 0;
        $maxAge = $limits['max'] ?? 100;
        
        return $age >= $minAge && $age <= $maxAge;
    }

    /**
     * Valide les documents requis
     */
    public function validateRequiredDocuments(array $documents, ?string $level = null): array
    {
        $required = $this->getRequiredDocuments();
        
        if ($level && isset($required[$level])) {
            $required = $required[$level];
        } else {
            $required = $required['general'] ?? [];
        }
        
        $missing = [];
        foreach ($required as $document) {
            if (!in_array($document, $documents)) {
                $missing[] = $document;
            }
        }
        
        return $missing;
    }

    /**
     * Calcule le statut d'évaluation selon les seuils
     */
    public function calculateGradeStatus(float $average): string
    {
        $thresholds = $this->getEvaluationThresholds();
        
        foreach ($thresholds as $status => $threshold) {
            if ($average >= $threshold) {
                return $status;
            }
        }
        
        return 'echec';
    }

    /**
     * Récupère les frais selon le niveau
     */
    public function getFeesForLevel(string $level): array
    {
        $feeStructure = $this->getFeeStructure();
        return $feeStructure[$level] ?? [];
    }

    /**
     * Initialise les paramètres par défaut pour cette école
     */
    public function initializeDefaultSettings(): void
    {
        if ($this->school) {
            $this->repository->initializeSchoolSettings($this->school->id, $this->schoolType);
        }
    }

    /**
     * Exporte tous les paramètres
     */
    public function exportSettings(): array
    {
        return $this->repository->exportSettings($this->school?->id, $this->schoolType);
    }

    /**
     * Parse une clé de configuration (category.key)
     */
    private function parseKey(string $key): array
    {
        $parts = explode('.', $key, 2);
        
        if (count($parts) !== 2) {
            throw new \InvalidArgumentException("Format de clé invalide. Utilisez 'category.key'");
        }
        
        return $parts;
    }
}