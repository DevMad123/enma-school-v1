<?php

namespace App\Domains\Deliberation;

/**
 * Interface pour les services de délibération
 * Définit les méthodes communes pour la gestion des conseils/jurys
 */
interface DeliberationServiceInterface
{
    /**
     * Organiser une session de délibération
     *
     * @param array $sessionData
     * @return array
     */
    public function organizeDeliberationSession(array $sessionData): array;

    /**
     * Calculer les résultats pour délibération
     *
     * @param array $academicContext
     * @return array
     */
    public function calculateDeliberationResults(array $academicContext): array;

    /**
     * Déterminer les décisions finales
     *
     * @param array $studentsResults
     * @param array $deliberationRules
     * @return array
     */
    public function determineFinalDecisions(array $studentsResults, array $deliberationRules): array;

    /**
     * Générer le procès-verbal de délibération
     *
     * @param array $sessionData
     * @param array $decisions
     * @return array
     */
    public function generateDeliberationReport(array $sessionData, array $decisions): array;

    /**
     * Valider les décisions de délibération
     *
     * @param array $decisions
     * @return array
     */
    public function validateDeliberationDecisions(array $decisions): array;

    /**
     * Notifier les décisions aux étudiants
     *
     * @param array $decisions
     * @param array $notificationOptions
     * @return array
     */
    public function notifyDeliberationResults(array $decisions, array $notificationOptions = []): array;

    /**
     * Gérer les recours/appels
     *
     * @param array $appealData
     * @return array
     */
    public function processAppeal(array $appealData): array;

    /**
     * Obtenir l'historique des délibérations
     *
     * @param array $criteria
     * @return array
     */
    public function getDeliberationHistory(array $criteria): array;
}