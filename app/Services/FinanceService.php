<?php

namespace App\Services;

use App\Models\SchoolFee;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Student;
use App\Models\School;
use App\Models\AcademicYear;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service pour la gestion financière de l'école
 * 
 * Ce service centralise la logique métier pour :
 * - Gestion des frais scolaires et validation
 * - Traitement des paiements et génération de reçus
 * - Calculs financiers et statistiques
 * - Vérification des règles métier financières
 * 
 * @package App\Services
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 */
class FinanceService
{
    /**
     * Obtenir les statistiques du tableau de bord financier
     * 
     * @return array Statistiques détaillées
     */
    public function getDashboardStatistics(): array
    {
        try {
            $school = School::getActiveSchool();
            $currentAcademicYear = AcademicYear::currentForSchool($school->id)->first();
            
            $schoolFees = SchoolFee::with(['schoolClass', 'level', 'cycle', 'academicYear'])
                                   ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                                       return $query->where('academic_year_id', $currentAcademicYear->id);
                                   })
                                   ->paginate(10);
            
            $recentPayments = Payment::with(['student.user', 'schoolFee'])
                                    ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                                        return $query->whereHas('schoolFee', function ($q) use ($currentAcademicYear) {
                                            $q->where('academic_year_id', $currentAcademicYear->id);
                                        });
                                    })
                                    ->latest()
                                    ->limit(5)
                                    ->get();
            
            // Calculs financiers
            $totalFeesAmount = SchoolFee::where('status', 'active')
                                      ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                                          return $query->where('academic_year_id', $currentAcademicYear->id);
                                      })
                                      ->sum('amount');
            
            $totalPayments = Payment::where('status', 'confirmed')
                                   ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                                       return $query->whereHas('schoolFee', function ($q) use ($currentAcademicYear) {
                                           $q->where('academic_year_id', $currentAcademicYear->id);
                                       });
                                   })
                                   ->sum('amount');
            
            $pendingPayments = Payment::where('status', 'pending')
                                     ->when($currentAcademicYear, function ($query) use ($currentAcademicYear) {
                                         return $query->whereHas('schoolFee', function ($q) use ($currentAcademicYear) {
                                             $q->where('academic_year_id', $currentAcademicYear->id);
                                         });
                                     })
                                     ->count();
            
            return [
                'school_fees' => $schoolFees,
                'recent_payments' => $recentPayments,
                'total_fees_amount' => $totalFeesAmount,
                'total_payments' => $totalPayments,
                'pending_payments' => $pendingPayments,
                'collection_rate' => $totalFeesAmount > 0 ? round(($totalPayments / $totalFeesAmount) * 100, 2) : 0,
                'current_academic_year' => $currentAcademicYear,
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur calcul statistiques financières', [
                'error' => $e->getMessage(),
                'school_id' => School::getActiveSchool()->id ?? null
            ]);
            
            return [
                'school_fees' => collect(),
                'recent_payments' => collect(),
                'total_fees_amount' => 0,
                'total_payments' => 0,
                'pending_payments' => 0,
                'collection_rate' => 0,
                'current_academic_year' => null,
            ];
        }
    }

    /**
     * Créer un nouveau frais scolaire avec validation complète
     * 
     * @param array $data Données du frais scolaire
     * @return SchoolFee Frais créé
     * @throws BusinessRuleException Si validation échoue
     */
    public function createSchoolFee(array $data): SchoolFee
    {
        try {
            DB::beginTransaction();
            
            // Validation des règles métier
            $this->validateSchoolFeeBusinessRules($data);
            
            // Ajouter l'école active
            $data['school_id'] = School::getActiveSchool()->id;
            
            // Créer le frais scolaire
            $schoolFee = SchoolFee::create($data);
            
            // Log de création pour audit financier
            $this->logFinancialAction('school_fee_created', $schoolFee, [
                'amount' => $data['amount'],
                'academic_year_id' => $data['academic_year_id'],
                'is_mandatory' => $data['is_mandatory'] ?? false,
            ]);
            
            DB::commit();
            return $schoolFee;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création frais scolaire', [
                'data' => $data,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour un frais scolaire avec validation
     * 
     * @param SchoolFee $schoolFee Frais à mettre à jour
     * @param array $data Nouvelles données
     * @return SchoolFee Frais mis à jour
     * @throws BusinessRuleException Si validation échoue
     */
    public function updateSchoolFee(SchoolFee $schoolFee, array $data): SchoolFee
    {
        try {
            DB::beginTransaction();
            
            // Vérifier s'il y a des paiements associés pour certaines modifications critiques
            $this->validateSchoolFeeUpdate($schoolFee, $data);
            
            // Validation des règles métier
            $this->validateSchoolFeeBusinessRules($data, $schoolFee->id);
            
            $oldData = $schoolFee->toArray();
            
            // Mettre à jour
            $schoolFee->update($data);
            
            // Logger la modification
            $this->logFinancialAction('school_fee_updated', $schoolFee, [
                'old_data' => $oldData,
                'new_data' => $data,
            ]);
            
            DB::commit();
            return $schoolFee->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour frais scolaire', [
                'school_fee_id' => $schoolFee->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un frais scolaire avec vérifications
     * 
     * @param SchoolFee $schoolFee Frais à supprimer
     * @throws BusinessRuleException Si suppression non autorisée
     */
    public function deleteSchoolFee(SchoolFee $schoolFee): void
    {
        try {
            DB::beginTransaction();
            
            // Vérifier s'il y a des paiements associés
            if ($schoolFee->payments()->exists()) {
                throw new BusinessRuleException(
                    'Impossible de supprimer ce frais car des paiements y sont associés.'
                );
            }
            
            $schoolFeeId = $schoolFee->id;
            $schoolFeeName = $schoolFee->name;
            
            // Supprimer le frais
            $schoolFee->delete();
            
            // Logger la suppression
            $this->logFinancialAction('school_fee_deleted', null, [
                'school_fee_id' => $schoolFeeId,
                'school_fee_name' => $schoolFeeName,
            ]);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression frais scolaire', [
                'school_fee_id' => $schoolFee->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Créer un paiement avec validation et génération de reçu
     * 
     * @param array $data Données du paiement
     * @return Payment Paiement créé
     * @throws BusinessRuleException Si validation échoue
     */
    public function createPayment(array $data): Payment
    {
        try {
            DB::beginTransaction();
            
            // Validation métier du paiement
            $this->validatePaymentBusinessRules($data);
            
            // Ajouter l'utilisateur qui traite le paiement
            $data['processed_by'] = auth()->id();
            
            // Créer le paiement
            $payment = Payment::create($data);
            
            // Auto-confirmer et générer le reçu si nécessaire
            if ($data['auto_confirm'] ?? true) {
                $this->confirmPayment($payment);
            }
            
            DB::commit();
            return $payment;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création paiement', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Confirmer un paiement et générer le reçu
     * 
     * @param Payment $payment Paiement à confirmer
     * @return Receipt Reçu généré
     */
    public function confirmPayment(Payment $payment): Receipt
    {
        try {
            DB::beginTransaction();
            
            // Marquer le paiement comme confirmé
            $payment->update(['status' => 'confirmed']);
            
            // Générer le numéro de reçu
            $receiptNumber = $this->generateReceiptNumber();
            
            // Créer le reçu
            $receipt = Receipt::create([
                'payment_id' => $payment->id,
                'receipt_number' => $receiptNumber,
                'issued_date' => now(),
                'issued_by' => auth()->id(),
                'status' => 'issued',
            ]);
            
            // Logger la confirmation
            $this->logFinancialAction('payment_confirmed', $payment, [
                'receipt_id' => $receipt->id,
                'receipt_number' => $receiptNumber,
            ]);
            
            DB::commit();
            return $receipt;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur confirmation paiement', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculer le total des frais pour un étudiant
     * 
     * @param Student $student Étudiant
     * @return float Total des frais
     */
    public function calculateStudentTotalFees(Student $student): float
    {
        $enrollment = $student->enrollments()->latest()->first();
        if (!$enrollment) {
            return 0;
        }

        return SchoolFee::where('status', 'active')
                       ->where(function ($query) use ($enrollment) {
                           $query->whereNull('school_class_id')
                                 ->orWhere('school_class_id', $enrollment->school_class_id);
                       })
                       ->sum('amount');
    }

    /**
     * Calculer le total payé par un étudiant
     * 
     * @param Student $student Étudiant
     * @return float Total payé
     */
    public function calculateStudentTotalPaid(Student $student): float
    {
        return Payment::where('student_id', $student->id)
                     ->where('status', 'confirmed')
                     ->sum('amount');
    }

    /**
     * Obtenir les frais applicables à un étudiant
     * 
     * @param Student $student Étudiant
     * @return \Illuminate\Database\Eloquent\Collection Frais applicables
     */
    public function getApplicableFeesForStudent(Student $student)
    {
        $enrollment = $student->enrollments()->with('schoolClass')->latest()->first();
        
        if (!$enrollment) {
            return collect();
        }

        return SchoolFee::where('status', 'active')
                       ->where(function ($query) use ($enrollment) {
                           $query->whereNull('school_class_id')
                                 ->orWhere('school_class_id', $enrollment->school_class_id);
                       })
                       ->with(['schoolClass', 'level', 'cycle', 'academicYear'])
                       ->get();
    }

    /**
     * Valider les règles métier pour les frais scolaires
     * 
     * @param array $data Données à valider
     * @param int|null $excludeId ID à exclure pour validation unicité
     * @throws BusinessRuleException Si validation échoue
     */
    private function validateSchoolFeeBusinessRules(array $data, ?int $excludeId = null): void
    {
        // Vérifier qu'il n'y a pas de conflit de portée
        if (isset($data['school_class_id']) && isset($data['level_id'])) {
            throw new BusinessRuleException(
                'Un frais ne peut pas cibler à la fois une classe et un niveau spécifiques.'
            );
        }
        
        if (isset($data['level_id']) && isset($data['cycle_id'])) {
            throw new BusinessRuleException(
                'Un frais ne peut pas cibler à la fois un niveau et un cycle spécifiques.'
            );
        }
        
        // Vérifier la cohérence de l'année scolaire
        $academicYear = AcademicYear::find($data['academic_year_id']);
        if (!$academicYear || $academicYear->status !== 'active') {
            throw new BusinessRuleException(
                'L\'année scolaire sélectionnée n\'est pas active.'
            );
        }
        
        // Vérifier l'échéance par rapport à l'année scolaire
        if (isset($data['due_date'])) {
            $dueDate = Carbon::parse($data['due_date']);
            if ($dueDate->lt($academicYear->start_date) || $dueDate->gt($academicYear->end_date)) {
                throw new BusinessRuleException(
                    'La date d\'échéance doit être comprise dans l\'année scolaire sélectionnée.'
                );
            }
        }
    }

    /**
     * Valider la mise à jour d'un frais scolaire
     * 
     * @param SchoolFee $schoolFee Frais existant
     * @param array $data Nouvelles données
     * @throws BusinessRuleException Si modification interdite
     */
    private function validateSchoolFeeUpdate(SchoolFee $schoolFee, array $data): void
    {
        // Vérifier si on modifie le montant et qu'il y a des paiements
        if (isset($data['amount']) && $data['amount'] != $schoolFee->amount) {
            if ($schoolFee->payments()->where('status', 'confirmed')->exists()) {
                throw new BusinessRuleException(
                    'Impossible de modifier le montant car des paiements confirmés existent pour ce frais.'
                );
            }
        }
    }

    /**
     * Valider les règles métier pour les paiements
     * 
     * @param array $data Données du paiement
     * @throws BusinessRuleException Si validation échoue
     */
    private function validatePaymentBusinessRules(array $data): void
    {
        $schoolFee = SchoolFee::find($data['school_fee_id']);
        
        if (!$schoolFee) {
            throw new BusinessRuleException('Frais scolaire introuvable.');
        }
        
        // Vérifier que le montant ne dépasse pas le solde restant
        $remainingBalance = $this->calculateRemainingBalance($schoolFee, $data['student_id']);
        
        if ($data['amount'] > $remainingBalance) {
            throw new BusinessRuleException(
                "Le montant ne peut pas dépasser le solde restant ({$remainingBalance} FCFA)"
            );
        }
        
        // Vérifier que l'étudiant peut payer ce frais
        $student = Student::find($data['student_id']);
        $applicableFees = $this->getApplicableFeesForStudent($student);
        
        if (!$applicableFees->contains('id', $schoolFee->id)) {
            throw new BusinessRuleException(
                'Ce frais n\'est pas applicable à cet étudiant.'
            );
        }
    }

    /**
     * Calculer le solde restant pour un frais et un étudiant
     * 
     * @param SchoolFee $schoolFee Frais scolaire
     * @param int $studentId ID étudiant
     * @return float Solde restant
     */
    private function calculateRemainingBalance(SchoolFee $schoolFee, int $studentId): float
    {
        $totalPaid = Payment::where('school_fee_id', $schoolFee->id)
                           ->where('student_id', $studentId)
                           ->where('status', 'confirmed')
                           ->sum('amount');
                           
        return max(0, $schoolFee->amount - $totalPaid);
    }

    /**
     * Générer un numéro de reçu unique
     * 
     * @return string Numéro de reçu
     */
    private function generateReceiptNumber(): string
    {
        $year = now()->year;
        $lastReceipt = Receipt::whereYear('created_at', $year)
                            ->orderBy('id', 'desc')
                            ->first();
        
        $sequence = $lastReceipt ? (int)substr($lastReceipt->receipt_number, -6) + 1 : 1;
        
        return sprintf('REC%d%06d', $year, $sequence);
    }

    /**
     * Logger une action financière
     * 
     * @param string $action Action effectuée
     * @param mixed $subject Sujet de l'action
     * @param array $properties Propriétés additionnelles
     */
    private function logFinancialAction(string $action, $subject, array $properties = []): void
    {
        try {
            // Utiliser le modèle ActivityLog directement si le package activity est installé
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                \Spatie\Activitylog\Models\Activity::create([
                    'log_name' => 'financial',
                    'description' => "Action financière: {$action}",
                    'subject_type' => $subject ? get_class($subject) : null,
                    'subject_id' => $subject ? $subject->id : null,
                    'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                    'causer_id' => auth()->id(),
                    'properties' => array_merge([
                        'action' => $action,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'school_id' => School::getActiveSchool()->id ?? null,
                    ], $properties)
                ]);
            } else {
                // Fallback vers les logs Laravel standards
                Log::info("Action financière: {$action}", [
                    'user_id' => auth()->id(),
                    'subject' => $subject ? get_class($subject) . ':' . $subject->id : null,
                    'properties' => $properties
                ]);
            }
            
        } catch (\Exception $e) {
            Log::warning('Échec du logging d\'action financière', [
                'action' => $action,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
        }
    }
}