<?php

namespace App\Domains\Enrollment;

use App\Domains\Enrollment\EnrollmentServiceInterface;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\School;
use App\Models\Level;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de base pour les inscriptions
 * Fonctionnalités communes aux deux contextes éducatifs
 */
abstract class BaseEnrollmentService implements EnrollmentServiceInterface
{
    /**
     * Types de documents de base requis
     */
    protected const BASIC_DOCUMENTS = [
        'birth_certificate' => 'Acte de naissance',
        'identity_document' => 'Pièce d\'identité',
        'passport_photos' => 'Photos d\'identité',
        'medical_certificate' => 'Certificat médical',
    ];

    /**
     * Valider les données d'inscription de base
     *
     * @param array $enrollmentData
     * @return array
     */
    public function validateEnrollment(array $enrollmentData): array
    {
        $errors = [];

        // Validation des champs obligatoires de base
        $requiredFields = $this->getRequiredFields();
        foreach ($requiredFields as $field) {
            if (!isset($enrollmentData[$field]) || empty($enrollmentData[$field])) {
                $errors[] = "Le champ {$field} est obligatoire";
            }
        }

        // Validation de l'âge
        if (isset($enrollmentData['birth_date'])) {
            $age = $this->calculateAge($enrollmentData['birth_date']);
            $ageValidation = $this->validateAge($age);
            if (!$ageValidation['is_valid']) {
                $errors = array_merge($errors, $ageValidation['errors']);
            }
        }

        // Validation des doublons
        if (isset($enrollmentData['student_number'])) {
            $duplicateCheck = $this->checkDuplicateStudentNumber($enrollmentData['student_number']);
            if (!$duplicateCheck['is_valid']) {
                $errors[] = $duplicateCheck['error'];
            }
        }

        // Validations spécifiques au contexte (implémentées dans les classes enfants)
        $contextErrors = $this->validateContextSpecific($enrollmentData);
        $errors = array_merge($errors, $contextErrors);

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Générer un numéro d'étudiant
     *
     * @param School $school
     * @param array $context
     * @return string
     */
    protected function generateStudentNumber(School $school, array $context = []): string
    {
        $academicYear = $context['academic_year'] ?? date('Y');
        $schoolCode = $school->code ?? substr($school->name, 0, 3);
        
        // Format: ÉCOLE + ANNÉE + SÉQUENTIEL
        $lastNumber = Student::where('school_id', $school->id)
            ->whereYear('created_at', $academicYear)
            ->max('student_number');

        if ($lastNumber) {
            // Extraire le numéro séquentiel
            $lastSequence = (int) substr($lastNumber, -4);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return strtoupper($schoolCode) . $academicYear . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculer l'âge d'un étudiant
     *
     * @param string $birthDate
     * @return int
     */
    protected function calculateAge(string $birthDate): int
    {
        return now()->diffInYears($birthDate);
    }

    /**
     * Vérifier les doublons de numéro d'étudiant
     *
     * @param string $studentNumber
     * @return array
     */
    protected function checkDuplicateStudentNumber(string $studentNumber): array
    {
        $exists = Student::where('student_number', $studentNumber)->exists();
        
        return [
            'is_valid' => !$exists,
            'error' => $exists ? 'Ce numéro d\'étudiant existe déjà' : null,
        ];
    }

    /**
     * Traiter le transfert d'un étudiant
     *
     * @param array $transferData
     * @return array
     */
    public function processTransfer(array $transferData): array
    {
        try {
            DB::beginTransaction();

            $student = Student::findOrFail($transferData['student_id']);
            $newSchool = School::findOrFail($transferData['new_school_id']);
            $newLevel = Level::findOrFail($transferData['new_level_id']);

            // Valider le transfert
            $validation = $this->validateTransfer($student, $newSchool, $newLevel);
            if (!$validation['is_valid']) {
                return $validation;
            }

            // Clôturer l'inscription actuelle
            $this->closeCurrentEnrollment($student);

            // Créer la nouvelle inscription
            $newEnrollment = $this->createTransferEnrollment($student, $transferData);

            DB::commit();

            return [
                'success' => true,
                'enrollment' => $newEnrollment,
                'message' => 'Transfert effectué avec succès',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => 'Erreur lors du transfert: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Gérer la réinscription
     *
     * @param array $reenrollmentData
     * @return array
     */
    public function processReenrollment(array $reenrollmentData): array
    {
        try {
            $student = Student::findOrFail($reenrollmentData['student_id']);
            
            // Vérifier l'éligibilité à la réinscription
            $eligibilityCheck = $this->checkReenrollmentEligibility($student);
            if (!$eligibilityCheck['is_eligible']) {
                return [
                    'success' => false,
                    'error' => $eligibilityCheck['reason'],
                ];
            }

            // Calculer les nouveaux frais
            $feeCalculation = $this->calculateReenrollmentFees($student, $reenrollmentData);

            // Créer la nouvelle inscription
            $enrollment = $this->createReenrollment($student, $reenrollmentData, $feeCalculation);

            return [
                'success' => true,
                'enrollment' => $enrollment,
                'fees' => $feeCalculation,
                'message' => 'Réinscription effectuée avec succès',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur lors de la réinscription: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculer les statistiques d'inscription
     *
     * @param array $criteria
     * @return array
     */
    public function calculateEnrollmentStatistics(array $criteria): array
    {
        $query = Enrollment::query();

        // Appliquer les filtres
        if (isset($criteria['academic_year'])) {
            $query->whereHas('academicYear', function ($q) use ($criteria) {
                $q->where('year', $criteria['academic_year']);
            });
        }

        if (isset($criteria['school_id'])) {
            $query->where('school_id', $criteria['school_id']);
        }

        if (isset($criteria['level_id'])) {
            $query->where('level_id', $criteria['level_id']);
        }

        $enrollments = $query->with(['student', 'level'])->get();

        return [
            'total_enrollments' => $enrollments->count(),
            'by_gender' => [
                'male' => $enrollments->where('student.gender', 'male')->count(),
                'female' => $enrollments->where('student.gender', 'female')->count(),
            ],
            'by_age_group' => $this->calculateAgeGroupDistribution($enrollments),
            'by_level' => $enrollments->groupBy('level.name')->map(function ($group) {
                return $group->count();
            }),
            'enrollment_trend' => $this->calculateEnrollmentTrend($enrollments),
        ];
    }

    /**
     * Calculer la distribution par groupe d'âge
     *
     * @param Collection $enrollments
     * @return array
     */
    protected function calculateAgeGroupDistribution(Collection $enrollments): array
    {
        $ageGroups = [
            '0-5' => 0,
            '6-10' => 0,
            '11-15' => 0,
            '16-20' => 0,
            '21-25' => 0,
            '26+' => 0,
        ];

        foreach ($enrollments as $enrollment) {
            $age = $this->calculateAge($enrollment->student->birth_date);
            
            if ($age <= 5) $ageGroups['0-5']++;
            elseif ($age <= 10) $ageGroups['6-10']++;
            elseif ($age <= 15) $ageGroups['11-15']++;
            elseif ($age <= 20) $ageGroups['16-20']++;
            elseif ($age <= 25) $ageGroups['21-25']++;
            else $ageGroups['26+']++;
        }

        return $ageGroups;
    }

    /**
     * Calculer la tendance d'inscription
     *
     * @param Collection $enrollments
     * @return array
     */
    protected function calculateEnrollmentTrend(Collection $enrollments): array
    {
        return $enrollments->groupBy(function ($enrollment) {
            return $enrollment->created_at->format('Y-m');
        })->map(function ($group) {
            return $group->count();
        })->sortKeys()->toArray();
    }

    // Méthodes abstraites à implémenter dans les classes enfants

    /**
     * Obtenir les champs obligatoires spécifiques au contexte
     *
     * @return array
     */
    abstract protected function getRequiredFields(): array;

    /**
     * Valider l'âge selon le contexte éducatif
     *
     * @param int $age
     * @return array
     */
    abstract protected function validateAge(int $age): array;

    /**
     * Validations spécifiques au contexte
     *
     * @param array $enrollmentData
     * @return array
     */
    abstract protected function validateContextSpecific(array $enrollmentData): array;

    /**
     * Valider un transfert
     *
     * @param Student $student
     * @param School $newSchool
     * @param Level $newLevel
     * @return array
     */
    abstract protected function validateTransfer(Student $student, School $newSchool, Level $newLevel): array;

    /**
     * Clôturer l'inscription actuelle
     *
     * @param Student $student
     * @return void
     */
    abstract protected function closeCurrentEnrollment(Student $student): void;

    /**
     * Créer une inscription de transfert
     *
     * @param Student $student
     * @param array $transferData
     * @return Enrollment
     */
    abstract protected function createTransferEnrollment(Student $student, array $transferData): Enrollment;

    /**
     * Vérifier l'éligibilité à la réinscription
     *
     * @param Student $student
     * @return array
     */
    abstract protected function checkReenrollmentEligibility(Student $student): array;

    /**
     * Calculer les frais de réinscription
     *
     * @param Student $student
     * @param array $reenrollmentData
     * @return array
     */
    abstract protected function calculateReenrollmentFees(Student $student, array $reenrollmentData): array;

    /**
     * Créer une réinscription
     *
     * @param Student $student
     * @param array $reenrollmentData
     * @param array $feeCalculation
     * @return Enrollment
     */
    abstract protected function createReenrollment(Student $student, array $reenrollmentData, array $feeCalculation): Enrollment;
}