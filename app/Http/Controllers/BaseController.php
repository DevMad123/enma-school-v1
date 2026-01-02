<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Traits\HasSchoolContext;
use App\Traits\HasCrudOperations;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as IlluminateController;

/**
 * Contrôleur de base pour l'application ENMA School
 * 
 * Ce contrôleur abstrait fournit :
 * - Logique commune pour tous les contrôleurs
 * - Méthodes de gestion du contexte scolaire
 * - Utilitaires de validation et réponses
 * - Traits communs pour les opérations CRUD
 * 
 * @abstract
 * @package App\Http\Controllers
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 * 
 * @uses HasSchoolContext Trait pour le contexte scolaire
 * @uses HasCrudOperations Trait pour les opérations CRUD
 */
abstract class BaseController extends IlluminateController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use HasSchoolContext, HasCrudOperations;

    /**
     * École active en cache
     * 
     * @var School|null
     */
    protected ?School $activeSchool = null;

    /**
     * Constructeur de base
     * 
     * Initialise les middlewares communs et la configuration de base
     */
    public function __construct()
    {
        // Middleware d'authentification par défaut
        $this->middleware('auth');
        
        // Middleware pour assurer l'existence d'une école
        $this->middleware('school.exists');
    }

    /**
     * Créer une réponse JSON standardisée
     * 
     * @param bool $success Indicateur de succès
     * @param string $message Message de la réponse
     * @param mixed $data Données additionnelles
     * @param int $status Code de statut HTTP
     * @return \Illuminate\Http\JsonResponse Réponse JSON
     */
    protected function jsonResponse(
        bool $success, 
        string $message, 
        $data = null, 
        int $status = 200
    ): \Illuminate\Http\JsonResponse {
        $response = [
            'success' => $success,
            'message' => $message,
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $response['timestamp'] = now()->toISOString();
        
        return response()->json($response, $status);
    }

    /**
     * Créer une réponse d'erreur standardisée
     * 
     * @param string $message Message d'erreur
     * @param mixed $errors Détails des erreurs
     * @param int $status Code de statut HTTP
     * @return \Illuminate\Http\JsonResponse Réponse JSON d'erreur
     */
    protected function errorResponse(
        string $message, 
        $errors = null, 
        int $status = 400
    ): \Illuminate\Http\JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
        
        if ($errors !== null) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $status);
    }

    /**
     * Gérer les réponses mixtes (web/API) selon le type de requête
     * 
     * @param Request $request Requête HTTP
     * @param bool $success Succès de l'opération
     * @param string $message Message
     * @param string $redirectRoute Route de redirection pour le web
     * @param array $routeParams Paramètres de la route
     * @param mixed $data Données pour l'API
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function handleMixedResponse(
        Request $request,
        bool $success,
        string $message,
        string $redirectRoute,
        array $routeParams = [],
        $data = null
    ): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse {
        if ($request->expectsJson() || $request->wantsJson()) {
            return $success 
                ? $this->jsonResponse($success, $message, $data)
                : $this->errorResponse($message, null, $success ? 200 : 400);
        }
        
        $messageType = $success ? 'success' : 'error';
        
        return redirect()->route($redirectRoute, $routeParams)
            ->with($messageType, $message);
    }

    /**
     * Valider les permissions pour une action donnée
     * 
     * @param string $permission Permission requise
     * @param mixed $resource Resource optionnelle pour la validation
     * @throws \Illuminate\Auth\Access\AuthorizationException Si non autorisé
     */
    protected function authorizeAction(string $permission, $resource = null): void
    {
        if ($resource) {
            $this->authorize($permission, $resource);
        } else {
            $this->authorize($permission);
        }
    }

    /**
     * Logger une action utilisateur
     * 
     * @param string $action Action effectuée
     * @param string $description Description de l'action
     * @param mixed $subject Sujet de l'action (model)
     * @param array $properties Propriétés additionnelles
     */
    protected function logUserAction(
        string $action,
        string $description,
        $subject = null,
        array $properties = []
    ): void {
        try {
            // Utiliser le modèle ActivityLog directement si le package activity est installé
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                \Spatie\Activitylog\Models\Activity::create([
                    'log_name' => 'default',
                    'description' => $description,
                    'subject_type' => $subject ? get_class($subject) : null,
                    'subject_id' => $subject ? $subject->id : null,
                    'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                    'causer_id' => auth()->id(),
                    'properties' => array_merge([
                        'action' => $action,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'school_id' => $this->getActiveSchool()->id ?? null,
                    ], $properties)
                ]);
            } else {
                // Fallback vers les logs Laravel standards
                \Log::info("Action utilisateur: {$description}", [
                    'action' => $action,
                    'user_id' => auth()->id(),
                    'subject' => $subject ? get_class($subject) . ':' . $subject->id : null,
                    'properties' => $properties
                ]);
            }
            
        } catch (\Exception $e) {
            // Ne pas faire échouer l'opération principale si le logging échoue
            \Log::warning('Échec du logging d\'action utilisateur', [
                'action' => $action,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Obtenir la pagination par défaut selon le type de contenu
     * 
     * @param string $type Type de contenu (list, grid, table)
     * @return int Nombre d'éléments par page
     */
    protected function getDefaultPagination(string $type = 'list'): int
    {
        return match($type) {
            'grid' => 12,
            'table' => 25,
            'list' => 15,
            default => 15
        };
    }

    /**
     * Formater une réponse de validation d'erreur
     * 
     * @param \Illuminate\Validation\Validator $validator Validateur avec erreurs
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    protected function validationErrorResponse(\Illuminate\Validation\Validator $validator)
    {
        if (request()->expectsJson()) {
            return $this->errorResponse(
                'Données invalides',
                $validator->errors()->toArray(),
                422
            );
        }
        
        return redirect()->back()
            ->withInput()
            ->withErrors($validator);
    }
}