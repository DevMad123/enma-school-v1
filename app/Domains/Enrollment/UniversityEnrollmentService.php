<?php

namespace App\Domains\Enrollment;

use App\Domains\Enrollment\BaseEnrollmentService;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\School;
use App\Models\Program;
use App\Models\Semester;
use App\Models\Level;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

/**
 * Service d'inscription pour le secteur universitaire (LMD)
 * Gère les inscriptions universitaires avec validation des prérequis académiques
 */
class UniversityEnrollmentService extends BaseEnrollmentService
{
    /**
     * Documents requis pour l'universitaire
     */
    protected const UNIVERSITY_DOCUMENTS = [
        'bac_diploma' => 'Diplôme du Baccalauréat ou équivalent',
        'academic_transcript' => 'Relevé de notes complet',
        'orientation_letter' => 'Lettre d\'orientation',
        'university_application' => 'Demande d\'admission universitaire',
    ];

    /**
     * Frais universitaires par cycle
     */
    protected const UNIVERSITY_FEES = [
        'L1' => ['registration' => 75000, 'tuition' => 400000, 'library' => 25000, 'sports' => 15000],
        'L2' => ['registration' => 65000, 'tuition' => 380000, 'library' => 25000, 'sports' => 15000],
        'L3' => ['registration' => 65000, 'tuition' => 380000, 'library' => 25000, 'sports' => 15000],
        'M1' => ['registration' => 85000, 'tuition' => 500000, 'library' => 30000, 'research' => 40000],
        'M2' => ['registration' => 85000, 'tuition' => 500000, 'library' => 30000, 'research' => 40000],
        'D1' => ['registration' => 100000, 'tuition' => 300000, 'research' => 100000, 'thesis' => 200000],
    ];

    /**
     * Inscrire un étudiant à l'université
     *
     * @param array $enrollmentData
     * @return array
     */
    public function enrollStudent(array $enrollmentData): array
    {
        try {
            DB::beginTransaction();

            // Validation complète LMD
            $validation = $this->validateEnrollment($enrollmentData);
            if (!$validation['is_valid']) {
                return $validation;
            }

            // Vérification des prérequis académiques
            $prerequisites = $this->checkPrerequisites(
                $enrollmentData,
                [
                    'program_id' => $enrollmentData['program_id'],
                    'level' => $enrollmentData['level'],
                ]
            );
            if (!$prerequisites['is_valid']) {
                return $prerequisites;
            }

            // Validation pédagogique par département
            $pedagogicalValidation = $this->validatePedagogicalAdmission($enrollmentData);
            if (!$pedagogicalValidation['is_valid']) {
                return $pedagogicalValidation;
            }

            // Calcul des frais universitaires
            $fees = $this->calculateFees($enrollmentData, [
                'program_id' => $enrollmentData['program_id'],
                'level' => $enrollmentData['level'],
                'school_id' => $enrollmentData['school_id'],
            ]);

            // Créer ou mettre à jour l'étudiant
            $student = $this->createOrUpdateUniversityStudent($enrollmentData);

            // Créer l'inscription universitaire
            $enrollment = $this->createUniversityEnrollment($student, $enrollmentData, $fees);

            // Inscription aux semestres actifs
            $semesterEnrollments = $this->enrollInActiveSemesters($student, $enrollmentData);

            DB::commit();

            return [
                'success' => true,
                'student' => $student,
                'enrollment' => $enrollment,
                'semester_enrollments' => $semesterEnrollments,
                'fees' => $fees,
                'next_steps' => $this->getEnrollmentNextSteps($enrollment),
                'message' => 'Inscription universitaire effectuée avec succès',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'inscription universitaire: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Calculer les frais universitaires
     *
     * @param array $studentData
     * @param array $academicContext
     * @return array
     */
    public function calculateFees(array $studentData, array $academicContext): array
    {
        $program = Program::with('department')->findOrFail($academicContext['program_id']);
        $level = $academicContext['level'];
        
        // Frais de base selon le niveau
        $baseFees = self::UNIVERSITY_FEES[$level] ?? self::UNIVERSITY_FEES['L1'];
        
        // Frais spécifiques au programme
        $programFees = $this->calculateProgramSpecificFees($program, $level);
        
        // Merge des frais
        $allFees = array_merge($baseFees, $programFees);

        // Calcul des réductions
        $discounts = $this->calculateUniversityDiscounts($studentData, $allFees, $program);

        // Totaux
        $subtotal = array_sum($allFees);
        $totalDiscounts = array_sum($discounts);
        $total = $subtotal - $totalDiscounts;

        return [
            'base_fees' => $baseFees,
            'program_fees' => $programFees,
            'all_fees' => $allFees,
            'discounts' => $discounts,
            'subtotal' => $subtotal,
            'total_discounts' => $totalDiscounts,
            'total' => max(0, $total),
            'payment_schedule' => $this->generateUniversityPaymentSchedule($total),
            'ects_cost_per_credit' => $total / 60, // Coût par crédit ECTS
        ];
    }

    /**
     * Vérifier les prérequis universitaires
     *
     * @param array $studentData
     * @param array $targetProgram
     * @return array
     */
    public function checkPrerequisites(array $studentData, array $targetProgram): array
    {
        $errors = [];
        
        $program = Program::with(['department', 'semesters'])->findOrFail($targetProgram['program_id']);
        $targetLevel = $targetProgram['level'];

        // Vérification du diplôme d'accès
        $diplomaCheck = $this->checkAccessDiploma($studentData, $targetLevel);
        if (!$diplomaCheck['is_valid']) {
            $errors = array_merge($errors, $diplomaCheck['errors']);
        }

        // Vérification des prérequis académiques spécifiques
        if (isset($studentData['previous_results'])) {
            $academicCheck = $this->checkAcademicPrerequisites($studentData['previous_results'], $program, $targetLevel);
            if (!$academicCheck['is_valid']) {
                $errors = array_merge($errors, $academicCheck['errors']);
            }
        }

        // Vérification de la capacité d'accueil
        $capacityCheck = $this->checkProgramCapacity($program, $targetLevel);
        if (!$capacityCheck['available']) {
            $errors[] = $capacityCheck['message'];
        }

        // Vérification des prérequis spéciaux (concours, entretien, etc.)
        $specialCheck = $this->checkSpecialPrerequisites($studentData, $program);
        if (!$specialCheck['is_valid']) {
            $errors = array_merge($errors, $specialCheck['errors']);
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Obtenir les documents requis pour l'universitaire
     *
     * @param string $enrollmentType
     * @param array $context
     * @return array
     */
    public function getRequiredDocuments(string $enrollmentType, array $context = []): array
    {
        $documents = self::BASIC_DOCUMENTS;
        
        // Ajouter les documents spécifiques universitaires
        $documents = array_merge($documents, self::UNIVERSITY_DOCUMENTS);

        // Documents selon le type d'inscription
        switch ($enrollmentType) {
            case 'first_year':
                $documents['orientation_decision'] = 'Décision d\'orientation';
                break;

            case 'transfer':
                $documents['university_transcript'] = 'Relevé universitaire complet';
                $documents['transfer_agreement'] = 'Accord de transfert';
                $documents['credits_validation'] = 'Validation des crédits acquis';
                break;

            case 'foreign_student':
                $documents['diploma_equivalence'] = 'Équivalence diplôme étranger';
                $documents['french_proficiency'] = 'Attestation niveau français';
                $documents['visa_student'] = 'Visa étudiant';
                break;

            case 'readmission':
                $documents['readmission_request'] = 'Demande de réadmission';
                $documents['justification_absence'] = 'Justificatif interruption études';
                break;
        }

        // Documents spécifiques selon le niveau
        $level = $context['level'] ?? 'L1';
        if (in_array($level, ['M1', 'M2'])) {
            $documents['license_diploma'] = 'Diplôme de Licence';
            $documents['recommendation_letters'] = 'Lettres de recommandation';
        }

        if (str_starts_with($level, 'D')) {
            $documents['master_diploma'] = 'Diplôme de Master';
            $documents['research_proposal'] = 'Projet de recherche';
            $documents['supervisor_agreement'] = 'Accord directeur de thèse';
        }

        return $documents;
    }

    // Méthodes spécifiques à l'universitaire

    /**
     * Validation pédagogique de l'admission
     *
     * @param array $enrollmentData
     * @return array
     */
    protected function validatePedagogicalAdmission(array $enrollmentData): array
    {
        $errors = [];
        $program = Program::findOrFail($enrollmentData['program_id']);

        // Vérifier les conditions d'admission spéciales
        if ($program->has_entrance_exam && !($enrollmentData['entrance_exam_passed'] ?? false)) {
            $errors[] = 'Concours d\'entrée requis et non validé';
        }

        if ($program->requires_interview && !($enrollmentData['interview_completed'] ?? false)) {
            $errors[] = 'Entretien d\'admission requis';
        }

        // Vérifier la moyenne minimale requise
        if (isset($enrollmentData['previous_average']) && $program->minimum_average) {
            if ($enrollmentData['previous_average'] < $program->minimum_average) {
                $errors[] = "Moyenne insuffisante. Minimum requis: {$program->minimum_average}/20";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Inscrire aux semestres actifs
     *
     * @param Student $student
     * @param array $enrollmentData
     * @return array
     */
    protected function enrollInActiveSemesters(Student $student, array $enrollmentData): array
    {
        $program = Program::with('semesters')->findOrFail($enrollmentData['program_id']);
        $targetLevel = $enrollmentData['level'];
        
        // Déterminer les semestres actifs pour ce niveau
        $activeSemesters = $program->semesters()
            ->where('level', $targetLevel)
            ->where('is_active', true)
            ->get();

        $semesterEnrollments = [];
        
        foreach ($activeSemesters as $semester) {
            $semesterEnrollments[] = [
                'semester_id' => $semester->id,
                'student_id' => $student->id,
                'academic_year_id' => $enrollmentData['academic_year_id'],
                'enrollment_date' => now(),
                'status' => 'enrolled',
                'target_credits' => $semester->total_credits,
            ];
        }

        return $semesterEnrollments;
    }

    /**
     * Obtenir les prochaines étapes après inscription
     *
     * @param Enrollment $enrollment
     * @return array
     */
    protected function getEnrollmentNextSteps(Enrollment $enrollment): array
    {
        return [
            'course_selection' => 'Sélection des cours/UE obligatoires et optionnelles',
            'academic_advisor' => 'Rendez-vous avec le conseiller pédagogique',
            'student_card' => 'Retrait de la carte étudiante',
            'library_access' => 'Activation de l\'accès bibliothèque',
            'online_platform' => 'Création du compte plateforme numérique',
            'orientation_session' => 'Participation à la séance d\'orientation',
        ];
    }

    // Implémentation des méthodes abstraites

    protected function getRequiredFields(): array
    {
        return [
            'first_name',
            'last_name', 
            'birth_date',
            'birth_place',
            'gender',
            'nationality',
            'phone',
            'email',
            'address',
            'emergency_contact',
            'emergency_phone',
            'program_id',
            'level',
            'academic_year_id',
            'bac_series', // Série du bac
            'bac_year',   // Année d'obtention
            'bac_average', // Moyenne du bac
        ];
    }

    protected function validateAge(int $age): array
    {
        $errors = [];

        if ($age < 16) {
            $errors[] = 'L\'âge minimum pour l\'enseignement universitaire est 16 ans';
        }

        if ($age > 40) {
            // Souplesse pour formation continue
            if (!($this->isContiningEducation ?? false)) {
                $errors[] = 'Vérification requise pour inscription au-delà de 40 ans';
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function validateContextSpecific(array $enrollmentData): array
    {
        $errors = [];

        // Validation email universitaire obligatoire
        if (!isset($enrollmentData['email']) || !filter_var($enrollmentData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email valide obligatoire pour les communications universitaires';
        }

        // Validation contact d'urgence
        if (!isset($enrollmentData['emergency_contact']) || empty($enrollmentData['emergency_contact'])) {
            $errors[] = 'Contact d\'urgence obligatoire';
        }

        // Validation données baccalauréat
        if (!isset($enrollmentData['bac_series']) || empty($enrollmentData['bac_series'])) {
            $errors[] = 'Série du baccalauréat obligatoire';
        }

        if (!isset($enrollmentData['bac_year']) || $enrollmentData['bac_year'] < 1990) {
            $errors[] = 'Année d\'obtention du baccalauréat invalide';
        }

        return $errors;
    }

    protected function validateTransfer(Student $student, School $newSchool, Level $newLevel): array
    {
        $errors = [];

        // L'université accepte les transferts uniquement en début de semestre
        if (!$this->isBeginningOfSemester()) {
            $errors[] = 'Les transferts universitaires ne sont acceptés qu\'en début de semestre';
        }

        // Vérifier la compatibilité des crédits
        $currentCredits = $this->calculateStudentCredits($student);
        $newLevelRequirement = $this->getMinimumCreditsForLevel($newLevel->name);
        
        if ($currentCredits < $newLevelRequirement) {
            $errors[] = 'Crédits ECTS insuffisants pour ce niveau dans l\'université d\'accueil';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function closeCurrentEnrollment(Student $student): void
    {
        // Clôturer l'inscription universitaire
        $currentEnrollment = $student->currentEnrollment;
        if ($currentEnrollment) {
            $currentEnrollment->update([
                'status' => 'transferred',
                'end_date' => now(),
                'final_credits' => $this->calculateStudentCredits($student),
                'notes' => 'Transfert universitaire - crédits validés',
            ]);
        }

        // Clôturer les inscriptions semestrielles en cours
        $student->semesterEnrollments()
            ->where('status', 'active')
            ->update([
                'status' => 'transferred',
                'end_date' => now(),
            ]);
    }

    protected function createTransferEnrollment(Student $student, array $transferData): Enrollment
    {
        return Enrollment::create([
            'student_id' => $student->id,
            'school_id' => $transferData['new_school_id'],
            'program_id' => $transferData['new_program_id'],
            'level' => $transferData['new_level'],
            'academic_year_id' => $transferData['academic_year_id'],
            'enrollment_type' => 'university_transfer',
            'enrollment_date' => now(),
            'status' => 'active',
            'transferred_credits' => $transferData['validated_credits'] ?? 0,
            'fees' => $transferData['fees'] ?? [],
            'notes' => 'Transfert universitaire avec validation crédits',
        ]);
    }

    protected function checkReenrollmentEligibility(Student $student): array
    {
        // Vérifier l'exclusion académique
        if ($student->hasAcademicExclusion()) {
            return [
                'is_eligible' => false,
                'reason' => 'Exclusion académique en cours',
            ];
        }

        // Vérifier la limite de redoublement
        $repeatCount = $student->getRepeatCount();
        if ($repeatCount >= 2) {
            return [
                'is_eligible' => false,
                'reason' => 'Limite de redoublement atteinte (2 maximum)',
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

    protected function calculateReenrollmentFees(Student $student, array $reenrollmentData): array
    {
        $baseFees = $this->calculateFees($student->toArray(), $reenrollmentData);
        
        // Frais de réinscription spéciaux
        $additionalFees = [
            'reprocessing_fee' => 25000, // Frais de retraitement dossier
        ];

        return array_merge($baseFees, [
            'additional_fees' => $additionalFees,
            'final_total' => $baseFees['total'] + array_sum($additionalFees),
        ]);
    }

    protected function createReenrollment(Student $student, array $reenrollmentData, array $feeCalculation): Enrollment
    {
        return Enrollment::create([
            'student_id' => $student->id,
            'school_id' => $reenrollmentData['school_id'],
            'program_id' => $reenrollmentData['program_id'],
            'level' => $reenrollmentData['level'],
            'academic_year_id' => $reenrollmentData['academic_year_id'],
            'enrollment_type' => 'university_reenrollment',
            'enrollment_date' => now(),
            'status' => 'active',
            'is_repeat' => true,
            'previous_attempts' => $student->getRepeatCount() + 1,
            'fees' => $feeCalculation,
            'notes' => 'Réinscription universitaire',
        ]);
    }

    // Méthodes utilitaires universitaires

    private function createOrUpdateUniversityStudent(array $data): Student
    {
        $school = School::findOrFail($data['school_id']);
        
        return Student::create([
            'student_number' => $this->generateUniversityStudentNumber($school, $data),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'birth_date' => $data['birth_date'],
            'birth_place' => $data['birth_place'],
            'gender' => $data['gender'],
            'nationality' => $data['nationality'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'emergency_contact' => $data['emergency_contact'],
            'emergency_phone' => $data['emergency_phone'],
            'bac_series' => $data['bac_series'],
            'bac_year' => $data['bac_year'],
            'bac_average' => $data['bac_average'],
            'school_id' => $data['school_id'],
            'student_type' => 'university',
        ]);
    }

    private function generateUniversityStudentNumber(School $school, array $data): string
    {
        $academicYear = $data['academic_year'] ?? date('Y');
        $program = Program::findOrFail($data['program_id']);
        
        // Format: UNIV + CODE_PROGRAM + ANNÉE + SÉQUENTIEL
        $programCode = $program->code ?? substr($program->name, 0, 3);
        
        $lastNumber = Student::where('school_id', $school->id)
            ->where('student_type', 'university')
            ->whereYear('created_at', $academicYear)
            ->max('student_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -4)) + 1 : 1;

        return 'UNIV' . strtoupper($programCode) . $academicYear . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function createUniversityEnrollment(Student $student, array $data, array $fees): Enrollment
    {
        return Enrollment::create([
            'student_id' => $student->id,
            'school_id' => $data['school_id'],
            'program_id' => $data['program_id'],
            'level' => $data['level'],
            'academic_year_id' => $data['academic_year_id'],
            'enrollment_type' => 'university_new',
            'enrollment_date' => now(),
            'status' => 'active',
            'target_credits' => 60, // Année complète LMD
            'fees' => $fees,
            'notes' => $data['notes'] ?? 'Nouvelle inscription universitaire',
        ]);
    }

    private function calculateProgramSpecificFees(Program $program, string $level): array
    {
        $fees = [];
        
        // Frais spécifiques selon le type de programme
        if ($program->department->name === 'Informatique') {
            $fees['computer_lab'] = 50000;
        }
        
        if (in_array($level, ['M1', 'M2'])) {
            $fees['master_supplement'] = 75000;
        }

        if (str_starts_with($level, 'D')) {
            $fees['thesis_supervision'] = 150000;
        }

        return $fees;
    }

    private function calculateUniversityDiscounts(array $studentData, array $fees, Program $program): array
    {
        $discounts = [];

        // Bourse de mérite
        if (($studentData['bac_average'] ?? 0) >= 15) {
            $discounts['merit_scholarship'] = array_sum($fees) * 0.15; // 15%
        }

        // Bourse sociale
        if ($studentData['social_scholarship'] ?? false) {
            $discounts['social_aid'] = array_sum($fees) * 0.3; // 30%
        }

        // Réduction excellence académique
        if (($studentData['previous_average'] ?? 0) >= 16) {
            $discounts['academic_excellence'] = $fees['tuition'] * 0.1; // 10% sur scolarité
        }

        return $discounts;
    }

    private function generateUniversityPaymentSchedule(float $total): array
    {
        // Paiement universitaire en 2 échéances semestrielles
        $semesterAmount = $total / 2;
        
        return [
            [
                'due_date' => now()->addWeeks(2)->format('Y-m-d'),
                'amount' => $semesterAmount,
                'description' => 'Inscription 1er semestre',
                'semester' => 'S1',
            ],
            [
                'due_date' => now()->addMonths(5)->format('Y-m-d'),
                'amount' => $semesterAmount,  
                'description' => 'Inscription 2ème semestre',
                'semester' => 'S2',
            ],
        ];
    }

    private function checkAccessDiploma(array $studentData, string $targetLevel): array
    {
        $errors = [];
        
        $requiredDiplomas = [
            'L1' => ['bac', 'equivalent'],
            'L2' => ['L1_validated'],
            'L3' => ['L2_validated'],
            'M1' => ['license'],
            'M2' => ['M1_validated'],
            'D1' => ['master'],
        ];

        $required = $requiredDiplomas[$targetLevel] ?? [];
        
        if (in_array('bac', $required) && !($studentData['has_bac'] ?? false)) {
            $errors[] = 'Diplôme du baccalauréat requis';
        }

        if (in_array('license', $required) && !($studentData['has_license'] ?? false)) {
            $errors[] = 'Diplôme de licence requis';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    private function checkAcademicPrerequisites(array $previousResults, Program $program, string $targetLevel): array
    {
        // TODO: Implémenter vérification détaillée des prérequis académiques
        return ['is_valid' => true];
    }

    private function checkProgramCapacity(Program $program, string $targetLevel): array
    {
        // TODO: Vérifier capacité d'accueil par programme/niveau
        return ['available' => true];
    }

    private function checkSpecialPrerequisites(array $studentData, Program $program): array
    {
        // TODO: Vérifier prérequis spéciaux (concours, etc.)
        return ['is_valid' => true];
    }

    private function isBeginningOfSemester(): bool
    {
        // TODO: Vérifier si on est en période d'inscription
        return true;
    }

    private function calculateStudentCredits(Student $student): int
    {
        // TODO: Calculer crédits ECTS acquis
        return 0;
    }

    private function getMinimumCreditsForLevel(string $levelName): int
    {
        $requirements = [
            'L2' => 60,
            'L3' => 120, 
            'M1' => 180,
            'M2' => 240,
        ];

        return $requirements[$levelName] ?? 0;
    }
}