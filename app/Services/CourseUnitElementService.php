<?php

namespace App\Services;

use App\Models\CourseUnit;
use App\Models\CourseUnitElement;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service pour la gestion des ECUE (Éléments Constitutifs d'Unités d'Enseignement)
 * 
 * Ce service centralise la logique métier pour :
 * - Gestion des ECUE et synchronisation avec les UE
 * - Validation des règles métier LMD
 * - Opérations CRUD avec contraintes
 * - Calculs et validations spécifiques aux ECUE
 * 
 * @package App\Services
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 */
class CourseUnitElementService
{
    /**
     * Créer un nouvel ECUE
     * 
     * @param CourseUnit $courseUnit UE parente
     * @param array $data Données de l'ECUE
     * @return CourseUnitElement ECUE créé
     * @throws BusinessRuleException Si validation échoue
     */
    public function createElement(CourseUnit $courseUnit, array $data): CourseUnitElement
    {
        try {
            DB::beginTransaction();
            
            // Valider les données métier
            $this->validateElementData($courseUnit, $data);
            
            // Créer l'ECUE
            $element = new CourseUnitElement($data);
            $element->course_unit_id = $courseUnit->id;
            $element->save();
            
            // Logger la création
            Log::info('ECUE créé', [
                'ecue_id' => $element->id,
                'ecue_code' => $element->code,
                'course_unit_id' => $courseUnit->id,
                'created_by' => auth()->id()
            ]);
            
            DB::commit();
            
            return $element->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création ECUE', [
                'course_unit_id' => $courseUnit->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour un ECUE
     * 
     * @param CourseUnitElement $element ECUE à mettre à jour
     * @param array $data Nouvelles données
     * @return CourseUnitElement ECUE mis à jour
     * @throws BusinessRuleException Si validation échoue
     */
    public function updateElement(CourseUnitElement $element, array $data): CourseUnitElement
    {
        try {
            DB::beginTransaction();
            
            // Valider les données métier
            $this->validateElementData($element->courseUnit, $data, $element->id);
            
            $oldCode = $element->code;
            $element->update($data);
            
            // Logger la modification
            Log::info('ECUE mis à jour', [
                'ecue_id' => $element->id,
                'old_code' => $oldCode,
                'new_code' => $element->code,
                'updated_by' => auth()->id()
            ]);
            
            DB::commit();
            
            return $element->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour ECUE', [
                'ecue_id' => $element->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un ECUE
     * 
     * @param CourseUnitElement $element ECUE à supprimer
     * @throws BusinessRuleException Si suppression impossible
     */
    public function deleteElement(CourseUnitElement $element): void
    {
        try {
            DB::beginTransaction();
            
            // Vérifier les contraintes
            $this->validateElementDeletion($element);
            
            $elementId = $element->id;
            $elementCode = $element->code;
            $courseUnitId = $element->course_unit_id;
            
            // Supprimer l'ECUE
            $element->delete();
            
            // Logger la suppression
            Log::info('ECUE supprimé', [
                'ecue_id' => $elementId,
                'ecue_code' => $elementCode,
                'course_unit_id' => $courseUnitId,
                'deleted_by' => auth()->id()
            ]);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression ECUE', [
                'ecue_id' => $element->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Synchroniser une UE depuis ses ECUE
     * 
     * @param CourseUnit $courseUnit UE à synchroniser
     */
    public function syncUEFromECUEs(CourseUnit $courseUnit): void
    {
        $courseUnit->syncFromElements();
        
        Log::info('UE synchronisée depuis ECUE', [
            'course_unit_id' => $courseUnit->id,
            'elements_count' => $courseUnit->elements()->count()
        ]);
    }

    /**
     * Valider les contraintes ECUE d'une UE
     * 
     * @param CourseUnit $courseUnit UE à valider
     * @return array Liste des erreurs
     */
    public function validateECUEConstraints(CourseUnit $courseUnit): array
    {
        return $courseUnit->validateEcueConsistency();
    }

    /**
     * Obtenir les statistiques ECUE d'une UE
     * 
     * @param CourseUnit $courseUnit UE à analyser
     * @return array Statistiques détaillées
     */
    public function getElementsStats(CourseUnit $courseUnit): array
    {
        // Récupérer tous les ECUE, quel que soit leur statut
        $elements = $courseUnit->elements()->get();
        
        if ($elements->isEmpty()) {
            return [
                'total_elements' => 0,
                'total_credits' => 0,
                'total_hours' => 0,
                'hours_distribution' => ['cm' => 0, 'td' => 0, 'tp' => 0],
                'evaluation_types' => [],
                'consistency_errors' => []
            ];
        }
        
        $totalCredits = $elements->sum('credits');
        $totalHours = $elements->sum('hours_total');
        $totalCm = $elements->sum('hours_cm');
        $totalTd = $elements->sum('hours_td');
        $totalTp = $elements->sum('hours_tp');
        
        $hoursDistribution = [
            'cm' => $totalHours > 0 ? round(($totalCm / $totalHours) * 100, 1) : 0,
            'td' => $totalHours > 0 ? round(($totalTd / $totalHours) * 100, 1) : 0,
            'tp' => $totalHours > 0 ? round(($totalTp / $totalHours) * 100, 1) : 0,
        ];
        
        $evaluationTypes = $elements->groupBy('evaluation_type')->map(fn($group) => $group->count())->toArray();
        
        return [
            'total_elements' => $elements->count(),
            'total_credits' => $totalCredits,
            'total_hours' => $totalHours,
            'hours_distribution' => $hoursDistribution,
            'evaluation_types' => $evaluationTypes,
            'consistency_errors' => $this->validateECUEConstraints($courseUnit)
        ];
    }

    /**
     * Valider les données d'un ECUE
     * 
     * @param CourseUnit $courseUnit UE parente
     * @param array $data Données à valider
     * @param int|null $excludeId ID à exclure pour validation unicité
     * @throws BusinessRuleException Si validation échoue
     */
    private function validateElementData(CourseUnit $courseUnit, array $data, ?int $excludeId = null): void
    {
        // Vérifier l'unicité du code dans l'UE
        $codeQuery = CourseUnitElement::where('course_unit_id', $courseUnit->id)
                                    ->where('code', $data['code']);
                                    
        if ($excludeId) {
            $codeQuery->where('id', '!=', $excludeId);
        }
        
        if ($codeQuery->exists()) {
            throw new BusinessRuleException('Ce code ECUE existe déjà dans cette UE.');
        }
        
        // Valider les crédits
        if (isset($data['credits'])) {
            $otherElementsCredits = CourseUnitElement::where('course_unit_id', $courseUnit->id)
                                                  ->where('status', 'active')
                                                  ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                                                  ->sum('credits');
            
            $totalCreditsAfter = $otherElementsCredits + $data['credits'];
            
            if ($totalCreditsAfter > $courseUnit->credits) {
                throw new BusinessRuleException(
                    "Le total des crédits ECUE ({$totalCreditsAfter}) dépasserait les crédits de l'UE ({$courseUnit->credits})."
                );
            }
        }
        
        // Valider le coefficient
        if (isset($data['coefficient']) && $data['coefficient'] <= 0) {
            throw new BusinessRuleException('Le coefficient doit être supérieur à 0.');
        }
        
        // Valider les heures
        $hourFields = ['hours_cm', 'hours_td', 'hours_tp'];
        foreach ($hourFields as $field) {
            if (isset($data[$field]) && $data[$field] < 0) {
                throw new BusinessRuleException('Les heures ne peuvent pas être négatives.');
            }
        }
    }

    /**
     * Valider la suppression d'un ECUE
     * 
     * @param CourseUnitElement $element ECUE à supprimer
     * @throws BusinessRuleException Si suppression impossible
     */
    private function validateElementDeletion(CourseUnitElement $element): void
    {
        if (!$element->canBeDeleted()) {
            throw new BusinessRuleException(
                'Impossible de supprimer cet ECUE car il contient des évaluations ou des notes.'
            );
        }
    }
}