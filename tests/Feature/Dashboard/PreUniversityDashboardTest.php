<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Grade;
use App\Models\Person;
use App\Models\EducationalSetting;
use App\Services\Settings\PreUniversitySettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class PreUniversityDashboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private School $school;
    private AcademicYear $academicYear;
    private array $testData;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createTestEnvironment();
        $this->createTestData();
    }

    /** @test */
    public function admin_can_access_preuniversity_dashboard()
    {
        // Tester d'abord que le contrôleur peut être instancié et que les données sont correctes
        $controller = new \App\Http\Controllers\Academic\PreUniversityDashboardController(
            app(\App\Services\Settings\PreUniversitySettingsService::class)
        );
        
        // Simuler la requête avec le contexte éducationnel approprié
        $request = \Illuminate\Http\Request::create(route('academic.preuniversity.dashboard.index'));
        $request->setUserResolver(function() {
            return $this->admin;
        });
        
        // Ajouter le contexte éducationnel à la requête
        $context = new \stdClass();
        $context->school = $this->school;
        $context->academic_year = $this->academicYear;
        $request->attributes->set('educational_context', $context);
        
        // Tester que la méthode index fonctionne
        $response = $controller->index($request);
        
        $this->assertInstanceOf(\Illuminate\View\View::class, $response);
        $this->assertEquals('dashboards.preuniversity.index', $response->name());
        $response->assertViewHas([
            'school',
            'academicYear', 
            'stats',
            'levelMetrics',
            'alerts',
            'chartsData',
            'recentActivities'
        ]);
    }

    /** @test */
    public function dashboard_displays_correct_main_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $response->assertStatus(200);

        $viewData = $response->viewData('stats');
        
        $this->assertEquals($this->testData['expected_students'], $viewData['total_students']);
        $this->assertEquals($this->testData['expected_classes'], $viewData['total_classes']);
        $this->assertEquals($this->testData['expected_teachers'], $viewData['total_teachers']);
        $this->assertEquals($this->testData['expected_subjects'], $viewData['total_subjects']);
        $this->assertIsNumeric($viewData['success_rate']);
        $this->assertIsNumeric($viewData['global_average']);
    }

    /** @test */
    public function dashboard_calculates_correct_level_metrics()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $levelMetrics = $response->viewData('levelMetrics');
        
        $this->assertIsArray($levelMetrics);
        $this->assertNotEmpty($levelMetrics);
        
        foreach ($levelMetrics as $metric) {
            $this->assertObjectHasProperty('level_name', $metric);
            $this->assertObjectHasProperty('cycle_name', $metric);
            $this->assertObjectHasProperty('total_classes', $metric);
            $this->assertObjectHasProperty('total_students', $metric);
            $this->assertObjectHasProperty('average_capacity', $metric);
            $this->assertObjectHasProperty('occupancy_rate', $metric);
        }
    }

    /** @test */
    public function dashboard_shows_alerts_for_overcrowded_classes()
    {
        // Créer une classe surchargée
        $overcrowdedClass = SchoolClass::factory()->create([
            'school_id' => $this->school->id,
            'level_id' => $this->testData['levels']->first()->id,
            'capacity' => 20,
            'name' => 'Classe Surchargée Test'
        ]);

        // Créer plus d'élèves que la capacité
        $students = Student::factory()->count(25)->create();
        foreach ($students as $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'class_id' => $overcrowdedClass->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $alerts = $response->viewData('alerts');
        
        $overcrowdedAlert = collect($alerts)->firstWhere('type', 'warning');
        $this->assertNotNull($overcrowdedAlert);
        $this->assertStringContains('Classes surchargées', $overcrowdedAlert['title']);
    }

    /** @test */
    public function dashboard_provides_chart_data()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $chartsData = $response->viewData('chartsData');
        
        $this->assertArrayHasKey('level_distribution', $chartsData);
        $this->assertArrayHasKey('enrollment_trend', $chartsData);
        $this->assertArrayHasKey('gender_distribution', $chartsData);
        $this->assertArrayHasKey('class_averages', $chartsData);
        
        $this->assertIsArray($chartsData['level_distribution']);
        $this->assertIsArray($chartsData['enrollment_trend']);
        $this->assertIsArray($chartsData['gender_distribution']);
        $this->assertIsArray($chartsData['class_averages']);
    }

    /** @test */
    public function dashboard_shows_recent_activities()
    {
        // Créer une inscription récente
        $recentStudent = Student::factory()->create();
        $recentEnrollment = Enrollment::factory()->create([
            'student_id' => $recentStudent->id,
            'class_id' => $this->testData['classes']->first()->id,
            'academic_year_id' => $this->academicYear->id,
            'status' => 'active',
            'created_at' => now()->subDays(1)
        ]);

        // Créer une évaluation récente
        $recentEvaluation = Evaluation::factory()->create([
            'subject_id' => $this->testData['subjects']->first()->id,
            'school_class_id' => $this->testData['classes']->first()->id,
            'academic_year_id' => $this->academicYear->id,
            'evaluation_date' => now()->subDays(2),
            'created_at' => now()->subDays(2)
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $recentActivities = $response->viewData('recentActivities');
        
        $this->assertArrayHasKey('enrollments', $recentActivities);
        $this->assertArrayHasKey('evaluations', $recentActivities);
        $this->assertNotEmpty($recentActivities['enrollments']);
        $this->assertNotEmpty($recentActivities['evaluations']);
    }

    /** @test */
    public function dashboard_integrates_with_educational_settings()
    {
        // Créer des paramètres éducatifs spécifiques
        EducationalSetting::create([
            'school_id' => $this->school->id,
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

        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $response->assertStatus(200);
        
        // Vérifier que les seuils configurés sont utilisés
        $stats = $response->viewData('stats');
        $this->assertArrayHasKey('success_rate', $stats);
        $this->assertIsNumeric($stats['success_rate']);
    }

    /** @test */
    public function dashboard_calculates_success_rate_with_custom_thresholds()
    {
        // Créer des notes pour tester le calcul
        $students = Student::factory()->count(10)->create();
        $subject = $this->testData['subjects']->first();

        foreach ($students as $index => $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'class_id' => $this->testData['classes']->first()->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);

            // Créer des notes : 5 élèves réussissent (≥10), 5 échouent (<10)
            Grade::factory()->create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYear->id,
                'value' => $index < 5 ? 15 : 8  // 50% de réussite attendu
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $stats = $response->viewData('stats');
        $this->assertEquals(50.0, $stats['success_rate']);
    }

    /** @test */
    public function dashboard_performance_is_acceptable()
    {
        // Test de performance : le dashboard ne doit pas dépasser 2 secondes
        $startTime = microtime(true);

        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(2.0, $executionTime, 'Le dashboard prend trop de temps à charger');
    }

    /** @test */
    public function unauthorized_user_cannot_access_dashboard()
    {
        $unauthorizedUser = User::factory()->create();

        $response = $this->actingAs($unauthorizedUser)
            ->get(route('academic.preuniversity.dashboard.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function dashboard_handles_empty_data_gracefully()
    {
        // Créer une école sans données
        $emptySchool = School::factory()->create([
            'type' => 'preuniversity',
            'name' => 'École Vide Test'
        ]);

        // Créer un admin pour cette école
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $emptyAdmin = User::factory()->create(['school_id' => $emptySchool->id]);
        $emptyAdmin->assignRole($adminRole);

        // Simuler le contexte éducatif vide
        $this->app->instance('educational.context', (object)[
            'school' => $emptySchool,
            'school_type' => 'pre_university',
            'academic_year' => $this->academicYear
        ]);

        $response = $this->actingAs($emptyAdmin)
            ->get(route('academic.preuniversity.dashboard.index'));

        $response->assertStatus(200);
        
        $stats = $response->viewData('stats');
        $this->assertEquals(0, $stats['total_students']);
        $this->assertEquals(0, $stats['total_classes']);
        $this->assertEquals(0, $stats['success_rate']);
        $this->assertEquals(0, $stats['global_average']);
    }

    private function createTestEnvironment(): void
    {
        // Créer les rôles nécessaires
        $academicDirectorRole = Role::firstOrCreate(['name' => 'directeur_academique']);
        
        // Créer l'école
        $this->school = School::factory()->preUniversity()->create([
            'name' => 'École Test Préuniversitaire'
        ]);

        // Créer l'année académique
        $this->academicYear = AcademicYear::factory()->create([
            'name' => '2025-2026',
            'start_date' => Carbon::now()->startOfYear(),
            'end_date' => Carbon::now()->endOfYear(),
            'is_current' => true
        ]);

        // Créer l'admin
        $this->admin = User::factory()->create(['school_id' => $this->school->id]);
        $this->admin->assignRole($academicDirectorRole);

        // Simuler le contexte éducatif
        $this->app->instance('educational.context', (object)[
            'school' => $this->school,
            'school_type' => 'pre_university',
            'academic_year' => $this->academicYear
        ]);

        $this->app->instance('educational.settings', 
            new PreUniversitySettingsService(
                app(\App\Repositories\EducationalSettingsRepository::class),
                $this->school
            )
        );
    }

    private function createTestData(): void
    {
        // Créer les cycles
        $cycles = collect([
            ['name' => 'Préscolaire', 'school_id' => $this->school->id],
            ['name' => 'Primaire', 'school_id' => $this->school->id],
            ['name' => 'Secondaire', 'school_id' => $this->school->id]
        ])->map(fn($cycle) => Cycle::factory()->create($cycle));

        // Créer les niveaux
        $levels = collect();
        foreach ($cycles as $cycle) {
            for ($i = 1; $i <= 2; $i++) {
                $levels->push(Level::factory()->create([
                    'school_id' => $this->school->id,
                    'academic_year_id' => $this->academicYear->id,
                    'cycle_id' => $cycle->id,
                    'name' => $cycle->name . ' ' . $i,
                    'order' => $i
                ]));
            }
        }

        // Créer les classes
        $classes = collect();
        foreach ($levels as $level) {
            for ($i = 1; $i <= 2; $i++) {
                $classes->push(SchoolClass::factory()->create([
                    'school_id' => $this->school->id,
                    'academic_year_id' => $this->academicYear->id,
                    'cycle_id' => $level->cycle_id,
                    'level_id' => $level->id,
                    'name' => $level->name . ' - Classe ' . $i,
                    'capacity' => 30
                ]));
            }
        }

        // Créer les enseignants
        $teachers = Teacher::factory()->count(8)->create([
            'school_id' => $this->school->id,
            'status' => 'active'
        ]);

        // Créer les matières
        $subjects = collect();
        foreach ($levels as $level) {
            $subjectNames = ['Français', 'Mathématiques', 'Histoire', 'Sciences'];
            foreach ($subjectNames as $name) {
                $subjects->push(Subject::factory()->create([
                    'school_id' => $this->school->id,
                    'level_id' => $level->id,
                    'name' => $name,
                    'coefficient' => 2.0
                ]));
            }
        }

        // Créer les élèves et inscriptions
        $students = Student::factory()->count(120)->create();
        foreach ($students as $index => $student) {
            $classIndex = $index % $classes->count();
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'class_id' => $classes[$classIndex]->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);
        }

        $this->testData = [
            'cycles' => $cycles,
            'levels' => $levels,
            'classes' => $classes,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'students' => $students,
            'expected_students' => 120,
            'expected_classes' => $classes->count(),
            'expected_teachers' => 8,
            'expected_subjects' => $subjects->count()
        ];
    }
}