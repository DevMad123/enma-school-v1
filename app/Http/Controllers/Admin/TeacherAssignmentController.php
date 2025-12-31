<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * MODULE A4 - Contrôleur pour la gestion des affectations pédagogiques
 */
class TeacherAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin|directeur|responsable_pedagogique']);
    }

    /**
     * Afficher la liste des affectations
     */
    public function index(Request $request)
    {
        $query = TeacherAssignment::with(['teacher.user', 'subject', 'schoolClass.level', 'academicYear'])
            ->when($request->academic_year_id, function ($q, $yearId) {
                $q->where('academic_year_id', $yearId);
            })
            ->when($request->teacher_id, function ($q, $teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->when($request->class_id, function ($q, $classId) {
                $q->where('class_id', $classId);
            })
            ->when($request->subject_id, function ($q, $subjectId) {
                $q->where('subject_id', $subjectId);
            })
            ->when($request->assignment_type, function ($q, $type) {
                $q->where('assignment_type', $type);
            })
            ->when($request->status, function ($q, $status) {
                if ($status === 'active') {
                    $q->active();
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            });

        $assignments = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Données pour les filtres
        $academicYears = AcademicYear::active()->get();
        $teachers = Teacher::active()->with('user')->get();
        $classes = SchoolClass::with('level')->active()->get();
        $subjects = Subject::active()->get();
        
        return view('admin.assignments.index', compact(
            'assignments', 'academicYears', 'teachers', 'classes', 'subjects'
        ));
    }

    /**
     * Afficher le formulaire de création d'affectation
     */
    public function create()
    {
        $teachers = Teacher::where('status', 'active')->with('user')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $currentAcademicYear = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
        $classes = SchoolClass::with('level')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.assignments.create', compact(
            'teachers', 'academicYears', 'currentAcademicYear', 'classes', 'subjects'
        ));
    }

    /**
     * Enregistrer une nouvelle affectation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'assignment_type' => ['required', 'in:primary,substitute,assistant'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'weekly_hours' => ['required', 'integer', 'min:1', 'max:40'],
            'notes' => ['nullable', 'string'],
        ]);

        // Définir is_active par défaut à true
        $validated['is_active'] = true;

        // Validation métier : éviter les doublons
        $existingAssignment = TeacherAssignment::where([
            ['teacher_id', $validated['teacher_id']],
            ['academic_year_id', $validated['academic_year_id']],
            ['class_id', $validated['class_id']],
            ['subject_id', $validated['subject_id'] ?? null],
            ['is_active', true],
        ])->first();

        if ($existingAssignment) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cette affectation existe déjà pour cet enseignant.');
        }

        DB::transaction(function () use ($validated) {
            $assignment = TeacherAssignment::create($validated);
            
            // Log de l'action
            Log::info('Nouvelle affectation créée', [
                'assignment_id' => $assignment->id,
                'user_id' => auth()->id(),
                'teacher_id' => $assignment->teacher_id,
                'class_id' => $assignment->school_class_id
            ]);
        });

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Affectation créée avec succès.');
    }

    /**
     * Afficher les détails d'une affectation
     */
    public function show(TeacherAssignment $assignment)
    {
        $assignment->load([
            'teacher.user', 
            'subject', 
            'schoolClass.level.cycle', 
            'academicYear'
        ]);

        // Autres affectations du même enseignant pour la même année
        $relatedAssignments = TeacherAssignment::with(['subject', 'schoolClass'])
            ->where('teacher_id', $assignment->teacher_id)
            ->where('academic_year_id', $assignment->academic_year_id)
            ->get();

        return view('admin.assignments.show', compact('assignment', 'relatedAssignments'));
    }

    /**
     * Afficher le formulaire de modification d'affectation
     */
    public function edit(TeacherAssignment $assignment)
    {
        $teachers = Teacher::where('status', 'active')->with('user')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $classes = SchoolClass::with('level')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.assignments.edit', compact(
            'assignment', 'teachers', 'academicYears', 'classes', 'subjects'
        ));
    }

    /**
     * Mettre à jour une affectation
     */
    public function update(Request $request, TeacherAssignment $assignment)
    {
        $validated = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'assignment_type' => ['required', 'in:primary,substitute,assistant'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'weekly_hours' => ['required', 'integer', 'min:1', 'max:40'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Validation métier : éviter les doublons (sauf pour l'affectation courante)
        $existingAssignment = TeacherAssignment::where([
            ['teacher_id', $validated['teacher_id']],
            ['academic_year_id', $assignment->academic_year_id], // Garder la même année académique
            ['class_id', $validated['class_id']],
            ['subject_id', $validated['subject_id']],
            ['is_active', true],
        ])->where('id', '!=', $assignment->id)->first();

        if ($existingAssignment) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cette affectation existe déjà pour cet enseignant.');
        }

        $assignment->update($validated);

        // Log de l'action
        Log::info('Affectation mise à jour', [
            'assignment_id' => $assignment->id,
            'user_id' => auth()->id(),
            'teacher_id' => $assignment->teacher_id,
            'class_id' => $assignment->school_class_id
        ]);

        return redirect()->route('admin.assignments.show', $assignment)
            ->with('success', 'Affectation mise à jour avec succès.');
    }

    /**
     * Supprimer une affectation
     */
    public function destroy(TeacherAssignment $assignment)
    {
        if ($assignment->evaluations()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer une affectation qui a des évaluations.');
        }

        $assignment->delete();

        // Log de l'action
        Log::info('Affectation supprimée', [
            'assignment_id' => $assignment->id,
            'user_id' => auth()->id(),
            'teacher_id' => $assignment->teacher_id,
            'class_id' => $assignment->school_class_id
        ]);

        return redirect()->route('admin.assignments.index')
            ->with('success', 'Affectation supprimée avec succès.');
    }

    /**
     * Activer/désactiver une affectation
     */
    public function toggleStatus(TeacherAssignment $assignment)
    {
        $assignment->update(['is_active' => !$assignment->is_active]);

        $message = $assignment->is_active ? 'Affectation activée' : 'Affectation désactivée';

        return redirect()->back()
            ->with('success', $message . ' avec succès.');
    }

    /**
     * Afficher le planning des affectations
     */
    // public function schedule(Request $request)
    // {
    //     $academicYear = AcademicYear::findOrFail($request->academic_year_id ?? AcademicYear::active()->first()->id);
        
    //     $assignments = TeacherAssignment::with(['teacher.user', 'subject', 'schoolClass.level'])
    //         ->where('academic_year_id', $academicYear->id)
    //         ->active()
    //         ->get()
    //         ->groupBy(['teacher_id']);

    //     $teachers = Teacher::active()->with('user')->get();
    //     $academicYears = AcademicYear::active()->get();

    //     return view('admin.assignments.schedule', compact(
    //         'assignments', 'teachers', 'academicYear', 'academicYears'
    //     ));
    // }

    /**
     * Dupliquer les affectations d'une année à une autre
     */
    public function duplicate(Request $request)
    {
        $validated = $request->validate([
            'from_year_id' => ['required', 'exists:academic_years,id'],
            'to_year_id' => ['required', 'exists:academic_years,id', 'different:from_year_id'],
        ]);

        $fromAssignments = TeacherAssignment::where('academic_year_id', $validated['from_year_id'])
            ->active()
            ->get();

        $duplicated = 0;
        
        DB::transaction(function () use ($fromAssignments, $validated, &$duplicated) {
            foreach ($fromAssignments as $assignment) {
                // Vérifier si l'affectation n'existe pas déjà
                $exists = TeacherAssignment::where([
                    'teacher_id' => $assignment->teacher_id,
                    'academic_year_id' => $validated['to_year_id'],
                    'class_id' => $assignment->class_id,
                    'subject_id' => $assignment->subject_id,
                ])->exists();

                if (!$exists) {
                    TeacherAssignment::create([
                        'teacher_id' => $assignment->teacher_id,
                        'academic_year_id' => $validated['to_year_id'],
                        'class_id' => $assignment->class_id,
                        'subject_id' => $assignment->subject_id,
                        'assignment_type' => $assignment->assignment_type,
                        'weekly_hours' => $assignment->weekly_hours,
                        'is_active' => true,
                    ]);
                    
                    $duplicated++;
                }
            }
        });

        return redirect()->back()
            ->with('success', "{$duplicated} affectations dupliquées avec succès.");
    }

    /**
     * Afficher le planning des affectations
     */
    public function schedule(Request $request)
    {
        $currentSchool = auth()->user()->current_school_id ?? School::first()->id;
        
        // Récupération des filtres
        $yearId = $request->get('year_id', AcademicYear::current()->id ?? AcademicYear::first()->id);
        $levelId = $request->get('level_id');
        $cycleId = $request->get('cycle_id');
        $subjectId = $request->get('subject_id');

        // Requête de base pour les affectations
        $assignmentsQuery = TeacherAssignment::with(['teacher.user', 'subject', 'schoolClass.level.cycle'])
            ->whereHas('teacher', function ($q) use ($currentSchool) {
                $q->where('school_id', $currentSchool);
            })
            ->where('academic_year_id', $yearId)
            ->where('is_active', true);

        // Application des filtres
        if ($levelId) {
            $assignmentsQuery->whereHas('schoolClass.level', function ($q) use ($levelId) {
                $q->where('id', $levelId);
            });
        }

        if ($cycleId) {
            $assignmentsQuery->whereHas('schoolClass.level.cycle', function ($q) use ($cycleId) {
                $q->where('id', $cycleId);
            });
        }

        if ($subjectId) {
            $assignmentsQuery->where('subject_id', $subjectId);
        }

        // Récupération des assignments groupés par enseignant
        $assignments = $assignmentsQuery->orderBy('teacher_id')
            ->orderBy('subject_id')
            ->orderBy('class_id')
            ->get();

        // Groupement par enseignant
        $teacherAssignments = $assignments->groupBy(function ($assignment) {
            return $assignment->teacher->user->name;
        });

        // Toutes les affectations pour la vue tableau
        $allAssignments = $assignments;

        // Calcul des statistiques
        $stats = [
            'total_teachers' => $assignments->groupBy('teacher_id')->count(),
            'active_assignments' => $assignments->count(),
            'total_classes' => $assignments->groupBy('class_id')->count(),
            'total_subjects' => $assignments->groupBy('subject_id')->count(),
        ];

        // Données pour les filtres
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $levels = \App\Models\Level::orderBy('order')->get();
        $cycles = \App\Models\Cycle::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        
        return view('admin.assignments.schedule', compact(
            'teacherAssignments',
            'allAssignments',
            'stats',
            'academicYears',
            'levels',
            'cycles',
            'subjects'
        ));
    }
}