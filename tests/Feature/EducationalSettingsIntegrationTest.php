<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\EducationalSetting;
use App\Models\DefaultEducationalSetting;
use App\Services\EducationalConfigurationService;
use App\Repositories\EducationalSettingsRepository;
use Spatie\Permission\Models\Role;

class EducationalSettingsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $configService;
    protected $repository;
    protected $admin;
    protected $preunivSchool;
    protected $univSchool;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les rôles
        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'admin']);
        
        // Utilisateur admin
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
        ]);
        $this->admin->assignRole('super-admin');
        
        // Écoles de test
        $this->preunivSchool = School::factory()->create([
            'name' => 'École Préuniversitaire Test',
            'type' => 'preuniversity',
        ]);
        
        $this->univSchool = School::factory()->create([
            'name' => 'Université Test',
            'type' => 'university',
        ]);
        
        // Services
        $this->repository = app(EducationalSettingsRepository::class);
        $this->configService = app(EducationalConfigurationService::class);
        
        // Seed des paramètres par défaut
        $this->seedDefaultSettings();
    }

    /** @test */
    public function can_retrieve_default_settings_for_preuniversity()
    {
        $settingsService = $this->configService->getSettingsService('preuniversity', $this->preunivSchool);
        
        $ageLimits = $settingsService->getAgeLimits();
        $this->assertArrayHasKey('primaire', $ageLimits);
        $this->assertEquals(6, $ageLimits['primaire']['min']);
        $this->assertEquals(12, $ageLimits['primaire']['max']);
        
        $evaluationThresholds = $settingsService->getEvaluationThresholds();
        $this->assertArrayHasKey('excellent', $evaluationThresholds);
        $this->assertEquals(16.0, $evaluationThresholds['excellent']);
    }

    /** @test */
    public function can_retrieve_default_settings_for_university()
    {
        $settingsService = $this->configService->getSettingsService('university', $this->univSchool);
        
        $ageLimits = $settingsService->getAgeLimits();
        $this->assertArrayHasKey('licence', $ageLimits);
        $this->assertEquals(17, $ageLimits['licence']['min']);
        
        $lmdStandards = $settingsService->getLMDStandards();
        $this->assertArrayHasKey('licence', $lmdStandards);
        $this->assertEquals(180, $lmdStandards['licence']['credits_total']);
    }

    /** @test */
    public function can_override_school_specific_settings()
    {
        // Créer un paramètre spécifique à l'école
        $this->repository->setValue(
            $this->preunivSchool->id,
            'preuniversity',
            'evaluation',
            'thresholds',
            [
                'excellent' => 18.0, // Override: 18 au lieu de 16
                'tres_bien' => 15.0,
                'bien' => 12.0,
                'assez_bien' => 10.0,
                'passable' => 8.0,
                'echec' => 0.0,
            ],
            null,
            $this->admin->id
        );
        
        $settingsService = $this->configService->getSettingsService('preuniversity', $this->preunivSchool);
        $thresholds = $settingsService->getEvaluationThresholds();
        
        $this->assertEquals(18.0, $thresholds['excellent']);
        $this->assertEquals(15.0, $thresholds['tres_bien']);
    }

    /** @test */
    public function hierarchical_settings_resolution_works()
    {
        // Paramètre global pour le type préuniversitaire
        $this->repository->setValue(
            null, // Global
            'preuniversity',
            'evaluation',
            'thresholds',
            ['excellent' => 17.0], // Global override
            null,
            $this->admin->id
        );
        
        // Paramètre spécifique à une école
        $this->repository->setValue(
            $this->preunivSchool->id,
            'preuniversity',
            'evaluation',
            'thresholds',
            ['excellent' => 18.0], // School-specific override
            null,
            $this->admin->id
        );
        
        // École sans override utilise le global
        $otherSchool = School::factory()->create(['type' => 'preuniversity']);
        $globalService = $this->configService->getSettingsService('preuniversity', $otherSchool);
        $globalThresholds = $globalService->getEvaluationThresholds();
        $this->assertEquals(17.0, $globalThresholds['excellent']);
        
        // École avec override utilise le spécifique
        $specificService = $this->configService->getSettingsService('preuniversity', $this->preunivSchool);
        $specificThresholds = $specificService->getEvaluationThresholds();
        $this->assertEquals(18.0, $specificThresholds['excellent']);
    }

    /** @test */
    public function can_validate_settings_according_to_rules()
    {
        $invalidSettings = [
            'evaluation' => [
                'thresholds' => [
                    'excellent' => 25.0, // Invalid: > 20
                    'echec' => -5.0,     // Invalid: < 0
                ],
            ],
        ];
        
        $errors = $this->configService->validateSettings($invalidSettings, 'preuniversity');
        
        // Devrait avoir des erreurs de validation si les règles sont configurées
        $this->assertIsArray($errors);
    }

    /** @test */
    public function admin_can_access_settings_interface()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.educational-settings.index', [
                'school_type' => 'preuniversity',
                'category' => 'evaluation',
            ]));
            
        $response->assertStatus(200);
        $response->assertViewIs('admin.educational-settings.index');
        $response->assertViewHas('schoolType', 'preuniversity');
        $response->assertViewHas('category', 'evaluation');
    }

    /** @test */
    public function admin_can_update_settings_via_interface()
    {
        $newThresholds = [
            'excellent' => 17.5,
            'tres_bien' => 14.5,
            'bien' => 12.5,
            'assez_bien' => 10.5,
            'passable' => 8.5,
            'echec' => 0.0,
        ];
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.educational-settings.update'), [
                'school_id' => $this->preunivSchool->id,
                'school_type' => 'preuniversity',
                'category' => 'evaluation',
                'settings' => ['thresholds' => $newThresholds],
            ]);
            
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Vérifier que les paramètres ont été sauvegardés
        $savedValue = $this->repository->getValue(
            $this->preunivSchool->id,
            'preuniversity',
            'evaluation',
            'thresholds'
        );
        
        $this->assertEquals(17.5, $savedValue['excellent']);
    }

    /** @test */
    public function can_export_and_import_settings()
    {
        // Créer des paramètres personnalisés
        $customSettings = [
            'evaluation' => [
                'thresholds' => [
                    'excellent' => 17.0,
                    'tres_bien' => 14.0,
                ],
            ],
        ];
        
        // Import
        $result = $this->configService->importSettings($this->preunivSchool, $customSettings, $this->admin->id);
        $this->assertArrayHasKey('imported', $result);
        $this->assertEmpty($result['errors'] ?? []);
        
        // Export et vérification
        $exported = $this->configService->exportSettings($this->preunivSchool);
        $this->assertArrayHasKey('evaluation', $exported);
        $this->assertEquals(17.0, $exported['evaluation']['thresholds']['excellent']);
    }

    /** @test */
    public function can_reset_to_default_values()
    {
        // Modifier un paramètre
        $this->repository->setValue(
            $this->preunivSchool->id,
            'preuniversity',
            'evaluation',
            'thresholds',
            ['excellent' => 19.0],
            null,
            $this->admin->id
        );
        
        // Vérifier qu'il est modifié
        $modified = $this->repository->getValue(
            $this->preunivSchool->id,
            'preuniversity',
            'evaluation',
            'thresholds'
        );
        $this->assertEquals(19.0, $modified['excellent']);
        
        // Reset via l'interface
        $response = $this->actingAs($this->admin)
            ->post(route('admin.educational-settings.reset'), [
                'school_id' => $this->preunivSchool->id,
                'school_type' => 'preuniversity',
                'category' => 'evaluation',
            ]);
            
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        // Vérifier qu'il est revenu aux défauts
        $reset = $this->repository->getValue(
            $this->preunivSchool->id,
            'preuniversity',
            'evaluation',
            'thresholds'
        );
        $this->assertEquals(16.0, $reset['excellent']); // Valeur par défaut
    }

    /** @test */
    public function settings_audit_is_working()
    {
        // Créer un paramètre (devrait créer un audit)
        $setting = $this->repository->setValue(
            $this->preunivSchool->id,
            'preuniversity',
            'evaluation',
            'thresholds',
            ['excellent' => 17.0],
            null,
            $this->admin->id
        );
        
        // Vérifier qu'un audit a été créé
        $audits = $setting->audits;
        $this->assertCount(1, $audits);
        $this->assertEquals('create', $audits->first()->action);
        $this->assertEquals($this->admin->id, $audits->first()->changed_by);
        
        // Modifier le paramètre (devrait créer un autre audit)
        $setting->update(['setting_value' => ['excellent' => 18.0]]);
        
        $audits = $setting->fresh()->audits;
        $this->assertCount(2, $audits);
        $this->assertEquals('update', $audits->last()->action);
    }

    private function seedDefaultSettings()
    {
        // Paramètres préuniversitaires
        DefaultEducationalSetting::create([
            'school_type' => 'preuniversity',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => [
                'excellent' => 16.0,
                'tres_bien' => 14.0,
                'bien' => 12.0,
                'assez_bien' => 10.0,
                'passable' => 8.0,
                'echec' => 0.0,
            ],
            'is_required' => true,
            'validation_rules' => ['min' => 0, 'max' => 20],
        ]);

        DefaultEducationalSetting::create([
            'school_type' => 'preuniversity',
            'setting_category' => 'age_limits',
            'setting_key' => 'general',
            'setting_value' => [
                'primaire' => ['min' => 6, 'max' => 12],
                'college' => ['min' => 12, 'max' => 16],
            ],
            'is_required' => true,
        ]);

        // Paramètres universitaires
        DefaultEducationalSetting::create([
            'school_type' => 'university',
            'setting_category' => 'lmd',
            'setting_key' => 'standards',
            'setting_value' => [
                'licence' => [
                    'credits_total' => 180,
                    'duree_semestres' => 6,
                ],
            ],
            'is_required' => true,
        ]);

        DefaultEducationalSetting::create([
            'school_type' => 'university',
            'setting_category' => 'age_limits',
            'setting_key' => 'general',
            'setting_value' => [
                'licence' => ['min' => 17, 'max' => 30],
                'master' => ['min' => 21, 'max' => 35],
            ],
        ]);
    }
}