<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\HasSchoolContext;
use App\Services\Dashboard\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du dashboard d'administration globale
 * 
 * Destiné aux rôles : super_admin, admin, directeur
 * Contexte : Universitaire + Préuniversitaire
 * 
 * @package App\Http\Controllers\Admin
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class AdminDashboardController extends Controller
{
    use HasSchoolContext;

    /**
     * Service du dashboard administrateur
     * 
     * @var AdminDashboardService
     */
    protected AdminDashboardService $dashboardService;

    /**
     * Constructeur
     * 
     * @param AdminDashboardService $dashboardService
     */
    public function __construct(AdminDashboardService $dashboardService)
    {
        $this->middleware(['auth', 'school.context']);
        $this->middleware('can:view_dashboard');
        
        $this->dashboardService = $dashboardService;
    }

    /**
     * Afficher le dashboard d'administration globale
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

        // Récupérer les données du dashboard selon le rôle et le contexte
        $dashboardData = $this->dashboardService->getAdminDashboardData($user, $school);

        // Données de contexte pour la vue
        $contextData = $this->getSchoolContextData();

        return view('dashboards.admin.index', array_merge($dashboardData, $contextData));
    }

    /**
     * Tableau de bord spécifique gouvernance (pour directeur)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function governance(Request $request)
    {
        $this->authorize('manage_school_governance');

        $user = Auth::user();
        $school = $this->getCurrentSchoolRequired();

        $governanceData = $this->dashboardService->getGovernanceDashboardData($user, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.admin.governance', array_merge($governanceData, $contextData));
    }

    /**
     * Tableau de bord supervision système (Module A6)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function supervision(Request $request)
    {
        $this->authorize('view_activity_logs');

        $user = Auth::user();
        $school = $this->getCurrentSchoolRequired();

        $supervisionData = $this->dashboardService->getSupervisionDashboardData($user, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.admin.supervision', array_merge($supervisionData, $contextData));
    }

    /**
     * Statistiques académiques globales
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function academicStats(Request $request)
    {
        $this->authorize('view_analytics');

        $school = $this->getCurrentSchoolRequired();
        $period = $request->input('period', 'current_year');

        $stats = $this->dashboardService->getAcademicStats($school, $period);

        return response()->json($stats);
    }

    /**
     * Statistiques financières globales
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function financialStats(Request $request)
    {
        $this->authorize('view_reports');

        $school = $this->getCurrentSchoolRequired();
        $period = $request->input('period', 'current_month');

        $stats = $this->dashboardService->getFinancialStats($school, $period);

        return response()->json($stats);
    }
}