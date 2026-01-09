<?php

namespace Tests\Feature\Dashboard;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Level;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Evaluation;
use App\Models\Mark;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class DashboardChartsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private School $school;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestEnvironment();
    }

    /** @test */
    public function enrollments_trend_chart_returns_correct_data()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.enrollments-trend'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        $this->assertIsArray($data['labels']);
        $this->assertIsArray($data['datasets']);
        
        // Vérifier que nous avons 12 mois de données
        $this->assertCount(12, $data['labels']);
        
        // Vérifier la structure des datasets
        foreach ($data['datasets'] as $dataset) {
            $this->assertArrayHasKey('label', $dataset);
            $this->assertArrayHasKey('data', $dataset);
            $this->assertArrayHasKey('borderColor', $dataset);
            $this->assertArrayHasKey('backgroundColor', $dataset);
        }
    }

    /** @test */
    public function evaluation_statistics_chart_shows_grade_distribution()
    {
        // Créer des notes avec distribution connue
        $this->createEvaluationsWithKnownDistribution();

        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.evaluation-stats'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        
        // Vérifier les catégories de notes
        $expectedLabels = ['Excellent (16-20)', 'Très Bien (14-16)', 'Bien (12-14)', 'Assez Bien (10-12)', 'Insuffisant (0-10)'];
        $this->assertEquals($expectedLabels, $data['labels']);
        
        // Vérifier que nous avons des données pour chaque catégorie
        $dataset = $data['datasets'][0];
        $this->assertCount(5, $dataset['data']);
        $this->assertGreaterThan(0, array_sum($dataset['data']));
    }

    /** @test */
    public function level_performance_chart_compares_all_levels()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.level-performance'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        
        // Vérifier que tous les niveaux sont représentés
        $levels = Level::where('school_id', $this->school->id)->pluck('name')->toArray();
        $this->assertEquals($levels, $data['labels']);
        
        // Vérifier les métriques pour chaque niveau
        $expectedDatasets = ['Moyenne Générale', 'Taux de Réussite', 'Nombre d\'Étudiants'];
        foreach ($data['datasets'] as $dataset) {
            $this->assertContains($dataset['label'], $expectedDatasets);
        }
    }

    /** @test */
    public function subject_performance_chart_shows_subject_averages()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.subject-performance'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        
        // Vérifier la structure des données
        $this->assertIsArray($data['labels']);
        $this->assertGreaterThan(0, count($data['labels']));
        
        foreach ($data['datasets'] as $dataset) {
            $this->assertArrayHasKey('label', $dataset);
            $this->assertArrayHasKey('data', $dataset);
            $this->assertCount(count($data['labels']), $dataset['data']);
        }
    }

    /** @test */
    public function monthly_activity_chart_tracks_school_activity()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.monthly-activity'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        
        // Vérifier que nous avons 12 mois
        $this->assertCount(12, $data['labels']);
        
        // Vérifier les types d'activités trackées
        $expectedDatasets = ['Nouvelles Inscriptions', 'Évaluations', 'Activités Académiques'];
        foreach ($data['datasets'] as $dataset) {
            $this->assertContains($dataset['label'], $expectedDatasets);
        }
    }

    /** @test */
    public function attendance_trend_chart_shows_presence_patterns()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.attendance-trend'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        
        // Vérifier les données d'assiduité
        foreach ($data['datasets'] as $dataset) {
            foreach ($dataset['data'] as $value) {
                $this->assertIsNumeric($value);
                $this->assertGreaterThanOrEqual(0, $value);
                $this->assertLessThanOrEqual(100, $value); // Pourcentages
            }
        }
    }

    /** @test */
    public function chart_data_respects_date_filters()
    {
        $startDate = Carbon::now()->subMonths(3)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.enrollments-trend', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]));

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Vérifier que les données respectent la période filtrée
        $this->assertLessThanOrEqual(4, count($data['labels'])); // 3-4 mois max
    }

    /** @test */
    public function chart_data_respects_level_filters()
    {
        $level = Level::where('school_id', $this->school->id)->first();

        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.evaluation-stats', [
                'level_id' => $level->id
            ]));

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Vérifier que les données sont filtrées par niveau
        $this->assertArrayHasKey('datasets', $data);
        $this->assertGreaterThan(0, count($data['datasets']));
    }

    /** @test */
    public function charts_handle_empty_data_gracefully()
    {
        // Supprimer toutes les données
        Mark::query()->delete();
        Evaluation::query()->delete();
        Enrollment::query()->delete();

        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.evaluation-stats'));

        $response->assertStatus(200);
        
        $data = $response->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('datasets', $data);
        
        // Vérifier que les graphiques gèrent l'absence de données
        if (!empty($data['datasets'])) {
            foreach ($data['datasets'] as $dataset) {
                $this->assertIsArray($dataset['data']);
            }
        }
    }

    /** @test */
    public function chart_colors_are_consistent_and_accessible()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.level-performance'));

        $response->assertStatus(200);
        
        $data = $response->json();
        
        foreach ($data['datasets'] as $dataset) {
            $this->assertArrayHasKey('backgroundColor', $dataset);
            $this->assertArrayHasKey('borderColor', $dataset);
            
            // Vérifier que les couleurs sont valides (hex ou rgba)
            $bgColor = $dataset['backgroundColor'];
            $borderColor = $dataset['borderColor'];
            
            $this->assertTrue(
                $this->isValidColor($bgColor),
                "Invalid background color: {$bgColor}"
            );
            
            $this->assertTrue(
                $this->isValidColor($borderColor),
                "Invalid border color: {$borderColor}"
            );
        }
    }

    /** @test */
    public function chart_data_includes_metadata()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.enrollments-trend'));

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Vérifier les métadonnées
        $this->assertArrayHasKey('metadata', $data);
        $metadata = $data['metadata'];
        
        $this->assertArrayHasKey('title', $metadata);
        $this->assertArrayHasKey('description', $metadata);
        $this->assertArrayHasKey('total_records', $metadata);
        $this->assertArrayHasKey('date_range', $metadata);
    }

    /** @test */
    public function chart_export_functionality_works()
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.export', [
                'chart' => 'evaluation-stats',
                'format' => 'json'
            ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /** @test */
    public function real_time_chart_updates_work()
    {
        // Obtenir les données initiales
        $initialResponse = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.enrollments-trend'));

        $initialData = $initialResponse->json();
        $initialTotal = array_sum($initialData['datasets'][0]['data'] ?? []);

        // Ajouter de nouvelles inscriptions
        $this->createNewEnrollments();

        // Obtenir les données mises à jour
        $updatedResponse = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.chart.enrollments-trend'));

        $updatedData = $updatedResponse->json();
        $updatedTotal = array_sum($updatedData['datasets'][0]['data'] ?? []);

        // Vérifier que les données ont été mises à jour
        $this->assertGreaterThan($initialTotal, $updatedTotal);
    }

    private function createTestEnvironment(): void
    {
        // Créer les rôles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);

        // Créer l'école
        $this->school = School::factory()->preUniversity()->create([
            'name' => 'École Test Charts'
        ]);

        // Créer l'année académique
        $this->academicYear = AcademicYear::factory()->create([
            'is_current' => true
        ]);

        // Créer l'admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
        $this->admin->schools()->attach($this->school->id);

        // Créer des données de test
        $this->createTestData();

        // Simuler le contexte éducationnel
        $this->app->instance('educational.context', (object)[
            'school' => $this->school,
            'school_type' => 'pre_university',
            'academic_year' => $this->academicYear
        ]);
    }

    private function createTestData(): void
    {
        // Créer des niveaux
        $levels = Level::factory(3)->create([
            'school_id' => $this->school->id
        ]);

        // Créer des classes
        $classrooms = collect();
        foreach ($levels as $level) {
            $levelClassrooms = Classroom::factory(2)->create([
                'level_id' => $level->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id
            ]);
            $classrooms = $classrooms->merge($levelClassrooms);
        }

        // Créer des matières
        $subjects = Subject::factory(5)->create([
            'school_id' => $this->school->id
        ]);

        // Créer des enseignants
        $teachers = User::factory(5)->create();
        $teacherRole = Role::where('name', 'teacher')->first();
        foreach ($teachers as $teacher) {
            $teacher->assignRole($teacherRole);
            $teacher->schools()->attach($this->school->id);
        }

        // Créer des étudiants et inscriptions
        foreach ($classrooms as $classroom) {
            $students = Student::factory(15)->create([
                'school_id' => $this->school->id
            ]);
            
            foreach ($students as $student) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'academic_year_id' => $this->academicYear->id,
                    'enrollment_date' => Carbon::now()->subMonths(rand(1, 8)),
                    'status' => 'active'
                ]);
            }
        }

        // Créer des évaluations et notes
        foreach ($classrooms as $classroom) {
            foreach ($subjects as $subject) {
                $evaluation = Evaluation::factory()->create([
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teachers->random()->id,
                    'academic_year_id' => $this->academicYear->id,
                    'date' => Carbon::now()->subDays(rand(1, 90))
                ]);

                $students = Student::whereHas('enrollments', function($query) use ($classroom) {
                    $query->where('classroom_id', $classroom->id);
                })->get();

                foreach ($students as $student) {
                    Mark::factory()->create([
                        'student_id' => $student->id,
                        'evaluation_id' => $evaluation->id,
                        'value' => rand(8, 20) + (rand(0, 100) / 100),
                        'academic_year_id' => $this->academicYear->id
                    ]);
                }
            }
        }
    }

    private function createEvaluationsWithKnownDistribution(): void
    {
        $classroom = Classroom::where('school_id', $this->school->id)->first();
        $subject = Subject::where('school_id', $this->school->id)->first();
        $teacher = User::role('teacher')->first();

        $evaluation = Evaluation::factory()->create([
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'academic_year_id' => $this->academicYear->id
        ]);

        $students = Student::whereHas('enrollments', function($query) use ($classroom) {
            $query->where('classroom_id', $classroom->id);
        })->take(20)->get();

        // Distribution connue : 4 excellents, 6 très bien, 5 bien, 3 assez bien, 2 insuffisant
        $grades = array_merge(
            array_fill(0, 4, 18), // Excellent
            array_fill(0, 6, 15), // Très bien
            array_fill(0, 5, 13), // Bien
            array_fill(0, 3, 11), // Assez bien
            array_fill(0, 2, 8)   // Insuffisant
        );

        foreach ($students as $index => $student) {
            if (isset($grades[$index])) {
                Mark::factory()->create([
                    'student_id' => $student->id,
                    'evaluation_id' => $evaluation->id,
                    'value' => $grades[$index],
                    'academic_year_id' => $this->academicYear->id
                ]);
            }
        }
    }

    private function createNewEnrollments(): void
    {
        $classroom = Classroom::where('school_id', $this->school->id)->first();
        $newStudents = Student::factory(5)->create([
            'school_id' => $this->school->id
        ]);

        foreach ($newStudents as $student) {
            Enrollment::create([
                'student_id' => $student->id,
                'classroom_id' => $classroom->id,
                'academic_year_id' => $this->academicYear->id,
                'enrollment_date' => Carbon::now(),
                'status' => 'active'
            ]);
        }
    }

    private function isValidColor(string $color): bool
    {
        // Vérifier les formats hex (#fff, #ffffff)
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
            return true;
        }

        // Vérifier les formats rgba/rgb
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[01]?\.?\d*)?\s*\)$/', $color)) {
            return true;
        }

        return false;
    }
}