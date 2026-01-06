<?php

namespace App\Traits;

use App\Models\School;
use App\Services\SchoolContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Trait unifié pour gérer le contexte scolaire
 * 
 * Ce trait fournit une interface unifiée pour :
 * - Accéder au contexte école courant
 * - Valider les modes d'école (universitaire/pré-universitaire)
 * - Gérer les réponses selon le contexte
 * 
 * ⚠️ Ce trait remplace tous les anciens traits de contexte (HasUniversityContext, etc.)
 * 
 * @trait
 * @package App\Traits
 * @author EnmaSchool Core Team
 * @version 1.0 - Refactoring complet
 * @since 2026-01-06
 */
trait HasSchoolContext
{
    /**
     * Instance du service de contexte école
     * 
     * @var SchoolContextService|null
     */
    protected ?SchoolContextService $schoolContextService = null;

    /**
     * Obtenir l'instance du service de contexte école
     * 
     * @return SchoolContextService
     */
    protected function getSchoolContextService(): SchoolContextService
    {
        if (!$this->schoolContextService) {
            $this->schoolContextService = app(SchoolContextService::class);
        }

        return $this->schoolContextService;
    }

    /**
     * Obtenir l'école courante depuis le contexte
     * 
     * @return School|null L'école courante ou null
     */
    protected function getCurrentSchool(): ?School
    {
        // Priorité 1 : Récupérer depuis l'injection dans la request
        if (request()->has('current_school')) {
            return request()->input('current_school');
        }

        // Priorité 2 : Récupérer depuis le container Laravel
        try {
            $schoolFromContainer = app('current_school');
            if ($schoolFromContainer) {
                return $schoolFromContainer;
            }
        } catch (\Illuminate\Contracts\Container\BindingResolutionException $e) {
            // Le binding n'existe pas, continuer avec la priorité 3
        }

        // Priorité 3 : Récupérer via le service
        return $this->getSchoolContextService()->getCurrentSchoolContext();
    }

    /**
     * Obtenir l'école courante avec validation obligatoire
     * 
     * @return School L'école courante
     * @throws \RuntimeException Si aucune école n'est configurée
     */
    protected function getCurrentSchoolRequired(): School
    {
        $school = $this->getCurrentSchool();
        
        if (!$school) {
            throw new \RuntimeException('Aucun contexte école disponible. Veuillez configurer un établissement.');
        }
        
        return $school;
    }

    /**
     * Vérifier si l'utilisateur courant a accès au contexte école
     * 
     * @return bool True si l'utilisateur a un contexte école valide
     */
    protected function hasValidSchoolContext(): bool
    {
        return $this->getSchoolContextService()->hasValidSchoolContext();
    }

    /**
     * S'assurer qu'un contexte école existe, rediriger vers la création si ce n'est pas le cas
     * 
     * @return \Illuminate\Http\RedirectResponse|null Redirection ou null si OK
     */
    protected function ensureSchoolExists()
    {
        if (!$this->getCurrentSchool()) {
            if (request()->expectsJson()) {
                abort(422, 'Aucun établissement configuré. Veuillez configurer votre établissement.');
            }

            return redirect()->route('admin.schools.create')
                ->with('warning', 'Veuillez d\'abord configurer votre établissement.');
        }

        return null;
    }

    /**
     * S'assurer que l'école courante est en mode universitaire
     * 
     * @throws \RuntimeException Si l'école n'est pas en mode universitaire
     */
    protected function ensureUniversityMode(): void
    {
        if (!$this->isUniversityMode()) {
            throw new \RuntimeException('Cette fonctionnalité n\'est disponible qu\'en mode universitaire.');
        }
    }

    /**
     * S'assurer que l'école courante est en mode pré-universitaire
     * 
     * @throws \RuntimeException Si l'école n'est pas en mode pré-universitaire
     */
    protected function ensurePreUniversityMode(): void
    {
        if (!$this->isPreUniversityMode()) {
            throw new \RuntimeException('Cette fonctionnalité n\'est disponible qu\'en mode pré-universitaire.');
        }
    }

    /**
     * Obtenir le type d'école courante
     * 
     * @return string|null Le type d'école ('university', 'pre_university') ou null
     */
    protected function getSchoolType(): ?string
    {
        return $this->getSchoolContextService()->getCurrentSchoolType();
    }

    /**
     * Vérifier si l'école courante est en mode universitaire
     * 
     * @return bool True si l'école est universitaire
     */
    protected function isUniversityMode(): bool
    {
        return $this->getSchoolContextService()->isUniversityContext();
    }

    /**
     * Vérifier si l'école courante est en mode pré-universitaire
     * 
     * @return bool True si l'école est pré-universitaire
     */
    protected function isPreUniversityMode(): bool
    {
        return $this->getSchoolContextService()->isPreUniversityContext();
    }

    /**
     * Rediriger selon le type d'école avec un message d'erreur
     * 
     * @param string $message Message d'erreur personnalisé
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectWithSchoolTypeError(string $message = null): \Illuminate\Http\RedirectResponse
    {
        $defaultMessage = 'Cette fonctionnalité n\'est pas disponible pour le type d\'établissement configuré.';
        $errorMessage = $message ?? $defaultMessage;

        return redirect()->back()->with('error', $errorMessage);
    }

    /**
     * Obtenir l'école pour l'utilisateur courant
     * 
     * @return School|null L'école de l'utilisateur ou null
     */
    protected function getUserSchool(): ?School
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }

        return $this->getSchoolContextService()->getSchoolForUser($user);
    }

    /**
     * Vérifier si l'utilisateur a accès à une école spécifique
     * 
     * @param School $school L'école à vérifier
     * @return bool True si l'utilisateur a accès
     */
    protected function userHasSchoolAccess(School $school): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return $this->getSchoolContextService()->validateSchoolAccess($user, $school);
    }

    /**
     * Obtenir toutes les écoles accessibles à l'utilisateur courant
     * 
     * @return \Illuminate\Database\Eloquent\Collection Collection des écoles accessibles
     */
    protected function getAccessibleSchools()
    {
        $user = Auth::user();
        
        if (!$user) {
            return collect([]);
        }

        return $this->getSchoolContextService()->getAccessibleSchoolsForUser($user);
    }

    /**
     * Réinitialiser le cache du contexte école
     * 
     * Utile après des changements de configuration
     * 
     * @return void
     */
    protected function resetSchoolContextCache(): void
    {
        $this->schoolContextService = null;
        $this->getSchoolContextService()->resetContext();
    }

    /**
     * Obtenir des données de contexte école pour les vues
     * 
     * @return array Données de contexte
     */
    protected function getSchoolContextData(): array
    {
        $school = $this->getCurrentSchool();
        
        if (!$school) {
            return [
                'school' => null,
                'school_type' => null,
                'is_university' => false,
                'is_pre_university' => false,
            ];
        }

        return [
            'school' => $school,
            'school_type' => $school->type,
            'is_university' => $school->type === 'university',
            'is_pre_university' => $school->type === 'pre_university',
        ];
    }

    /**
     * Validation des permissions selon le contexte école
     * 
     * @param string $permission Nom de la permission
     * @return bool True si l'utilisateur a la permission dans le contexte école courant
     */
    protected function hasSchoolContextPermission(string $permission): bool
    {
        $user = Auth::user();
        
        if (!$user || !$this->hasValidSchoolContext()) {
            return false;
        }

        // Pour V1 : utilisation standard des permissions Spatie
        // En V2, on ajoutera la logique de permissions contextuelles par école
        return $user->hasPermissionTo($permission);
    }

    /**
     * Validation des rôles selon le contexte école
     * 
     * @param string $role Nom du rôle
     * @return bool True si l'utilisateur a le rôle dans le contexte école courant
     */
    protected function hasSchoolContextRole(string $role): bool
    {
        $user = Auth::user();
        
        if (!$user || !$this->hasValidSchoolContext()) {
            return false;
        }

        // Pour V1 : utilisation standard des rôles Spatie
        // En V2, on ajoutera la logique de rôles contextuels par école
        return $user->hasRole($role);
    }
}