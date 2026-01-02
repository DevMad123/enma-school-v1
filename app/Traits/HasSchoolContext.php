<?php

namespace App\Traits;

use App\Models\School;
use Illuminate\Http\Request;

/**
 * Trait pour gérer le contexte scolaire
 * 
 * Ce trait fournit des méthodes pour :
 * - Obtenir l'école active
 * - Valider le mode de l'école (universitaire/pré-universitaire)
 * - Gérer les réponses selon le contexte
 * 
 * @trait
 * @package App\Traits
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 */
trait HasSchoolContext
{
    /**
     * École mise en cache
     * 
     * @var School|null
     */
    protected ?School $cachedSchool = null;

    /**
     * Get the active school
     *
     * @return School|null
     */
    protected function getActiveSchool()
    {
        if (!$this->cachedSchool) {
            $this->cachedSchool = School::getActiveSchool();
        }
        
        return $this->cachedSchool;
    }

    /**
     * Obtenir l'école active avec validation
     * 
     * @return School École active
     * @throws \RuntimeException Si aucune école n'est configurée
     */
    protected function getCurrentSchool(): School
    {
        $school = $this->getActiveSchool();
        
        if (!$school) {
            throw new \RuntimeException('Aucun établissement configuré dans le système.');
        }
        
        return $school;
    }

    /**
     * Ensure a school exists, redirect to school creation if not
     */
    protected function ensureSchoolExists()
    {
        if (!$this->getActiveSchool()) {
            return redirect()->route('admin.schools.create')
                ->with('warning', 'Veuillez d\'abord configurer votre établissement.');
        }

        return null;
    }

    /**
     * S'assurer que l'école est en mode universitaire
     * 
     * @throws \RuntimeException Si l'école n'est pas en mode universitaire
     */
    protected function ensureUniversityMode(): void
    {
        if (!$this->getCurrentSchool()->isUniversity()) {
            throw new \RuntimeException('Cette fonctionnalité n\'est disponible qu\'en mode universitaire.');
        }
    }

    /**
     * S'assurer que l'école est en mode pré-universitaire
     * 
     * @throws \RuntimeException Si l'école n'est pas en mode pré-universitaire
     */
    protected function ensurePreUniversityMode(): void
    {
        if (!$this->getCurrentSchool()->isPreUniversity()) {
            throw new \RuntimeException('Cette fonctionnalité n\'est disponible qu\'en mode pré-universitaire.');
        }
    }

    /**
     * Get a school setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getSchoolSetting(string $key, $default = null)
    {
        $school = $this->getActiveSchool();
        return $school ? $school->getSetting($key, $default) : $default;
    }

    /**
     * Obtenir le contexte de base de l'école
     * 
     * @return array<string, mixed> Contexte scolaire
     */
    protected function getSchoolContext(): array
    {
        $school = $this->getCurrentSchool();
        
        return [
            'school' => $school,
            'school_id' => $school->id,
            'school_name' => $school->name,
            'school_type' => $school->type,
            'is_university' => $school->isUniversity(),
            'is_pre_university' => $school->isPreUniversity(),
            'academic_year' => \App\Models\AcademicYear::current()->first(),
            'context_timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Obtenir le contexte universitaire avec validation
     * 
     * @return array<string, mixed> Contexte universitaire
     * @throws \RuntimeException Si pas en mode universitaire
     */
    protected function getUniversityContext(): array
    {
        $this->ensureUniversityMode();
        
        $context = $this->getSchoolContext();
        $school = $this->getCurrentSchool();
        
        // Ajouter des statistiques universitaires de base
        $context['university_stats'] = [
            'total_ufrss' => $school->ufrss()->count(),
            'total_departments' => $school->departments()->count(),
            'total_programs' => $school->programs()->count(),
            'total_students' => $school->students()->count(),
        ];
        
        return $context;
    }

    /**
     * Créer une réponse avec contexte scolaire
     * 
     * @param Request $request Requête HTTP
     * @param string $view Vue à retourner
     * @param array $data Données supplémentaires
     * @param string|null $successMessage Message de succès
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    protected function createContextualResponse(
        Request $request,
        string $view,
        array $data = [],
        ?string $successMessage = null
    ) {
        $contextData = array_merge($this->getSchoolContext(), $data);
        
        if ($request->expectsJson()) {
            $response = [
                'success' => true,
                'data' => $contextData,
            ];
            
            if ($successMessage) {
                $response['message'] = $successMessage;
            }
            
            return response()->json($response);
        }
        
        return view($view, $contextData);
    }

    /**
     * Gérer les erreurs avec contexte approprié
     * 
     * @param Request $request Requête HTTP
     * @param string $errorMessage Message d'erreur
     * @param \Exception|null $exception Exception source
     * @param string|null $fallbackRoute Route de redirection
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleContextualError(
        Request $request,
        string $errorMessage,
        ?\Exception $exception = null,
        ?string $fallbackRoute = null
    ) {
        // Logger l'erreur avec contexte
        if ($exception) {
            \Log::error('Erreur contextuelle', [
                'message' => $errorMessage,
                'exception' => $exception->getMessage(),
                'school_id' => $this->getCurrentSchool()->id ?? null,
                'user_id' => auth()->id(),
                'request_url' => $request->url(),
            ]);
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_code' => $exception ? get_class($exception) : 'GENERAL_ERROR',
                'timestamp' => now()->toISOString(),
            ], 400);
        }
        
        $redirectTarget = $fallbackRoute ? route($fallbackRoute) : back();
        
        return redirect($redirectTarget)->with('error', $errorMessage);
    }

    /**
     * Valider les permissions basées sur le contexte scolaire
     * 
     * @param string $permission Permission requise
     * @param mixed $resource Resource optionnelle
     * @param bool $requireUniversityMode Exiger le mode universitaire
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \RuntimeException
     */
    protected function validateContextualPermissions(
        string $permission,
        $resource = null,
        bool $requireUniversityMode = false
    ): void {
        // Vérifier le mode si requis
        if ($requireUniversityMode) {
            $this->ensureUniversityMode();
        }
        
        // Vérifier les permissions Laravel
        if ($resource) {
            $this->authorize($permission, $resource);
        } else {
            $this->authorize($permission);
        }
    }

    /**
     * Obtenir les routes contextuelles selon le mode de l'école
     * 
     * @param string $baseRoute Route de base
     * @return array<string, string> Routes disponibles
     */
    protected function getContextualRoutes(string $baseRoute): array
    {
        $school = $this->getCurrentSchool();
        $prefix = $school->isUniversity() ? 'university' : 'pre-university';
        
        return [
            'index' => "{$prefix}.{$baseRoute}.index",
            'create' => "{$prefix}.{$baseRoute}.create",
            'store' => "{$prefix}.{$baseRoute}.store",
            'show' => "{$prefix}.{$baseRoute}.show",
            'edit' => "{$prefix}.{$baseRoute}.edit",
            'update' => "{$prefix}.{$baseRoute}.update",
            'destroy' => "{$prefix}.{$baseRoute}.destroy",
        ];
    }

    /**
     * Créer des réponses CRUD standardisées avec contexte
     * 
     * @param Request $request Requête HTTP
     * @param bool $success Succès de l'opération
     * @param string $message Message
     * @param string $indexRoute Route d'index
     * @param array $routeParams Paramètres de route
     * @param mixed $data Données additionnelles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function createCrudResponse(
        Request $request,
        bool $success,
        string $message,
        string $indexRoute,
        array $routeParams = [],
        $data = null
    ) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
                'context' => $this->getSchoolContext(),
                'timestamp' => now()->toISOString(),
            ], $success ? 200 : 400);
        }
        
        $messageType = $success ? 'success' : 'error';
        
        return redirect()->route($indexRoute, $routeParams)
            ->with($messageType, $message);
    }
}