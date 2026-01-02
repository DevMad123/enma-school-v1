<?php

namespace App\Traits;

use App\Models\School;
use Illuminate\Http\Request;

/**
 * Trait pour fournir le contexte universitaire aux contrôleurs
 * 
 * Ce trait permet aux contrôleurs de :
 * - Obtenir le contexte universitaire standardisé
 * - Accéder aux informations de l'école active
 * - Récupérer les statistiques de base
 * 
 * @trait HasUniversityContext
 * @package App\Traits
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 * 
 * @method array getUniversityContext(?Request $request = null)
 * @method School getActiveSchool()
 * @method bool isUniversityMode()
 */
trait HasUniversityContext
{
    /**
     * École active en cache
     * 
     * @var School|null
     */
    protected $activeSchool;
    
    /**
     * Obtenir le contexte universitaire depuis le middleware ou la session
     * 
     * Cette méthode centralise l'obtention du contexte universitaire en :
     * 1. Récupérant d'abord depuis le middleware si disponible
     * 2. Puis depuis l'école active par défaut
     * 3. Calculant les statistiques de base
     * 
     * @param Request|null $request Requête HTTP courante
     * @return array<string, mixed> Contexte universitaire complet
     * 
     * @throws \Exception Si aucune école active n'est trouvée
     */
    protected function getUniversityContext(?Request $request = null): array
    {
        // Récupérer le contexte depuis le middleware si disponible
        if ($request && $request->has('university_context')) {
            $context = $request->get('university_context');
            $school = $context['school'];
        } else {
            $school = $this->getActiveSchool();
        }
        
        if (!$school) {
            throw new \Exception('Aucun établissement configuré pour le mode universitaire.');
        }
        
        // Mise en cache de l'école active
        $this->activeSchool = $school;
        
        return [
            'school' => $school,
            'isUniversity' => true,
            'isPreUniversity' => false,
            'totalUFRs' => $school->ufrs()->count(),
            'totalDepartments' => $school->departments()->count(), 
            'totalPrograms' => $school->programs()->count(),
            'activePrograms' => $school->programs()->active()->count(),
            'currentAcademicYear' => \App\Models\AcademicYear::current()->first(),
            'universityStats' => $this->getBasicUniversityStats($school)
        ];
    }
    
    /**
     * Obtenir l'école active
     * 
     * @return School|null École active ou null si non trouvée
     */
    protected function getActiveSchool(): ?School
    {
        if ($this->activeSchool) {
            return $this->activeSchool;
        }
        
        $this->activeSchool = School::getActiveSchool();
        return $this->activeSchool;
    }
    
    /**
     * Vérifier si l'application est en mode universitaire
     * 
     * @return bool True si en mode universitaire
     */
    protected function isUniversityMode(): bool
    {
        $school = $this->getActiveSchool();
        return $school && $school->isUniversity();
    }
    
    /**
     * Obtenir le service universitaire (injection paresseuse)
     * 
     * @return \App\Services\UniversityService Instance du service
     */
    protected function getUniversityService(): \App\Services\UniversityService
    {
        return app(\App\Services\UniversityService::class);
    }
    
    /**
     * Valider que l'école est en mode universitaire
     * 
     * @throws \Exception Si l'école n'est pas configurée pour l'université
     */
    protected function ensureUniversityMode(): void
    {
        if (!$this->isUniversityMode()) {
            throw new \Exception(
                'Cette fonctionnalité n\'est disponible qu\'en mode universitaire. '
                . 'Veuillez configurer l\'établissement comme université.'
            );
        }
    }
    
    /**
     * Créer une réponse standardisée pour les opérations CRUD
     * 
     * @param string $operation Type d'opération (création, modification, suppression)
     * @param string $entity Type d'entité (UFR, département, programme, etc.)
     * @param bool $success Succès de l'opération
     * @param string|null $entityName Nom de l'entité pour le message
     * @param string|null $redirectRoute Route de redirection
     * @param array $routeParams Paramètres pour la route de redirection
     * @return \Illuminate\Http\RedirectResponse Réponse de redirection
     */
    protected function createCrudResponse(
        string $operation, 
        string $entity, 
        bool $success, 
        ?string $entityName = null,
        ?string $redirectRoute = null,
        array $routeParams = []
    ): \Illuminate\Http\RedirectResponse {
        
        $operations = [
            'create' => ['créé', 'créée', 'créé'],
            'update' => ['modifié', 'modifiée', 'mis à jour'], 
            'delete' => ['supprimé', 'supprimée', 'supprimé']
        ];
        
        $entities = [
            'ufr' => ['UFR', 'f'],
            'department' => ['département', 'm'],
            'program' => ['programme', 'm'],
            'semester' => ['semestre', 'm'],
            'course_unit' => ['unité d\'enseignement', 'f']
        ];
        
        $operationData = $operations[$operation] ?? ['traité', 'traitée', 'traité'];
        $entityData = $entities[$entity] ?? [$entity, 'm'];
        
        $gender = $entityData[1];
        $verb = $operationData[$gender === 'f' ? 1 : 0];
        
        if ($success) {
            $message = $entityData[0] . ($entityName ? " '{$entityName}'" : '') . " {$verb} avec succès.";
            $messageType = 'success';
        } else {
            $message = "Erreur lors de l'opération sur " . $entityData[0] . ($entityName ? " '{$entityName}'" : '') . ".";
            $messageType = 'error';
        }
        
        $redirect = redirect();
        
        if ($redirectRoute) {
            $redirect = $redirect->route($redirectRoute, $routeParams);
        } else {
            $redirect = $redirect->back();
        }
        
        return $redirect->with($messageType, ucfirst($message));
    }
    
    /**
     * Obtenir les statistiques de base pour une école universitaire
     * 
     * @param School $school École pour laquelle calculer les statistiques
     * @return array<string, int> Statistiques de base
     */
    private function getBasicUniversityStats(School $school): array
    {
        return [
            'total_students' => $school->students()->count(),
            'active_students' => $school->students()->where('status', 'active')->count(),
            'total_classes' => $school->classes()->count(),
            'total_ufrs' => $school->ufrs()->count(),
            'total_departments' => $school->departments()->count(),
            'total_programs' => $school->programs()->count(),
            'active_programs' => $school->programs()->where('is_active', true)->count(),
            'total_staff' => $school->staff()->count()
        ];
    }
}