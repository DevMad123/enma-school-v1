<?php

namespace App\Services;

use App\Models\User;
use App\Models\School;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Service central de gestion du contexte école
 * 
 * Ce service centralise toute la logique liée au contexte école :
 * - Détermination de l'école courante d'un utilisateur
 * - Validation des accès utilisateur ↔ école
 * - Gestion du contexte école dans l'application
 * 
 * @package App\Services
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class SchoolContextService
{
    /**
     * École courante mise en cache
     * 
     * @var School|null
     */
    protected ?School $currentSchool = null;

    /**
     * Indicateur de contexte initialisé
     * 
     * @var bool
     */
    protected bool $contextInitialized = false;

    /**
     * Obtenir l'école associée à un utilisateur
     * 
     * @param User $user L'utilisateur
     * @return School|null L'école de l'utilisateur ou null
     */
    public function getSchoolForUser(User $user): ?School
    {
        // Charger la relation school si elle n'est pas déjà chargée
        if (!$user->relationLoaded('school')) {
            $user->load('school');
        }

        return $user->school;
    }

    /**
     * Définir le contexte école pour un utilisateur
     * 
     * @param User $user L'utilisateur
     * @param School $school L'école à associer
     * @return void
     * @throws InvalidArgumentException Si l'école n'est pas active
     */
    public function setUserSchoolContext(User $user, School $school): void
    {
        if (!$school->is_active) {
            throw new InvalidArgumentException("L'école '{$school->name}' n'est pas active.");
        }

        // Valider l'accès utilisateur à l'école
        if (!$this->validateSchoolAccess($user, $school)) {
            throw new InvalidArgumentException("L'utilisateur n'a pas accès à l'école '{$school->name}'.");
        }

        // Mettre à jour l'association utilisateur ↔ école
        $user->school_id = $school->id;
        $user->save();

        // Mettre à jour le cache du contexte courant
        $this->currentSchool = $school;
        $this->contextInitialized = true;

        Log::info("Contexte école défini", [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'school_id' => $school->id,
            'school_name' => $school->name
        ]);
    }

    /**
     * Obtenir le contexte école courant
     * 
     * @return School|null L'école courante ou null
     */
    public function getCurrentSchoolContext(): ?School
    {
        if (!$this->contextInitialized) {
            $this->initializeContext();
        }

        return $this->currentSchool;
    }

    /**
     * Valider l'accès d'un utilisateur à une école
     * 
     * Pour l'instant, cette validation est simple (utilisateur actif + école active).
     * En V2, on pourra ajouter des règles plus complexes (permissions, rôles contextuels, etc.)
     * 
     * @param User $user L'utilisateur
     * @param School $school L'école
     * @return bool True si l'accès est autorisé
     */
    public function validateSchoolAccess(User $user, School $school): bool
    {
        // Vérifier que l'utilisateur est actif
        if (!$user->is_active) {
            return false;
        }

        // Vérifier que l'école est active
        if (!$school->is_active) {
            return false;
        }

        // Pour V1 : l'accès est autorisé si les deux entités sont actives
        // En V2, on ajoutera ici la logique de permissions contextuelles
        return true;
    }

    /**
     * Vérifier si l'utilisateur courant a accès au contexte école
     * 
     * @return bool True si l'utilisateur a un contexte école valide
     */
    public function hasValidSchoolContext(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        $school = $this->getCurrentSchoolContext();
        
        return $school && $this->validateSchoolAccess($user, $school);
    }

    /**
     * Réinitialiser le contexte école
     * 
     * Utile pour les tests ou les changements de contexte
     * 
     * @return void
     */
    public function resetContext(): void
    {
        $this->currentSchool = null;
        $this->contextInitialized = false;
    }

    /**
     * Obtenir toutes les écoles accessibles à un utilisateur
     * 
     * Pour V1 : retourne toutes les écoles actives (l'utilisateur peut être associé à une seule école)
     * En V2 : retournera les écoles selon les permissions contextuelles
     * 
     * @param User $user L'utilisateur
     * @return \Illuminate\Database\Eloquent\Collection Collection des écoles accessibles
     */
    public function getAccessibleSchoolsForUser(User $user)
    {
        // Pour V1 : retourner toutes les écoles actives
        // En V2, on filtrera selon les permissions contextuelles de l'utilisateur
        return School::where('is_active', true)->get();
    }

    /**
     * Initialiser le contexte à partir de l'utilisateur authentifié
     * 
     * @return void
     */
    protected function initializeContext(): void
    {
        $user = Auth::user();
        
        if ($user) {
            $this->currentSchool = $this->getSchoolForUser($user);
        }

        $this->contextInitialized = true;
    }

    /**
     * Obtenir le type d'école courante (universitaire/préuniversitaire)
     * 
     * @return string|null Le type d'école ou null
     */
    public function getCurrentSchoolType(): ?string
    {
        $school = $this->getCurrentSchoolContext();
        
        return $school ? $school->type : null;
    }

    /**
     * Vérifier si l'école courante est universitaire
     * 
     * @return bool True si l'école courante est universitaire
     */
    public function isUniversityContext(): bool
    {
        return $this->getCurrentSchoolType() === 'university';
    }

    /**
     * Vérifier si l'école courante est préuniversitaire
     * 
     * @return bool True si l'école courante est préuniversitaire
     */
    public function isPreUniversityContext(): bool
    {
        return $this->getCurrentSchoolType() === 'pre_university';
    }
}