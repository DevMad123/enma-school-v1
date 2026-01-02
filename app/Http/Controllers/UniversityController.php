<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\UFR;
use App\Models\Department;
use App\Models\Program;
use App\Models\Semester;
use App\Models\CourseUnit;
use App\Models\CourseUnitElement;
use App\Models\AcademicYear;
use App\Services\UniversityService;
use App\Services\ProgramService;
use App\Services\SemesterService;
use App\Services\CourseUnitElementService;
use App\Http\Requests\StoreECUERequest;
use App\Http\Requests\UpdateECUERequest;
use App\Traits\HasUniversityContext;
use App\Traits\HasCrudOperations;
use App\Exceptions\BusinessRuleException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Contrôleur pour la gestion universitaire complète
 * 
 * Ce contrôleur gère l'ensemble des entités universitaires :
 * - UFR (Unités de Formation et de Recherche)
 * - Départements  
 * - Programmes d'études
 * - Semestres
 * - Unités d'enseignement (Course Units)
 * 
 * Le contrôleur a été refactorisé pour utiliser :
 * - UniversityService : logique métier centralisée
 * - HasUniversityContext : contexte universitaire standardisé  
 * - HasCrudOperations : opérations CRUD réutilisables
 * 
 * @package App\Http\Controllers
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 * 
 * @uses UniversityService Service de logique métier universitaire
 * @uses HasUniversityContext Trait pour le contexte universitaire
 * @uses HasCrudOperations Trait pour les opérations CRUD
 */
class UniversityController extends Controller
{
    use HasUniversityContext, HasCrudOperations;

    /**
     * Service universitaire pour la logique métier
     * 
     * @var UniversityService
     */
    protected UniversityService $universityService;
    
    /**
     * Service des programmes pour la gestion spécialisée
     * 
     * @var ProgramService
     */
    protected ProgramService $programService;
    
    /**
     * Service des semestres pour la gestion spécialisée
     * 
     * @var SemesterService
     */
    protected SemesterService $semesterService;

    /**
     * Service des éléments constitutifs pour la gestion des ECUE
     * 
     * @var CourseUnitElementService
     */
    protected CourseUnitElementService $courseUnitElementService;

    /**
     * Constructeur du contrôleur
     * 
     * @param UniversityService $universityService Service universitaire injecté
     * @param ProgramService $programService Service des programmes injecté
     * @param SemesterService $semesterService Service des semestres injecté
     * @param CourseUnitElementService $courseUnitElementService Service des ECUE injecté
     */
    public function __construct(
        UniversityService $universityService,
        ProgramService $programService,
        SemesterService $semesterService,
        CourseUnitElementService $courseUnitElementService
    ) {
        $this->universityService = $universityService;
        $this->programService = $programService;
        $this->semesterService = $semesterService;
        $this->courseUnitElementService = $courseUnitElementService;
        
        // Assurer que l'école est en mode universitaire pour toutes les méthodes
        $this->middleware(function ($request, $next) {
            $this->ensureUniversityMode();
            return $next($request);
        });
    }

    /**
     * Tableau de bord universitaire avec statistiques complètes
     * 
     * Affiche une vue d'ensemble de l'université avec :
     * - Statistiques générales (UFR, départements, programmes)
     * - Informations sur l'année académique courante
     * - Données du tableau de bord
     * 
     * @param Request $request Requête HTTP
     * @return View Vue du tableau de bord
     */
    public function dashboard(Request $request): View
    {
        $context = $this->getUniversityContext($request);
        $school = $this->getActiveSchool();
        
        // Utiliser le service pour obtenir les statistiques complètes
        $stats = $this->universityService->getDashboardStats($school);

        return view('university.dashboard', array_merge($context, compact('stats')));
    }

    // ================================
    // GESTION DES UFR
    // ================================

    /**
     * Lister toutes les UFR avec statistiques
     * 
     * @return View Vue de la liste des UFR
     */
    public function ufrs(): View
    {
        $context = $this->getUniversityContext();
        
        $ufrs = UFR::with(['departments', 'school'])
            ->withCount(['departments'])
            ->orderBy('name')
            ->get();

        // Calculer le nombre total de programmes via le service
        $school = $this->getActiveSchool();
        $totalPrograms = Program::whereHas('department.ufr', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->count();

        return view('university.ufrs.index', array_merge(
            compact('ufrs', 'totalPrograms'), 
            $context
        ));
    }

    /**
     * Afficher le formulaire de création d'UFR
     * 
     * @return View Vue de création d'UFR
     */
    public function createUFR(): View
    {
        $context = $this->getUniversityContext();
        return view('university.ufrs.create', $context);
    }

    /**
     * Enregistrer une nouvelle UFR
     * 
     * @param Request $request Requête avec données de l'UFR
     * @return RedirectResponse Redirection avec message de succès/erreur
     */
    public function storeUFR(Request $request): RedirectResponse
    {
        try {
            // Validation des données
            $validated = $this->validateData($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:u_f_r_s,code',
                'short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'dean_name' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string|max:20',
                'building' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            // Données additionnelles
            $school = $this->getActiveSchool();
            $additionalData = [
                'school_id' => $school->id,
                'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
            ];

            // Créer l'UFR via le trait CRUD
            $ufr = $this->createEntity(UFR::class, $validated, 'UFR', $additionalData);

            return $this->createCrudResponse(
                'create', 
                'ufr', 
                true, 
                $ufr->name,
                'university.ufrs.index'
            );
            
        } catch (\Exception $e) {
            return $this->createCrudResponse('create', 'ufr', false)
                ->withInput();
        }
    }

    /**
     * Afficher une UFR avec ses statistiques détaillées
     * 
     * @param UFR $ufr UFR à afficher
     * @return View Vue de détail de l'UFR
     */
    public function showUFR(UFR $ufr): View
    {
        $context = $this->getUniversityContext();
        
        // Utiliser le service pour obtenir les statistiques
        $stats = $this->universityService->getUFRStats($ufr);
        
        return view('university.ufrs.show', array_merge(
            compact('ufr'),
            $stats,
            $context
        ));
    }

    /**
     * Afficher le formulaire d'édition d'UFR
     * 
     * @param UFR $ufr UFR à éditer
     * @return View Vue d'édition d'UFR
     */
    public function editUFR(UFR $ufr): View
    {
        $context = $this->getUniversityContext();
        return view('university.ufrs.edit', array_merge(compact('ufr'), $context));
    }

    /**
     * Mettre à jour une UFR
     * 
     * @param Request $request Requête avec nouvelles données
     * @param UFR $ufr UFR à mettre à jour
     * @return RedirectResponse Redirection avec message de succès/erreur
     */
    public function updateUFR(Request $request, UFR $ufr): RedirectResponse
    {
        try {
            // Validation des données
            $validated = $this->validateData($request->all(), [
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:u_f_r_s,code,' . $ufr->id,
                'short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'dean_name' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string|max:20',
                'building' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : false;

            // Mettre à jour via le trait CRUD
            $this->updateEntity($ufr, $validated, 'UFR');

            return $this->createCrudResponse(
                'update', 
                'ufr', 
                true, 
                $ufr->name,
                'university.ufrs.show',
                [$ufr]
            );
            
        } catch (\Exception $e) {
            return $this->createCrudResponse('update', 'ufr', false)
                ->withInput();
        }
    }

    /**
     * Supprimer une UFR avec vérifications
     * 
     * @param UFR $ufr UFR à supprimer
     * @return RedirectResponse Redirection avec message de succès/erreur
     */
    public function destroyUFR(UFR $ufr): RedirectResponse
    {
        try {
            $name = $ufr->name;
            
            // Supprimer via le service universitaire qui gère les validations
            $this->universityService->deleteUniversityEntity($ufr, 'ufr');

            return $this->createCrudResponse(
                'delete', 
                'ufr', 
                true, 
                $name,
                'university.ufrs.index'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->route('university.ufrs.show', $ufr)
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->createCrudResponse('delete', 'ufr', false);
        }
    }

    // ================================
    // GESTION DES DÉPARTEMENTS  
    // ================================

    /**
     * Lister tous les départements avec leurs UFR
     * 
     * @return View Vue de la liste des départements
     */
    public function departments(): View
    {
        $context = $this->getUniversityContext();
        
        $departments = Department::with(['ufr', 'school'])
            ->withCount(['programs'])
            ->orderBy('name')
            ->get();
            
        $ufrs = UFR::active()->orderBy('name')->get();

        return view('university.departments.index', array_merge(
            compact('departments', 'ufrs'), 
            $context
        ));
    }

    /**
     * Afficher le formulaire de création de département
     * 
     * @return View Vue de création de département
     */
    public function createDepartment(): View
    {
        $context = $this->getUniversityContext();
        $ufrs = UFR::active()->orderBy('name')->get();
        
        return view('university.departments.create', array_merge(
            compact('ufrs'), 
            $context
        ));
    }

    /**
     * Enregistrer un nouveau département
     * 
     * @param Request $request Requête avec données du département
     * @return RedirectResponse Redirection avec message de succès/erreur
     */
    public function storeDepartment(Request $request): RedirectResponse
    {
        try {
            // Validation des données
            $validated = $this->validateData($request->all(), [
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

            // Données additionnelles
            $school = $this->getActiveSchool();
            $additionalData = ['school_id' => $school->id];

            // Créer le département
            $department = $this->createEntity(
                Department::class, 
                $validated, 
                'département', 
                $additionalData
            );

            return $this->createCrudResponse(
                'create', 
                'department', 
                true, 
                $department->name,
                'university.departments.index'
            );
            
        } catch (\Exception $e) {
            return $this->createCrudResponse('create', 'department', false)
                ->withInput();
        }
    }

    /**
     * Afficher un département avec ses statistiques
     * 
     * @param Department $department Département à afficher
     * @return View Vue de détail du département
     */
    public function showDepartment(Department $department): View
    {
        $context = $this->getUniversityContext();
        
        // Utiliser le service pour obtenir les statistiques
        $stats = $this->universityService->getDepartmentStats($department);
        $department->load(['ufr', 'programs.semesters', 'school']);
        
        return view('university.departments.show', array_merge(
            compact('department'),
            $stats,
            $context
        ));
    }

    /**
     * Afficher le formulaire d'édition de département
     * 
     * @param Department $department Département à éditer
     * @return View Vue d'édition de département
     */
    public function editDepartment(Department $department): View
    {
        $context = $this->getUniversityContext();
        $ufrs = UFR::active()->orderBy('name')->get();
        
        return view('university.departments.edit', array_merge(
            compact('department', 'ufrs'), 
            $context
        ));
    }

    /**
     * Mettre à jour un département
     * 
     * @param Request $request Requête avec nouvelles données
     * @param Department $department Département à mettre à jour
     * @return RedirectResponse Redirection avec message de succès/erreur
     */
    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        try {
            // Validation des données
            $validated = $this->validateData($request->all(), [
                'ufr_id' => 'required|exists:u_f_r_s,id',
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:departments,code,' . $department->id,
                'short_name' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'head_of_department' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email',
                'contact_phone' => 'nullable|string|max:20',
                'office_location' => 'nullable|string|max:255',
            ]);

            // Mettre à jour
            $this->updateEntity($department, $validated, 'département');

            return $this->createCrudResponse(
                'update', 
                'department', 
                true, 
                $department->name,
                'university.departments.show',
                [$department]
            );
            
        } catch (\Exception $e) {
            return $this->createCrudResponse('update', 'department', false)
                ->withInput();
        }
    }

    /**
     * Supprimer un département avec vérifications
     * 
     * @param Department $department Département à supprimer
     * @return RedirectResponse Redirection avec message de succès/erreur
     */
    public function destroyDepartment(Department $department): RedirectResponse
    {
        try {
            $name = $department->name;
            
            // Supprimer via le service qui gère les validations
            $this->universityService->deleteUniversityEntity($department, 'department');

            return $this->createCrudResponse(
                'delete', 
                'department', 
                true, 
                $name,
                'university.departments.index'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->route('university.departments.show', $department)
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->createCrudResponse('delete', 'department', false);
        }
    }

    /**
     * ================================
     * GESTION DES PROGRAMMES
     * ================================
     */

    /**
     * Lister tous les programmes
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Vue avec liste des programmes
     */
    public function programs()
    {
        try {
            $context = $this->getUniversityContext();
            $programs = $this->programService->getAllProgramsWithStats();
            $departments = Department::with('ufr')->active()->orderBy('name')->get();

            return view('university.programs.index', array_merge(compact('programs', 'departments'), $context));
            
        } catch (\Exception $e) {
            return redirect()->route('university.dashboard')
                ->with('error', 'Erreur lors du chargement des programmes: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de création de programme
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Vue de création
     */
    public function createProgram()
    {
        try {
            $context = $this->getUniversityContext();
            $departments = Department::with('ufr')->active()->orderBy('name')->get();
            
            return view('university.programs.create', array_merge(compact('departments'), $context));
            
        } catch (\Exception $e) {
            return redirect()->route('university.programs.index')
                ->with('error', 'Erreur lors du chargement du formulaire: ' . $e->getMessage());
        }
    }

    /**
     * Créer un nouveau programme
     * 
     * @param Request $request Requête avec données du programme
     * @return \Illuminate\Http\RedirectResponse Redirection avec message
     */
    public function storeProgram(Request $request)
    {
        try {
            // Validation des données
            $validated = $this->validateData($request->all(), [
                'department_id' => 'required|exists:departments,id',
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:programs,code',
                'short_name' => 'nullable|string|max:20',
                'level' => 'required|in:licence,master,doctorat,dut,bts',
                'duration_semesters' => 'required|integer|min:1|max:10',
                'total_credits' => 'required|integer|min:1|max:500',
                'description' => 'nullable|string',
                'objectives_text' => 'nullable|string',
                'diploma_title' => 'required|string|max:255',
            ]);

            // Créer le programme via le service
            $program = $this->programService->createProgram($validated);

            return $this->createCrudResponse(
                'create',
                'program',
                true,
                $program->name,
                'university.programs.index'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->createCrudResponse('create', 'program', false)
                ->withInput();
        }
    }

    /**
     * Afficher un programme spécifique
     * 
     * @param Program $program Programme à afficher
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Vue du programme
     */
    public function showProgram(Program $program)
    {
        try {
            $context = $this->getUniversityContext();
            $program = $this->programService->enrichProgramWithStats($program);
            $program->load(['department.ufr', 'semesters.courseUnits', 'school']);
            
            // Calculer les statistiques pour la vue
            $totalSemesters = $program->semesters->count();
            $totalCourseUnits = $program->semesters->sum(function ($semester) {
                return $semester->courseUnits->count();
            });
            $totalCredits = $program->semesters->sum(function ($semester) {
                return $semester->courseUnits->sum('credits');
            });
            
            return view('university.programs.show', array_merge(
                compact('program', 'totalSemesters', 'totalCourseUnits', 'totalCredits'), 
                $context
            ));
            
        } catch (\Exception $e) {
            return redirect()->route('university.programs.index')
                ->with('error', 'Erreur lors du chargement du programme: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition d'un programme
     * 
     * @param Program $program Programme à modifier
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse Vue d'édition
     */
    public function editProgram(Program $program)
    {
        try {
            $this->authorizeAction('update', $program);
            
            $context = $this->getUniversityContext();
            $departments = Department::with('ufr')->active()->orderBy('name')->get();
            $objectivesText = $this->programService->formatObjectivesForEdit($program);
            
            return view('university.programs.edit', array_merge(
                compact('program', 'departments', 'objectivesText'), 
                $context
            ));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement du formulaire d\'édition: ' . $e->getMessage(),
                $e,
                'university.programs.show',
                ['program' => $program]
            );
        }
    }

    /**
     * Mettre à jour un programme
     * 
     * @param Request $request Requête avec nouvelles données
     * @param Program $program Programme à mettre à jour
     * @return \Illuminate\Http\RedirectResponse Redirection avec message
     */
    public function updateProgram(Request $request, Program $program)
    {
        try {
            // Validation des données
            $validated = $this->validateData($request->all(), [
                'department_id' => 'required|exists:departments,id',
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:50|unique:programs,code,' . $program->id,
                'short_name' => 'nullable|string|max:20',
                'level' => 'required|in:licence,master,doctorat,dut,bts',
                'duration_semesters' => 'required|integer|min:1|max:10',
                'total_credits' => 'required|integer|min:1|max:500',
                'description' => 'nullable|string',
                'objectives_text' => 'nullable|string',
                'diploma_title' => 'required|string|max:255',
            ]);

            // Mettre à jour via le service
            $updatedProgram = $this->programService->updateProgram($program, $validated);

            return $this->createCrudResponse(
                'update',
                'program', 
                true,
                $updatedProgram->name,
                'university.programs.show',
                ['program' => $updatedProgram]
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->createCrudResponse('update', 'program', false)
                ->withInput();
        }
    }

    /**
     * Supprimer un programme
     * 
     * @param Program $program Programme à supprimer
     * @return \Illuminate\Http\RedirectResponse Redirection avec message
     */
    public function destroyProgram(Program $program)
    {
        try {
            $programName = $program->name;
            
            // Supprimer via le service qui gère les validations
            $this->programService->deleteProgram($program);

            return $this->createCrudResponse(
                'delete',
                'program',
                true,
                $programName,
                'university.programs.index'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->route('university.programs.show', $program)
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return redirect()->route('university.programs.show', $program)
                ->with('error', 'Erreur lors de la suppression du programme: ' . $e->getMessage());
        }
    }

    /**
     * ================================
     * GESTION DES SEMESTRES
     * ================================
     */

    public function semesters(Program $program)
    {
        $context = $this->getUniversityContext();
        $program->load(['department.ufr', 'semesters.courseUnits']);
        
        return view('university.semesters.index', array_merge(compact('program'), $context));
    }

    public function createSemester(Program $program)
    {
        $context = $this->getUniversityContext();
        $program->load(['department.ufr']);
        
        return view('university.semesters.create', array_merge(compact('program'), $context));
    }

    public function storeSemester(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => 'required|integer|min:1|max:10',
            'required_credits' => 'required|integer|min:1|max:60',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
        ]);

        $school = School::getActiveSchool();
        
        // Utiliser l'année académique courante de l'école
        $currentAcademicYear = AcademicYear::currentForSchool($school->id)->first();
        
        // Vérifier qu'il y a une année académique courante
        if (!$currentAcademicYear) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucune année académique courante définie pour cette école. Veuillez d\'abord définir une année académique comme courante.');
        }
        
        // Vérifier l'unicité du numéro de semestre pour ce programme dans cette année académique
        $existingSemester = Semester::where('program_id', $program->id)
            ->where('academic_year_id', $currentAcademicYear->id)
            ->where('semester_number', $validated['semester_number'])
            ->first();
            
        if ($existingSemester) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['semester_number' => 'Un semestre avec ce numéro existe déjà pour ce programme dans l\'année académique courante.']);
        }
        
        $validated['program_id'] = $program->id;
        $validated['school_id'] = $school->id;
        $validated['academic_year_id'] = $currentAcademicYear->id;
        $validated['is_active'] = $validated['status'] === 'active';
        $validated['is_current'] = false; // Par défaut, pas le semestre courant
        
        // Calculer le niveau académique (1-8)
        $validated['academic_level'] = $this->getAcademicLevel($program->level, $validated['semester_number']);
        
        // Calculer les dates de début et fin du semestre
        $dates = $this->getSemesterDates($currentAcademicYear, $validated['semester_number'], $program->duration_semesters);
        $validated['start_date'] = $dates['start_date'];
        $validated['end_date'] = $dates['end_date'];
        
        unset($validated['status']);

        Semester::create($validated);

        return redirect()->route('university.semesters', $program)
            ->with('success', 'Semestre créé avec succès pour l\'année académique : ' . $currentAcademicYear->name);
    }

    public function showSemester(Program $program, Semester $semester)
    {
        $context = $this->getUniversityContext();
        $semester->load(['program.department.ufr', 'academicYear', 'courseUnits', 'school']);
        
        // Calculer les statistiques
        $totalCourseUnits = $semester->courseUnits->count();
        $totalCredits = $semester->courseUnits->sum('credits');
        $totalHours = $semester->courseUnits->sum('hours_total');
        
        return view('university.semesters.show', array_merge(compact('semester', 'totalCourseUnits', 'totalCredits', 'totalHours'), $context));
    }

    public function showSemesterAlt(Semester $semester)
    {
        $program = $semester->program;
        return $this->showSemester($program, $semester);
    }

    public function editSemester(Program $program, Semester $semester)
    {
        $context = $this->getUniversityContext();
        $semester->load(['program.department.ufr', 'academicYear']);
        
        return view('university.semesters.edit', array_merge(compact('semester'), $context));
    }

    public function editSemesterAlt(Semester $semester)
    {
        $program = $semester->program;
        return $this->editSemester($program, $semester);
    }

    public function updateSemester(Request $request, Program $program, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => 'required|integer|min:1|max:10',
            'required_credits' => 'required|integer|min:1|max:60',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
        ]);

        // Vérifier l'unicité du numéro de semestre pour ce programme dans cette année académique (sauf pour le semestre actuel)
        $existingSemester = Semester::where('program_id', $semester->program_id)
            ->where('academic_year_id', $semester->academic_year_id)
            ->where('semester_number', $validated['semester_number'])
            ->where('id', '!=', $semester->id)
            ->first();
            
        if ($existingSemester) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['semester_number' => 'Un semestre avec ce numéro existe déjà pour ce programme dans cette année académique.']);
        }
        
        // Recalculer le niveau académique et les dates si le numéro de semestre a changé
        if ($validated['semester_number'] != $semester->semester_number) {
            $validated['academic_level'] = $this->getAcademicLevel($semester->program->level, $validated['semester_number']);
            $dates = $this->getSemesterDates($semester->academicYear, $validated['semester_number'], $semester->program->duration_semesters);
            $validated['start_date'] = $dates['start_date'];
            $validated['end_date'] = $dates['end_date'];
        }
        
        $validated['is_active'] = $validated['status'] === 'active';
        unset($validated['status']);

        $semester->update($validated);

        return redirect()->route('university.semesters.show', [$program, $semester])
            ->with('success', 'Semestre modifié avec succès.');
    }

    public function updateSemesterAlt(Request $request, Semester $semester)
    {
        $program = $semester->program;
        return $this->updateSemester($request, $program, $semester);
    }

    public function destroySemester(Program $program, Semester $semester)
    {
        // Vérifier que le semestre appartient bien au programme
        if ($semester->program_id !== $program->id) {
            return redirect()->route('university.semesters', $program)
                ->with('error', 'Ce semestre n\'appartient pas à ce programme.');
        }
        
        // Vérifier s'il y a des unités d'enseignement liées
        if ($semester->courseUnits()->count() > 0) {
            return redirect()->route('university.semesters.show', [$program, $semester])
                ->with('error', 'Impossible de supprimer ce semestre car il contient des unités d\'enseignement.');
        }

        $name = $semester->name;
        $semester->delete();

        return redirect()->route('university.semesters', $program)
            ->with('success', "Le semestre '{$name}' a été supprimé avec succès.");
    }

    public function destroySemesterAlt(Semester $semester)
    {
        $program = $semester->program;
        
        // Vérifier s'il y a des unités d'enseignement liées
        if ($semester->courseUnits()->count() > 0) {
            return redirect()->route('university.semesters.show', [$program, $semester])
                ->with('error', 'Impossible de supprimer ce semestre car il contient des unités d\'enseignement.');
        }

        $name = $semester->name;
        $semester->delete();

        return redirect()->route('university.semesters', $program)
            ->with('success', "Le semestre '{$name}' a été supprimé avec succès.");
    }

    /**
     * ================================
     * GESTION DES UE (COURSE UNITS)
     * ================================
     */

    public function courseUnits(Semester $semester)
    {
        $context = $this->getUniversityContext();
        $semester->load(['program.department.ufr', 'courseUnits']);
        
        return view('university.course-units.index', array_merge(compact('semester'), $context));
    }

    public function createCourseUnit(Semester $semester)
    {
        $context = $this->getUniversityContext();
        $semester->load(['program.department.ufr']);
        
        return view('university.course-units.create', array_merge(compact('semester'), $context));
    }

    public function storeCourseUnit(Request $request, Semester $semester)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:course_units,code',
            'credits' => 'required|numeric|min:0.5|max:30',
            'hours_cm' => 'nullable|integer|min:0',
            'hours_td' => 'nullable|integer|min:0',
            'hours_tp' => 'nullable|integer|min:0',
            'hours_total' => 'required|integer|min:0',
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'type' => 'required|in:mandatory,optional,specialization,project,internship',
            'description' => 'nullable|string',
            'prerequisites' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
        ]);

        $school = School::getActiveSchool();
        
        // Utiliser l'année académique du semestre
        $academicYear = $semester->academicYear;
        
        // Vérifier que le semestre a bien une année académique
        if (!$academicYear) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Le semestre n\'est pas associé à une année académique valide.');
        }
        
        // Vérifier que la somme des crédits ne dépasse pas le maximum du semestre
        $totalCreditsUsed = $semester->courseUnits->sum('credits');
        $newTotal = $totalCreditsUsed + $validated['credits'];
        
        if ($newTotal > $semester->required_credits) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['credits' => "La somme des crédits ({$newTotal}) dépasserait le maximum autorisé pour ce semestre ({$semester->required_credits} crédits). Crédits déjà utilisés : {$totalCreditsUsed}."]);
        }
        
        $validated['semester_id'] = $semester->id;
        $validated['school_id'] = $school->id;
        $validated['academic_year_id'] = $academicYear->id;
        $validated['is_active'] = $validated['status'] === 'active';
        
        unset($validated['status']);

        CourseUnit::create($validated);

        return redirect()->route('university.course-units', $semester)
            ->with('success', 'Unité d\'enseignement créée avec succès. Crédits utilisés : ' . $newTotal . '/' . $semester->required_credits);
    }

    public function showCourseUnit(CourseUnit $courseUnit)
    {
        $context = $this->getUniversityContext();
        $courseUnit->load(['semester.program.department.ufr', 'semester.academicYear', 'school']);
        
        return view('university.course-units.show', array_merge(compact('courseUnit'), $context));
    }

    public function editCourseUnit(CourseUnit $courseUnit)
    {
        $context = $this->getUniversityContext();
        $courseUnit->load(['semester.program.department.ufr', 'semester.academicYear']);
        
        return view('university.course-units.edit', array_merge(compact('courseUnit'), $context));
    }

    public function updateCourseUnit(Request $request, CourseUnit $courseUnit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:course_units,code,' . $courseUnit->id,
            'credits' => 'required|numeric|min:0.5|max:30',
            'hours_cm' => 'nullable|integer|min:0',
            'hours_td' => 'nullable|integer|min:0',
            'hours_tp' => 'nullable|integer|min:0',
            'hours_total' => 'required|integer|min:0',
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'type' => 'required|in:mandatory,optional,specialization,project,internship',
            'description' => 'nullable|string',
            'prerequisites' => 'nullable|string',
            'status' => 'required|in:active,inactive,draft',
        ]);

        $semester = $courseUnit->semester;
        
        // Vérifier que la somme des crédits ne dépasse pas le maximum du semestre (excluant les crédits actuels)
        $totalCreditsUsed = $semester->courseUnits()->where('id', '!=', $courseUnit->id)->sum('credits');
        $newTotal = $totalCreditsUsed + $validated['credits'];
        
        if ($newTotal > $semester->required_credits) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['credits' => "La somme des crédits ({$newTotal}) dépasserait le maximum autorisé pour ce semestre ({$semester->required_credits} crédits). Crédits déjà utilisés : {$totalCreditsUsed}."]);
        }
        
        $validated['is_active'] = $validated['status'] === 'active';
        unset($validated['status']);

        $courseUnit->update($validated);

        return redirect()->route('university.course-units.show', $courseUnit)
            ->with('success', 'Unité d\'enseignement modifiée avec succès.');
    }

    public function destroyCourseUnit(CourseUnit $courseUnit)
    {
        $semester = $courseUnit->semester;
        $name = $courseUnit->name;
        
        // Ici on pourrait vérifier s'il y a des évaluations ou notes liées
        // if ($courseUnit->evaluations()->count() > 0) {
        //     return redirect()->route('university.course-units.show', $courseUnit)
        //         ->with('error', 'Impossible de supprimer cette unité d\'enseignement car elle contient des évaluations.');
        // }

        $courseUnit->delete();

        return redirect()->route('university.course-units', $semester)
            ->with('success', "L'unité d'enseignement '{$name}' a été supprimée avec succès.");
    }
    
    /**
     * ================================
     * MÉTHODES HELPER POUR SEMESTRES
     * ================================
     */
    
    /**
     * Obtenir le niveau académique (1-8) selon le niveau et le numéro de semestre
     */
    private function getAcademicLevel(string $level, int $semesterNumber): int
    {
        switch ($level) {
            case 'licence':
                // L1: 1-2, L2: 3-4, L3: 5-6
                return ceil($semesterNumber / 2);
                
            case 'master':
                // M1: 1-2 (niveau 4-5), M2: 3-4 (niveau 5-6)
                return 3 + ceil($semesterNumber / 2);
                
            case 'doctorat':
                // D1: 1-2 (niveau 6-7), D2: 3-4 (niveau 7-8), etc.
                return 5 + ceil($semesterNumber / 2);
                
            case 'dut':
            case 'bts':
                // Niveau post-bac : 1-2
                return ceil($semesterNumber / 2);
                
            default:
                return $semesterNumber;
        }
    }
    
    /**
     * Calculer les dates de début et fin d'un semestre
     */
    private function getSemesterDates($academicYear, int $semesterNumber, int $totalSemesters): array
    {
        $startDate = $academicYear->start_date;
        $endDate = $academicYear->end_date;
        
        // Calculer la durée totale de l'année académique en jours
        $totalDays = $startDate->diffInDays($endDate);
        
        // Pour les programmes multi-années, on divise l'année académique en semestres
        // En supposant 2 semestres par année académique
        $semestersPerYear = min($totalSemesters, 2);
        $semesterDurationDays = intval($totalDays / $semestersPerYear);
        
        // Calculer les dates pour ce semestre
        // Les semestres impairs commencent plus tôt, les pairs plus tard dans l'année
        $semesterIndex = (($semesterNumber - 1) % $semestersPerYear);
        
        $semesterStartDate = $startDate->copy()->addDays($semesterIndex * $semesterDurationDays);
        
        // La date de fin est soit le début du semestre suivant - 1 jour, soit la fin de l'année académique
        if ($semesterIndex < $semestersPerYear - 1) {
            $semesterEndDate = $semesterStartDate->copy()->addDays($semesterDurationDays - 1);
        } else {
            $semesterEndDate = $endDate->copy();
        }
        
        return [
            'start_date' => $semesterStartDate,
            'end_date' => $semesterEndDate,
        ];
    }

    /**
     * ================================
     * GESTION DES ECUE (ÉLÉMENTS CONSTITUTIFS)
     * ================================
     */

    /**
     * Afficher la liste des ECUE d'une UE
     */
    public function showCourseUnitElements(CourseUnit $courseUnit)
    {
        try {
            $context = $this->getUniversityContext();
            
            // Charger les relations nécessaires
            $courseUnit->load([
                'semester.program.department.ufr',
                'elements' => function ($query) {
                    $query->orderBy('code');
                }
            ]);

            // Obtenir les statistiques des ECUE
            $elementsStats = $this->courseUnitElementService->getElementsStats($courseUnit);
            
            return view('university.course-units.elements.index', array_merge(
                compact('courseUnit', 'elementsStats'), 
                $context
            ));
            
        } catch (\Exception $e) {
            return redirect()->route('university.semesters.course-units.index', $courseUnit->semester)
                ->with('error', 'Erreur lors du chargement des ECUE: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un ECUE
     */
    public function showCourseUnitElement(CourseUnit $courseUnit, CourseUnitElement $element)
    {
        try {
            // Vérifier que l'ECUE appartient bien à l'UE
            if ($element->course_unit_id !== $courseUnit->id) {
                throw new BusinessRuleException('Cet ECUE n\'appartient pas à cette UE.');
            }

            $context = $this->getUniversityContext();
            
            // Charger les relations nécessaires
            $courseUnit->load('semester.program.department.ufr');
            $element->load('courseUnit');
            
            return view('university.course-units.elements.show', array_merge(
                compact('courseUnit', 'element'), 
                $context
            ));
            
        } catch (BusinessRuleException $e) {
            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('error', 'Erreur lors du chargement de l\'ECUE: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de création d'ECUE
     */
    public function createCourseUnitElement(CourseUnit $courseUnit)
    {
        try {
            $context = $this->getUniversityContext();
            $courseUnit->load('semester.program.department.ufr');
            
            return view('university.course-units.elements.create', array_merge(
                compact('courseUnit'), 
                $context
            ));
            
        } catch (\Exception $e) {
            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('error', 'Erreur lors du chargement du formulaire: ' . $e->getMessage());
        }
    }

    /**
     * Sauvegarder un nouvel ECUE
     */
    public function storeCourseUnitElement(StoreECUERequest $request, CourseUnit $courseUnit)
    {
        try {
            $element = $this->courseUnitElementService->createElement($courseUnit, $request->validated());

            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('success', "L'ECUE '{$element->name}' a été créé avec succès.");
                
        } catch (BusinessRuleException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de l\'ECUE: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition d'ECUE
     */
    public function editCourseUnitElement(CourseUnit $courseUnit, CourseUnitElement $element)
    {
        try {
            // Vérifier que l'ECUE appartient bien à l'UE
            if ($element->course_unit_id !== $courseUnit->id) {
                throw new BusinessRuleException('Cet ECUE n\'appartient pas à cette UE.');
            }
            
            $context = $this->getUniversityContext();
            $courseUnit->load('semester.program.department.ufr');
            
            return view('university.course-units.elements.edit', array_merge(
                compact('courseUnit', 'element'), 
                $context
            ));
            
        } catch (\Exception $e) {
            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('error', 'Erreur lors du chargement du formulaire: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour un ECUE
     */
    public function updateCourseUnitElement(UpdateECUERequest $request, CourseUnit $courseUnit, CourseUnitElement $element)
    {
        try {
            // Vérifier que l'ECUE appartient bien à l'UE
            if ($element->course_unit_id !== $courseUnit->id) {
                throw new BusinessRuleException('Cet ECUE n\'appartient pas à cette UE.');
            }
            
            $this->courseUnitElementService->updateElement($element, $request->validated());

            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('success', "L'ECUE '{$element->name}' a été mis à jour avec succès.");
                
        } catch (BusinessRuleException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour de l\'ECUE: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un ECUE
     */
    public function destroyCourseUnitElement(CourseUnit $courseUnit, CourseUnitElement $element)
    {
        try {
            // Vérifier que l'ECUE appartient bien à l'UE
            if ($element->course_unit_id !== $courseUnit->id) {
                throw new BusinessRuleException('Cet ECUE n\'appartient pas à cette UE.');
            }
            
            $elementName = $element->name;
            $this->courseUnitElementService->deleteElement($element);

            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('success', "L'ECUE '{$elementName}' a été supprimé avec succès.");
                
        } catch (BusinessRuleException $e) {
            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return redirect()->route('university.course-units.elements.index', $courseUnit)
                ->with('error', 'Erreur lors de la suppression de l\'ECUE: ' . $e->getMessage());
        }
    }
}
