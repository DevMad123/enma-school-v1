<?php

namespace App\Domains\Evaluation;

use Illuminate\Support\Collection;

/**
 * Interface commune pour les systèmes d'évaluation
 * Définit les méthodes standard pour le calcul des notes et moyennes
 */
interface EvaluationSystemInterface
{
    /**
     * Calculer une note sur la base d'un score brut
     *
     * @param float $rawScore Score brut obtenu
     * @param float $maxScore Score maximum possible
     * @return float Note calculée
     */
    public function calculateGrade(float $rawScore, float $maxScore): float;

    /**
     * Calculer une moyenne à partir d'un ensemble de notes
     *
     * @param Collection $grades Collection des notes avec leurs coefficients
     * @return float Moyenne calculée
     */
    public function calculateAverage(Collection $grades): float;

    /**
     * Déterminer le statut de réussite d'un élève/étudiant
     *
     * @param float $average Moyenne obtenue
     * @param array $context Contexte spécifique (matière, période, etc.)
     * @return string Statut (pass, fail, compensation, etc.)
     */
    public function determinePassingStatus(float $average, array $context = []): string;

    /**
     * Valider une note selon les critères du système
     *
     * @param float $grade Note à valider
     * @return array Résultat de validation avec erreurs éventuelles
     */
    public function validateGrade(float $grade): array;

    /**
     * Obtenir les seuils de réussite du système
     *
     * @return array Seuils configurés
     */
    public function getPassingThresholds(): array;

    /**
     * Calculer les mentions/appréciations
     *
     * @param float $average Moyenne obtenue
     * @return string|null Mention attribuée
     */
    public function calculateMention(float $average): ?string;
}