<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Evaluation;
use App\Models\Enrollment;
use App\Services\Settings\PreUniversitySettingsService;
use App\Traits\HasEducationalSettings;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PreUniversityDashboardController extends Controller
{
    use HasEducationalSettings;

    public function __construct(
        private PreUniversitySettingsService $settingsService
    ) {
        $this->middleware(['auth', 'educational_context:preuniversity']);
    }

    /**
     * Dashboard principal préuniversitaire
     */
    public function index(Request $request): View
    {
        $school = $request->attributes->get('educational_context')->school;
        $academicYear = $request->attributes->get('educational_context')->academic_year;

        // Statistiques principales
        $stats = $this->getMainStatistics($school, $academicYear);
        
        // Métriques par niveau
        $levelMetrics = $this->getLevelMetrics($school, $academicYear);
        
        // Alertes et notifications
        $alerts = $this->getAlerts($school, $academicYear);
        
        // Données pour les graphiques
        $chartsData = $this->getChartsData($school, $academicYear);
        
        // Activités récentes
        $recentActivities = $this->getRecentActivities($school, $academicYear);

        return view('dashboards.preuniversity.index', compact(
            'school',
            'academicYear',
            'stats',
            'levelMetrics',
            'alerts',
            'chartsData',
            'recentActivities'
        ));
    }

    /**
     * Statistiques principales
     */
    private function getMainStatistics(School $school, AcademicYear $academicYear): array
    {
        $totalStudents = Student::whereHas('enrollments', function($query) use ($school, $academicYear) {
            $query->where('school_id', $school->id)
                  ->where('academic_year_id', $academicYear->id)
                  ->where('status', 'active');
        })->count();

        $totalClasses = SchoolClass::where('school_id', $school->id)
            ->whereHas('level.cycle.academicYear', function($query) use ($academicYear) {
                $query->where('id', $academicYear->id);
            })
            ->count();

        $totalTeachers = Teacher::where('school_id', $school->id)
            ->where('is_active', true)
            ->count();

        $totalSubjects = Subject::where('school_id', $school->id)
            ->whereHas('level.cycle.academicYear', function($query) use ($academicYear) {
                $query->where('id', $academicYear->id);
            })
            ->count();

        // Taux de réussite global
        $successRate = $this->calculateGlobalSuccessRate($school, $academicYear);

        // Moyenne générale
        $globalAverage = $this->calculateGlobalAverage($school, $academicYear);

        return [
            'total_students' => $totalStudents,
            'total_classes' => $totalClasses,
            'total_teachers' => $totalTeachers,
            'total_subjects' => $totalSubjects,
            'success_rate' => $successRate,
            'global_average' => $globalAverage,
        ];
    }

    /**
     * Métriques par niveau éducatif
     */
    private function getLevelMetrics(School $school, AcademicYear $academicYear): array
    {
        return DB::table('levels as l')
            ->join('cycles as c', 'l.cycle_id', '=', 'c.id')
            ->join('school_classes as sc', 'l.id', '=', 'sc.level_id')
            ->leftJoin('enrollments as e', function($join) use ($academicYear) {
                $join->on('sc.id', '=', 'e.school_class_id')
                     ->where('e.academic_year_id', $academicYear->id)
                     ->where('e.status', 'active');
            })
            ->leftJoin('students as s', 'e.student_id', '=', 's.id')
            ->where('c.school_id', $school->id)
            ->where('c.academic_year_id', $academicYear->id)
            ->select([
                'l.id',
                'l.name as level_name',
                'c.name as cycle_name',
                DB::raw('COUNT(DISTINCT sc.id) as total_classes'),
                DB::raw('COUNT(DISTINCT s.id) as total_students'),
                DB::raw('COALESCE(AVG(sc.capacity), 0) as average_capacity'),
                DB::raw('CASE 
                    WHEN AVG(sc.capacity) > 0 
                    THEN (COUNT(DISTINCT s.id) / (COUNT(DISTINCT sc.id) * AVG(sc.capacity))) * 100 
                    ELSE 0 
                END as occupancy_rate')
            ])
            ->groupBy('l.id', 'l.name', 'c.name')
            ->orderBy('c.name')
            ->orderBy('l.name')
            ->get()
            ->toArray();
    }

    /**
     * Alertes et notifications importantes
     */
    private function getAlerts(School $school, AcademicYear $academicYear): array
    {
        $alerts = [];

        // Classes surchargées
        $overcrowdedClasses = SchoolClass::where('school_id', $school->id)
            ->whereHas('level.cycle.academicYear', function($query) use ($academicYear) {
                $query->where('id', $academicYear->id);
            })
            ->withCount(['enrollments as active_students' => function($query) use ($academicYear) {
                $query->where('academic_year_id', $academicYear->id)
                      ->where('status', 'active');
            }])
            ->get()
            ->filter(function($class) {
                return $class->active_students > $class->capacity;
            });

        if ($overcrowdedClasses->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Classes surchargées',
                'message' => $overcrowdedClasses->count() . ' classe(s) dépassent leur capacité',
                'count' => $overcrowdedClasses->count(),
                'route' => 'academic.classes.index'
            ];
        }

        // Enseignants sans affectation
        $unassignedTeachers = Teacher::where('school_id', $school->id)
            ->where('is_active', true)
            ->whereDoesntHave('assignments', function($query) use ($academicYear) {
                $query->where('academic_year_id', $academicYear->id)
                      ->where('is_active', true);
            })
            ->count();

        if ($unassignedTeachers > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Enseignants non affectés',
                'message' => $unassignedTeachers . ' enseignant(s) sans affectation',
                'count' => $unassignedTeachers,
                'route' => 'teacher-assignments.index'
            ];
        }

        // Évaluations en retard
        $lateEvaluations = $this->getLateEvaluationsCount($school, $academicYear);
        if ($lateEvaluations > 0) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Évaluations en retard',
                'message' => $lateEvaluations . ' évaluation(s) non saisies',
                'count' => $lateEvaluations,
                'route' => 'evaluations.index'
            ];
        }

        return $alerts;
    }

    /**
     * Données pour les graphiques
     */
    private function getChartsData(School $school, AcademicYear $academicYear): array
    {
        // Distribution par niveau
        $levelDistribution = $this->getLevelDistribution($school, $academicYear);
        
        // Évolution des inscriptions
        $enrollmentTrend = $this->getEnrollmentTrend($school, $academicYear);
        
        // Répartition par sexe
        $genderDistribution = $this->getGenderDistribution($school, $academicYear);
        
        // Moyennes par classe
        $classAverages = $this->getClassAverages($school, $academicYear);

        return [
            'level_distribution' => $levelDistribution,
            'enrollment_trend' => $enrollmentTrend,
            'gender_distribution' => $genderDistribution,
            'class_averages' => $classAverages,
        ];
    }

    /**
     * Activités récentes
     */
    private function getRecentActivities(School $school, AcademicYear $academicYear): array
    {
        // Inscriptions récentes
        $recentEnrollments = Enrollment::with(['student.person', 'schoolClass.level'])
            ->where('school_id', $school->id)
            ->where('academic_year_id', $academicYear->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Évaluations récentes
        $recentEvaluations = Evaluation::with(['subject', 'schoolClass'])
            ->whereHas('schoolClass', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })
            ->where('academic_year_id', $academicYear->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return [
            'enrollments' => $recentEnrollments->map(function($enrollment) {
                return [
                    'type' => 'enrollment',
                    'title' => 'Nouvelle inscription',
                    'description' => $enrollment->student->person->full_name . ' en ' . $enrollment->schoolClass->level->name,
                    'date' => $enrollment->created_at,
                    'route' => 'enrollments.show',
                    'route_params' => ['enrollment' => $enrollment->id]
                ];
            }),
            'evaluations' => $recentEvaluations->map(function($evaluation) {
                return [
                    'type' => 'evaluation',
                    'title' => 'Nouvelle évaluation',
                    'description' => $evaluation->subject->name . ' - ' . $evaluation->schoolClass->name,
                    'date' => $evaluation->created_at,
                    'route' => 'evaluations.show',
                    'route_params' => ['evaluation' => $evaluation->id]
                ];
            })
        ];
    }

    /**
     * Calculs des métriques
     */
    private function calculateGlobalSuccessRate(School $school, AcademicYear $academicYear): float
    {
        $thresholds = $this->getEducationalSetting('evaluation', 'thresholds');
        $passingGrade = $thresholds['assez_bien'] ?? 10;

        $totalStudents = Student::whereHas('enrollments', function($query) use ($school, $academicYear) {
            $query->where('school_id', $school->id)
                  ->where('academic_year_id', $academicYear->id)
                  ->where('status', 'active');
        })->count();

        if ($totalStudents === 0) return 0;

        $passingStudents = Student::whereHas('enrollments', function($query) use ($school, $academicYear) {
            $query->where('school_id', $school->id)
                  ->where('academic_year_id', $academicYear->id)
                  ->where('status', 'active');
        })
        ->whereHas('grades', function($query) use ($academicYear, $passingGrade) {
            $query->where('academic_year_id', $academicYear->id)
                  ->havingRaw('AVG(value) >= ?', [$passingGrade]);
        })
        ->count();

        return round(($passingStudents / $totalStudents) * 100, 2);
    }

    private function calculateGlobalAverage(School $school, AcademicYear $academicYear): float
    {
        $average = DB::table('grades as g')
            ->join('students as s', 'g.student_id', '=', 's.id')
            ->join('enrollments as e', 's.id', '=', 'e.student_id')
            ->where('e.school_id', $school->id)
            ->where('g.academic_year_id', $academicYear->id)
            ->where('e.academic_year_id', $academicYear->id)
            ->where('e.status', 'active')
            ->avg('g.value');

        return round($average ?? 0, 2);
    }

    private function getLateEvaluationsCount(School $school, AcademicYear $academicYear): int
    {
        // Compter les évaluations qui devaient être saisies mais ne le sont pas encore
        return Evaluation::whereHas('schoolClass', function($query) use ($school) {
            $query->where('school_id', $school->id);
        })
        ->where('academic_year_id', $academicYear->id)
        ->where('evaluation_date', '<=', now()->subDays(3))
        ->whereDoesntHave('grades')
        ->count();
    }

    private function getLevelDistribution(School $school, AcademicYear $academicYear): array
    {
        return DB::table('levels as l')
            ->join('cycles as c', 'l.cycle_id', '=', 'c.id')
            ->join('school_classes as sc', 'l.id', '=', 'sc.level_id')
            ->join('enrollments as e', 'sc.id', '=', 'e.school_class_id')
            ->join('students as s', 'e.student_id', '=', 's.id')
            ->where('c.school_id', $school->id)
            ->where('e.academic_year_id', $academicYear->id)
            ->where('e.status', 'active')
            ->select('l.name', DB::raw('COUNT(s.id) as student_count'))
            ->groupBy('l.id', 'l.name')
            ->orderBy('l.name')
            ->get()
            ->toArray();
    }

    private function getEnrollmentTrend(School $school, AcademicYear $academicYear): array
    {
        return DB::table('enrollments as e')
            ->where('e.school_id', $school->id)
            ->where('e.academic_year_id', $academicYear->id)
            ->where('e.status', 'active')
            ->select(
                DB::raw('DATE(e.created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('e.created_at', '>=', $academicYear->start_date)
            ->groupBy(DB::raw('DATE(e.created_at)'))
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    private function getGenderDistribution(School $school, AcademicYear $academicYear): array
    {
        return DB::table('students as s')
            ->join('people as p', 's.person_id', '=', 'p.id')
            ->join('enrollments as e', 's.id', '=', 'e.student_id')
            ->where('e.school_id', $school->id)
            ->where('e.academic_year_id', $academicYear->id)
            ->where('e.status', 'active')
            ->select('p.gender', DB::raw('COUNT(*) as count'))
            ->groupBy('p.gender')
            ->get()
            ->toArray();
    }

    private function getClassAverages(School $school, AcademicYear $academicYear): array
    {
        return DB::table('school_classes as sc')
            ->leftJoin('enrollments as e', function($join) use ($academicYear) {
                $join->on('sc.id', '=', 'e.school_class_id')
                     ->where('e.academic_year_id', $academicYear->id)
                     ->where('e.status', 'active');
            })
            ->leftJoin('grades as g', function($join) use ($academicYear) {
                $join->on('e.student_id', '=', 'g.student_id')
                     ->where('g.academic_year_id', $academicYear->id);
            })
            ->where('sc.school_id', $school->id)
            ->select(
                'sc.name as class_name',
                DB::raw('ROUND(AVG(g.value), 2) as average')
            )
            ->groupBy('sc.id', 'sc.name')
            ->orderBy('sc.name')
            ->get()
            ->toArray();
    }
}