<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EducationalConfigurationService;
use App\Services\Settings\PreUniversitySettingsService;
use App\Services\Settings\UniversitySettingsService;
use App\Repositories\EducationalSettingsRepository;

class EducationalSettingsSimpleTest extends TestCase
{
    public function test_services_instantiation()
    {
        $this->assertTrue(true);
        $this->assertInstanceOf(
            EducationalConfigurationService::class, 
            app(EducationalConfigurationService::class)
        );
    }

    public function test_repository_instantiation()
    {
        $this->assertInstanceOf(
            EducationalSettingsRepository::class, 
            app(EducationalSettingsRepository::class)
        );
    }

    public function test_service_provider_bindings()
    {
        // Vérifier que les services sont bien liés dans le conteneur
        $this->assertTrue(app()->bound(EducationalSettingsRepository::class));
        $this->assertTrue(app()->bound(EducationalConfigurationService::class));
        
        // Vérifier qu'on obtient des singletons
        $repo1 = app(EducationalSettingsRepository::class);
        $repo2 = app(EducationalSettingsRepository::class);
        $this->assertSame($repo1, $repo2);
        
        $service1 = app(EducationalConfigurationService::class);
        $service2 = app(EducationalConfigurationService::class);
        $this->assertSame($service1, $service2);
    }

    public function test_preuniversity_service_defaults()
    {
        // Test avec des données simulées
        $repository = $this->createMock(EducationalSettingsRepository::class);
        
        // Simuler les valeurs par défaut pour l'âge
        $repository->method('getValue')
            ->willReturnMap([
                [null, 'preuniversity', 'age_limits', 'general', null, [
                    'primaire' => ['min' => 6, 'max' => 12],
                    'college' => ['min' => 12, 'max' => 16],
                ]],
                [null, 'preuniversity', 'evaluation', 'thresholds', null, [
                    'excellent' => 16.0,
                    'tres_bien' => 14.0,
                    'bien' => 12.0,
                    'assez_bien' => 10.0,
                    'passable' => 8.0,
                    'echec' => 0.0,
                ]],
            ]);

        $school = (object) ['id' => 1, 'type' => 'preuniversity'];
        $service = new PreUniversitySettingsService($repository, $school);
        
        $ageLimits = $service->getAgeLimits();
        $this->assertIsArray($ageLimits);
        $this->assertArrayHasKey('primaire', $ageLimits);
        $this->assertEquals(6, $ageLimits['primaire']['min']);
        $this->assertEquals(12, $ageLimits['primaire']['max']);
        
        $thresholds = $service->getEvaluationThresholds();
        $this->assertIsArray($thresholds);
        $this->assertArrayHasKey('excellent', $thresholds);
        $this->assertEquals(16.0, $thresholds['excellent']);
    }

    public function test_university_service_defaults()
    {
        // Test avec des données simulées
        $repository = $this->createMock(EducationalSettingsRepository::class);
        
        // Simuler les valeurs par défaut pour l'université
        $repository->method('getValue')
            ->willReturnMap([
                [null, 'university', 'age_limits', 'general', null, [
                    'licence' => ['min' => 17, 'max' => 30],
                    'master' => ['min' => 21, 'max' => 35],
                ]],
                [null, 'university', 'lmd', 'standards', null, [
                    'licence' => [
                        'credits_total' => 180,
                        'duree_semestres' => 6,
                    ],
                    'master' => [
                        'credits_total' => 120,
                        'duree_semestres' => 4,
                    ],
                ]],
            ]);

        $school = (object) ['id' => 2, 'type' => 'university'];
        $service = new UniversitySettingsService($repository, $school);
        
        $ageLimits = $service->getAgeLimits();
        $this->assertIsArray($ageLimits);
        $this->assertArrayHasKey('licence', $ageLimits);
        $this->assertEquals(17, $ageLimits['licence']['min']);
        
        $lmdStandards = $service->getLMDStandards();
        $this->assertIsArray($lmdStandards);
        $this->assertArrayHasKey('licence', $lmdStandards);
        $this->assertEquals(180, $lmdStandards['licence']['credits_total']);
    }

    public function test_hierarchical_resolution_logic()
    {
        $repository = $this->createMock(EducationalSettingsRepository::class);
        
        // Simuler une résolution hiérarchique : école spécifique > type par défaut
        $repository->method('getValue')
            ->willReturnCallback(function ($schoolId, $schoolType, $category, $key, $educationalLevel) {
                if ($schoolId === 1) {
                    // École spécifique : seuil d'excellence plus élevé
                    return ['excellent' => 18.0, 'echec' => 0.0];
                }
                // Valeur par défaut pour le type
                return ['excellent' => 16.0, 'echec' => 0.0];
            });

        $schoolWithOverride = (object) ['id' => 1, 'type' => 'preuniversity'];
        $schoolWithDefaults = (object) ['id' => 2, 'type' => 'preuniversity'];

        $serviceOverride = new PreUniversitySettingsService($repository, $schoolWithOverride);
        $serviceDefault = new PreUniversitySettingsService($repository, $schoolWithDefaults);
        
        $thresholdsOverride = $serviceOverride->getEvaluationThresholds();
        $thresholdsDefault = $serviceDefault->getEvaluationThresholds();
        
        $this->assertEquals(18.0, $thresholdsOverride['excellent']); // École spécifique
        $this->assertEquals(16.0, $thresholdsDefault['excellent']);  // Valeur par défaut
    }

    public function test_configuration_service_factory_method()
    {
        $configService = app(EducationalConfigurationService::class);
        
        // Test avec mock school pour éviter les erreurs de type
        $preunivSchool = $this->createMock(\App\Models\School::class);
        $preunivSchool->method('getAttribute')->willReturn('preuniversity');
        
        $univSchool = $this->createMock(\App\Models\School::class);
        $univSchool->method('getAttribute')->willReturn('university');
        
        $preunivService = $configService->getSettingsService('preuniversity', $preunivSchool);
        $this->assertInstanceOf(PreUniversitySettingsService::class, $preunivService);
        
        $univService = $configService->getSettingsService('university', $univSchool);
        $this->assertInstanceOf(UniversitySettingsService::class, $univService);
    }
}