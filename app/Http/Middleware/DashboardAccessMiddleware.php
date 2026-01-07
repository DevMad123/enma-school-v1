<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\SchoolContextService;

/**
 * Middleware de routage intelligent des dashboards
 * 
 * Ce middleware redirige automatiquement vers le dashboard approprié
 * selon le rôle de l'utilisateur et le contexte école.
 * 
 * @package App\Http\Middleware
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class DashboardAccessMiddleware
{
    /**
     * Instance du service de contexte école
     * 
     * @var SchoolContextService
     */
    protected SchoolContextService $schoolContextService;

    /**
     * Constructeur
     * 
     * @param SchoolContextService $schoolContextService
     */
    public function __construct(SchoolContextService $schoolContextService)
    {
        $this->schoolContextService = $schoolContextService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null $dashboardType Type de dashboard requis (admin, staff, teacher, student)
     */
    public function handle(Request $request, Closure $next, ?string $dashboardType = null): Response
    {
        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Debug logging
        Log::info('DashboardAccessMiddleware Debug', [
            'user_email' => $user->email,
            'dashboard_type' => $dashboardType,
            'user_roles' => $user->getRoleNames()->toArray(),
            'has_super_admin' => $user->hasRole('super_admin'),
            'has_any_admin_role' => $user->hasAnyRole(['super_admin', 'admin', 'directeur']),
            'access_check_result' => $dashboardType ? $this->hasAccessToDashboard($user, $dashboardType) : 'no_dashboard_type'
        ]);

        // Si un type de dashboard spécifique est requis, vérifier les permissions
        if ($dashboardType && !$this->hasAccessToDashboard($user, $dashboardType)) {
            return response()->view('errors.403', [
                'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page. Contactez votre administrateur si vous pensez qu\'il s\'agit d\'une erreur.',
                'user' => $user,
                'requested_dashboard' => $dashboardType,
                'current_url' => $request->url(),
                'error_code' => '403-' . now()->format('YmdHis'),
            ], 403);
        }

        return $next($request);
    }

    /**
     * Déterminer la route de dashboard appropriée pour un utilisateur
     * 
     * @param \App\Models\User $user
     * @return string|null
     */
    protected function determineDashboardRoute($user): ?string
    {
        // Dashboard Administration (priorité haute)
        if ($user->hasAnyRole(['super_admin', 'admin', 'directeur'])) {
            return 'admin.dashboard';
        }

        // Dashboard Enseignant
        if ($user->hasRole('teacher') || $user->isTeacher()) {
            return 'teacher.dashboard';
        }

        // Dashboard Étudiant (contextualisé)
        if ($user->hasRole('student') || $user->isStudent()) {
            return $this->getStudentDashboardRoute($user);
        }

        // Dashboard Staff/Personnel  
        if ($user->hasAnyRole(['staff', 'accountant', 'supervisor']) || $user->isStaff()) {
            return 'staff.dashboard';
        }

        // Dashboard Parent (à implémenter)
        if ($user->hasRole('parent') || $user->isParent()) {
            return 'parent.dashboard'; // À créer en Phase 3
        }

        // Pas de dashboard spécifique trouvé
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
        $schoolType = $this->schoolContextService->getCurrentSchoolType();

        // Si pas de contexte école, utiliser l'école de l'utilisateur
        if (!$schoolType) {
            $userSchool = $this->schoolContextService->getSchoolForUser($user);
            $schoolType = $userSchool ? $userSchool->type : 'pre_university'; // Défaut préuniversitaire
        }

        // Retourner la route appropriée selon le type d'école
        if ($schoolType === 'university') {
            return 'student.university.dashboard';
        }

        return 'student.preuniversity.dashboard';
    }

    /**
     * Vérifier les permissions d'accès à un dashboard spécifique
     * 
     * @param \App\Models\User $user
     * @param string $dashboardType
     * @return bool
     */
    protected function hasAccessToDashboard($user, string $dashboardType): bool
    {
        // Vérifications de rôles selon le type de dashboard
        switch ($dashboardType) {
            case 'admin':
                return $user->hasAnyRole(['super_admin', 'admin', 'directeur']);
            case 'staff':
                return $user->hasAnyRole(['staff', 'accountant', 'supervisor']) || $user->isStaff();
            case 'teacher':
                return $user->hasRole('teacher') || $user->isTeacher();
            case 'student':
                return $user->hasRole('student') || $user->isStudent();
            case 'student.university':
            case 'student.preuniversity':
                return $user->hasRole('student') || $user->isStudent();
            default:
                return false;
        }
    }

    /**
     * Obtenir l'URL du dashboard par défaut si aucun dashboard spécifique n'est trouvé
     * 
     * @return string
     */
    protected function getDefaultDashboardRoute(): string
    {
        return 'dashboard.default';
    }
}
