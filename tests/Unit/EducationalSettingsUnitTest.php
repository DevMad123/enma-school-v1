<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\EducationalConfigurationService;
use App\Services\PreUniversitySettingsService;
use App\Services\UniversitySettingsService;
use App\Repositories\EducationalSettingsRepository;
use App\Models\DefaultEducationalSetting;

class EducationalSettingsUnitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_can_instantiate_configuration_service()
    {
        $service = app(EducationalConfigurationService::class);
        $this->assertInstanceOf(EducationalConfigurationService::class, $service);
    }

    public function test_can_instantiate_repository()
    {
        $repository = app(EducationalSettingsRepository::class);
        $this->assertInstanceOf(EducationalSettingsRepository::class, $repository);
    }

    public function test_can_get_preuniversity_service()
    {
        $configService = app(EducationalConfigurationService::class);
        
        // Mock d'une école préuniversitaire
        $school = (object) ['id' => 1, 'type' => 'preuniversity'];
        
        $service = $configService->getSettingsService('preuniversity', $school);
        $this->assertInstanceOf(PreUniversitySettingsService::class, $service);
    }

    public function test_can_get_university_service()
    {
        $configService = app(EducationalConfigurationService::class);
        
        // Mock d'une université
        $school = (object) ['id' => 2, 'type' => 'university'];
        
        $service = $configService->getSettingsService('university', $school);
        $this->assertInstanceOf(UniversitySettingsService::class, $service);
    }

    public function test_default_preuniversity_age_limits()
    {
        $service = new PreUniversitySettingsService(
            app(EducationalSettingsRepository::class),
            (object) ['id' => 1, 'type' => 'preuniversity']
        );
        
        $ageLimits = $service->getAgeLimits();
        $this->assertIsArray($ageLimits);
        
        // Vérifier la structure par défaut
        $this->assertArrayHasKey('primaire', $ageLimits);
        $this->assertArrayHasKey('min', $ageLimits['primaire']);
        $this->assertArrayHasKey('max', $ageLimits['primaire']);
    }

    public function test_default_preuniversity_evaluation_thresholds()
    {
        $service = new PreUniversitySettingsService(
            app(EducationalSettingsRepository::class),
            (object) ['id' => 1, 'type' => 'preuniversity']
        );
        
        $thresholds = $service->getEvaluationThresholds();
        $this->assertIsArray($thresholds);
        
        // Vérifier les seuils par défaut
        $this->assertArrayHasKey('excellent', $thresholds);
        $this->assertArrayHasKey('echec', $thresholds);
        $this->assertIsFloat($thresholds['excellent']);
        $this->assertIsFloat($thresholds['echec']);
        
        // Logique de validation des seuils
        $this->assertGreaterThanOrEqual(0, $thresholds['echec']);
        $this->assertLessThanOrEqual(20, $thresholds['excellent']);
    }

    public function test_default_university_lmd_standards()
    {
        $service = new UniversitySettingsService(
            app(EducationalSettingsRepository::class),
            (object) ['id' => 2, 'type' => 'university']
        );
        
        $standards = $service->getLMDStandards();
        $this->assertIsArray($standards);
        
        // Vérifier la structure LMD par défaut
        $this->assertArrayHasKey('licence', $standards);
        $this->assertArrayHasKey('credits_total', $standards['licence']);
        $this->assertIsInt($standards['licence']['credits_total']);
        $this->assertEquals(180, $standards['licence']['credits_total']);
    }

    public function test_default_university_age_limits()
    {
        $service = new UniversitySettingsService(
            app(EducationalSettingsRepository::class),
            (object) ['id' => 2, 'type' => 'university']
        );
        
        $ageLimits = $service->getAgeLimits();
        $this->assertIsArray($ageLimits);
        
        // Vérifier la structure universitaire
        $this->assertArrayHasKey('licence', $ageLimits);
        $this->assertArrayHasKey('min', $ageLimits['licence']);
        $this->assertIsInt($ageLimits['licence']['min']);
        $this->assertGreaterThanOrEqual(16, $ageLimits['licence']['min']);
    }

    public function test_settings_validation_basic()
    {
        $configService = app(EducationalConfigurationService::class);
        
        $validSettings = [
            'evaluation' => [
                'thresholds' => [
                    'excellent' => 16.0,
                    'echec' => 0.0,
                ],
            ],
        ];
        
        $errors = $configService->validateSettings($validSettings, 'preuniversity');
        $this->assertIsArray($errors);
    }

    public function test_can_resolve_service_provider_bindings()
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
}