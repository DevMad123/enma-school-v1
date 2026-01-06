<?php

namespace App\Http\Controllers;

use App\Traits\HasSchoolContext;
use App\Services\SchoolContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur de routage intelligent des dashboards
 * 
 * Ce contrôleur sert de point d'entrée unique et redirige automatiquement
 * vers le dashboard approprié selon le rôle et le contexte de l'utilisateur.
 * 
 * @package App\Http\Controllers
 * @author EnmaSchool Core Team
 * @version 2.0 - Refactoring complet
 * @since 2026-01-06
 */
class DashboardController extends Controller
{
    use HasSchoolContext;

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->middleware(['auth', 'school.context']);
    }

    /**
     * Point d'entrée principal - Routage intelligent vers le dashboard approprié
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Vérifier le contexte école
        $redirectResponse = $this->ensureSchoolExists();
        if ($redirectResponse) {
            return $redirectResponse;
        }

        // Routage intelligent selon le rôle et le contexte
        $dashboardRoute = $this->determineDashboardRoute($user);

        if ($dashboardRoute) {
            return redirect()->route($dashboardRoute);
        }

        // Fallback vers le dashboard par défaut
        return $this->defaultDashboard();
    }

    /**
     * Dashboard par défaut pour les utilisateurs sans dashboard spécifique
     * 
     * @return \Illuminate\View\View
     */
    public function defaultDashboard()
    {
        $user = Auth::user();
        $contextData = $this->getSchoolContextData();

        return view('dashboards.default', array_merge([
            'user' => $user,
            'message' => 'Bienvenue dans EnmaSchool. Votre profil est en cours de configuration.',
            'available_actions' => $this->getAvailableActions($user),
        ], $contextData));
    }

    /**
     * API pour obtenir les informations du dashboard courant
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function currentDashboardInfo(Request $request)
    {
        $user = Auth::user();
        $school = $this->getCurrentSchool();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->getRoleNames(),
                'profile_type' => $this->getUserProfileType($user),
            ],
            'school' => [
                'id' => $school?->id,
                'name' => $school?->name,
                'type' => $school?->type,
            ],
            'dashboard' => [
                'type' => $this->getCurrentDashboardType($user),
                'route' => $this->determineDashboardRoute($user),
            ],
            'context' => $this->getSchoolContextData(),
        ]);
    }

    /**
     * Redirection forcée vers le dashboard approprié
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(Request $request)
    {
        $user = Auth::user();
        $dashboardRoute = $this->determineDashboardRoute($user);

        if ($dashboardRoute) {
            return redirect()->route($dashboardRoute)->with('info', 'Redirection vers votre espace de travail.');
        }

        return redirect()->route('dashboard.default')->with('warning', 'Profil non configuré. Contactez votre administrateur.');
    }

    /**
     * Déterminer la route de dashboard appropriée selon le rôle et le contexte
     * 
     * @param \App\Models\User $user
     * @return string|null
     */
    protected function determineDashboardRoute($user): ?string
    {
        // Dashboard Administration (priorité haute)
        if ($user->hasAnyRole(['super_admin', 'admin', 'directeur'])) {
            return 'admin.dashboard.index';
        }

        // Dashboard Enseignant
        if ($user->hasRole('teacher') || $user->isTeacher()) {
            return 'teacher.dashboard.index';
        }

        // Dashboard Étudiant (contextualisé selon le type d'école)
        if ($user->hasRole('student') || $user->isStudent()) {
            return $this->getStudentDashboardRoute($user);
        }

        // Dashboard Staff/Personnel  
        if ($user->hasAnyRole(['staff', 'accountant', 'supervisor']) || $user->isStaff()) {
            return 'staff.dashboard.index';
        }

        // Dashboard Parent (à implémenter en Phase 3)
        if ($user->hasRole('parent') || $user->isParent()) {
            return 'parent.dashboard.index'; // Route à créer
        }

        return null;
    }

    /**
     * Déterminer la route de dashboard étudiant selon le contexte école
     * 
     * @param \App\Models\User $user
     * @return string
     */
    protected function getStudentDashboardRoute($user): string
    {
        // Récupérer le type d'école depuis le contexte
        $schoolType = $this->getSchoolContextService()->getCurrentSchoolType();

        // Si pas de contexte école, utiliser l'école de l'utilisateur
        if (!$schoolType) {
            $userSchool = $this->getSchoolContextService()->getSchoolForUser($user);
            $schoolType = $userSchool ? $userSchool->type : 'pre_university';
        }

        // Retourner la route appropriée selon le type d'école
        if ($schoolType === 'university') {
            return 'student.university.dashboard.index';
        }

        return 'student.preuniversity.dashboard.index';
    }

    /**
     * Obtenir le type de profil utilisateur
     * 
     * @param \App\Models\User $user
     * @return string
     */
    protected function getUserProfileType($user): string
    {
        if ($user->isStudent()) return 'student';
        if ($user->isTeacher()) return 'teacher';
        if ($user->isParent()) return 'parent';
        if ($user->isStaff()) return 'staff';
        
        return 'user';
    }

    /**
     * Obtenir le type de dashboard courant
     * 
     * @param \App\Models\User $user
     * @return string
     */
    protected function getCurrentDashboardType($user): string
    {
        if ($user->hasAnyRole(['super_admin', 'admin', 'directeur'])) {
            return 'admin';
        }
        
        if ($user->hasRole('teacher') || $user->isTeacher()) {
            return 'teacher';
        }
        
        if ($user->hasRole('student') || $user->isStudent()) {
            $schoolType = $this->getSchoolContextService()->getCurrentSchoolType();
            return $schoolType === 'university' ? 'university_student' : 'preuniversity_student';
        }
        
        if ($user->hasAnyRole(['staff', 'accountant', 'supervisor'])) {
            return 'staff';
        }
        
        return 'default';
    }

    /**
     * Obtenir les actions disponibles pour l'utilisateur
     * 
     * @param \App\Models\User $user
     * @return array
     */
    protected function getAvailableActions($user): array
    {
        $actions = [];

        // Actions selon les permissions
        if ($user->can('view_own_data')) {
            $actions[] = [
                'title' => 'Mon Profil',
                'description' => 'Consulter et modifier mes informations',
                'route' => 'profile.edit',
                'icon' => 'user',
            ];
        }

        if ($user->can('view_dashboard')) {
            $actions[] = [
                'title' => 'Tableau de bord',
                'description' => 'Accéder à mon espace de travail',
                'route' => 'dashboard.redirect',
                'icon' => 'dashboard',
            ];
        }

        return $actions;
    }
}