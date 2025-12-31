<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Payment;
use App\Models\SchoolFee;
use App\Models\UserLog;
use App\Models\ActivityLog;
use App\Models\TeacherAssignment;
use App\Models\Grade;
use App\Models\Evaluation;
use App\Traits\HasSchoolContext;
use App\Helpers\ActivityHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupervisionController extends Controller
{
    use HasSchoolContext;

    /**
     * Dashboard supervision avec statistiques globales
     */
    public function index()
    {
        // Statistiques globales
        $stats = $this->getGlobalStatistics();
        
        // Statistiques de connexions récentes
        $recentLogins = $this->getRecentLoginStats();
        
        // Top activités récentes
        $recentActivities = $this->getRecentActivities();

        return view('admin.supervision.index', compact('stats', 'recentLogins', 'recentActivities'));
    }

    /**
     * Activités des enseignants
     */
    public function teacherActivities(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        // Statistiques par enseignant
        $teacherStats = $this->getTeacherActivityStats($dateFrom, $dateTo);

        // Activités détaillées
        $activities = ActivityLog::forTeachers()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('user.teacher')
            ->latest()
            ->paginate(20);

        return view('admin.supervision.teacher-activities', compact('teacherStats', 'activities', 'dateFrom', 'dateTo'));
    }

    /**
     * Activités des étudiants
     */
    public function studentActivities(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());

        // Statistiques par étudiant
        $studentStats = $this->getStudentActivityStats($dateFrom, $dateTo);

        // Activités détaillées
        $activities = ActivityLog::forStudents()
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('user.student')
            ->latest()
            ->paginate(20);

        return view('admin.supervision.student-activities', compact('studentStats', 'activities', 'dateFrom', 'dateTo'));
    }

    /**
     * Logs de connexion
     */
    public function userLogs(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfWeek()->toDateString());
        $dateTo = $request->get('date_to', Carbon::now()->toDateString());
        $action = $request->get('action', '');

        $query = UserLog::with('user')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->latest()->paginate(50);

        // Statistiques des connexions
        $loginStats = $this->getLoginStatistics($dateFrom, $dateTo);

        return view('admin.supervision.user-logs', compact('logs', 'loginStats', 'dateFrom', 'dateTo', 'action'));
    }

    /**
     * API pour les graphiques du dashboard
     */
    public function getDashboardChartData(Request $request)
    {
        $period = $request->get('period', '7days');
        
        switch ($period) {
            case '30days':
                $startDate = Carbon::now()->subDays(30);
                $groupBy = 'DATE(created_at)';
                break;
            case '90days':
                $startDate = Carbon::now()->subDays(90);
                $groupBy = 'DATE(created_at)';
                break;
            default:
                $startDate = Carbon::now()->subDays(7);
                $groupBy = 'DATE(created_at)';
        }

        // Connexions par jour
        $loginData = UserLog::select(DB::raw($groupBy . ' as date'), DB::raw('COUNT(*) as count'))
            ->where('action', 'logged_in')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Activités par jour
        $activityData = ActivityLog::select(DB::raw($groupBy . ' as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'logins' => $loginData,
            'activities' => $activityData
        ]);
    }

    /**
     * Statistiques globales
     */
    private function getGlobalStatistics()
    {
        return [
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'total_classes' => SchoolClass::count(),
            'total_users' => User::count(),
            
            // Statistiques financières
            'total_payments' => Payment::sum('amount'),
            'pending_fees' => SchoolFee::where('status', 'pending')->sum('amount'),
            
            // Statistiques académiques
            'total_assignments' => TeacherAssignment::count(),
            'graded_evaluations' => Grade::count(),
            'pending_evaluations' => Evaluation::whereDoesntHave('grades')->count(),
            
            // Statistiques d'activité (30 derniers jours)
            'monthly_logins' => UserLog::byAction('logged_in')->thisMonth()->count(),
            'monthly_activities' => ActivityLog::thisMonth()->count(),
            
            // Statistiques d'aujourd'hui
            'today_logins' => UserLog::byAction('logged_in')->today()->count(),
            'today_activities' => ActivityLog::today()->count(),
        ];
    }

    /**
     * Statistiques de connexions récentes
     */
    private function getRecentLoginStats()
    {
        $today = Carbon::today();
        
        return [
            'today_unique_users' => UserLog::byAction('logged_in')
                ->whereDate('created_at', $today)
                ->distinct('user_id')
                ->count(),
            
            'this_week_unique_users' => UserLog::byAction('logged_in')
                ->thisWeek()
                ->distinct('user_id')
                ->count(),
                
            'most_active_users' => UserLog::byAction('logged_in')
                ->thisWeek()
                ->select('user_id', DB::raw('COUNT(*) as login_count'))
                ->with('user')
                ->groupBy('user_id')
                ->orderBy('login_count', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Activités récentes
     */
    private function getRecentActivities()
    {
        return ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Statistiques d'activité des enseignants
     */
    private function getTeacherActivityStats($dateFrom, $dateTo)
    {
        return Teacher::with('user')
            ->get()
            ->map(function ($teacher) use ($dateFrom, $dateTo) {
                $activities = ActivityLog::where('user_id', $teacher->user_id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo]);

                return [
                    'teacher' => $teacher,
                    'total_activities' => $activities->count(),
                    'courses_created' => $activities->clone()->byEntity('course')->byAction('created')->count(),
                    'assignments_created' => $activities->clone()->byEntity('assignment')->byAction('created')->count(),
                    'grades_given' => $activities->clone()->byEntity('grade')->byAction('created')->count(),
                    'last_activity' => $activities->clone()->latest()->first()?->created_at,
                ];
            })->sortByDesc('total_activities');
    }

    /**
     * Statistiques d'activité des étudiants
     */
    private function getStudentActivityStats($dateFrom, $dateTo)
    {
        return Student::with('user')
            ->get()
            ->map(function ($student) use ($dateFrom, $dateTo) {
                $activities = ActivityLog::where('user_id', $student->user_id)
                    ->whereBetween('created_at', [$dateFrom, $dateTo]);

                return [
                    'student' => $student,
                    'total_activities' => $activities->count(),
                    'courses_viewed' => $activities->clone()->byEntity('course')->byAction('viewed')->count(),
                    'assignments_submitted' => $activities->clone()->byEntity('assignment')->byAction('submitted')->count(),
                    'documents_downloaded' => $activities->clone()->byAction('downloaded')->count(),
                    'last_activity' => $activities->clone()->latest()->first()?->created_at,
                ];
            })->sortByDesc('total_activities');
    }

    /**
     * Statistiques des connexions
     */
    private function getLoginStatistics($dateFrom, $dateTo)
    {
        $loginLogs = UserLog::loginActions()
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        return [
            'total_logins' => $loginLogs->clone()->byAction('logged_in')->count(),
            'total_logouts' => $loginLogs->clone()->byAction('logged_out')->count(),
            'unique_users' => $loginLogs->clone()->byAction('logged_in')->distinct('user_id')->count(),
            
            'by_role' => UserLog::byAction('logged_in')
                ->whereBetween('user_logs.created_at', [$dateFrom, $dateTo])
                ->join('users', 'user_logs.user_id', '=', 'users.id')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name as role', DB::raw('COUNT(*) as count'))
                ->groupBy('roles.name')
                ->get(),
                
            'hourly_distribution' => UserLog::byAction('logged_in')
                ->whereBetween('user_logs.created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('HOUR(user_logs.created_at) as hour'), DB::raw('COUNT(*) as count'))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get(),
        ];
    }
}