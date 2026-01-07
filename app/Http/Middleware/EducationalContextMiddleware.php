<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\EducationalConfigurationService;
use App\Repositories\SchoolRepository;
use App\ValueObjects\EducationalContext;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class EducationalContextMiddleware
{
    public function __construct(
        private EducationalConfigurationService $configService,
        private SchoolRepository $schoolRepository
    ) {}

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Résoud le contexte éducatif complet
            $context = $this->resolveEducationalContext($request);
            
            // Injection du contexte dans le container
            $this->injectContextServices($context);
            
            // Cache des paramètres pour la requête
            $this->cacheSettingsForRequest($context);
            
            // Traitement de la requête
            $response = $next($request);
            
            // Ajout d'headers de contexte si en mode debug
            if (config('app.debug')) {
                $response = $this->addContextHeaders($response, $context);
            }
            
            return $response;
            
        } catch (SchoolContextRequiredException $e) {
            // Redirection vers la sélection d'école si nécessaire
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Contexte éducatif requis',
                    'message' => $e->getMessage(),
                    'redirect_to' => route('admin.schools.select'),
                ], 422);
            }
            
            return redirect()->route('admin.schools.select')
                ->with('error', $e->getMessage());
        }
    }
    
    /**
     * Résout le contexte éducatif complet
     */
    private function resolveEducationalContext(Request $request): EducationalContext
    {
        $school = $this->resolveSchool($request);
        $educationalLevel = $this->resolveEducationalLevel($request, $school);
        $academicYear = $this->resolveAcademicYear($request, $school);
        $user = Auth::user();
        
        // Récupération des permissions et du rôle
        [$userRole, $permissions] = $this->resolveUserRoleAndPermissions($user, $school);
        
        // Cache des paramètres fréquemment utilisés
        $cachedSettings = $this->preloadSettings($school, $educationalLevel);
        
        return new EducationalContext([
            'school' => $school,
            'school_type' => $school->type,
            'educational_level' => $educationalLevel,
            'academic_year' => $academicYear,
            'user_role' => $userRole,
            'permissions' => $permissions,
            'cached_settings' => $cachedSettings,
        ]);
    }
    
    /**
     * Résout l'école selon le contexte
     */
    private function resolveSchool(Request $request): School
    {
        // Priorité: Paramètre de route > Session > Utilisateur connecté > Défaut
        
        // 1. Paramètre de route explicite
        if ($schoolId = $request->route('school')) {
            if (is_object($schoolId) && $schoolId instanceof School) {
                return $schoolId;
            }
            if ($school = School::find($schoolId)) {
                session(['current_school_id' => $school->id]);
                return $school;
            }
        }
        
        // 2. Session utilisateur
        if ($schoolId = session('current_school_id')) {
            if ($school = School::find($schoolId)) {
                return $school;
            }
        }
        
        // 3. École par défaut de l'utilisateur connecté
        $user = Auth::user();
        if ($user && $user->school_id) {
            $school = School::find($user->school_id);
            if ($school) {
                session(['current_school_id' => $school->id]);
                return $school;
            }
        }
        
        // 4. Première école disponible (pour super admin)
        if ($user && $user->hasRole('super-admin')) {
            $school = School::first();
            if ($school) {
                session(['current_school_id' => $school->id]);
                return $school;
            }
        }
        
        throw new SchoolContextRequiredException('Aucun établissement sélectionné');
    }
    
    /**
     * Résout le niveau éducatif selon la route ou le contexte
     */
    private function resolveEducationalLevel(Request $request, School $school): ?string
    {
        // Paramètre explicite dans la route
        if ($level = $request->get('educational_level')) {
            return $level;
        }
        
        // Extraction depuis la route (exemple: /university/licence/students)
        $routeName = $request->route()->getName();
        if ($school->isUniversity()) {
            if (str_contains($routeName, 'licence')) return 'licence';
            if (str_contains($routeName, 'master')) return 'master';
            if (str_contains($routeName, 'doctorat')) return 'doctorat';
        } elseif ($school->isPreUniversity()) {
            if (str_contains($routeName, 'prescolaire')) return 'prescolaire';
            if (str_contains($routeName, 'primaire')) return 'primaire';
            if (str_contains($routeName, 'college')) return 'college';
            if (str_contains($routeName, 'lycee')) return 'lycee';
        }
        
        return null;
    }
    
    /**
     * Résout l'année académique
     */
    private function resolveAcademicYear(Request $request, School $school): AcademicYear
    {
        if ($yearId = $request->get('academic_year_id')) {
            $year = AcademicYear::find($yearId);
            if ($year) return $year;
        }
        
        // Année académique courante de l'école ou globale
        return $school->getCurrentAcademicYear() ?? AcademicYear::current();
    }
    
    /**
     * Résout le rôle et les permissions de l'utilisateur
     */
    private function resolveUserRoleAndPermissions(?User $user, School $school): array
    {
        if (!$user) {
            return [null, []];
        }
        
        // Récupération du rôle principal dans le contexte de l'école
        $userRole = $user->getRoleInSchool($school);
        
        // Récupération des permissions dans ce contexte
        $permissions = $user->getPermissionsInSchool($school);
        
        return [$userRole, $permissions];
    }
    
    /**
     * Précharge les paramètres fréquemment utilisés
     */
    private function preloadSettings(School $school, ?string $educationalLevel): array
    {
        $cacheKey = "preload_settings:{$school->id}:{$school->type}:" . ($educationalLevel ?? 'all');
        
        return Cache::remember($cacheKey, 1800, function () use ($school, $educationalLevel) {
            $settingsService = $this->configService->getSettingsService($school->type, $school, $educationalLevel);
            
            // Paramètres critiques à précharger
            $settings = [];
            
            try {
                $settings['age_limits'] = $settingsService->getAgeLimits();
                $settings['evaluation_thresholds'] = $settingsService->getEvaluationThresholds();
                $settings['required_documents'] = $settingsService->getRequiredDocuments();
                $settings['fee_structure'] = $settingsService->getFeeStructure();
                
                // Paramètres spécifiques selon le type
                if ($school->isPreUniversity()) {
                    $settings['promotion_rules'] = $settingsService->getPromotionRules();
                    $settings['subject_coefficients'] = $settingsService->getSubjectCoefficients();
                } elseif ($school->isUniversity()) {
                    $settings['lmd_standards'] = $settingsService->getLMDStandards();
                    $settings['validation_rules'] = $settingsService->getLMDValidationRules();
                }
            } catch (\Exception $e) {
                // Log l'erreur mais ne casse pas le middleware
                logger()->error('Erreur lors du préchargement des paramètres', [
                    'school_id' => $school->id,
                    'error' => $e->getMessage(),
                ]);
            }
            
            return $settings;
        });
    }
    
    /**
     * Injection du contexte et des services dans le container
     */
    private function injectContextServices(EducationalContext $context): void
    {
        // Injection du contexte
        app()->instance('educational.context', $context);
        app()->instance(EducationalContext::class, $context);
        
        // Injection des services spécialisés
        $settingsService = $this->configService->getSettingsService($context->school_type, $context->school, $context->educational_level);
        app()->instance('educational.settings', $settingsService);
        
        // Services métier selon le type
        if ($context->isPreUniversity()) {
            app()->instance('evaluation.service', app(\App\Services\PreUniversity\PreUniversityEvaluationService::class));
            app()->instance('enrollment.service', app(\App\Services\PreUniversity\PreUniversityEnrollmentService::class));
        } elseif ($context->isUniversity()) {
            app()->instance('evaluation.service', app(\App\Services\University\UniversityEvaluationService::class));
            app()->instance('enrollment.service', app(\App\Services\University\UniversityEnrollmentService::class));
        }
    }
    
    /**
     * Cache les paramètres pour la durée de la requête
     */
    private function cacheSettingsForRequest(EducationalContext $context): void
    {
        $cacheKey = $context->getSettingsCacheKey();
        
        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, $context->cached_settings, 3600);
        }
        
        // Instance pour la requête courante
        app()->instance('educational.cached_settings', $context->cached_settings);
    }
    
    /**
     * Ajoute des headers de debug avec le contexte
     */
    private function addContextHeaders(Response $response, EducationalContext $context): Response
    {
        $contextInfo = $context->toDebugArray();
        
        $response->headers->set('X-Educational-Context', json_encode($contextInfo));
        $response->headers->set('X-School-Id', $context->school->id);
        $response->headers->set('X-School-Type', $context->school_type);
        
        return $response;
    }
}

/**
 * Exception lancée quand le contexte éducatif est requis mais non résolu
 */
class SchoolContextRequiredException extends \Exception
{
    //
}