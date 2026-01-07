<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Traits\HasSchoolContext;
use App\Services\Dashboard\UniversityStudentDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du dashboard étudiant universitaire
 * 
 * Destiné aux rôles : student (en contexte universitaire)
 * Contexte : Universitaire uniquement
 * 
 * @package App\Http\Controllers\Student
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class UniversityStudentDashboardController extends Controller
{
    use HasSchoolContext;

    /**
     * Service du dashboard étudiant universitaire
     * 
     * @var UniversityStudentDashboardService
     */
    protected UniversityStudentDashboardService $dashboardService;

    /**
     * Constructeur
     * 
     * @param UniversityStudentDashboardService $dashboardService
     */
    public function __construct(UniversityStudentDashboardService $dashboardService)
    {
        $this->middleware(['auth', 'school.context']);
        $this->middleware('can:access_university_student_dashboard');
        $this->middleware('university'); // Middleware pour forcer le contexte universitaire
        
        $this->dashboardService = $dashboardService;
    }

    /**
     * Afficher le dashboard étudiant universitaire
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Vérifier le contexte école et le mode universitaire
        $redirectResponse = $this->ensureSchoolExists();
        if ($redirectResponse) {
            return $redirectResponse;
        }

        $this->ensureUniversityMode();

        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Profil étudiant non trouvé.');
        }

        $school = $this->getCurrentSchoolRequired();

        // Récupérer les données du dashboard universitaire
        $dashboardData = $this->dashboardService->getUniversityDashboardData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.university.index', array_merge($dashboardData, $contextData));
    }

    /**
     * Parcours académique (UE, semestres)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function academicPath(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $academicData = $this->dashboardService->getAcademicPathData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.university.academic-path', array_merge($academicData, $contextData));
    }

    /**
     * Notes par UE (Unités d'enseignement)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function grades(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $semester = $request->input('semester', 'current');
        $year = $request->input('year', 'current');

        $gradesData = $this->dashboardService->getUniversityGrades($student, $school, $semester, $year);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.university.grades', array_merge($gradesData, $contextData));
    }

    /**
     * Inscription et réinscription
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function enrollment(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $enrollmentData = $this->dashboardService->getEnrollmentData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.university.enrollment', array_merge($enrollmentData, $contextData));
    }

    /**
     * Documents académiques (attestations, relevés)
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function documents(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $documentsData = $this->dashboardService->getAcademicDocuments($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.university.documents', array_merge($documentsData, $contextData));
    }

    /**
     * Suivi financier et paiements
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function finances(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $financialData = $this->dashboardService->getFinancialData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.university.finances', array_merge($financialData, $contextData));
    }

    /**
     * Statistiques de progression académique
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function progressStats(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $stats = $this->dashboardService->getProgressStatistics($student, $school);

        return response()->json($stats);
    }

    /**
     * Télécharger un document académique
     * 
     * @param Request $request
     * @param string $documentType
     * @return \Illuminate\Http\Response
     */
    public function downloadDocument(Request $request, string $documentType)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $allowedDocuments = ['attestation', 'releve_notes', 'certificat_scolarite'];
        
        if (!in_array($documentType, $allowedDocuments)) {
            abort(404);
        }

        return $this->dashboardService->generateDocument($student, $school, $documentType);
    }
}