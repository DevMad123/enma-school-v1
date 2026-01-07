<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasContextualValidation
{
    use HasEducationalSettings;
    
    /**
     * Valide l'âge selon les limites configurées
     */
    protected function validateAge(Carbon $birthDate, string $level): bool
    {
        $ageLimits = $this->getEducationalSetting('age_limits', 'general');
        
        if (!isset($ageLimits[$level])) {
            return true; // Pas de limite définie
        }
        
        $age = $birthDate->age;
        $limits = $ageLimits[$level];
        
        $minAge = $limits['min'] ?? 0;
        $maxAge = $limits['max'] ?? 100;
        
        return $age >= $minAge && $age <= $maxAge;
    }
    
    /**
     * Valide les documents requis selon le contexte
     */
    protected function validateRequiredDocuments(array $documents, ?string $level = null): array
    {
        $settings = app('educational.settings');
        $required = $settings->getRequiredDocuments();
        
        if ($level && isset($required[$level])) {
            $required = $required[$level];
        } else {
            $required = $required['general'] ?? [];
        }
        
        $missing = [];
        foreach ($required as $document) {
            if (!in_array($document, $documents)) {
                $missing[] = $document;
            }
        }
        
        return $missing;
    }
    
    /**
     * Valide une note selon les seuils configurés
     */
    protected function validateGrade(float $grade): bool
    {
        $thresholds = $this->getEvaluationThresholds();
        return $grade >= 0 && $grade <= 20; // Base ivoirienne
    }
    
    /**
     * Calcule le statut selon les seuils configurés
     */
    protected function calculateGradeStatus(float $average): string
    {
        $thresholds = $this->getEvaluationThresholds();
        
        foreach ($thresholds as $status => $threshold) {
            if ($average >= $threshold) {
                return $status;
            }
        }
        
        return 'echec';
    }
    
    /**
     * Valide les frais selon la structure configurée
     */
    protected function validateFees(string $level, array $fees): array
    {
        $feeStructure = $this->getFeeStructure();
        $expectedFees = $feeStructure[$level] ?? [];
        
        $errors = [];
        foreach ($expectedFees as $feeType => $expectedAmount) {
            if (!isset($fees[$feeType])) {
                $errors[] = "Frais manquant : {$feeType}";
            } elseif ($fees[$feeType] < 0) {
                $errors[] = "Montant invalide pour {$feeType}";
            }
        }
        
        return $errors;
    }
    
    /**
     * Valide selon les règles LMD (pour universitaire)
     */
    protected function validateLMDCredits(int $credits, string $level): bool
    {
        $context = app('educational.context');
        
        if (!$context->isUniversity()) {
            return true;
        }
        
        $lmdStandards = $this->getEducationalSetting('lmd', 'standards');
        $levelStandards = $lmdStandards[$level] ?? [];
        
        $minCredits = $levelStandards['credits_par_ue'][0] ?? 3;
        $maxCredits = $levelStandards['credits_par_ue'][1] ?? 9;
        
        return $credits >= $minCredits && $credits <= $maxCredits;
    }
    
    /**
     * Calcule la mention selon les seuils configurés
     */
    protected function calculateMention(float $average): string
    {
        $context = app('educational.context');
        
        if ($context->isUniversity()) {
            $thresholds = $this->getEducationalSetting('lmd', 'thresholds');
            
            if ($average >= $thresholds['mention_excellence']) return 'Excellent';
            if ($average >= $thresholds['mention_tb']) return 'Très Bien';
            if ($average >= $thresholds['mention_bien']) return 'Bien';
            if ($average >= $thresholds['mention_ab']) return 'Assez Bien';
        } else {
            $thresholds = $this->getEvaluationThresholds();
            
            if ($average >= $thresholds['excellent']) return 'Excellent';
            if ($average >= $thresholds['tres_bien']) return 'Très Bien';
            if ($average >= $thresholds['bien']) return 'Bien';
            if ($average >= $thresholds['assez_bien']) return 'Assez Bien';
            if ($average >= $thresholds['passable']) return 'Passable';
        }
        
        return 'Insuffisant';
    }
}