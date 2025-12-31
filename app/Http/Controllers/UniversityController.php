<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\UFR;
use App\Models\Department;
use App\Models\Program;
use App\Models\Semester;
use App\Models\CourseUnit;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    public function __construct()
    {
        // S'assurer que l'école est de type universitaire
        $this->middleware(function ($request, $next) {
            $school = School::getActiveSchool();
            
            if (!$school || !$school->isUniversity()) {
                return redirect()->route('academic.levels')
                    ->with('error', 'Cette section est réservée aux établissements universitaires.');
            }
            
            return $next($request);
        });
    }

    /**
     * Obtenir le contexte universitaire
     */
    protected function getUniversityContext()
    {
        $school = School::getActiveSchool();
        
        return [
            'school' => $school,
            'isUniversity' => true,
            'totalUFRs' => $school->ufrs()->count(),
            'totalDepartments' => $school->departments()->count(),
            'totalPrograms' => $school->programs()->count(),
            'activePrograms' => $school->programs()->active()->count(),
        ];
    }

    /**
     * Tableau de bord universitaire
     */
    public function dashboard()
    {
        $context = $this->getUniversityContext();
        
        $stats = [
            'ufrs' => UFR::active()->count(),
            'departments' => Department::active()->count(),
            'programs' => Program::active()->count(),
            'current_semester' => Semester::current()->count(),
        ];

        return view('university.dashboard', array_merge($context, compact('stats')));
    }

    /**
     * ================================
     * GESTION DES UFR
     * ================================
     */

    public function ufrs()
    {
        $context = $this->getUniversityContext();
        $ufrs = UFR::with(['departments', 'school'])
            ->withCount(['departments'])
            ->orderBy('name')
            ->get();

        // Calculer le nombre total de programmes
        $totalPrograms = Program::whereHas('department.ufr', function ($query) {
            $query->where('school_id', $this->school->id);
        })->count();

        return view('university.ufrs.index', array_merge(compact('ufrs', 'totalPrograms'), $context));
    }

    public function createUFR()
    {
        $context = $this->getUniversityContext();
        return view('university.ufrs.create', $context);
    }

    public function storeUFR(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:u_f_r_s,code',
            'short_name' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'dean_name' => 'nullable|string|max:255',
            'dean_email' => 'nullable|email',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'building' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $school = School::getActiveSchool();
        $validated['school_id'] = $school->id;
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;

        UFR::create($validated);

        return redirect()->route('university.ufrs.index')
            ->with('success', 'UFR créée avec succès.');
    }

    public function showUFR(UFR $ufr)
    {
        $context = $this->getUniversityContext();
        $ufr->load(['departments.programs.semesters.courseUnits', 'school']);
        
        // Calculer les statistiques
        $totalPrograms = $ufr->departments->sum(function ($department) {
            return $department->programs->count();
        });
        
        $totalSemesters = $ufr->departments->flatMap(function ($department) {
            return $department->programs->flatMap->semesters;
        })->count();
        
        $totalCourseUnits = $ufr->departments->flatMap(function ($department) {
            return $department->programs->flatMap(function ($program) {
                return $program->semesters->flatMap->courseUnits;
            });
        })->count();
        
        return view('university.ufrs.show', array_merge(compact('ufr', 'totalPrograms', 'totalSemesters', 'totalCourseUnits'), $context));
    }

    /**
     * ================================
     * GESTION DES DÉPARTEMENTS
     * ================================
     */

    public function departments()
    {
        $context = $this->getUniversityContext();
        $departments = Department::with(['ufr', 'school'])
            ->withCount(['programs'])
            ->orderBy('name')
            ->get();
            
        $ufrs = UFR::active()->orderBy('name')->get();

        return view('university.departments.index', array_merge(compact('departments', 'ufrs'), $context));
    }

    public function createDepartment()
    {
        $context = $this->getUniversityContext();
        $ufrs = UFR::active()->orderBy('name')->get();
        
        return view('university.departments.create', array_merge(compact('ufrs'), $context));
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'ufr_id' => 'required|exists:u_f_r_s,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments,code',
            'short_name' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'head_of_department' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'office_location' => 'nullable|string|max:255',
        ]);

        $school = School::getActiveSchool();
        $validated['school_id'] = $school->id;

        Department::create($validated);

        return redirect()->route('university.departments')
            ->with('success', 'Département créé avec succès.');
    }

    /**
     * ================================
     * GESTION DES PROGRAMMES
     * ================================
     */

    public function programs()
    {
        $context = $this->getUniversityContext();
        $programs = Program::with(['department.ufr'])
            ->withCount(['semesters'])
            ->orderBy('level')
            ->orderBy('name')
            ->get();
            
        $departments = Department::with('ufr')->active()->orderBy('name')->get();

        return view('university.programs.index', array_merge(compact('programs', 'departments'), $context));
    }

    public function createProgram()
    {
        $context = $this->getUniversityContext();
        $departments = Department::with('ufr')->active()->orderBy('name')->get();
        
        return view('university.programs.create', array_merge(compact('departments'), $context));
    }

    public function storeProgram(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:programs,code',
            'short_name' => 'nullable|string|max:20',
            'level' => 'required|in:licence,master,doctorat,dut,bts',
            'duration_semesters' => 'required|integer|min:1|max:10',
            'total_credits' => 'required|integer|min:1|max:500',
            'description' => 'nullable|string',
            'objectives' => 'nullable|array',
            'diploma_title' => 'required|string|max:255',
        ]);

        $school = School::getActiveSchool();
        $validated['school_id'] = $school->id;

        Program::create($validated);

        return redirect()->route('university.programs')
            ->with('success', 'Programme créé avec succès.');
    }
}
