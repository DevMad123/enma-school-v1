<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Payment;
use App\Models\SchoolFee;
use App\Models\AcademicYear;
use App\Models\TeacherAssignment;
use App\Models\UserLog;
use App\Models\ActivityLog;
use Carbon\Carbon;

/**
 * Service pour les données du dashboard d'administration
 * 
 * @package App\Services\Dashboard
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class AdminDashboardService
{
    /**
     * Obtenir les données complètes du dashboard admin
     * 
     * @param User $user
     * @param School $school
     * @return array
     */
    public function getAdminDashboardData(User $user, School $school): array
    {
        $overviewStats = $this->getOverviewStatistics($school);
        $financialSummary = $this->getFinancialSummary($school);
        $supervisionStats = $this->getSupervisionStatistics($school);
        
        return [
            'overview_stats' => $overviewStats,
            'financial_stats' => $financialSummary,
            'supervision_data' => $supervisionStats,
            'recent_activities' => $this->getRecentActivities($school),
            'quick_actions' => $this->getQuickActions(),
            'enrollment_trend' => $this->getEnrollmentTrend($school),
        ];
    }

    /**
     * Obtenir les données du dashboard gouvernance
     * 
     * @param User $user
     * @param School $school
     * @return array
     */
    public function getGovernanceDashboardData(User $user, School $school): array
    {
        return [
            'school_governance' => $this->getGovernanceMetrics($school),
            'academic_structure' => $this->getAcademicStructureStatus($school),
            'staff_overview' => $this->getStaffOverview($school),
            'regulatory_compliance' => $this->getRegulatoryCompliance($school),
            'strategic_indicators' => $this->getStrategicIndicators($school),
        ];
    }

    /**
     * Obtenir les données du dashboard supervision
     * 
     * @param User $user
     * @param School $school
     * @return array
     */
    public function getSupervisionDashboardData(User $user, School $school): array
    {
        return [
            'supervision_metrics' => $this->getSupervisionMetrics($school),
            'user_activities' => $this->getUserActivities($school),
            'system_alerts' => $this->getSystemAlerts($school),
            'security_events' => $this->getSecurityEvents($school),
            'audit_trails' => $this->getAuditTrails($school),
        ];
    }

    /**
     * Statistiques générales
     * 
     * @param School $school
     * @return array
     */
    protected function getOverviewStatistics(School $school): array
    {
        return [
            'school_context' => $school->name . ' (' . ucfirst($school->type) . ')',
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_classes' => SchoolClass::count(),
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'new_students_this_month' => Student::whereMonth('created_at', Carbon::now()->month)->count(),
            'new_teachers_this_month' => Teacher::whereMonth('created_at', Carbon::now()->month)->count(),
        ];
    }

    /**
     * Résumé financier
     * 
     * @param School $school
     * @return array
     */
    protected function getFinancialSummary(School $school): array
    {
        $currentYear = AcademicYear::current();
        $totalRevenue = Payment::where('status', 'confirmed')->sum('amount');
        $totalExpected = SchoolFee::where('status', 'active')->sum('amount');
        
        return [
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => Payment::where('status', 'confirmed')
                ->whereMonth('created_at', Carbon::now()->month)
                ->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'overdue_payments' => SchoolFee::where('due_date', '<', Carbon::now())
                ->where('status', 'active')
                ->count(),
            'collection_rate' => $totalExpected > 0 ? round(($totalRevenue / $totalExpected) * 100, 1) : 0,
        ];
    }

    /**
     * Résumé académique
     * 
     * @param School $school
     * @return array
     */
    protected function getAcademicSummary(School $school): array
    {
        $currentYear = AcademicYear::current();
        
        return [
            'current_academic_year' => $currentYear,
            'active_classes' => SchoolClass::where('is_active', true)->count(),
            'teacher_assignments' => TeacherAssignment::count(),
            'enrollment_stats' => $this->getEnrollmentStats($school),
            'academic_progress' => $this->getAcademicProgress($school),
        ];
    }

    /**
     * Statistiques de supervision
     * 
     * @param School $school
     * @return array
     */
    protected function getSupervisionStatistics(School $school): array
    {
        return [
            'today_logins' => UserLog::byAction('logged_in')->today()->count(),
            'active_users_week' => UserLog::byAction('logged_in')->thisWeek()->distinct('user_id')->count(),
            'system_activities' => ActivityLog::thisMonth()->count(),
            'active_teachers' => $this->getActiveTeachersCount(),
            'active_students' => $this->getActiveStudentsCount(),
            'system_usage' => $this->getSystemUsageMetrics(),
        ];
    }

    /**
     * Activités récentes
     * 
     * @param School $school
     * @return array
     */
    protected function getRecentActivities(School $school): \Illuminate\Support\Collection
    {
        $activities = collect();

        // Paiements récents
        Payment::with(['student.user', 'schoolFee'])
            ->where('status', 'confirmed')
            ->latest()
            ->limit(3)
            ->get()
            ->each(function($payment) use ($activities) {
                $activities->push([
                    'icon' => 'money-bill-wave',
                    'title' => 'Paiement reçu',
                    'description' => $payment->student->user->name . ' - ' . number_format($payment->amount) . ' XOF',
                    'date' => $payment->created_at
                ]);
            });

        // Inscriptions récentes
        Student::with('user')
            ->latest()
            ->limit(3)
            ->get()
            ->each(function($student) use ($activities) {
                $activities->push([
                    'icon' => 'user-plus',
                    'title' => 'Nouvelle inscription',
                    'description' => 'Nouvel étudiant : ' . $student->user->name,
                    'date' => $student->created_at
                ]);
            });

        // Activités système récentes
        if (class_exists('App\Models\ActivityLog')) {
            ActivityLog::with('user')
                ->latest()
                ->limit(2)
                ->get()
                ->each(function($log) use ($activities) {
                    $activities->push([
                        'icon' => 'cog',
                        'title' => 'Activité système',
                        'description' => $log->description ?? 'Action système',
                        'date' => $log->created_at
                    ]);
                });
        }

        return $activities->sortByDesc('date')->take(8);
    }

    /**
     * Actions en attente
     * 
     * @param School $school
     * @return array
     */
    protected function getPendingActions(School $school): array
    {
        return [
            'pending_registrations' => $this->getPendingRegistrations(),
            'pending_payments' => $this->getPendingPayments(),
            'pending_approvals' => $this->getPendingApprovals(),
            'urgent_tasks' => $this->getUrgentTasks(),
        ];
    }

    /**
     * Santé du système
     * 
     * @param School $school
     * @return array
     */
    protected function getSystemHealthData(School $school): array
    {
        return [
            'database_status' => 'healthy',
            'storage_usage' => $this->getStorageUsage(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'error_rates' => $this->getErrorRates(),
        ];
    }

    // Méthodes utilitaires

    protected function getRevenueTrend(): array
    {
        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $amount = Payment::where('status', 'confirmed')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('amount');
            
            $trend->push([
                'month' => $month->format('M'),
                'amount' => $amount
            ]);
        }
        return $trend->toArray();
    }

    protected function getEnrollmentStats(School $school): array
    {
        $trend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Student::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            
            $trend->push([
                'month' => $month->format('M'),
                'count' => $count
            ]);
        }
        return $trend->toArray();
    }

    protected function getActiveTeachersCount(): int
    {
        return UserLog::byAction('logged_in')
            ->thisWeek()
            ->whereHas('user.teacher')
            ->distinct('user_id')
            ->count();
    }

    protected function getActiveStudentsCount(): int
    {
        return UserLog::byAction('logged_in')
            ->thisWeek()
            ->whereHas('user.student')
            ->distinct('user_id')
            ->count();
    }

    protected function getPendingRegistrations(): int
    {
        // À implémenter selon la logique métier
        return 0;
    }

    protected function getPendingPayments(): int
    {
        return Payment::where('status', 'pending')->count();
    }

    protected function getPendingApprovals(): int
    {
        // À implémenter selon la logique métier
        return 0;
    }

    protected function getUrgentTasks(): array
    {
        // À implémenter selon la logique métier
        return [];
    }

    protected function getStorageUsage(): array
    {
        // À implémenter selon les besoins
        return ['used' => 0, 'total' => 100, 'percentage' => 0];
    }

    protected function getPerformanceMetrics(): array
    {
        // À implémenter selon les besoins
        return ['response_time' => 150, 'cpu_usage' => 25, 'memory_usage' => 45];
    }

    protected function getErrorRates(): array
    {
        // À implémenter selon les besoins
        return ['error_rate' => 0.1, 'critical_errors' => 0];
    }

    // Méthodes pour gouvernance et supervision (à étendre selon les besoins)
    
    protected function getGovernanceMetrics(School $school): array
    {
        return [];
    }

    protected function getAcademicStructureStatus(School $school): array
    {
        return [];
    }

    protected function getStaffOverview(School $school): array
    {
        return [];
    }

    protected function getRegulatoryCompliance(School $school): array
    {
        return [];
    }

    protected function getStrategicIndicators(School $school): array
    {
        return [];
    }

    protected function getSupervisionMetrics(School $school): array
    {
        return [];
    }

    protected function getUserActivities(School $school): array
    {
        return [];
    }

    protected function getSystemAlerts(School $school): array
    {
        return [];
    }

    protected function getSecurityEvents(School $school): array
    {
        return [];
    }

    protected function getAuditTrails(School $school): array
    {
        return [];
    }

    protected function getAcademicProgress(School $school): array
    {
        return [];
    }

    protected function getSystemUsageMetrics(): array
    {
        return [];
    }

    /**
     * Statistiques académiques
     * 
     * @param School $school
     * @param string $period
     * @return array
     */
    public function getAcademicStats(School $school, string $period): array
    {
        // À implémenter selon les besoins
        return [];
    }

    /**
     * Statistiques financières
     * 
     * @param School $school
     * @param string $period
     * @return array
     */
    public function getFinancialStats(School $school, string $period): array
    {
        // À implémenter selon les besoins
        return [];
    }

    /**
     * Actions rapides disponibles pour l'admin
     * 
     * @return array
     */
    protected function getQuickActions(): array
    {
        return [
            [
                'title' => 'Nouvel Étudiant',
                'route' => 'admin.students.create',
                'icon' => 'user-plus',
                'color' => 'primary',
                'permission' => 'create_students'
            ],
            [
                'title' => 'Nouvel Enseignant',
                'route' => 'admin.teachers.create',
                'icon' => 'chalkboard-teacher',
                'color' => 'success',
                'permission' => 'create_teachers'
            ],
            [
                'title' => 'Nouvelle Année',
                'route' => 'admin.academic-years.create',
                'icon' => 'calendar-plus',
                'color' => 'info',
                'permission' => 'manage_academic_years'
            ],
            [
                'title' => 'Rapports',
                'route' => 'admin.supervision.index',
                'icon' => 'chart-bar',
                'color' => 'warning',
                'permission' => 'view_reports'
            ]
        ];
    }

    /**
     * Tendance des inscriptions sur 6 mois
     * 
     * @param School $school
     * @return \Illuminate\Support\Collection
     */
    protected function getEnrollmentTrend(School $school): \Illuminate\Support\Collection
    {
        $months = collect();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = Student::whereYear('created_at', $date->year)
                          ->whereMonth('created_at', $date->month)
                          ->count();
            
            $months->push([
                'month' => $date->format('M'),
                'count' => $count
            ]);
        }
        
        return $months;
    }
}