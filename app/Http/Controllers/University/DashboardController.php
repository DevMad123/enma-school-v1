<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\UFR;
use App\Models\Department;
use App\Models\Program;
use App\Models\Semester;
use App\Models\CourseUnit;
use App\Models\AcademicYear;
use App\Traits\HasUniversityContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur pour le dashboard universitaire
 * Migré depuis UniversityController pour une meilleure organisation
 */
class DashboardController extends Controller
{
    use HasUniversityContext;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:view-university-dashboard');
    }

    /**
     * Afficher le tableau de bord universitaire
     */
    public function index(Request $request): View
    {
        $context = $this->getUniversityContext();
        $school = School::find($context['school_id']);

        // Statistiques générales
        $statistics = $this->getStatistics($school);
        
        // Données pour les graphiques
        $chartsData = $this->getChartsData($school);
        
        // Activités récentes
        $recentActivities = $this->getRecentActivities($school);

        return view('university.dashboard', [
            'school' => $school,
            'statistics' => $statistics,
            'chartsData' => $chartsData,
            'recentActivities' => $recentActivities,
            'academicYear' => $context['academic_year']
        ]);
    }

    /**
     * Récupère les statistiques générales
     */
    private function getStatistics(School $school): array
    {
        return [
            'ufrs_count' => UFR::where('school_id', $school->id)->count(),
            'departments_count' => Department::whereHas('ufr', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->count(),
            'programs_count' => Program::whereHas('department.ufr', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->count(),
            'semesters_count' => Semester::whereHas('program.department.ufr', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->count(),
            'course_units_count' => CourseUnit::where('school_id', $school->id)->count(),
            'active_programs' => Program::whereHas('department.ufr', function($query) use ($school) {
                $query->where('school_id', $school->id);
            })->where('is_active', true)->count(),
        ];
    }

    /**
     * Récupère les données pour les graphiques
     */
    private function getChartsData(School $school): array
    {
        // Distribution des programmes par UFR
        $programsByUfr = UFR::where('school_id', $school->id)
            ->withCount(['programs' => function($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->map(function($ufr) {
                return [
                    'name' => $ufr->name,
                    'count' => $ufr->programs_count
                ];
            });

        // Distribution des UE par type
        $courseUnitsByType = CourseUnit::where('school_id', $school->id)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function($item) {
                return [$item->type => $item->count];
            });

        return [
            'programsByUfr' => $programsByUfr,
            'courseUnitsByType' => $courseUnitsByType,
        ];
    }

    /**
     * Récupère les activités récentes (mock data pour l'instant)
     */
    private function getRecentActivities(School $school): array
    {
        // TODO: Implémenter avec un vrai système d'audit
        return [
            [
                'action' => 'Création de programme',
                'description' => 'Nouveau programme Master en Informatique créé',
                'user' => 'Admin Université',
                'date' => now()->subHours(2),
            ],
            [
                'action' => 'Modification UE',
                'description' => 'UE Algorithmique mise à jour',
                'user' => 'Dr. Martin Kouadio',
                'date' => now()->subHours(5),
            ],
            [
                'action' => 'Création semestre',
                'description' => 'Semestre 3 ajouté au programme Licence Info',
                'user' => 'Admin Université',
                'date' => now()->subDay(),
            ],
        ];
    }
}