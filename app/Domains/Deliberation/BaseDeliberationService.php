<?php

namespace App\Domains\Deliberation;

use App\Domains\Deliberation\DeliberationServiceInterface;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de base pour les délibérations
 * Fonctionnalités communes aux conseils de classe et jurys universitaires
 */
abstract class BaseDeliberationService implements DeliberationServiceInterface
{
    /**
     * Types de décisions communes
     */
    protected const DECISION_TYPES = [
        'admitted' => 'Admis',
        'repeat' => 'Redouble',
        'excluded' => 'Exclu',
        'transferred' => 'Orienté',
        'special_case' => 'Cas particulier',
    ];

    /**
     * Statuts de session de délibération
     */
    protected const SESSION_STATUSES = [
        'scheduled' => 'Programmée',
        'in_progress' => 'En cours',
        'completed' => 'Terminée',
        'validated' => 'Validée',
        'archived' => 'Archivée',
    ];

    /**
     * Organiser une session de délibération
     *
     * @param array $sessionData
     * @return array
     */
    public function organizeDeliberationSession(array $sessionData): array
    {
        try {
            DB::beginTransaction();

            // Valider les données de session
            $validation = $this->validateSessionData($sessionData);
            if (!$validation['is_valid']) {
                return $validation;
            }

            // Créer la session de délibération
            $session = $this->createDeliberationSession($sessionData);

            // Calculer les résultats préliminaires
            $preliminaryResults = $this->calculateDeliberationResults($sessionData);

            // Préparer les dossiers pour délibération
            $studentFiles = $this->prepareStudentFiles($sessionData, $preliminaryResults);

            DB::commit();

            return [
                'success' => true,
                'session' => $session,
                'preliminary_results' => $preliminaryResults,
                'student_files' => $studentFiles,
                'statistics' => $this->calculateSessionStatistics($preliminaryResults),
                'message' => 'Session de délibération organisée avec succès',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'error' => 'Erreur lors de l\'organisation de la délibération: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Déterminer les décisions finales
     *
     * @param array $studentsResults
     * @param array $deliberationRules
     * @return array
     */
    public function determineFinalDecisions(array $studentsResults, array $deliberationRules): array
    {
        $decisions = [];
        
        foreach ($studentsResults as $studentResult) {
            $proposedDecision = $this->calculateProposedDecision($studentResult, $deliberationRules);
            
            $decisions[] = [
                'student_id' => $studentResult['student_id'],
                'student_info' => $studentResult['student_info'],
                'academic_results' => $studentResult['results'],
                'proposed_decision' => $proposedDecision,
                'final_decision' => $proposedDecision['decision'], // Peut être modifié par le conseil
                'justification' => $proposedDecision['justification'],
                'special_mentions' => $this->calculateSpecialMentions($studentResult),
                'recommendations' => $this->generateRecommendations($studentResult),
                'appeal_deadline' => now()->addDays(15)->format('Y-m-d'),
            ];
        }

        return [
            'decisions' => $decisions,
            'summary' => $this->calculateDecisionsSummary($decisions),
            'validation_required' => $this->requiresSpecialValidation($decisions),
        ];
    }

    /**
     * Générer le procès-verbal de délibération
     *
     * @param array $sessionData
     * @param array $decisions
     * @return array
     */
    public function generateDeliberationReport(array $sessionData, array $decisions): array
    {
        $reportData = [
            'header' => $this->generateReportHeader($sessionData),
            'session_info' => $this->formatSessionInfo($sessionData),
            'participants' => $this->formatParticipants($sessionData['participants'] ?? []),
            'academic_context' => $this->formatAcademicContext($sessionData),
            'decisions' => $this->formatDecisionsForReport($decisions),
            'statistics' => $this->generateDetailedStatistics($decisions),
            'signatures' => $this->prepareSignatureSection($sessionData),
            'appendices' => $this->generateAppendices($sessionData, $decisions),
        ];

        return [
            'report_data' => $reportData,
            'pdf_ready' => true,
            'file_name' => $this->generateReportFileName($sessionData),
            'generated_at' => now(),
        ];
    }

    /**
     * Valider les décisions de délibération
     *
     * @param array $decisions
     * @return array
     */
    public function validateDeliberationDecisions(array $decisions): array
    {
        $errors = [];
        $warnings = [];
        
        foreach ($decisions['decisions'] as $decision) {
            // Validation de cohérence
            $coherenceCheck = $this->checkDecisionCoherence($decision);
            if (!$coherenceCheck['is_valid']) {
                $errors[] = "Étudiant {$decision['student_id']}: {$coherenceCheck['error']}";
            }

            // Vérifications spéciales
            $specialChecks = $this->performSpecialChecks($decision);
            $warnings = array_merge($warnings, $specialChecks['warnings']);
            $errors = array_merge($errors, $specialChecks['errors']);
        }

        // Validation globale de la session
        $globalValidation = $this->validateGlobalCoherence($decisions);
        if (!$globalValidation['is_valid']) {
            $errors = array_merge($errors, $globalValidation['errors']);
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
            'requires_review' => !empty($warnings),
        ];
    }

    /**
     * Notifier les décisions aux étudiants
     *
     * @param array $decisions
     * @param array $notificationOptions
     * @return array
     */
    public function notifyDeliberationResults(array $decisions, array $notificationOptions = []): array
    {
        $notifications = [];
        $failed = [];

        foreach ($decisions['decisions'] as $decision) {
            try {
                $notification = $this->sendStudentNotification($decision, $notificationOptions);
                $notifications[] = $notification;
            } catch (\Exception $e) {
                $failed[] = [
                    'student_id' => $decision['student_id'],
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_sent' => count($notifications),
            'total_failed' => count($failed),
            'success_rate' => count($notifications) / (count($notifications) + count($failed)) * 100,
            'notifications' => $notifications,
            'failed_notifications' => $failed,
        ];
    }

    /**
     * Gérer les recours/appels
     *
     * @param array $appealData
     * @return array
     */
    public function processAppeal(array $appealData): array
    {
        try {
            // Valider la recevabilité du recours
            $admissibilityCheck = $this->checkAppealAdmissibility($appealData);
            if (!$admissibilityCheck['is_admissible']) {
                return [
                    'success' => false,
                    'appeal_status' => 'rejected',
                    'reason' => $admissibilityCheck['reason'],
                ];
            }

            // Enregistrer le recours
            $appeal = $this->registerAppeal($appealData);

            // Programmer l'examen du recours
            $reviewSchedule = $this->scheduleAppealReview($appeal);

            return [
                'success' => true,
                'appeal' => $appeal,
                'review_schedule' => $reviewSchedule,
                'next_steps' => $this->getAppealNextSteps($appeal),
                'message' => 'Recours enregistré et programmé pour examen',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Erreur lors du traitement du recours: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtenir l'historique des délibérations
     *
     * @param array $criteria
     * @return array
     */
    public function getDeliberationHistory(array $criteria): array
    {
        $query = $this->buildHistoryQuery($criteria);
        $sessions = $query->get();

        return [
            'total_sessions' => $sessions->count(),
            'sessions' => $sessions->map(function ($session) {
                return $this->formatSessionForHistory($session);
            }),
            'statistics' => $this->calculateHistoryStatistics($sessions),
            'trends' => $this->analyzeTrends($sessions),
        ];
    }

    // Méthodes utilitaires communes

    /**
     * Valider les données de session
     *
     * @param array $sessionData
     * @return array
     */
    protected function validateSessionData(array $sessionData): array
    {
        $errors = [];

        $requiredFields = ['type', 'academic_year_id', 'school_id', 'session_date', 'participants'];
        foreach ($requiredFields as $field) {
            if (!isset($sessionData[$field]) || empty($sessionData[$field])) {
                $errors[] = "Le champ {$field} est obligatoire";
            }
        }

        // Valider la date de session
        if (isset($sessionData['session_date'])) {
            $sessionDate = \Carbon\Carbon::parse($sessionData['session_date']);
            if ($sessionDate->isPast()) {
                $errors[] = 'La date de session ne peut pas être dans le passé';
            }
        }

        // Valider les participants
        if (isset($sessionData['participants']) && count($sessionData['participants']) < 3) {
            $errors[] = 'Au moins 3 participants sont requis pour une délibération valide';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Calculer les mentions spéciales
     *
     * @param array $studentResult
     * @return array
     */
    protected function calculateSpecialMentions(array $studentResult): array
    {
        $mentions = [];
        
        $average = $studentResult['results']['average'] ?? 0;
        
        // Mentions académiques standard
        if ($average >= 16) {
            $mentions[] = 'Félicitations du conseil';
        } elseif ($average >= 14) {
            $mentions[] = 'Encouragements du conseil';
        }

        // Mentions spéciales
        if (isset($studentResult['progress']) && $studentResult['progress'] >= 3) {
            $mentions[] = 'Progrès remarquables';
        }

        if (isset($studentResult['attendance_rate']) && $studentResult['attendance_rate'] >= 95) {
            $mentions[] = 'Assiduité exemplaire';
        }

        return $mentions;
    }

    /**
     * Générer des recommandations
     *
     * @param array $studentResult
     * @return array
     */
    protected function generateRecommendations(array $studentResult): array
    {
        $recommendations = [];
        
        $average = $studentResult['results']['average'] ?? 0;
        $weakSubjects = $studentResult['results']['weak_subjects'] ?? [];

        if ($average < 10) {
            $recommendations[] = 'Intensifier le travail personnel';
            $recommendations[] = 'Solliciter un soutien pédagogique';
        }

        if (!empty($weakSubjects)) {
            $recommendations[] = 'Se concentrer particulièrement sur: ' . implode(', ', $weakSubjects);
        }

        if (isset($studentResult['attendance_issues']) && $studentResult['attendance_issues']) {
            $recommendations[] = 'Améliorer l\'assiduité et la ponctualité';
        }

        return $recommendations;
    }

    /**
     * Calculer les statistiques de session
     *
     * @param array $results
     * @return array
     */
    protected function calculateSessionStatistics(array $results): array
    {
        $total = count($results);
        if ($total === 0) return [];

        $averages = collect($results)->pluck('average');
        
        return [
            'total_students' => $total,
            'average_class' => round($averages->avg(), 2),
            'highest_average' => $averages->max(),
            'lowest_average' => $averages->min(),
            'median_average' => $averages->median(),
            'pass_rate_estimate' => $averages->filter(fn($avg) => $avg >= 10)->count() / $total * 100,
        ];
    }

    /**
     * Formater les informations de session
     *
     * @param array $sessionData
     * @return array
     */
    protected function formatSessionInfo(array $sessionData): array
    {
        return [
            'session_id' => $sessionData['id'] ?? null,
            'type' => $sessionData['type'],
            'date' => $sessionData['session_date'],
            'duration' => $sessionData['duration'] ?? 'Non spécifiée',
            'location' => $sessionData['location'] ?? 'Non spécifiée',
            'academic_year' => $sessionData['academic_year']['year'] ?? null,
            'school' => $sessionData['school']['name'] ?? null,
        ];
    }

    // Méthodes abstraites à implémenter dans les classes enfants

    /**
     * Calculer la décision proposée spécifique au contexte
     *
     * @param array $studentResult
     * @param array $rules
     * @return array
     */
    abstract protected function calculateProposedDecision(array $studentResult, array $rules): array;

    /**
     * Créer une session de délibération spécifique au contexte
     *
     * @param array $sessionData
     * @return mixed
     */
    abstract protected function createDeliberationSession(array $sessionData);

    /**
     * Préparer les dossiers étudiants pour délibération
     *
     * @param array $sessionData
     * @param array $results
     * @return array
     */
    abstract protected function prepareStudentFiles(array $sessionData, array $results): array;

    /**
     * Générer l'en-tête du rapport spécifique au contexte
     *
     * @param array $sessionData
     * @return array
     */
    abstract protected function generateReportHeader(array $sessionData): array;

    /**
     * Vérifier la cohérence d'une décision selon le contexte
     *
     * @param array $decision
     * @return array
     */
    abstract protected function checkDecisionCoherence(array $decision): array;

    /**
     * Envoyer une notification à un étudiant
     *
     * @param array $decision
     * @param array $options
     * @return array
     */
    abstract protected function sendStudentNotification(array $decision, array $options): array;

    /**
     * Construire la requête pour l'historique
     *
     * @param array $criteria
     * @return mixed
     */
    abstract protected function buildHistoryQuery(array $criteria);

    /**
     * Formater une session pour l'historique
     *
     * @param mixed $session
     * @return array
     */
    abstract protected function formatSessionForHistory($session): array;

    /**
     * Calculer les statistiques détaillées
     *
     * @param array $decisions
     * @return array
     */
    protected function generateDetailedStatistics(array $decisions): array
    {
        $decisions = $decisions['decisions'] ?? $decisions;
        $total = count($decisions);
        
        if ($total === 0) return [];

        $decisionCounts = [];
        foreach ($decisions as $decision) {
            $decisionType = $decision['final_decision']['decision'] ?? 'unknown';
            $decisionCounts[$decisionType] = ($decisionCounts[$decisionType] ?? 0) + 1;
        }

        return [
            'total_students' => $total,
            'decision_breakdown' => $decisionCounts,
            'percentages' => array_map(fn($count) => round($count / $total * 100, 1), $decisionCounts),
            'special_cases' => count(array_filter($decisions, fn($d) => !empty($d['special_mentions']))),
            'appeals_eligible' => count(array_filter($decisions, fn($d) => $d['final_decision']['decision'] !== 'admitted')),
        ];
    }

    /**
     * Vérifier si des validations spéciales sont requises
     *
     * @param array $decisions
     * @return bool
     */
    protected function requiresSpecialValidation(array $decisions): bool
    {
        foreach ($decisions as $decision) {
            if (($decision['final_decision']['decision'] ?? '') === 'special_case') {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculer un résumé des décisions
     *
     * @param array $decisions
     * @return array
     */
    protected function calculateDecisionsSummary(array $decisions): array
    {
        return $this->generateDetailedStatistics($decisions);
    }

    /**
     * Effectuer des vérifications spéciales
     *
     * @param array $decision
     * @return array
     */
    protected function performSpecialChecks(array $decision): array
    {
        $warnings = [];
        $errors = [];

        // Vérifier les décisions exceptionnelles
        if (($decision['final_decision']['decision'] ?? '') === 'admitted' && 
            ($decision['academic_results']['average'] ?? 0) < 8) {
            $warnings[] = "Admission exceptionnelle avec moyenne très faible pour l'étudiant {$decision['student_id']}";
        }

        return [
            'warnings' => $warnings,
            'errors' => $errors,
        ];
    }

    /**
     * Valider la cohérence globale
     *
     * @param array $decisions
     * @return array
     */
    protected function validateGlobalCoherence(array $decisions): array
    {
        $errors = [];
        
        // Vérifier les taux anormaux
        $admissionRate = $this->calculateAdmissionRate($decisions);
        if ($admissionRate < 30) {
            $errors[] = 'Taux d\'admission anormalement bas (< 30%)';
        } elseif ($admissionRate > 95) {
            $errors[] = 'Taux d\'admission anormalement élevé (> 95%)';
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Calculer le taux d'admission
     *
     * @param array $decisions
     * @return float
     */
    protected function calculateAdmissionRate(array $decisions): float
    {
        $total = count($decisions['decisions'] ?? $decisions);
        if ($total === 0) return 0;

        $admitted = count(array_filter($decisions['decisions'] ?? $decisions, function ($decision) {
            return ($decision['final_decision']['decision'] ?? '') === 'admitted';
        }));

        return ($admitted / $total) * 100;
    }
}