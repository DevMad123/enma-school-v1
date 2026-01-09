<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\EducationalSetting;
use App\Models\DefaultEducationalSetting;
use App\Services\Settings\PreUniversitySettingsService;
use App\Http\Middleware\EducationalContextMiddleware;
use App\Services\EducationalConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Cache;

class EducationalSettingsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private School $preunivSchool;
    private School $univSchool;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestEnvironment();
    }

    /** @test */
    public function middleware_injects_correct_educational_context()
    {
        $request = Request::create('/academic/preuniversity/dashboard', 'GET');
        $request->setUserResolver(fn() => $this->admin);

        $middleware = new EducationalContextMiddleware(
            app(EducationalConfigurationService::class),
            app(\App\Repositories\SchoolRepository::class)
        );

        $response = $middleware->handle($request, function ($req) {
            $context = $req->attributes->get('educational_context');
            
            $this->assertNotNull($context);
            $this->assertEquals($this->preunivSchool->id, $context->school->id);
            $this->assertEquals('preuniversity', $context->school_type);
            
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function settings_service_retrieves_school_specific_configuration()
    {
        // Créer un paramètre global
        DefaultEducationalSetting::create([
            'school_type' => 'pre_university',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode([
                'excellent' => 16,
                'tres_bien' => 14,
                'bien' => 12,
                'assez_bien' => 10
            ])
        ]);

        // Créer un paramètre spécifique à l'école qui override le global
        EducationalSetting::create([
            'school_id' => $this->preunivSchool->id,
            'school_type' => 'pre_university',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode([
                'excellent' => 18,  // Seuil plus élevé pour cette école
                'tres_bien' => 15,
                'bien' => 12,
                'assez_bien' => 10
            ])
        ]);

        $settingsService = new PreUniversitySettingsService(
            app(\App\Repositories\EducationalSettingsRepository::class),
            $this->preunivSchool
        );

        $thresholds = $settingsService->getEvaluationThresholds();
        
        $this->assertEquals(18, $thresholds['excellent']);
        $this->assertEquals(15, $thresholds['tres_bien']);
    }

    /** @test */
    public function settings_fall_back_to_defaults_when_no_custom_config()
    {
        // Créer seulement le paramètre par défaut
        DefaultEducationalSetting::create([
            'school_type' => 'preuniversity',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode([
                'excellent' => 16,
                'tres_bien' => 14,
                'bien' => 12,
                'assez_bien' => 10
            ])
        ]);

        $settingsService = new PreUniversitySettingsService(
            app(\App\Repositories\EducationalSettingsRepository::class),
            $this->preunivSchool
        );

        $thresholds = $settingsService->getEvaluationThresholds();
        
        $this->assertEquals(16, $thresholds['excellent']);
        $this->assertEquals(14, $thresholds['tres_bien']);
    }

    /** @test */
    public function different_school_types_have_different_configurations()
    {
        // Configuration préuniversitaire
        DefaultEducationalSetting::create([
            'school_type' => 'preuniversity',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode([
                'excellent' => 16,
                'tres_bien' => 14,
                'bien' => 12,
                'assez_bien' => 10
            ])
        ]);

        // Configuration universitaire avec standards LMD
        DefaultEducationalSetting::create([
            'school_type' => 'university',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode([
                'A' => 16,
                'B' => 14,
                'C' => 12,
                'D' => 10
            ])
        ]);

        $preunivSettingsService = new PreUniversitySettingsService(
            app(\App\Repositories\EducationalSettingsRepository::class),
            $this->preunivSchool
        );

        $univSettingsService = app(EducationalConfigurationService::class)
            ->getSettingsService('university', $this->univSchool);

        $preunivThresholds = $preunivSettingsService->getEvaluationThresholds();
        $univThresholds = $univSettingsService->getEvaluationThresholds();

        $this->assertArrayHasKey('excellent', $preunivThresholds);
        $this->assertArrayHasKey('A', $univThresholds);
    }

    /** @test */
    public function settings_cache_improves_performance()
    {
        // Créer un paramètre
        DefaultEducationalSetting::create([
            'school_type' => 'preuniversity',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode(['assez_bien' => 10])
        ]);

        $settingsService = new PreUniversitySettingsService(
            app(\App\Repositories\EducationalSettingsRepository::class),
            $this->preunivSchool
        );

        // Premier appel - met en cache
        $startTime = microtime(true);
        $thresholds1 = $settingsService->getEvaluationThresholds();
        $firstCallTime = microtime(true) - $startTime;

        // Deuxième appel - utilise le cache
        $startTime = microtime(true);
        $thresholds2 = $settingsService->getEvaluationThresholds();
        $secondCallTime = microtime(true) - $startTime;

        $this->assertEquals($thresholds1, $thresholds2);
        $this->assertLessThan($firstCallTime, $secondCallTime);
    }

    /** @test */
    public function configuration_admin_interface_works()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.educational-settings.index', [
                'school_type' => 'preuniversity',
                'category' => 'evaluation'
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.educational-settings.index');
        $response->assertViewHas('schoolType', 'preuniversity');
        $response->assertViewHas('category', 'evaluation');
    }

    /** @test */
    public function admin_can_update_educational_settings()
    {
        $newThresholds = [
            'excellent' => 18,
            'tres_bien' => 15,
            'bien' => 12,
            'assez_bien' => 10
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.educational-settings.update'), [
                'school_id' => $this->preunivSchool->id,
                'school_type' => 'preuniversity',
                'category' => 'evaluation',
                'settings' => [
                    'thresholds' => json_encode($newThresholds)
                ]
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Vérifier que les paramètres ont été sauvegardés
        $this->assertDatabaseHas('educational_settings', [
            'school_id' => $this->preunivSchool->id,
            'school_type' => 'preuniversity',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds'
        ]);
    }

    /** @test */
    public function settings_validation_prevents_invalid_data()
    {
        $invalidThresholds = [
            'excellent' => 25,  // > 20, invalide
            'tres_bien' => -5,  // < 0, invalide
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.educational-settings.update'), [
                'school_id' => $this->preunivSchool->id,
                'school_type' => 'preuniversity', 
                'category' => 'evaluation',
                'settings' => [
                    'thresholds' => json_encode($invalidThresholds)
                ]
            ]);

        $response->assertSessionHasErrors();

        // Vérifier qu'aucun paramètre invalide n'a été sauvegardé
        $this->assertDatabaseMissing('educational_settings', [
            'school_id' => $this->preunivSchool->id,
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds'
        ]);
    }

    /** @test */
    public function settings_audit_tracks_changes()
    {
        // Créer un paramètre initial
        $setting = EducationalSetting::create([
            'school_id' => $this->preunivSchool->id,
            'school_type' => 'preuniversity',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode(['assez_bien' => 10]),
            'created_by' => $this->admin->id
        ]);

        // Modifier le paramètre
        $response = $this->actingAs($this->admin)
            ->post(route('admin.educational-settings.update'), [
                'school_id' => $this->preunivSchool->id,
                'school_type' => 'preuniversity',
                'category' => 'evaluation',
                'settings' => [
                    'thresholds' => json_encode(['assez_bien' => 12])
                ]
            ]);

        $response->assertRedirect();

        // Vérifier l'audit trail
        $this->assertDatabaseHas('educational_settings_audit', [
            'setting_id' => $setting->id,
            'action' => 'update',
            'changed_by' => $this->admin->id
        ]);
    }

    /** @test */
    public function settings_export_includes_all_configurations()
    {
        // Créer plusieurs paramètres
        EducationalSetting::create([
            'school_id' => $this->preunivSchool->id,
            'school_type' => 'preuniversity',
            'setting_category' => 'evaluation',
            'setting_key' => 'thresholds',
            'setting_value' => json_encode(['assez_bien' => 10])
        ]);

        EducationalSetting::create([
            'school_id' => $this->preunivSchool->id,
            'school_type' => 'preuniversity',
            'setting_category' => 'age_limits',
            'setting_key' => 'primaire',
            'setting_value' => json_encode(['min' => 6, 'max' => 12])
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.educational-settings.export', [
                'school_type' => 'preuniversity',
                'school_id' => $this->preunivSchool->id
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('evaluation', $content);
        $this->assertArrayHasKey('age_limits', $content);
    }

    /** @test */
    public function cache_invalidation_works_when_settings_change()
    {
        $cacheKey = "settings:{$this->preunivSchool->id}:preuniversity";
        
        // Mettre quelque chose en cache
        Cache::put($cacheKey, ['test' => 'value'], 3600);
        $this->assertTrue(Cache::has($cacheKey));

        // Modifier un paramètre
        $this->actingAs($this->admin)
            ->post(route('admin.educational-settings.update'), [
                'school_id' => $this->preunivSchool->id,
                'school_type' => 'preuniversity',
                'category' => 'evaluation',
                'settings' => [
                    'thresholds' => json_encode(['assez_bien' => 10])
                ]
            ]);

        // Vérifier que le cache a été invalidé
        $this->assertFalse(Cache::has($cacheKey));
    }

    private function createTestEnvironment(): void
    {
        // Créer les rôles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        
        // Créer les écoles
        $this->preunivSchool = School::factory()->preUniversity()->create([
            'name' => 'École Préuniversitaire Test'
        ]);

        $this->univSchool = School::factory()->university()->create([
            'name' => 'Université Test'
        ]);

        // Créer l'année académique
        $this->academicYear = AcademicYear::factory()->create([
            'is_current' => true
        ]);

        // Créer l'admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
        $this->admin->schools()->attach([$this->preunivSchool->id, $this->univSchool->id]);

        // Simuler le contexte par défaut
        $this->app->instance('educational.context', (object)[
            'school' => $this->preunivSchool,
            'school_type' => 'pre_university',
            'academic_year' => $this->academicYear
        ]);
    }
}