<?php

namespace App\Services;

use App\Repositories\EducationalSettingsRepository;
use App\Services\Settings\PreUniversitySettingsService;
use App\Services\Settings\UniversitySettingsService;
use App\Services\Settings\EducationalSettingsService;
use App\Models\School;

class EducationalConfigurationService
{
    public function __construct(
        private EducationalSettingsRepository $settingsRepository
    ) {}

    /**
     * Récupère le service de configuration approprié selon le type d'école
     */
    public function getSettingsService(string $schoolType, ?School $school = null, ?string $educationalLevel = null): EducationalSettingsService
    {
        return match($schoolType) {
            'preuniversity' => new PreUniversitySettingsService($this->settingsRepository, $school, $educationalLevel),
            'university' => new UniversitySettingsService($this->settingsRepository, $school, $educationalLevel),
            default => throw new \InvalidArgumentException("Type d'école non supporté : {$schoolType}"),
        };
    }

    /**
     * Initialise les paramètres par défaut pour une nouvelle école
     */
    public function initializeDefaultSettings(School $school): void
    {
        $settingsService = $this->getSettingsService($school->type, $school);
        $settingsService->initializeDefaultSettings();
    }

    /**
     * Valide un ensemble de paramètres selon le type d'école
     */
    public function validateSettings(array $settings, string $schoolType, ?string $educationalLevel = null): array
    {
        return $this->settingsRepository->validateSettings($settings, $schoolType, $educationalLevel);
    }

    /**
     * Export des paramètres d'une école
     */
    public function exportSettings(School $school): array
    {
        return $this->settingsRepository->exportSettings($school->id, $school->type);
    }

    /**
     * Import des paramètres pour une école
     */
    public function importSettings(School $school, array $settings, int $userId): array
    {
        // Validation avant import
        $errors = $this->validateSettings($settings, $school->type);
        
        if (!empty($errors)) {
            return ['errors' => $errors, 'imported' => []];
        }

        return $this->settingsRepository->importSettings($school->id, $school->type, $settings, $userId);
    }

    /**
     * Récupère les catégories de paramètres disponibles
     */
    public function getAvailableCategories(string $schoolType): array
    {
        $categories = [
            'age_limits' => 'Limites d\'âge',
            'documents' => 'Documents requis',
            'evaluation' => 'Paramètres d\'évaluation',
            'fees' => 'Structure des frais',
        ];

        if ($schoolType === 'preuniversity') {
            $categories = array_merge($categories, [
                'subjects' => 'Matières et coefficients',
                'bulletin' => 'Configuration des bulletins',
                'promotion' => 'Règles de passage',
            ]);
        } elseif ($schoolType === 'university') {
            $categories = array_merge($categories, [
                'lmd' => 'Standards LMD',
                'ects' => 'Système ECTS',
                'deliberation' => 'Délibérations et jurys',
                'honors' => 'Mentions et bourses',
                'internship' => 'Stages et mémoires',
            ]);
        }

        return $categories;
    }

    /**
     * Récupère les niveaux éducatifs disponibles
     */
    public function getEducationalLevels(string $schoolType): array
    {
        return match($schoolType) {
            'preuniversity' => [
                'prescolaire' => 'Préscolaire',
                'primaire' => 'Primaire',
                'college' => 'Collège',
                'lycee' => 'Lycée',
            ],
            'university' => [
                'licence' => 'Licence (L1-L3)',
                'master' => 'Master (M1-M2)',
                'doctorat' => 'Doctorat',
            ],
            default => [],
        };
    }

    /**
     * Génère un rapport de configuration pour une école
     */
    public function generateConfigurationReport(School $school): array
    {
        $settingsService = $this->getSettingsService($school->type, $school);
        $categories = $this->getAvailableCategories($school->type);
        
        $report = [
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
                'type' => $school->type,
                'generated_at' => now(),
            ],
            'settings' => [],
            'statistics' => [],
        ];

        foreach (array_keys($categories) as $category) {
            try {
                $categorySettings = $settingsService->getSettingsByCategory($category);
                $report['settings'][$category] = $categorySettings;
                $report['statistics'][$category] = count($categorySettings);
            } catch (\Exception $e) {
                $report['settings'][$category] = [];
                $report['statistics'][$category] = 0;
            }
        }

        $report['statistics']['total_settings'] = array_sum($report['statistics']);
        
        return $report;
    }

    /**
     * Compare les paramètres de deux écoles
     */
    public function compareSchoolSettings(School $school1, School $school2): array
    {
        if ($school1->type !== $school2->type) {
            throw new \InvalidArgumentException("Impossible de comparer des écoles de types différents");
        }

        $settings1 = $this->exportSettings($school1);
        $settings2 = $this->exportSettings($school2);

        $comparison = [
            'school1' => ['id' => $school1->id, 'name' => $school1->name],
            'school2' => ['id' => $school2->id, 'name' => $school2->name],
            'differences' => [],
            'only_in_school1' => [],
            'only_in_school2' => [],
        ];

        foreach ($settings1 as $category => $categorySettings) {
            foreach ($categorySettings as $key => $value) {
                if (!isset($settings2[$category][$key])) {
                    $comparison['only_in_school1'][] = "{$category}.{$key}";
                } elseif ($settings2[$category][$key] !== $value) {
                    $comparison['differences']["{$category}.{$key}"] = [
                        'school1' => $value,
                        'school2' => $settings2[$category][$key],
                    ];
                }
            }
        }

        foreach ($settings2 as $category => $categorySettings) {
            foreach ($categorySettings as $key => $value) {
                if (!isset($settings1[$category][$key])) {
                    $comparison['only_in_school2'][] = "{$category}.{$key}";
                }
            }
        }

        return $comparison;
    }

    /**
     * Synchronise les paramètres entre écoles du même type
     */
    public function syncSettings(School $sourceSchool, array $targetSchoolIds, array $categories = [], int $userId = null): array
    {
        $userId = $userId ?? auth()->id() ?? 1;
        $sourceSettings = $this->exportSettings($sourceSchool);
        
        if (!empty($categories)) {
            $sourceSettings = array_intersect_key($sourceSettings, array_flip($categories));
        }

        $results = [];
        
        foreach ($targetSchoolIds as $targetSchoolId) {
            $targetSchool = School::find($targetSchoolId);
            
            if (!$targetSchool || $targetSchool->type !== $sourceSchool->type) {
                $results[$targetSchoolId] = ['error' => 'École invalide ou type incompatible'];
                continue;
            }

            try {
                $importResult = $this->importSettings($targetSchool, $sourceSettings, $userId);
                $results[$targetSchoolId] = $importResult;
            } catch (\Exception $e) {
                $results[$targetSchoolId] = ['error' => $e->getMessage()];
            }
        }

        return $results;
    }
}