<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Payment;
use App\Models\SchoolFee;
use App\Models\AcademicYear;
use App\Traits\HasSchoolContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service pour les données du dashboard de gestion opérationnelle
 * 
 * @package App\Services\Dashboard
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class StaffDashboardService
{
    use HasSchoolContext;

    /**
     * Données complètes du dashboard staff
     */
    public function getStaffDashboardData(User $user, School $school): array
    {
        return [
            'user_role' => $user->getRoleNames()->first(),
            'daily_tasks' => $this->getDailyTasks($user, $school),
            'operational_stats' => $this->getOperationalStats($school),
            'priority_alerts' => $this->getPriorityAlerts($school),
            'financial_summary' => $this->getFinancialSummary($school),
            'recent_activities' => $this->getRecentActivities($school),
        ];
    }

    /**
     * Données du dashboard financier
     */
    public function getFinancialDashboardData(User $user, School $school): array
    {
        $currentYear = AcademicYear::current();
        
        return [
            'payment_stats' => $this->getPaymentStatistics($school, $currentYear),
            'collection_report' => $this->getCollectionReport($school),
            'pending_payments' => $this->getPendingPayments($school),
            'overdue_analysis' => $this->getOverdueAnalysis($school),
            'revenue_trend' => $this->getRevenueTrend($school),
        ];
    }

    /**
     * Données de supervision des étudiants
     */
    public function getStudentSupervisionData(User $user, School $school): array
    {
        return [
            'enrollment_pipeline' => $this->getEnrollmentPipeline($school),
            'student_status_overview' => $this->getStudentStatusOverview($school),
            'academic_alerts' => $this->getAcademicAlerts($school),
            'attendance_summary' => $this->getAttendanceSummary($school),
        ];
    }

    /**
     * Statistiques opérationnelles quotidiennes
     */
    public function getDailyOperationalStats(School $school, string $date): array
    {
        $targetDate = Carbon::parse($date);
        
        return [
            'date' => $targetDate->format('d/m/Y'),
            'enrollments_today' => $this->getEnrollmentsCount($school, $targetDate),
            'payments_today' => $this->getPaymentsCount($school, $targetDate),
            'student_activities' => $this->getStudentActivitiesCount($school, $targetDate),
            'staff_productivity' => $this->getStaffProductivity($school, $targetDate),
        ];
    }

    /**
     * Tâches prioritaires pour l'utilisateur
     */
    public function getPriorityTasks(User $user, School $school): array
    {
        $tasks = collect();
        
        // Tâches selon le rôle
        if ($user->hasRole('accountant')) {
            $tasks = $tasks->merge($this->getAccountingTasks($school));
        }
        
        if ($user->hasRole('supervisor')) {
            $tasks = $tasks->merge($this->getSupervisionTasks($school));
        }
        
        if ($user->hasRole('staff')) {
            $tasks = $tasks->merge($this->getGeneralStaffTasks($school));
        }
        
        return $tasks->sortBy('priority')->take(8)->values()->toArray();
    }

    /**
     * Tâches quotidiennes
     */
    protected function getDailyTasks(User $user, School $school): array
    {
        return [
            [
                'title' => 'Vérifier les nouveaux paiements',
                'description' => 'Traiter les paiements en attente de validation',
                'priority' => 'high',
                'count' => Payment::where('status', 'pending')->count(),
                'route' => 'payments.pending',
            ],
            [
                'title' => 'Suivre les inscriptions en cours',
                'description' => 'Valider les dossiers d\'inscription soumis',
                'priority' => 'medium',
                'count' => 3, // TODO: Lier au système d'inscription
                'route' => 'enrollments.pending',
            ],
            [
                'title' => 'Relances automatiques',
                'description' => 'Envoyer les rappels de paiement',
                'priority' => 'low',
                'count' => $this->getOverduePaymentsCount($school),
                'route' => 'payments.reminders',
            ],
        ];
    }

    /**
     * Statistiques opérationnelles
     */
    protected function getOperationalStats(School $school): array
    {
        return [
            'total_students' => Student::where('school_id', $school->id)->count(),
            'active_teachers' => Teacher::where('school_id', $school->id)
                ->where('status', 'active')->count(),
            'pending_enrollments' => 5, // TODO: Implémenter système d'inscription
            'financial_health' => $this->calculateFinancialHealth($school),
        ];
    }

    /**
     * Alertes prioritaires
     */
    protected function getPriorityAlerts(School $school): Collection
    {
        $alerts = collect();
        
        // Alertes financières
        $overdueCount = $this->getOverduePaymentsCount($school);
        if ($overdueCount > 0) {
            $alerts->push([
                'type' => 'financial',
                'level' => 'warning',
                'title' => 'Paiements en retard',
                'message' => "$overdueCount paiements en retard nécessitent un suivi",
                'action_route' => 'payments.overdue',
                'count' => $overdueCount,
            ]);
        }
        
        // Alertes académiques
        $lowAttendance = $this->getLowAttendanceCount($school);
        if ($lowAttendance > 0) {
            $alerts->push([
                'type' => 'academic',
                'level' => 'info',
                'title' => 'Présence faible',
                'message' => "$lowAttendance étudiants avec taux de présence < 80%",
                'action_route' => 'attendance.alerts',
                'count' => $lowAttendance,
            ]);
        }
        
        return $alerts;
    }

    /**
     * Résumé financier
     */
    protected function getFinancialSummary(School $school): array
    {
        $currentMonth = Carbon::now();
        
        return [
            'monthly_target' => 5000000, // TODO: Configurer les objectifs
            'monthly_collected' => Payment::where('status', 'confirmed')
                ->whereMonth('created_at', $currentMonth->month)
                ->whereHas('student', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                })
                ->sum('amount'),
            'collection_rate' => $this->calculateCollectionRate($school),
            'pending_amount' => $this->calculatePendingAmount($school),
        ];
    }

    /**
     * Activités récentes
     */
    protected function getRecentActivities(School $school): Collection
    {
        return collect([
            [
                'type' => 'payment',
                'title' => 'Nouveau paiement reçu',
                'description' => 'Frais de scolarité - 75,000 XOF',
                'time' => Carbon::now()->subMinutes(15),
                'icon' => 'credit-card',
            ],
            [
                'type' => 'enrollment',
                'title' => 'Nouvelle inscription validée',
                'description' => 'Marie Kouassi - Classe CM2',
                'time' => Carbon::now()->subHours(2),
                'icon' => 'user-plus',
            ],
            [
                'type' => 'system',
                'title' => 'Rapport mensuel généré',
                'description' => 'Rapport financier décembre 2025',
                'time' => Carbon::now()->subHours(6),
                'icon' => 'file-alt',
            ],
        ]);
    }

    // Méthodes utilitaires privées
    private function getOverduePaymentsCount(School $school): int
    {
        return SchoolFee::where('due_date', '<', Carbon::now())
            ->where('status', 'active')
            ->whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->count();
    }

    private function calculateFinancialHealth(School $school): string
    {
        $collectionRate = $this->calculateCollectionRate($school);
        
        if ($collectionRate >= 90) return 'excellent';
        if ($collectionRate >= 75) return 'good';
        if ($collectionRate >= 60) return 'fair';
        return 'poor';
    }

    private function calculateCollectionRate(School $school): float
    {
        $currentYear = AcademicYear::current();
        if (!$currentYear) return 0.0;
        
        $totalDue = SchoolFee::where('academic_year_id', $currentYear->id)->sum('amount');
        $totalPaid = Payment::where('status', 'confirmed')
            ->whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->sum('amount');
            
        return $totalDue > 0 ? round(($totalPaid / $totalDue) * 100, 2) : 0.0;
    }

    private function calculatePendingAmount(School $school): int
    {
        return Payment::where('status', 'pending')
            ->whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->sum('amount');
    }

    private function getLowAttendanceCount(School $school): int
    {
        // TODO: Implémenter calcul de présence
        return 3;
    }

    private function getPaymentStatistics(School $school, ?AcademicYear $year): array
    {
        // TODO: Implémenter statistiques de paiement détaillées
        return [];
    }

    private function getCollectionReport(School $school): array
    {
        // TODO: Implémenter rapport de recouvrement
        return [];
    }

    private function getPendingPayments(School $school): Collection
    {
        return Payment::with(['student.user', 'schoolFee'])
            ->where('status', 'pending')
            ->whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getOverdueAnalysis(School $school): array
    {
        // TODO: Implémenter analyse des retards de paiement
        return [];
    }

    private function getRevenueTrend(School $school): array
    {
        // TODO: Implémenter tendance des revenus
        return [];
    }

    private function getEnrollmentPipeline(School $school): array
    {
        // TODO: Implémenter pipeline d'inscription
        return [];
    }

    private function getStudentStatusOverview(School $school): array
    {
        return [
            'active' => Student::where('school_id', $school->id)->where('status', 'active')->count(),
            'suspended' => Student::where('school_id', $school->id)->where('status', 'suspended')->count(),
            'graduated' => Student::where('school_id', $school->id)->where('status', 'graduated')->count(),
        ];
    }

    private function getAcademicAlerts(School $school): Collection
    {
        // TODO: Implémenter alertes académiques
        return collect();
    }

    private function getAttendanceSummary(School $school): array
    {
        // TODO: Implémenter résumé de présence
        return [];
    }

    private function getEnrollmentsCount(School $school, Carbon $date): int
    {
        return Student::where('school_id', $school->id)
            ->whereDate('created_at', $date)
            ->count();
    }

    private function getPaymentsCount(School $school, Carbon $date): int
    {
        return Payment::whereDate('created_at', $date)
            ->whereHas('student', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->count();
    }

    private function getStudentActivitiesCount(School $school, Carbon $date): int
    {
        // TODO: Implémenter comptage des activités étudiantes
        return 0;
    }

    private function getStaffProductivity(School $school, Carbon $date): array
    {
        // TODO: Implémenter métriques de productivité staff
        return [];
    }

    private function getAccountingTasks(School $school): Collection
    {
        return collect([
            [
                'title' => 'Rapprochement bancaire',
                'priority' => 1,
                'deadline' => Carbon::now()->addDay(),
                'route' => 'accounting.bank-reconciliation',
            ],
        ]);
    }

    private function getSupervisionTasks(School $school): Collection
    {
        return collect([
            [
                'title' => 'Rapport hebdomadaire',
                'priority' => 2,
                'deadline' => Carbon::now()->addDays(2),
                'route' => 'supervision.weekly-report',
            ],
        ]);
    }

    private function getGeneralStaffTasks(School $school): Collection
    {
        return collect([
            [
                'title' => 'Mise à jour des dossiers',
                'priority' => 3,
                'deadline' => Carbon::now()->addDays(3),
                'route' => 'staff.update-records',
            ],
        ]);
    }
}