<?php

namespace App\Observers;

use App\Models\CourseUnitElement;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CourseUnitElementObserver
{
    /**
     * Handle the CourseUnitElement "created" event.
     * Synchronise l'UE parente après création d'un ECUE.
     *
     * @param CourseUnitElement $courseUnitElement
     * @return void
     */
    public function created(CourseUnitElement $courseUnitElement): void
    {
        $this->syncParentCourseUnit($courseUnitElement, 'created');
    }

    /**
     * Handle the CourseUnitElement "updated" event.
     * Synchronise l'UE parente après modification d'un ECUE.
     *
     * @param CourseUnitElement $courseUnitElement
     * @return void
     */
    public function updated(CourseUnitElement $courseUnitElement): void
    {
        // Vérifier si les champs importants ont été modifiés
        $importantFields = ['credits', 'hours_cm', 'hours_td', 'hours_tp', 'hours_total', 'status'];
        $hasImportantChanges = false;

        foreach ($importantFields as $field) {
            if ($courseUnitElement->isDirty($field)) {
                $hasImportantChanges = true;
                break;
            }
        }

        if ($hasImportantChanges) {
            $this->syncParentCourseUnit($courseUnitElement, 'updated', $courseUnitElement->getOriginal());
        }
    }

    /**
     * Handle the CourseUnitElement "deleted" event.
     * Synchronise l'UE parente après suppression d'un ECUE.
     *
     * @param CourseUnitElement $courseUnitElement
     * @return void
     */
    public function deleted(CourseUnitElement $courseUnitElement): void
    {
        $this->syncParentCourseUnit($courseUnitElement, 'deleted');
    }

    /**
     * Handle the CourseUnitElement "restored" event.
     * Synchronise l'UE parente après restauration d'un ECUE.
     *
     * @param CourseUnitElement $courseUnitElement
     * @return void
     */
    public function restored(CourseUnitElement $courseUnitElement): void
    {
        $this->syncParentCourseUnit($courseUnitElement, 'restored');
    }

    /**
     * Handle the CourseUnitElement "force deleted" event.
     * Synchronise l'UE parente après suppression définitive d'un ECUE.
     *
     * @param CourseUnitElement $courseUnitElement
     * @return void
     */
    public function forceDeleted(CourseUnitElement $courseUnitElement): void
    {
        $this->syncParentCourseUnit($courseUnitElement, 'force_deleted');
    }

    /**
     * Synchronise l'UE parente avec les totaux calculés des ECUE.
     *
     * @param CourseUnitElement $courseUnitElement
     * @param string $action
     * @param array|null $originalData
     * @return void
     */
    protected function syncParentCourseUnit(CourseUnitElement $courseUnitElement, string $action, ?array $originalData = null): void
    {
        try {
            DB::transaction(function () use ($courseUnitElement, $action, $originalData) {
                // Charger l'UE parente
                $courseUnit = $courseUnitElement->courseUnit;
                
                if (!$courseUnit) {
                    Log::warning("CourseUnit parent non trouvé pour ECUE {$courseUnitElement->id}");
                    return;
                }

                // Calculer les nouveaux totaux
                $activeElements = $courseUnit->elements()
                    ->where('status', 'active')
                    ->get();

                $newTotalCredits = $activeElements->sum('credits');
                $newTotalHoursCm = $activeElements->sum('hours_cm');
                $newTotalHoursTd = $activeElements->sum('hours_td');
                $newTotalHoursTp = $activeElements->sum('hours_tp');
                $newTotalHours = $activeElements->sum('hours_total');

                // Calculer la répartition horaire
                $hoursDistribution = $this->calculateHoursDistribution(
                    $newTotalHoursCm, 
                    $newTotalHoursTd, 
                    $newTotalHoursTp, 
                    $newTotalHours
                );

                // Préparer les données de mise à jour
                $updateData = [];

                // Mise à jour conditionnelle des champs
                if ($courseUnit->sync_credits_with_elements) {
                    $updateData['credits'] = $newTotalCredits;
                }

                if ($courseUnit->sync_hours_with_elements) {
                    $updateData['hours_cm'] = $newTotalHoursCm;
                    $updateData['hours_td'] = $newTotalHoursTd;
                    $updateData['hours_tp'] = $newTotalHoursTp;
                    $updateData['hours_total'] = $newTotalHours;
                }

                // Toujours mettre à jour les statistiques calculées
                $updateData['elements_count'] = $activeElements->count();
                $updateData['hours_distribution'] = $hoursDistribution;
                $updateData['last_element_sync'] = now();

                // Appliquer les mises à jour
                if (!empty($updateData)) {
                    $courseUnit->updateQuietly($updateData);
                    
                    Log::info("UE {$courseUnit->code} synchronisée après {$action} ECUE {$courseUnitElement->code}", [
                        'course_unit_id' => $courseUnit->id,
                        'element_id' => $courseUnitElement->id,
                        'action' => $action,
                        'new_totals' => [
                            'credits' => $newTotalCredits,
                            'hours_total' => $newTotalHours,
                            'elements_count' => $activeElements->count()
                        ],
                        'original_data' => $originalData
                    ]);
                }

                // Valider la cohérence après synchronisation
                $this->validatePostSyncConsistency($courseUnit, $activeElements, $action);

            }, 5); // Timeout de 5 secondes

        } catch (\Exception $e) {
            Log::error("Erreur lors de la synchronisation UE après {$action} ECUE", [
                'course_unit_element_id' => $courseUnitElement->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Programmer une resynchronisation différée si nécessaire
            $this->handleSyncError($courseUnitElement, $action, $e);

            // Ne pas propager l'erreur pour éviter de bloquer l'opération principale
            Log::error("Échec de synchronisation UE après {$action} ECUE", [
                'course_unit_element_id' => $courseUnitElement->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'sync_failed' => true,
                'recovery_job_attempted' => true
            ]);
        }
    }

    /**
     * Calcule la répartition des heures en pourcentages.
     *
     * @param float $hoursCm
     * @param float $hoursTd
     * @param float $hoursTp
     * @param float $totalHours
     * @return array
     */
    protected function calculateHoursDistribution(float $hoursCm, float $hoursTd, float $hoursTp, float $totalHours): array
    {
        if ($totalHours <= 0) {
            return ['cm' => 0, 'td' => 0, 'tp' => 0];
        }

        return [
            'cm' => round(($hoursCm / $totalHours) * 100, 1),
            'td' => round(($hoursTd / $totalHours) * 100, 1),
            'tp' => round(($hoursTp / $totalHours) * 100, 1),
        ];
    }

    /**
     * Valide la cohérence du système après synchronisation.
     *
     * @param \App\Models\CourseUnit $courseUnit
     * @param \Illuminate\Support\Collection $activeElements
     * @param string $action
     * @return void
     */
    protected function validatePostSyncConsistency($courseUnit, $activeElements, string $action): void
    {
        $inconsistencies = [];

        // Vérification des limites LMD
        $maxCreditsLmd = 6; // Limite standard LMD pour une UE
        if ($courseUnit->credits > $maxCreditsLmd) {
            $inconsistencies[] = "Crédits UE dépassent limite LMD ({$courseUnit->credits} > {$maxCreditsLmd})";
        }

        // Vérification de la cohérence crédits-heures (ratio standard: 1 crédit ≈ 25-30h)
        if ($courseUnit->credits > 0 && $courseUnit->hours_total > 0) {
            $hoursPerCredit = $courseUnit->hours_total / $courseUnit->credits;
            if ($hoursPerCredit > 35) {
                $inconsistencies[] = "Ratio heures/crédit élevé ({$hoursPerCredit}h par crédit)";
            } elseif ($hoursPerCredit < 15) {
                $inconsistencies[] = "Ratio heures/crédit faible ({$hoursPerCredit}h par crédit)";
            }
        }

        // Vérification de la distribution des types d'enseignement
        if ($courseUnit->hours_total > 0) {
            $cmPercentage = ($courseUnit->hours_cm / $courseUnit->hours_total) * 100;
            if ($cmPercentage > 70) {
                $inconsistencies[] = "Proportion CM très élevée ({$cmPercentage}%)";
            } elseif ($cmPercentage < 30 && $courseUnit->hours_cm > 0) {
                $inconsistencies[] = "Proportion CM faible ({$cmPercentage}%)";
            }
        }

        // Vérification du nombre d'ECUE (recommandation: 2-4 ECUE par UE)
        $elementsCount = $activeElements->count();
        if ($elementsCount > 6) {
            $inconsistencies[] = "Nombre d'ECUE très élevé ({$elementsCount})";
        } elseif ($elementsCount === 0 && $action !== 'deleted') {
            $inconsistencies[] = "Aucun ECUE actif dans l'UE";
        }

        // Enregistrement des incohérences si détectées
        if (!empty($inconsistencies)) {
            Log::warning("Incohérences détectées après synchronisation UE {$courseUnit->code}", [
                'course_unit_id' => $courseUnit->id,
                'action' => $action,
                'inconsistencies' => $inconsistencies,
                'totals' => [
                    'credits' => $courseUnit->credits,
                    'hours_total' => $courseUnit->hours_total,
                    'elements_count' => $elementsCount
                ]
            ]);

            // Log pour le suivi
            Log::warning('Incohérences détectées après synchronisation UE-ECUE', [
                'course_unit_id' => $courseUnit->id,
                'action' => $action,
                'inconsistencies' => $inconsistencies,
                'auto_detected' => true
            ]);
        }
    }

    /**
     * Vérifie si la synchronisation automatique est activée pour une UE.
     *
     * @param \App\Models\CourseUnit $courseUnit
     * @return bool
     */
    protected function shouldSync($courseUnit): bool
    {
        return $courseUnit->auto_sync_enabled ?? true; // Par défaut activé
    }

    /**
     * Gère les cas d'erreur de synchronisation avec retry logic.
     *
     * @param CourseUnitElement $courseUnitElement
     * @param string $action
     * @param \Exception $exception
     * @return void
     */
    protected function handleSyncError(CourseUnitElement $courseUnitElement, string $action, \Exception $exception): void
    {
        // Tentative de resynchronisation différée via Job si nécessaire
        if ($exception instanceof \Illuminate\Database\QueryException) {
            // Programmer une resynchronisation différée via Job
            try {
                \App\Jobs\ResyncCourseUnitJob::dispatch(
                    $courseUnitElement->course_unit_id,
                    "sync_error_after_{$action}"
                )->delay(now()->addMinutes(2));
                
                Log::info("Resynchronisation différée programmée pour UE après erreur", [
                    'course_unit_id' => $courseUnitElement->course_unit_id,
                    'element_id' => $courseUnitElement->id,
                    'action' => $action,
                    'job_scheduled' => true,
                    'delay_minutes' => 2
                ]);
            } catch (\Exception $jobException) {
                Log::error("Échec programmation Job resynchronisation", [
                    'course_unit_id' => $courseUnitElement->course_unit_id,
                    'element_id' => $courseUnitElement->id,
                    'action' => $action,
                    'job_error' => $jobException->getMessage()
                ]);
            }
        }

        // Gestion d'autres types d'erreurs
        if ($exception instanceof \Illuminate\Database\Deadlock) {
            // Retry immédiat pour les deadlocks avec délai très court
            try {
                \App\Jobs\ResyncCourseUnitJob::dispatch(
                    $courseUnitElement->course_unit_id,
                    "deadlock_recovery_after_{$action}"
                )->delay(now()->addSeconds(30));
                
                Log::info("Resynchronisation différée programmée après deadlock", [
                    'course_unit_id' => $courseUnitElement->course_unit_id,
                    'element_id' => $courseUnitElement->id,
                    'action' => $action,
                    'delay_seconds' => 30
                ]);
            } catch (\Exception $jobException) {
                Log::error("Échec programmation Job après deadlock", [
                    'job_error' => $jobException->getMessage()
                ]);
            }
        }
    }

    /**
     * Méthode publique pour tester la gestion d'erreurs.
     * À utiliser uniquement en environnement de test.
     *
     * @param CourseUnitElement $courseUnitElement
     * @param string $action
     * @param \Exception $exception
     * @return void
     */
    public function testHandleSyncError(CourseUnitElement $courseUnitElement, string $action, \Exception $exception): void
    {
        if (app()->environment(['testing', 'local'])) {
            $this->handleSyncError($courseUnitElement, $action, $exception);
        } else {
            throw new \BadMethodCallException('testHandleSyncError ne peut être utilisé qu\'en environnement test/local');
        }
    }
}