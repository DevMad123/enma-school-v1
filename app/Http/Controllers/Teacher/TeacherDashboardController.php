<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Traits\HasSchoolContext;
use App\Services\Dashboard\TeacherDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du dashboard enseignant
 * 
 * Destiné aux rôles : teacher
 * Contexte : Selon type d'école (universitaire/préuniversitaire)
 * 
 * @package App\Http\Controllers\Teacher
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class TeacherDashboardController extends Controller
{
    use HasSchoolContext;

    /**
     * Service du dashboard enseignant
     * 
     * @var TeacherDashboardService
     */
    protected TeacherDashboardService $dashboardService;

    /**
     * Constructeur
     * 
     * @param TeacherDashboardService $dashboardService
     */
    public function __construct(TeacherDashboardService $dashboardService)
    {
        $this->middleware(['auth', 'school.context']);
        $this->middleware('can:access_teacher_dashboard');
        
        $this->dashboardService = $dashboardService;
    }

    /**
     * Afficher le dashboard enseignant
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Vérifier le contexte école
        $redirectResponse = $this->ensureSchoolExists();
        if ($redirectResponse) {
            return $redirectResponse;
        }

        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Profil enseignant non trouvé.');
        }

        $school = $this->getCurrentSchoolRequired();

        // Récupérer les données contextualisées selon le type d'école
        if ($this->isUniversityMode()) {
            $dashboardData = $this->dashboardService->getUniversityTeacherData($teacher, $school);
            $viewName = 'dashboards.teacher.university';
        } else {
            $dashboardData = $this->dashboardService->getPreUniversityTeacherData($teacher, $school);
            $viewName = 'dashboards.teacher.preuniversity';
        }

        $contextData = $this->getSchoolContextData();

        return view($viewName, array_merge($dashboardData, $contextData));
    }

    /**
     * Planning des cours
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function schedule(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $school = $this->getCurrentSchoolRequired();

        $scheduleData = $this->dashboardService->getTeacherSchedule($teacher, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.teacher.schedule', array_merge($scheduleData, $contextData));
    }

    /**
     * Gestion des évaluations
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function evaluations(Request $request)
    {
        $this->authorize('manage_evaluations');

        $user = Auth::user();
        $teacher = $user->teacher;
        $school = $this->getCurrentSchoolRequired();

        $evaluationData = $this->dashboardService->getTeacherEvaluations($teacher, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.teacher.evaluations', array_merge($evaluationData, $contextData));
    }

    /**
     * Suivi des classes
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function classes(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $school = $this->getCurrentSchoolRequired();

        $classesData = $this->dashboardService->getTeacherClasses($teacher, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.teacher.classes', array_merge($classesData, $contextData));
    }

    /**
     * Statistiques de performance des étudiants
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function studentStats(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $school = $this->getCurrentSchoolRequired();
        
        $classId = $request->input('class_id');
        $period = $request->input('period', 'current_term');

        $stats = $this->dashboardService->getStudentPerformanceStats($teacher, $school, $classId, $period);

        return response()->json($stats);
    }

    /**
     * Deadlines et échéances
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deadlines(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacher;
        $school = $this->getCurrentSchoolRequired();

        $deadlines = $this->dashboardService->getUpcomingDeadlines($teacher, $school);

        return response()->json($deadlines);
    }
}