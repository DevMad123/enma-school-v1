<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Traits\HasSchoolContext;
use App\Services\Dashboard\PreUniversityStudentDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Contrôleur du dashboard élève préuniversitaire
 * 
 * Destiné aux rôles : student (en contexte préuniversitaire)
 * Contexte : Préuniversitaire uniquement
 * 
 * @package App\Http\Controllers\Student
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class PreUniversityStudentDashboardController extends Controller
{
    use HasSchoolContext;

    /**
     * Service du dashboard élève préuniversitaire
     * 
     * @var PreUniversityStudentDashboardService
     */
    protected PreUniversityStudentDashboardService $dashboardService;

    /**
     * Constructeur
     * 
     * @param PreUniversityStudentDashboardService $dashboardService
     */
    public function __construct(PreUniversityStudentDashboardService $dashboardService)
    {
        $this->middleware(['auth', 'school.context']);
        $this->middleware('can:access_preuniversity_student_dashboard');
        $this->middleware('pre_university'); // Middleware pour forcer le contexte préuniversitaire
        
        $this->dashboardService = $dashboardService;
    }

    /**
     * Afficher le dashboard élève préuniversitaire
     * 
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        // Vérifier le contexte école et le mode préuniversitaire
        $redirectResponse = $this->ensureSchoolExists();
        if ($redirectResponse) {
            return $redirectResponse;
        }

        $this->ensurePreUniversityMode();

        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->route('dashboard')->with('error', 'Profil élève non trouvé.');
        }

        $school = $this->getCurrentSchoolRequired();

        // Récupérer les données du dashboard préuniversitaire
        $dashboardData = $this->dashboardService->getPreUniversityDashboardData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.preuniversity.index', array_merge($dashboardData, $contextData));
    }

    /**
     * Bulletin de notes
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function bulletin(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $trimester = $request->input('trimester', 'current');
        $year = $request->input('year', 'current');

        $bulletinData = $this->dashboardService->getBulletinData($student, $school, $trimester, $year);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.preuniversity.bulletin', array_merge($bulletinData, $contextData));
    }

    /**
     * Suivi des matières
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function subjects(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $subjectsData = $this->dashboardService->getSubjectsData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.preuniversity.subjects', array_merge($subjectsData, $contextData));
    }

    /**
     * Vie scolaire
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function schoolLife(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $schoolLifeData = $this->dashboardService->getSchoolLifeData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.preuniversity.school-life', array_merge($schoolLifeData, $contextData));
    }

    /**
     * Communication avec les parents
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function parentCommunication(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $communicationData = $this->dashboardService->getParentCommunicationData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.preuniversity.parent-communication', array_merge($communicationData, $contextData));
    }

    /**
     * Devoirs et travaux
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function homework(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $homeworkData = $this->dashboardService->getHomeworkData($student, $school);
        $contextData = $this->getSchoolContextData();

        return view('dashboards.student.preuniversity.homework', array_merge($homeworkData, $contextData));
    }

    /**
     * Statistiques de progression scolaire
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function progressStats(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $stats = $this->dashboardService->getSchoolProgressStats($student, $school);

        return response()->json($stats);
    }

    /**
     * Télécharger le bulletin de notes
     * 
     * @param Request $request
     * @param string $trimester
     * @return \Illuminate\Http\Response
     */
    public function downloadBulletin(Request $request, string $trimester)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        $allowedTrimesters = ['t1', 't2', 't3'];
        
        if (!in_array($trimester, $allowedTrimesters)) {
            abort(404);
        }

        return $this->dashboardService->generateBulletin($student, $school, $trimester);
    }

    /**
     * Télécharger un certificat de scolarité
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadCertificate(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;
        $school = $this->getCurrentSchoolRequired();

        return $this->dashboardService->generateScholarityCertificate($student, $school);
    }
}