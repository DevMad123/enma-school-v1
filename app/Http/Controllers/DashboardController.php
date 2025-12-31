<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Payment;
use App\Models\SchoolFee;
use App\Models\AcademicYear;
use App\Models\TeacherAssignment;
use App\Models\UserLog;
use App\Models\ActivityLog;
use App\Models\Grade;
use App\Models\Evaluation;
use App\Traits\HasSchoolContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard principal avec redirection selon le rôle
     */
    
    use HasSchoolContext;
    
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin') || $user->isStaff()) {
            return $this->adminDashboard();
        } elseif ($user->isTeacher()) {
            return $this->teacherDashboard();
        } elseif ($user->isStudent()) {
            return $this->studentDashboard();
        }
        
        // Dashboard par défaut si aucun rôle spécifique
        return $this->defaultDashboard();
    }

    /**
     * Dashboard Administrateur
     */
    public function adminDashboard()
    {
        // Statistiques globales
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();
        $totalUsers = User::count();

        // Statistiques financières
        $currentYear = AcademicYear::current();
        $totalRevenue = Payment::where('status', 'confirmed')->sum('amount');
        $monthlyRevenue = Payment::where('status', 'confirmed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');
        
        $pendingPayments = Payment::where('status', 'pending')->count();
        $overduePayments = SchoolFee::where('due_date', '<', Carbon::now())
            ->where('status', 'active')
            ->count();

        // Derniers paiements
        $recentPayments = Payment::with(['student.user', 'schoolFee'])
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Évolution des inscriptions sur 6 mois
        $enrollmentTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Student::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            
            $enrollmentTrend->push([
                'month' => $month->format('M'),
                'count' => $count
            ]);
        }

        // Actions rapides - dernières activités
        $recentActivities = [
            'new_students' => Student::whereDate('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'new_teachers' => Teacher::whereDate('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'total_assignments' => TeacherAssignment::count(), // Total des affectations au lieu de pending
        ];

        // Statistiques de supervision pour le Module A6
        $supervisionStats = [
            'today_logins' => UserLog::byAction('logged_in')->today()->count(),
            'week_unique_users' => UserLog::byAction('logged_in')->thisWeek()->distinct('user_id')->count(),
            'monthly_activities' => ActivityLog::thisMonth()->count(),
            'active_teachers' => UserLog::byAction('logged_in')
                ->thisWeek()
                ->whereHas('user', function($q) {
                    $q->whereHas('teacher');
                })
                ->distinct('user_id')
                ->count(),
            'active_students' => UserLog::byAction('logged_in')
                ->thisWeek()
                ->whereHas('user', function($q) {
                    $q->whereHas('student');
                })
                ->distinct('user_id')
                ->count(),
        ];

        // Activités récentes pour le Module A6
        $recentSystemActivities = ActivityLog::with('user')
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboards.admin', compact(
            'totalStudents', 'totalTeachers', 'totalClasses', 'totalUsers',
            'totalRevenue', 'monthlyRevenue', 'pendingPayments', 'overduePayments',
            'recentPayments', 'enrollmentTrend', 'recentActivities',
            'supervisionStats', 'recentSystemActivities'
        ));
    }

    /**
     * Dashboard Enseignant
     */
    public function teacherDashboard()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('dashboard')->with('error', 'Profil enseignant non trouvé.');
        }

        // Classes assignées
        $assignments = TeacherAssignment::with(['schoolClass.level', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->get(); // Suppression du where status car la colonne n'existe pas

        $totalClasses = $assignments->count();
        $totalStudents = $assignments->sum(function ($assignment) {
            return $assignment->schoolClass->students()->count();
        });

        // Prochaines évaluations et deadlines
        $upcomingDeadlines = collect([
            [
                'title' => 'Évaluation de Mathématiques - CM2',
                'date' => Carbon::now()->addDays(3),
                'type' => 'evaluation',
                'class' => 'CM2-A'
            ],
            [
                'title' => 'Remise des bulletins - CP1',
                'date' => Carbon::now()->addDays(7),
                'type' => 'deadline',
                'class' => 'CP1-B'
            ]
        ]);

        // Statistiques des classes
        $classStats = $assignments->map(function ($assignment) {
            $class = $assignment->schoolClass;
            return [
                'name' => $class->name,
                'subject' => $assignment->subject->name ?? 'Non défini',
                'students_count' => $class->students()->count(),
                'level' => $class->level->name ?? 'Non défini'
            ];
        });

        return view('dashboards.teacher', compact(
            'teacher', 'assignments', 'totalClasses', 'totalStudents',
            'upcomingDeadlines', 'classStats'
        ));
    }

    /**
     * Dashboard Élève
     */
    public function studentDashboard()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Profil étudiant non trouvé.');
        }

        // Informations de la classe
        $currentClass = $student->currentEnrollment()?->schoolClass;
        
        // Solde financier
        $currentYear = AcademicYear::current();
        $totalDue = $currentYear ? SchoolFee::where('academic_year_id', $currentYear->id)->sum('amount') : 0;
        
        $totalPaid = Payment::where('student_id', $student->id)
            ->where('status', 'confirmed')
            ->sum('amount');
            
        $balance = $totalDue - $totalPaid;

        // Historique des paiements
        $recentPayments = Payment::with('schoolFee')
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Notifications importantes
        $notifications = collect([
            [
                'type' => 'payment',
                'title' => 'Frais de scolarité en attente',
                'message' => 'Votre paiement du mois est en attente de confirmation',
                'date' => Carbon::now()->subDays(2),
                'urgent' => true
            ],
            [
                'type' => 'grade',
                'title' => 'Nouvelles notes disponibles',
                'message' => 'Vos notes de mathématiques ont été publiées',
                'date' => Carbon::now()->subDays(1),
                'urgent' => false
            ]
        ]);

        // Résultats récents (simulation)
        $recentGrades = collect([
            [
                'subject' => 'Mathématiques', 
                'grade' => 16, 
                'evaluation_type' => 'Devoir surveillé',
                'coefficient' => 3,
                'class_average' => 12.5,
                'rank' => 3,
                'total_students' => 25,
                'date' => Carbon::now()->subDays(3)
            ],
            [
                'subject' => 'Français', 
                'grade' => 14, 
                'evaluation_type' => 'Composition',
                'coefficient' => 4,
                'class_average' => 11.8,
                'rank' => 5,
                'total_students' => 25,
                'date' => Carbon::now()->subDays(5)
            ],
            [
                'subject' => 'Sciences', 
                'grade' => 18, 
                'evaluation_type' => 'Interrogation',
                'coefficient' => 2,
                'class_average' => 13.2,
                'rank' => 1,
                'total_students' => 25,
                'date' => Carbon::now()->subDays(7)
            ],
        ]);

        // Calcul de la moyenne générale et données supplémentaires
        $generalAverage = $recentGrades->avg('grade');
        $averageTrend = 1.2; // +1.2 points vs période précédente
        $totalSubjects = 8;
        $classRank = 4;
        $totalClassStudents = 25;
        $pendingPaymentsAmount = max(0, $balance);

        // Moyennes par matière
        $subjectAverages = collect([
            ['name' => 'Mathématiques', 'average' => 15.5, 'coefficient' => 4, 'rank' => 3],
            ['name' => 'Français', 'average' => 13.8, 'coefficient' => 4, 'rank' => 7],
            ['name' => 'Sciences', 'average' => 16.2, 'coefficient' => 3, 'rank' => 2],
            ['name' => 'Histoire-Géo', 'average' => 14.1, 'coefficient' => 2, 'rank' => 6],
            ['name' => 'Anglais', 'average' => 12.9, 'coefficient' => 2, 'rank' => 8],
            ['name' => 'EPS', 'average' => 17.5, 'coefficient' => 1, 'rank' => 1],
        ]);

        // Historique des paiements formaté
        $recentPayments = collect([
            [
                'description' => 'Frais de scolarité - Janvier 2024',
                'amount' => 50000,
                'date' => Carbon::now()->subDays(15),
                'status' => 'paid'
            ],
            [
                'description' => 'Frais cantine - T2 2024',
                'amount' => 25000,
                'date' => Carbon::now()->subDays(30),
                'status' => 'paid'
            ],
            [
                'description' => 'Matériel scolaire',
                'amount' => 15000,
                'date' => Carbon::now()->subDays(45),
                'status' => 'paid'
            ]
        ]);

        return view('dashboards.student', compact(
            'student', 'currentClass', 'totalDue', 'totalPaid', 'balance',
            'recentPayments', 'notifications', 'recentGrades', 'generalAverage',
            'averageTrend', 'totalSubjects', 'classRank', 'totalClassStudents',
            'pendingPaymentsAmount', 'subjectAverages'
        ));
    }

    /**
     * Dashboard par défaut
     */
    private function defaultDashboard()
    {
        return view('dashboards.default');
    }
}