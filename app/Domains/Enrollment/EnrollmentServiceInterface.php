<?php

namespace App\Domains\Enrollment;

/**
 * Interface pour les services d'inscription
 * Définit les méthodes communes pour la gestion des inscriptions
 */
interface EnrollmentServiceInterface
{
    /**
     * Inscrire un étudiant
     *
     * @param array $enrollmentData
     * @return array
     */
    public function enrollStudent(array $enrollmentData): array;

    /**
     * Valider une inscription
     *
     * @param array $enrollmentData
     * @return array
     */
    public function validateEnrollment(array $enrollmentData): array;

    /**
     * Calculer les frais d'inscription
     *
     * @param array $studentData
     * @param array $academicContext
     * @return array
     */
    public function calculateFees(array $studentData, array $academicContext): array;

    /**
     * Vérifier les prérequis d'inscription
     *
     * @param array $studentData
     * @param array $targetProgram
     * @return array
     */
    public function checkPrerequisites(array $studentData, array $targetProgram): array;

    /**
     * Obtenir les documents requis
     *
     * @param string $enrollmentType
     * @param array $context
     * @return array
     */
    public function getRequiredDocuments(string $enrollmentType, array $context = []): array;

    /**
     * Traiter le transfert d'un étudiant
     *
     * @param array $transferData
     * @return array
     */
    public function processTransfer(array $transferData): array;

    /**
     * Gérer la réinscription
     *
     * @param array $reenrollmentData
     * @return array
     */
    public function processReenrollment(array $reenrollmentData): array;

    /**
     * Calculer les statistiques d'inscription
     *
     * @param array $criteria
     * @return array
     */
    public function calculateEnrollmentStatistics(array $criteria): array;
}