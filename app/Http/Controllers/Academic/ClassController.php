<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Domains\Academic\ClassManagementService;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Contrôleur spécialisé pour la gestion des classes préuniversitaires
 * Utilise ClassManagementService du domaine Academic
 */
class ClassController extends Controller
{
    protected ClassManagementService $classService;

    public function __construct(ClassManagementService $classService)
    {
        $this->classService = $classService;
        $this->middleware('auth');
        $this->middleware('can:manage-academic');
    }

    /**
     * Afficher la liste des classes
     */
    public function index(Request $request): View
    {
        $filters = [
            'level_id' => $request->get('level_id'),
            'academic_year_id' => $request->get('academic_year_id'),
            'search' => $request->get('search'),
        ];

        $classesData = $this->classService->getAllClasses($filters);
        $levels = Level::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->limit(3)->get();

        return view('academic.classes.index', [
            'classes' => $classesData['classes'],
            'statistics' => $classesData['statistics'],
            'levels' => $levels,
            'academicYears' => $academicYears,
            'filters' => $filters,
        ]);
    }

    /**
     * Formulaire de création d'une classe
     */
    public function create(): View
    {
        $levels = Level::with('cycle')->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();
        
        return view('academic.classes.create', [
            'levels' => $levels,
            'academicYears' => $academicYears,
            'defaultCapacity' => $this->classService->getDefaultClassCapacity(),
        ]);
    }

    /**
     * Créer une nouvelle classe
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level_id' => 'required|exists:levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'capacity' => 'required|integer|min:10|max:60',
            'classroom' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $result = $this->classService->createClass($validated);

            if ($result['success']) {
                return redirect()
                    ->route('academic.classes.show', $result['class'])
                    ->with('success', 'Classe créée avec succès');
            } else {
                return back()
                    ->withInput()
                    ->withErrors($result['errors']);
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une classe
     */
    public function show(SchoolClass $schoolClass): View
    {
        $classDetails = $this->classService->getClassDetails($schoolClass->id);

        return view('academic.classes.show', [
            'class' => $schoolClass,
            'details' => $classDetails,
            'students' => $classDetails['students'],
            'subjects' => $classDetails['subjects'],
            'statistics' => $classDetails['statistics'],
            'enrollmentStatus' => $classDetails['enrollment_status'],
        ]);
    }

    /**
     * Formulaire d'édition d'une classe
     */
    public function edit(SchoolClass $schoolClass): View
    {
        $levels = Level::with('cycle')->orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('year', 'desc')->get();

        return view('academic.classes.edit', [
            'class' => $schoolClass,
            'levels' => $levels,
            'academicYears' => $academicYears,
        ]);
    }

    /**
     * Mettre à jour une classe
     */
    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level_id' => 'required|exists:levels,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'capacity' => 'required|integer|min:10|max:60',
            'classroom' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $result = $this->classService->updateClass($schoolClass->id, $validated);

            if ($result['success']) {
                return redirect()
                    ->route('academic.classes.show', $schoolClass)
                    ->with('success', 'Classe mise à jour avec succès');
            } else {
                return back()
                    ->withInput()
                    ->withErrors($result['errors']);
            }
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une classe
     */
    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        try {
            $result = $this->classService->deleteClass($schoolClass->id);

            if ($result['success']) {
                return redirect()
                    ->route('academic.classes.index')
                    ->with('success', 'Classe supprimée avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Gérer les inscriptions d'une classe
     */
    public function manageEnrollments(SchoolClass $schoolClass): View
    {
        $enrollmentData = $this->classService->getEnrollmentManagementData($schoolClass->id);

        return view('academic.classes.enrollments', [
            'class' => $schoolClass,
            'currentStudents' => $enrollmentData['current_students'],
            'availableStudents' => $enrollmentData['available_students'],
            'statistics' => $enrollmentData['statistics'],
        ]);
    }

    /**
     * Ajouter un étudiant à une classe
     */
    public function addStudent(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'enrollment_date' => 'nullable|date',
        ]);

        try {
            $result = $this->classService->addStudentToClass($schoolClass->id, $validated);

            if ($result['success']) {
                return back()->with('success', 'Étudiant ajouté à la classe avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'ajout: ' . $e->getMessage());
        }
    }

    /**
     * Retirer un étudiant d'une classe
     */
    public function removeStudent(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'reason' => 'nullable|string',
        ]);

        try {
            $result = $this->classService->removeStudentFromClass($schoolClass->id, $validated);

            if ($result['success']) {
                return back()->with('success', 'Étudiant retiré de la classe avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du retrait: ' . $e->getMessage());
        }
    }

    /**
     * Duplicquer une classe pour une nouvelle année
     */
    public function duplicate(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'copy_students' => 'boolean',
        ]);

        try {
            $result = $this->classService->duplicateClass($schoolClass->id, $validated);

            if ($result['success']) {
                return redirect()
                    ->route('academic.classes.show', $result['new_class'])
                    ->with('success', 'Classe dupliquée avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la duplication: ' . $e->getMessage());
        }
    }

    /**
     * Générer l'emploi du temps d'une classe
     */
    public function schedule(SchoolClass $schoolClass): View
    {
        $scheduleData = $this->classService->generateClassSchedule($schoolClass->id);

        return view('academic.classes.schedule', [
            'class' => $schoolClass,
            'schedule' => $scheduleData['schedule'],
            'subjects' => $scheduleData['subjects'],
            'teachers' => $scheduleData['teachers'],
            'conflicts' => $scheduleData['conflicts'],
        ]);
    }

    /**
     * Exporter les listes d'élèves
     */
    public function exportStudents(SchoolClass $schoolClass, Request $request)
    {
        $format = $request->get('format', 'pdf');

        try {
            $export = $this->classService->exportStudentsList($schoolClass->id, [
                'format' => $format,
                'include_photos' => $request->boolean('include_photos'),
                'include_parents' => $request->boolean('include_parents'),
            ]);

            return response()->download($export['file_path'], $export['file_name'])
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Statistiques de progression de la classe
     */
    public function progression(SchoolClass $schoolClass): View
    {
        $progressionData = $this->classService->analyzeClassProgression($schoolClass->id);

        return view('academic.classes.progression', [
            'class' => $schoolClass,
            'progression' => $progressionData,
            'trends' => $progressionData['trends'],
            'recommendations' => $progressionData['recommendations'],
        ]);
    }

    /**
     * Fermer automatiquement les inscriptions
     */
    public function closeEnrollments(SchoolClass $schoolClass): RedirectResponse
    {
        try {
            $result = $this->classService->closeClassEnrollments($schoolClass->id);

            if ($result['success']) {
                return back()->with('success', 'Inscriptions fermées avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la fermeture: ' . $e->getMessage());
        }
    }

    /**
     * Réouvrir les inscriptions
     */
    public function reopenEnrollments(SchoolClass $schoolClass): RedirectResponse
    {
        try {
            $result = $this->classService->reopenClassEnrollments($schoolClass->id);

            if ($result['success']) {
                return back()->with('success', 'Inscriptions réouvertes avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la réouverture: ' . $e->getMessage());
        }
    }
}