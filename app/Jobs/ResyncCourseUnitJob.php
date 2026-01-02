<?php

namespace App\Jobs;

use App\Models\CourseUnit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ResyncCourseUnitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120]; // Délais de retry en secondes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $courseUnitId,
        public string $reason = 'sync_error_recovery'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info("Début resynchronisation différée UE", [
                'course_unit_id' => $this->courseUnitId,
                'attempt' => $this->attempts(),
                'reason' => $this->reason
            ]);

            $courseUnit = CourseUnit::find($this->courseUnitId);
            
            if (!$courseUnit) {
                Log::warning("UE non trouvée pour resynchronisation", [
                    'course_unit_id' => $this->courseUnitId
                ]);
                return;
            }

            // Effectuer la resynchronisation dans une transaction
            DB::transaction(function () use ($courseUnit) {
                $this->resyncCourseUnitWithElements($courseUnit);
            }, 5);

            Log::info("Resynchronisation différée UE terminée avec succès", [
                'course_unit_id' => $this->courseUnitId,
                'course_unit_code' => $courseUnit->code,
                'attempt' => $this->attempts()
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur lors de la resynchronisation différée UE", [
                'course_unit_id' => $this->courseUnitId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-lancer l'exception pour déclencher le retry automatique
            throw $e;
        }
    }

    /**
     * Resynchronise l'UE avec ses ECUE.
     */
    protected function resyncCourseUnitWithElements(CourseUnit $courseUnit): void
    {
        // Récupérer tous les ECUE actifs
        $activeElements = $courseUnit->elements()
            ->where('status', 'active')
            ->get();

        Log::info("Resynchronisation UE: éléments trouvés", [
            'course_unit_id' => $courseUnit->id,
            'elements_count' => $activeElements->count()
        ]);

        // Calculer les nouveaux totaux
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

        // Mise à jour conditionnelle selon la configuration UE
        if ($courseUnit->sync_credits_with_elements ?? true) {
            $updateData['credits'] = $newTotalCredits;
        }

        if ($courseUnit->sync_hours_with_elements ?? true) {
            $updateData['hours_cm'] = $newTotalHoursCm;
            $updateData['hours_td'] = $newTotalHoursTd;
            $updateData['hours_tp'] = $newTotalHoursTp;
            $updateData['hours_total'] = $newTotalHours;
        }

        // Toujours mettre à jour les statistiques calculées
        $updateData['elements_count'] = $activeElements->count();
        $updateData['hours_distribution'] = $hoursDistribution;
        $updateData['last_element_sync'] = now();

        // Marquer comme resynchronisé par Job
        $updateData['last_job_sync'] = now();

        // Appliquer les mises à jour
        if (!empty($updateData)) {
            $originalData = $courseUnit->only(array_keys($updateData));
            
            $courseUnit->updateQuietly($updateData);
            
            Log::info("UE resynchronisée par Job", [
                'course_unit_id' => $courseUnit->id,
                'course_unit_code' => $courseUnit->code,
                'reason' => $this->reason,
                'changes' => $this->getChanges($originalData, $updateData),
                'new_totals' => [
                    'credits' => $newTotalCredits,
                    'hours_total' => $newTotalHours,
                    'elements_count' => $activeElements->count()
                ]
            ]);

            // Valider la cohérence post-synchronisation
            $this->validateConsistency($courseUnit, $activeElements);
        }
    }

    /**
     * Calcule la répartition des heures en pourcentages.
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
     * Compare les données avant/après pour logging.
     */
    protected function getChanges(array $original, array $updated): array
    {
        $changes = [];
        foreach ($updated as $key => $newValue) {
            $oldValue = $original[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'from' => $oldValue,
                    'to' => $newValue
                ];
            }
        }
        return $changes;
    }

    /**
     * Validation post-synchronisation.
     */
    protected function validateConsistency(CourseUnit $courseUnit, $activeElements): void
    {
        $warnings = [];

        // Vérification des limites
        if ($courseUnit->credits > 6) {
            $warnings[] = "Crédits UE dépassent limite LMD ({$courseUnit->credits} > 6)";
        }

        if ($courseUnit->credits > 0 && $courseUnit->hours_total > 0) {
            $hoursPerCredit = $courseUnit->hours_total / $courseUnit->credits;
            if ($hoursPerCredit > 35 || $hoursPerCredit < 15) {
                $warnings[] = "Ratio heures/crédit inhabituel ({$hoursPerCredit}h par crédit)";
            }
        }

        if (!empty($warnings)) {
            Log::warning("Avertissements post-resynchronisation Job", [
                'course_unit_id' => $courseUnit->id,
                'warnings' => $warnings,
                'job_sync' => true
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Échec définitif resynchronisation différée UE", [
            'course_unit_id' => $this->courseUnitId,
            'reason' => $this->reason,
            'total_attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
            'failed_permanently' => true
        ]);
    }
}