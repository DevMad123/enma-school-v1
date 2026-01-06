<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Traits\HasSchoolContext;
use App\Services\Dashboard\StaffDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du dashboard de gestion opérationnelle
 * 
 * Destiné aux rôles : staff, accountant, supervisor
 * Contexte : Selon école configurée
 * 
 * @package App\Http\Controllers\Staff
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class StaffDashboardController extends Controller
{
    use HasSchoolContext;

    /**
     * Service du dashboard staff
     * 
     * @var StaffDashboardService
     */
    protected StaffDashboardService $dashboardService;

    /**
     * Constructeur
     * 
     * @param StaffDashboardService $dashboardService
     */
    public function __construct(StaffDashboardService $dashboardService)
    {
        $this->middleware(['auth', 'school.context']);
        $this->middleware('can:access_staff_dashboard');
        
        $this->dashboardService = $dashboardService;
    }

    /**
     * Afficher le dashboard de gestion opérationnelle
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
        $school = $this->getCurrentSchoolRequired();

        // Récupérer les données selon le rôle spécifique
        $dashboardData = $this->dashboardService->getStaffDashboardData($user, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.staff.index', array_merge($dashboardData, $contextData));
    }

    /**
     * Dashboard spécifique comptabilité
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function financial(Request $request)
    {
        $this->authorize('view_financial_reports');

        $user = Auth::user();
        $school = $this->getCurrentSchoolRequired();

        $financialData = $this->dashboardService->getFinancialDashboardData($user, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.staff.financial', array_merge($financialData, $contextData));
    }

    /**
     * Dashboard spécifique surveillance
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function supervision(Request $request)
    {
        $this->authorize('manage_student_discipline');

        $user = Auth::user();
        $school = $this->getCurrentSchoolRequired();

        $supervisionData = $this->dashboardService->getStudentSupervisionData($user, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.staff.supervision', array_merge($supervisionData, $contextData));
    }

    /**
     * Statistiques opérationnelles quotidiennes
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailyStats(Request $request)
    {
        $school = $this->getCurrentSchoolRequired();
        $date = $request->input('date', now()->toDateString());

        $stats = $this->dashboardService->getDailyOperationalStats($school, $date);

        return response()->json($stats);
    }

    /**
     * Liste des tâches prioritaires
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function priorityTasks(Request $request)
    {
        $user = Auth::user();
        $school = $this->getCurrentSchoolRequired();

        $tasks = $this->dashboardService->getPriorityTasks($user, $school);

        return response()->json($tasks);
    }
}