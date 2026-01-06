<?php

namespace App\Domains\Enrollment;

use App\Domains\Enrollment\BaseEnrollmentService;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\School;
use App\Models\Level;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

/**
 * Service d'inscription pour le secteur préuniversitaire
 * Gère les inscriptions du préscolaire au secondaire
 */
class PreUniversityEnrollmentService extends BaseEnrollmentService
{
    /**
     * Documents requis pour le préuniversitaire
     */
    protected const PREUNIVERSITY_DOCUMENTS = [
        'previous_school_certificate' => 'Certificat de l\'école précédente',
        'parent_authorization' => 'Autorisation parentale',
        'residence_proof' => 'Justificatif de domicile',
        'vaccination_record' => 'Carnet de vaccination',
    ];

    /**
     * Limites d'âge par niveau
     */
    protected const AGE_LIMITS = [
        'prescolaire' => ['min' => 3, 'max' => 6],
        'primaire' => ['min' => 6, 'max' => 12],
        'college' => ['min' => 11, 'max' => 16],
        'lycee' => ['min' => 15, 'max' => 20],
    ];

    /**
     * Inscrire un étudiant dans le préuniversitaire
     *
     * @param array $enrollmentData
     * @return array
     */
    public function enrollStudent(array $enrollmentData): array
    {
        try {
            DB::beginTransaction();

            // Valider les données
            $validation = $this->validateEnrollment($enrollmentData);
            if (!$validation['is_valid']) {
                return $validation;
            }

            // Vérifier les prérequis
            $prerequisites = $this->checkPrerequisites(
                $enrollmentData,
                ['level_id' => $enrollmentData['level_id']]
            );
            if (!$prerequisites['is_valid']) {
                return $prerequisites;
            }

            // Calculer les frais
            $fees = $this->calculateFees($enrollmentData, [
                'level_id' => $enrollmentData['level_id'],
                'school_id' => $enrollmentData['school_id'],
            ]);

            // Créer ou récupérer l'étudiant
            $student = $this->createOrUpdateStudent($enrollmentData);

            // Créer l'inscription
            $enrollment = $this->createEnrollment($student, $enrollmentData, $fees);

            DB::commit();

            return [
                'success' => true,
                'student' => $student,
                'enrollment' => $enrollment,
                'fees' => $fees,
                'message' => 'Inscription effectuée avec succès',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'inscription: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculer les frais d'inscription
     *
     * @param array $studentData
     * @param array $academicContext
     * @return array
     */
    public function calculateFees(array $studentData, array $academicContext): array
    {
        $school = School::findOrFail($academicContext['school_id']);
        $level = Level::findOrFail($academicContext['level_id']);
        
        $baseFees = [
            'registration' => $this->getBaseFee($level, 'registration'),
            'tuition' => $this->getBaseFee($level, 'tuition'),
            'materials' => $this->getBaseFee($level, 'materials'),
            'activities' => $this->getBaseFee($level, 'activities'),
        ];

        // Appliquer les réductions
        $discounts = $this->calculateDiscounts($studentData, $baseFees);

        // Calculer les totaux
        $subtotal = array_sum($baseFees);
        $totalDiscounts = array_sum($discounts);
        $total = $subtotal - $totalDiscounts;

        return [
            'base_fees' => $baseFees,
            'discounts' => $discounts,
            'subtotal' => $subtotal,
            'total_discounts' => $totalDiscounts,
            'total' => max(0, $total), // Ne peut pas être négatif
            'payment_schedule' => $this->generatePaymentSchedule($total),
        ];
    }

    /**
     * Vérifier les prérequis d'inscription
     *
     * @param array $studentData
     * @param array $targetProgram
     * @return array
     */
    public function checkPrerequisites(array $studentData, array $targetProgram): array
    {
        $errors = [];

        $targetLevel = Level::findOrFail($targetProgram['level_id']);
        
        // Vérifier l'âge approprié
        if (isset($studentData['birth_date'])) {
            $age = $this->calculateAge($studentData['birth_date']);
            $ageValidation = $this->validateAgeForLevel($age, $targetLevel);
            if (!$ageValidation['is_valid']) {
                $errors = array_merge($errors, $ageValidation['errors']);
            }
        }

        // Vérifier le niveau précédent (sauf pour le préscolaire/CP)
        if (!$this->isEntryLevel($targetLevel)) {
            $previousLevelCheck = $this->checkPreviousLevel($studentData, $targetLevel);
            if (!$previousLevelCheck['is_valid']) {
                $errors[] = $previousLevelCheck['error'];
            }
        }

        // Vérifier la capacité d'accueil
        $capacityCheck = $this->checkLevelCapacity($targetLevel);
        if (!$capacityCheck['available']) {
            $errors[] = $capacityCheck['message'];
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Obtenir les documents requis
     *
     * @param string $enrollmentType
     * @param array $context
     * @return array
     */
    public function getRequiredDocuments(string $enrollmentType, array $context = []): array
    {
        $documents = self::BASIC_DOCUMENTS;

        // Ajouter les documents spécifiques au préuniversitaire
        $documents = array_merge($documents, self::PREUNIVERSITY_DOCUMENTS);

        // Documents spécifiques selon le type d'inscription
        switch ($enrollmentType) {
            case 'new_student':
                // Aucun document additionnel pour les nouveaux étudiants
                break;

            case 'transfer':
                $documents['transcript'] = 'Relevé de notes de l\'école précédente';
                $documents['transfer_certificate'] = 'Certificat de transfert';
                break;

            case 'repeat':
                $documents['repeat_authorization'] = 'Autorisation de redoublement';
                break;

            case 'foreign_student':
                $documents['passport'] = 'Passeport';
                $documents['visa'] = 'Visa ou titre de séjour';
                $documents['diploma_equivalence'] = 'Équivalence du diplôme';
                break;
        }

        return $documents;
    }

    /**
     * Obtenir les champs obligatoires spécifiques au préuniversitaire
     *
     * @return array
     */
    protected function getRequiredFields(): array
    {
        return [
            'first_name',
            'last_name',
            'birth_date',
            'birth_place',
            'gender',
            'nationality',
            'parent_name',
            'parent_phone',
            'address',
            'school_id',
            'level_id',
            'academic_year_id',
        ];
    }

    /**
     * Valider l'âge selon le contexte préuniversitaire
     *
     * @param int $age
     * @return array
     */
    protected function validateAge(int $age): array
    {
        $errors = [];

        if ($age < 3) {
            $errors[] = 'L\'âge minimum requis est de 3 ans';
        }

        if ($age > 25) {
            $errors[] = 'L\'âge maximum autorisé est de 25 ans';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Valider l'âge pour un niveau spécifique
     *
     * @param int $age
     * @param Level $level
     * @return array
     */
    protected function validateAgeForLevel(int $age, Level $level): array
    {
        $errors = [];
        $levelType = $this->determineLevelType($level);
        
        if (isset(self::AGE_LIMITS[$levelType])) {
            $limits = self::AGE_LIMITS[$levelType];
            
            if ($age < $limits['min']) {
                $errors[] = "L'âge minimum pour ce niveau est {$limits['min']} ans";
            }
            
            if ($age > $limits['max']) {
                $errors[] = "L'âge maximum pour ce niveau est {$limits['max']} ans";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validations spécifiques au préuniversitaire
     *
     * @param array $enrollmentData
     * @return array
     */
    protected function validateContextSpecific(array $enrollmentData): array
    {
        $errors = [];

        // Valider les informations parentales (obligatoires pour mineurs)
        if (isset($enrollmentData['birth_date'])) {
            $age = $this->calculateAge($enrollmentData['birth_date']);
            if ($age < 18) {
                if (!isset($enrollmentData['parent_name']) || empty($enrollmentData['parent_name'])) {
                    $errors[] = 'Le nom du parent/tuteur est obligatoire pour les mineurs';
                }
                
                if (!isset($enrollmentData['parent_phone']) || empty($enrollmentData['parent_phone'])) {
                    $errors[] = 'Le téléphone du parent/tuteur est obligatoire pour les mineurs';
                }
            }
        }

        // Valider l'adresse (obligatoire pour transport scolaire)
        if (!isset($enrollmentData['address']) || empty($enrollmentData['address'])) {
            $errors[] = 'L\'adresse est obligatoire';
        }

        return $errors;
    }

    /**
     * Valider un transfert
     *
     * @param Student $student
     * @param School $newSchool
     * @param Level $newLevel
     * @return array
     */
    protected function validateTransfer(Student $student, School $newSchool, Level $newLevel): array
    {
        $errors = [];

        // Vérifier que l'école accepte les transferts
        if (!$newSchool->accepts_transfers) {
            $errors[] = 'Cette école n\'accepte pas les transferts en cours d\'année';
        }

        // Vérifier la cohérence du niveau
        $currentLevel = $student->currentEnrollment->level ?? null;
        if ($currentLevel && !$this->isValidLevelProgression($currentLevel, $newLevel)) {
            $errors[] = 'Progression de niveau incohérente pour ce transfert';
        }

        // Vérifier la période de transfert
        if (!$this->isValidTransferPeriod()) {
            $errors[] = 'Les transferts ne sont pas autorisés pendant cette période';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Clôturer l'inscription actuelle
     *
     * @param Student $student
     * @return void
     */
    protected function closeCurrentEnrollment(Student $student): void
    {
        $currentEnrollment = $student->currentEnrollment;
        if ($currentEnrollment) {
            $currentEnrollment->update([
                'status' => 'transferred',
                'end_date' => now(),
                'notes' => 'Inscription clôturée suite à transfert',
            ]);
        }
    }

    /**
     * Créer une inscription de transfert
     *
     * @param Student $student
     * @param array $transferData
     * @return Enrollment
     */
    protected function createTransferEnrollment(Student $student, array $transferData): Enrollment
    {
        return Enrollment::create([
            'student_id' => $student->id,
            'school_id' => $transferData['new_school_id'],
            'level_id' => $transferData['new_level_id'],
            'academic_year_id' => $transferData['academic_year_id'],
            'enrollment_type' => 'transfer',
            'enrollment_date' => now(),
            'status' => 'active',
            'fees' => $transferData['fees'] ?? [],
            'notes' => 'Inscription par transfert',
        ]);
    }

    /**
     * Vérifier l'éligibilité à la réinscription
     *
     * @param Student $student
     * @return array
     */
    protected function checkReenrollmentEligibility(Student $student): array
    {
        // Vérifier l'âge limite
        $age = $this->calculateAge($student->birth_date);
        if ($age > 25) {
            return [
                'is_eligible' => false,
                'reason' => 'Âge limite dépassé pour une réinscription',
            ];
        }

        // Vérifier les antécédents disciplinaires
        if ($student->hasActiveDisciplinaryActions()) {
            return [
                'is_eligible' => false,
                'reason' => 'Actions disciplinaires en cours',
            ];
        }

        // Vérifier les dettes financières
        if ($student->hasOutstandingDebts()) {
            return [
                'is_eligible' => false,
                'reason' => 'Dettes financières non réglées',
            ];
        }

        return [
            'is_eligible' => true,
        ];
    }

    /**
     * Calculer les frais de réinscription
     *
     * @param Student $student
     * @param array $reenrollmentData
     * @return array
     */
    protected function calculateReenrollmentFees(Student $student, array $reenrollmentData): array
    {
        $baseFees = $this->calculateFees($student->toArray(), $reenrollmentData);
        
        // Réduction pour ancien élève
        $loyaltyDiscount = $baseFees['total'] * 0.05; // 5% de réduction
        
        return array_merge($baseFees, [
            'loyalty_discount' => $loyaltyDiscount,
            'final_total' => $baseFees['total'] - $loyaltyDiscount,
        ]);
    }

    /**
     * Créer une réinscription
     *
     * @param Student $student
     * @param array $reenrollmentData
     * @param array $feeCalculation
     * @return Enrollment
     */
    protected function createReenrollment(Student $student, array $reenrollmentData, array $feeCalculation): Enrollment
    {
        return Enrollment::create([
            'student_id' => $student->id,
            'school_id' => $reenrollmentData['school_id'],
            'level_id' => $reenrollmentData['level_id'],
            'academic_year_id' => $reenrollmentData['academic_year_id'],
            'enrollment_type' => 'reenrollment',
            'enrollment_date' => now(),
            'status' => 'active',
            'fees' => $feeCalculation,
            'notes' => 'Réinscription ancien élève',
        ]);
    }

    // Méthodes utilitaires

    private function createOrUpdateStudent(array $data): Student
    {
        $school = School::findOrFail($data['school_id']);
        
        return Student::create([
            'student_number' => $this->generateStudentNumber($school),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birth_date' => $data['birth_date'],
            'birth_place' => $data['birth_place'],
            'gender' => $data['gender'],
            'nationality' => $data['nationality'],
            'address' => $data['address'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'parent_name' => $data['parent_name'],
            'parent_phone' => $data['parent_phone'],
            'parent_email' => $data['parent_email'] ?? null,
            'school_id' => $data['school_id'],
        ]);
    }

    private function createEnrollment(Student $student, array $data, array $fees): Enrollment
    {
        return Enrollment::create([
            'student_id' => $student->id,
            'school_id' => $data['school_id'],
            'level_id' => $data['level_id'],
            'academic_year_id' => $data['academic_year_id'],
            'enrollment_type' => 'new',
            'enrollment_date' => now(),
            'status' => 'active',
            'fees' => $fees,
            'notes' => $data['notes'] ?? 'Nouvelle inscription',
        ]);
    }

    private function getBaseFee(Level $level, string $feeType): float
    {
        // TODO: Récupérer depuis la configuration des frais
        $defaultFees = [
            'registration' => 50000,
            'tuition' => 200000,
            'materials' => 30000,
            'activities' => 20000,
        ];

        return $defaultFees[$feeType] ?? 0.0;
    }

    private function calculateDiscounts(array $studentData, array $baseFees): array
    {
        $discounts = [];

        // Réduction pour familles nombreuses
        if (isset($studentData['siblings_count']) && $studentData['siblings_count'] >= 2) {
            $discounts['family'] = $baseFees['tuition'] * 0.1; // 10%
        }

        // Réduction pour mérite académique
        if (isset($studentData['has_scholarship']) && $studentData['has_scholarship']) {
            $discounts['merit'] = array_sum($baseFees) * 0.2; // 20%
        }

        return $discounts;
    }

    private function generatePaymentSchedule(float $total): array
    {
        // Diviser en 3 échéances
        $installment = $total / 3;
        
        return [
            [
                'due_date' => now()->addMonth()->format('Y-m-d'),
                'amount' => $installment,
                'description' => 'Premier versement',
            ],
            [
                'due_date' => now()->addMonths(2)->format('Y-m-d'),
                'amount' => $installment,
                'description' => 'Deuxième versement',
            ],
            [
                'due_date' => now()->addMonths(3)->format('Y-m-d'),
                'amount' => $installment,
                'description' => 'Solde final',
            ],
        ];
    }

    private function determineLevelType(Level $level): string
    {
        // TODO: Améliorer la détection basée sur les métadonnées du niveau
        $name = strtolower($level->name);
        
        if (str_contains($name, 'préscolaire') || str_contains($name, 'maternelle')) {
            return 'prescolaire';
        } elseif (str_contains($name, 'primaire') || str_contains($name, 'cp') || str_contains($name, 'ce') || str_contains($name, 'cm')) {
            return 'primaire';
        } elseif (str_contains($name, 'collège') || str_contains($name, '6ème') || str_contains($name, '5ème') || str_contains($name, '4ème') || str_contains($name, '3ème')) {
            return 'college';
        } elseif (str_contains($name, 'lycée') || str_contains($name, '2nde') || str_contains($name, '1ère') || str_contains($name, 'terminale')) {
            return 'lycee';
        }

        return 'autres';
    }

    private function isEntryLevel(Level $level): bool
    {
        $entryLevels = ['Préscolaire', 'CP', '6ème', '2nde'];
        return in_array($level->name, $entryLevels);
    }

    private function checkPreviousLevel(array $studentData, Level $targetLevel): array
    {
        // TODO: Implémenter la vérification du niveau précédent
        return ['is_valid' => true];
    }

    private function checkLevelCapacity(Level $level): array
    {
        // TODO: Vérifier la capacité d'accueil
        return ['available' => true];
    }

    private function isValidLevelProgression(Level $currentLevel, Level $newLevel): bool
    {
        // TODO: Vérifier la cohérence de progression
        return true;
    }

    private function isValidTransferPeriod(): bool
    {
        // TODO: Vérifier la période autorisée pour les transferts
        return true;
    }
}