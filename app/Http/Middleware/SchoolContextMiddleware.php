<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SchoolContextService;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

/**
 * Middleware central de gestion du contexte école
 * 
 * Ce middleware s'exécute après l'authentification et :
 * - Récupère le contexte école via le SchoolContextService
 * - Injecte le contexte dans la request et le container Laravel
 * - Partage les données de contexte avec les vues
 * - Redirige vers la configuration si aucune école n'est disponible
 * 
 * Ce middleware REMPLACE EnsureSchoolExists et centralise toute la logique de contexte.
 * 
 * @package App\Http\Middleware
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class SchoolContextMiddleware
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
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Routes exemptées qui n'ont pas besoin de contexte école
        $exemptRoutes = [
            'admin.schools.*',      // Gestion des écoles
            'login',
            'logout', 
            'password.*',
            'register',
            'verification.*',
            'profile.*',
            'settings.*',           // Paramètres généraux
            'api.health',           // API de santé
        ];

        // Vérifier si la route actuelle est exemptée
        foreach ($exemptRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Vérifier que l'utilisateur est authentifié
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Récupérer le contexte école pour l'utilisateur
        $school = $this->schoolContextService->getSchoolForUser($user);

        // Si l'utilisateur n'a pas d'école assignée, essayer de lui en assigner une
        if (!$school) {
            $school = $this->attemptSchoolAssignment($user);
        }

        // Si toujours pas d'école disponible, rediriger vers la configuration
        if (!$school) {
            return $this->handleMissingSchool($request);
        }

        // Valider l'accès de l'utilisateur à cette école
        if (!$this->schoolContextService->validateSchoolAccess($user, $school)) {
            return $this->handleInvalidSchoolAccess($request, $school);
        }

        // Injecter le contexte école dans l'application
        $this->injectSchoolContext($request, $school);

        return $next($request);
    }

    /**
     * Tenter d'assigner une école à un utilisateur qui n'en a pas
     * 
     * @param \App\Models\User $user
     * @return \App\Models\School|null
     */
    protected function attemptSchoolAssignment($user): ?School
    {
        // Récupérer la première école active disponible
        $activeSchool = School::where('is_active', true)->first();

        if ($activeSchool) {
            try {
                $this->schoolContextService->setUserSchoolContext($user, $activeSchool);
                return $activeSchool;
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas bloquer la requête
                \Log::warning("Impossible d'assigner l'école à l'utilisateur", [
                    'user_id' => $user->id,
                    'school_id' => $activeSchool->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return null;
    }

    /**
     * Gérer le cas où aucune école n'est disponible
     * 
     * @param Request $request
     * @return Response
     */
    protected function handleMissingSchool(Request $request): Response
    {
        // Pour les requêtes AJAX/API, retourner une erreur JSON
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Aucune école configurée',
                'message' => 'Veuillez configurer une école avant d\'accéder à cette fonctionnalité.',
                'redirect_url' => route('admin.schools.index')
            ], 422);
        }

        // Pour les requêtes web, rediriger vers la gestion des écoles
        if ($request->routeIs('admin.*')) {
            return redirect()->route('admin.schools.index')
                ->with('error', 'Aucune école configurée. Veuillez créer et activer une école.');
        }

        // Sinon, rediriger vers le tableau de bord avec un message
        return redirect()->route('dashboard')
            ->with('warning', 'Configuration incomplète. Contactez votre administrateur.');
    }

    /**
     * Gérer le cas où l'utilisateur n'a pas accès à l'école
     * 
     * @param Request $request
     * @param School $school
     * @return Response
     */
    protected function handleInvalidSchoolAccess(Request $request, School $school): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Accès école non autorisé',
                'message' => "Vous n'avez pas accès à l'école {$school->name}.",
            ], 403);
        }

        return redirect()->route('dashboard')
            ->with('error', "Accès non autorisé à l'école {$school->name}.");
    }

    /**
     * Injecter le contexte école dans l'application
     * 
     * @param Request $request
     * @param School $school
     * @return void
     */
    protected function injectSchoolContext(Request $request, School $school): void
    {
        // Injecter dans la request pour les contrôleurs
        $request->merge(['current_school' => $school]);
        
        // Injecter dans le container Laravel
        app()->instance('current_school', $school);
        app()->instance('school_context', $this->schoolContextService);

        // Partager avec toutes les vues
        View::share('currentSchool', $school);
        View::share('schoolType', $school->type);
        View::share('isUniversity', $school->type === 'university');
        View::share('isPreUniversity', $school->type === 'pre_university');

        // Mettre à jour le contexte dans le service (cache)
        $this->schoolContextService->resetContext(); // Reset pour éviter les conflits
    }
}
