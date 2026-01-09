<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Http\Controllers\Academic\PreUniversityDashboardController;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\Evaluation;
use App\Services\Settings\PreUniversitySettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use ReflectionClass;
use Carbon\Carbon;

class PreUniversityDashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    private PreUniversityDashboardController $controller;
    private School $school;
    private AcademicYear $academicYear;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->school = School::factory()->create(['type' => 'preuniversity']);
        $this->academicYear = AcademicYear::factory()->create(['is_current' => true]);
        
        $settingsService = $this->createMock(PreUniversitySettingsService::class);
        $settingsService->method('getEducationalSetting')
            ->willReturn(['assez_bien' => 10]);
        
        $this->controller = new PreUniversityDashboardController($settingsService);
    }

    /** @test */
    public function it_calculates_main_statistics_correctly()
    {
        // Créer des données de test
        $students = Student::factory()->count(50)->create();
        $classes = SchoolClass::factory()->count(5)->create(['school_id' => $this->school->id]);
        $teachers = Teacher::factory()->count(10)->create(['school_id' => $this->school->id, 'is_active' => true]);
        $subjects = Subject::factory()->count(8)->create(['school_id' => $this->school->id]);

        // Créer des inscriptions
        foreach ($students as $index => $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $classes[$index % 5]->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);
        }

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('getMainStatistics');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $this->school, $this->academicYear);

        $this->assertEquals(50, $result['total_students']);
        $this->assertEquals(5, $result['total_classes']);
        $this->assertEquals(10, $result['total_teachers']);
        $this->assertEquals(8, $result['total_subjects']);
        $this->assertIsFloat($result['success_rate']);
        $this->assertIsFloat($result['global_average']);
    }

    /** @test */
    public function it_calculates_success_rate_correctly()
    {
        // Créer 10 étudiants
        $students = Student::factory()->count(10)->create();
        $schoolClass = SchoolClass::factory()->create(['school_id' => $this->school->id]);
        $subject = Subject::factory()->create(['school_id' => $this->school->id]);

        foreach ($students as $index => $student) {
            // Créer l'inscription
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);

            // Créer une note : 6 étudiants réussissent (≥10), 4 échouent (<10)
            Grade::factory()->create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYear->id,
                'value' => $index < 6 ? 15 : 8  // 60% de réussite attendu
            ]);
        }

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calculateGlobalSuccessRate');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $this->school, $this->academicYear);

        $this->assertEquals(60.0, $result);
    }

    /** @test */
    public function it_calculates_global_average_correctly()
    {
        $students = Student::factory()->count(5)->create();
        $schoolClass = SchoolClass::factory()->create(['school_id' => $this->school->id]);
        $subject = Subject::factory()->create(['school_id' => $this->school->id]);

        $expectedGrades = [12, 15, 8, 18, 7]; // Moyenne = 12
        
        foreach ($students as $index => $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);

            Grade::factory()->create([
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $this->academicYear->id,
                'value' => $expectedGrades[$index]
            ]);
        }

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('calculateGlobalAverage');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $this->school, $this->academicYear);

        $this->assertEquals(12.0, $result);
    }

    /** @test */
    public function it_detects_late_evaluations()
    {
        $schoolClass = SchoolClass::factory()->create(['school_id' => $this->school->id]);
        
        // Créer une évaluation en retard (plus de 3 jours sans notes saisies)
        Evaluation::factory()->create([
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $this->academicYear->id,
            'evaluation_date' => now()->subDays(5), // 5 jours en arrière
        ]);

        // Créer une évaluation récente (pas en retard)
        Evaluation::factory()->create([
            'school_class_id' => $schoolClass->id,
            'academic_year_id' => $this->academicYear->id,
            'evaluation_date' => now()->subDay(), // 1 jour en arrière
        ]);

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('getLateEvaluationsCount');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $this->school, $this->academicYear);

        $this->assertEquals(1, $result); // Une seule évaluation en retard
    }

    /** @test */
    public function it_generates_level_distribution_data()
    {
        // Créer une structure de test avec cycles/niveaux/classes
        $cycle = \App\Models\Cycle::factory()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id
        ]);
        
        $level1 = \App\Models\Level::factory()->create([
            'cycle_id' => $cycle->id,
            'name' => 'CP1'
        ]);
        
        $level2 = \App\Models\Level::factory()->create([
            'cycle_id' => $cycle->id,
            'name' => 'CP2'
        ]);
        
        $class1 = SchoolClass::factory()->create([
            'school_id' => $this->school->id,
            'level_id' => $level1->id
        ]);
        
        $class2 = SchoolClass::factory()->create([
            'school_id' => $this->school->id,
            'level_id' => $level2->id
        ]);

        // Créer des élèves pour chaque classe
        $students1 = Student::factory()->count(20)->create();
        $students2 = Student::factory()->count(30)->create();

        foreach ($students1 as $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $class1->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);
        }

        foreach ($students2 as $student) {
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $class2->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);
        }

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('getLevelDistribution');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $this->school, $this->academicYear);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        
        $cp1Data = collect($result)->firstWhere('name', 'CP1');
        $cp2Data = collect($result)->firstWhere('name', 'CP2');
        
        $this->assertEquals(20, $cp1Data->student_count);
        $this->assertEquals(30, $cp2Data->student_count);
    }

    /** @test */
    public function it_generates_gender_distribution_data()
    {
        $schoolClass = SchoolClass::factory()->create(['school_id' => $this->school->id]);
        
        // Créer des étudiants avec sexes spécifiés
        $maleStudents = Student::factory()->count(15)->create();
        $femaleStudents = Student::factory()->count(10)->create();

        // Assurer que les personnes ont le bon sexe
        foreach ($maleStudents as $student) {
            $student->person()->update(['gender' => 'M']);
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);
        }

        foreach ($femaleStudents as $student) {
            $student->person()->update(['gender' => 'F']);
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active'
            ]);
        }

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('getGenderDistribution');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $this->school, $this->academicYear);
        
        $this->assertIsArray($result);
        
        $maleData = collect($result)->firstWhere('gender', 'M');
        $femaleData = collect($result)->firstWhere('gender', 'F');
        
        $this->assertEquals(15, $maleData->count);
        $this->assertEquals(10, $femaleData->count);
    }

    /** @test */
    public function it_handles_empty_data_gracefully()
    {
        // Test avec école sans données
        $emptySchool = School::factory()->create(['type' => 'preuniversity']);
        
        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('getMainStatistics');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $emptySchool, $this->academicYear);

        $this->assertEquals(0, $result['total_students']);
        $this->assertEquals(0, $result['total_classes']);
        $this->assertEquals(0, $result['total_teachers']);
        $this->assertEquals(0, $result['total_subjects']);
        $this->assertEquals(0, $result['success_rate']);
        $this->assertEquals(0, $result['global_average']);
    }

    /** @test */
    public function it_calculates_enrollment_trend_correctly()
    {
        $schoolClass = SchoolClass::factory()->create(['school_id' => $this->school->id]);
        
        // Créer des inscriptions sur différentes dates
        $dates = [
            now()->subDays(5),
            now()->subDays(4),
            now()->subDays(4), // 2 inscriptions ce jour
            now()->subDays(3),
        ];

        foreach ($dates as $date) {
            $student = Student::factory()->create();
            Enrollment::factory()->create([
                'student_id' => $student->id,
                'school_class_id' => $schoolClass->id,
                'school_id' => $this->school->id,
                'academic_year_id' => $this->academicYear->id,
                'status' => 'active',
                'created_at' => $date
            ]);
        }

        $reflection = new ReflectionClass($this->controller);
        $method = $reflection->getMethod('getEnrollmentTrend');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, $this->school, $this->academicYear);
        
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        
        // Vérifier qu'il y a 2 inscriptions pour le jour avec 2 inscriptions
        $dayWithTwoEnrollments = collect($result)->firstWhere('count', 2);
        $this->assertNotNull($dayWithTwoEnrollments);
    }
}