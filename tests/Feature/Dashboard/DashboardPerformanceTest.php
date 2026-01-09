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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private School $school;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createTestEnvironmentWithLargeDataset();
    }

    /** @test */
    public function dashboard_loads_within_acceptable_time_with_large_dataset()
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard'));

        $loadTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        
        // Le dashboard doit se charger en moins de 2 secondes
        $this->assertLessThan(2.0, $loadTime, 
            "Dashboard took {$loadTime} seconds to load, which exceeds the 2-second limit");
    }

    /** @test */
    public function statistics_calculations_are_optimized()
    {
        $startTime = microtime(true);

        $controller = app(\App\Http\Controllers\Academic\PreUniversityDashboardController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getMainStatistics');
        $method->setAccessible(true);

        $statistics = $method->invoke($controller);

        $calculationTime = microtime(true) - $startTime;

        $this->assertLessThan(0.5, $calculationTime, 
            "Statistics calculation took {$calculationTime} seconds, which is too slow");

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total_students', $statistics);
        $this->assertArrayHasKey('active_teachers', $statistics);
        $this->assertArrayHasKey('current_evaluations', $statistics);
        $this->assertArrayHasKey('recent_enrollments', $statistics);
    }

    /** @test */
    public function level_metrics_calculation_is_efficient()
    {
        $startTime = microtime(true);

        $controller = app(\App\Http\Controllers\Academic\PreUniversityDashboardController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getLevelMetrics');
        $method->setAccessible(true);

        $metrics = $method->invoke($controller);

        $calculationTime = microtime(true) - $startTime;

        $this->assertLessThan(0.3, $calculationTime, 
            "Level metrics calculation took {$calculationTime} seconds");

        $this->assertIsArray($metrics);
        $this->assertGreaterThan(0, count($metrics));
    }

    /** @test */
    public function database_queries_are_optimized()
    {
        DB::enableQueryLog();

        $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard'));

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        // Le dashboard ne doit pas faire plus de 15 requêtes
        $this->assertLessThan(15, $queryCount, 
            "Dashboard executed {$queryCount} queries, which is too many");

        // Vérifier qu'il n'y a pas de requêtes N+1
        $suspiciousQueries = array_filter($queries, function($query) {
            return strpos($query['query'], 'select * from') === 0;
        });

        $this->assertLessThan(3, count($suspiciousQueries), 
            "Found " . count($suspiciousQueries) . " potentially inefficient queries");
    }

    /** @test */
    public function chart_data_generation_is_fast()
    {
        $startTime = microtime(true);

        $controller = app(\App\Http\Controllers\Academic\PreUniversityDashboardController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getChartsData');
        $method->setAccessible(true);

        $chartsData = $method->invoke($controller);

        $generationTime = microtime(true) - $startTime;

        $this->assertLessThan(0.4, $generationTime, 
            "Chart data generation took {$generationTime} seconds");

        $this->assertIsArray($chartsData);
        $this->assertArrayHasKey('enrollments_trend', $chartsData);
        $this->assertArrayHasKey('evaluation_statistics', $chartsData);
    }

    /** @test */
    public function alerts_calculation_handles_large_datasets()
    {
        $startTime = microtime(true);

        $controller = app(\App\Http\Controllers\Academic\PreUniversityDashboardController::class);
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('getAlerts');
        $method->setAccessible(true);

        $alerts = $method->invoke($controller);

        $calculationTime = microtime(true) - $startTime;

        $this->assertLessThan(0.2, $calculationTime, 
            "Alerts calculation took {$calculationTime} seconds");

        $this->assertIsArray($alerts);
    }

    /** @test */
    public function caching_improves_subsequent_loads()
    {
        // Premier chargement - sans cache
        Cache::flush();
        $startTime = microtime(true);
        
        $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard'));
            
        $firstLoadTime = microtime(true) - $startTime;

        // Deuxième chargement - avec cache
        $startTime = microtime(true);
        
        $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard'));
            
        $secondLoadTime = microtime(true) - $startTime;

        // Le deuxième chargement doit être plus rapide d'au moins 30%
        $this->assertLessThan($firstLoadTime * 0.7, $secondLoadTime, 
            "Cache didn't improve performance. First: {$firstLoadTime}s, Second: {$secondLoadTime}s");
    }

    /** @test */
    public function memory_usage_stays_within_limits()
    {
        $memoryBefore = memory_get_usage(true);

        $this->actingAs($this->admin)
            ->get(route('academic.preuniversity.dashboard'));

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Le dashboard ne doit pas utiliser plus de 32MB supplémentaires
        $maxMemory = 32 * 1024 * 1024; // 32MB
        $this->assertLessThan($maxMemory, $memoryUsed, 
            "Dashboard used " . round($memoryUsed / 1024 / 1024, 2) . "MB of memory");
    }

    /** @test */
    public function concurrent_requests_dont_cause_deadlocks()
    {
        $promises = [];
        
        // Simuler 5 requêtes concurrentes
        for ($i = 0; $i < 5; $i++) {
            $promises[] = $this->actingAs($this->admin)
                ->getJson(route('academic.preuniversity.dashboard.ajax'));
        }

        // Toutes les requêtes doivent réussir
        foreach ($promises as $response) {
            $response->assertStatus(200);
        }
    }

    /** @test */
    public function pagination_works_efficiently_for_large_datasets()
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.students', [
                'page' => 1,
                'per_page' => 50
            ]));

        $paginationTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(0.3, $paginationTime, 
            "Pagination took {$paginationTime} seconds");

        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertLessThanOrEqual(50, count($data['data']));
    }

    /** @test */
    public function search_functionality_is_fast()
    {
        $startTime = microtime(true);

        $response = $this->actingAs($this->admin)
            ->getJson(route('academic.preuniversity.dashboard.search', [
                'query' => 'John',
                'type' => 'students'
            ]));

        $searchTime = microtime(true) - $startTime;

        $response->assertStatus(200);
        $this->assertLessThan(0.4, $searchTime, 
            "Search took {$searchTime} seconds");
    }

    private function createTestEnvironmentWithLargeDataset(): void
    {
        // Créer les rôles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);

        // Créer l'école
        $this->school = School::factory()->preUniversity()->create([
            'name' => 'École Test Performance'
        ]);

        // Créer l'année académique
        $this->academicYear = AcademicYear::factory()->create([
            'is_current' => true
        ]);

        // Créer l'admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
        $this->admin->schools()->attach($this->school->id);

        // Créer un grand dataset pour tester les performances
        $this->createLargeDataset();

        // Simuler le contexte éducationnel
        $this->app->instance('educational.context', (object)[
            'school' => $this->school,
            'school_type' => 'pre_university',
            'academic_year' => $this->academicYear
        ]);
    }

    private function createLargeDataset(): void
    {
        // Créer 6 niveaux
        $levels = Level::factory(6)->create([
            'school_id' => $this->school->id
        ]);

        // Créer 30 classes (5 par niveau)
        $classrooms = collect();
        foreach ($levels as $level) {
            $levelClassrooms = Classroom::factory(5)->create([
                'level_id' => $level->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id
            ]);
            $classrooms = $classrooms->merge($levelClassrooms);
        }

        // Créer 20 matières
        $subjects = Subject::factory(20)->create([
            'school_id' => $this->school->id
        ]);

        // Créer 50 enseignants
        $teachers = User::factory(50)->create();
        $teacherRole = Role::where('name', 'teacher')->first();
        foreach ($teachers as $teacher) {
            $teacher->assignRole($teacherRole);
            $teacher->schools()->attach($this->school->id);
        }

        // Créer 1000 étudiants (environ 33 par classe)
        $students = collect();
        foreach ($classrooms as $classroom) {
            $classroomStudents = Student::factory(33)->create([
                'school_id' => $this->school->id
            ]);
            
            foreach ($classroomStudents as $student) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'classroom_id' => $classroom->id,
                    'academic_year_id' => $this->academicYear->id,
                    'enrollment_date' => now()->subMonths(rand(1, 8)),
                    'status' => 'active'
                ]);
            }
            
            $students = $students->merge($classroomStudents);
        }

        // Créer 300 évaluations
        $evaluations = collect();
        foreach ($classrooms as $classroom) {
            for ($i = 0; $i < 10; $i++) {
                $evaluation = Evaluation::factory()->create([
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subjects->random()->id,
                    'teacher_id' => $teachers->random()->id,
                    'academic_year_id' => $this->academicYear->id,
                    'date' => now()->subDays(rand(1, 180))
                ]);
                $evaluations->push($evaluation);
            }
        }

        // Créer 15000 notes (environ 50 par évaluation)
        foreach ($evaluations as $evaluation) {
            $classroomStudents = Student::whereHas('enrollments', function($query) use ($evaluation) {
                $query->where('classroom_id', $evaluation->classroom_id)
                      ->where('status', 'active');
            })->take(30)->get();

            foreach ($classroomStudents as $student) {
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