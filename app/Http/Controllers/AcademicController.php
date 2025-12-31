<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicController extends Controller
{
    /**
     * Obtenir l'école active et ses informations contextuelles
     */
    protected function getSchoolContext()
    {
        $school = School::getActiveSchool();
        
        if (!$school) {
            abort(404, 'Aucun établissement configuré.');
        }
        
        return [
            'school' => $school,
            'isPreUniversity' => $school->isPreUniversity(),
            'isUniversity' => $school->isUniversity(),
            'availableCycles' => $school->getAvailableCycles(),
            'educationalLevels' => $school->getEducationalLevelsWithLabels(),
            'recommendedStructure' => $school->getRecommendedStructure()
        ];
    }
    // ================================
    // GESTION DES NIVEAUX
    // ================================

    /**
     * Afficher la liste des niveaux
     */
    public function levels()
    {
        $context = $this->getSchoolContext();
        
        $levels = Level::with(['cycle', 'classes', 'subjects'])
            ->orderBy('cycle_id')
            ->orderBy('name')
            ->get();

        $cycles = Cycle::orderBy('name')->get();

        return view('academic.levels.index', array_merge(compact('levels', 'cycles'), $context));
    }

    /**
     * Formulaire de création d'un niveau
     */
    public function createLevel()
    {
        $context = $this->getSchoolContext();
        $cycles = Cycle::orderBy('name')->get();
        
        return view('academic.levels.create', array_merge(compact('cycles'), $context));
    }

    /**
     * Afficher les détails d'un niveau
     */
    public function showLevel(Level $level)
    {
        $context = $this->getSchoolContext();
        $level->load(['cycle', 'classes.academicYear', 'classes.students', 'subjects']);
        
        return view('academic.levels.show', array_merge(compact('level'), $context));
    }

    /**
     * Formulaire d'édition d'un niveau
     */
    public function editLevel(Level $level)
    {
        $cycles = Cycle::orderBy('name')->get();
        $level->load(['cycle', 'classes', 'subjects']);
        return view('academic.levels.edit', compact('level', 'cycles'));
    }

    /**
     * Créer un nouveau niveau
     */
    public function storeLevel(Request $request)
    {
        $request->validate([
            'cycle_id' => 'required|exists:cycles,id',
            'name' => 'required|string|max:255',
        ]);

        // Vérifier l'unicité du nom dans le cycle
        $exists = Level::where('cycle_id', $request->cycle_id)
                      ->where('name', $request->name)
                      ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ce niveau existe déjà dans ce cycle.']);
        }

        Level::create([
            'cycle_id' => $request->cycle_id,
            'name' => $request->name,
        ]);

        return redirect()->route('academic.levels')
            ->with('success', 'Niveau créé avec succès.');
    }

    /**
     * Mettre à jour un niveau
     */
    public function updateLevel(Request $request, Level $level)
    {
        $request->validate([
            'cycle_id' => 'required|exists:cycles,id',
            'name' => 'required|string|max:255',
        ]);

        // Vérifier l'unicité du nom dans le cycle (sauf pour le niveau actuel)
        $exists = Level::where('cycle_id', $request->cycle_id)
                      ->where('name', $request->name)
                      ->where('id', '!=', $level->id)
                      ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Ce niveau existe déjà dans ce cycle.']);
        }

        $level->update([
            'cycle_id' => $request->cycle_id,
            'name' => $request->name,
        ]);

        return redirect()->route('academic.levels')
            ->with('success', 'Niveau mis à jour avec succès.');
    }

    /**
     * Supprimer un niveau
     */
    public function destroyLevel(Level $level)
    {
        // Vérifier s'il y a des classes associées
        if ($level->classes()->count() > 0) {
            return back()->withErrors(['delete' => 'Impossible de supprimer ce niveau, il contient des classes.']);
        }

        $level->delete();

        return redirect()->route('academic.levels')
            ->with('success', 'Niveau supprimé avec succès.');
    }

    // ================================
    // GESTION DES CLASSES
    // ================================

    /**
     * Afficher la liste des classes
     */
    public function classes()
    {
        $context = $this->getSchoolContext();
        
        $query = SchoolClass::with(['academicYear', 'cycle', 'level', 'students']);

        // Filtre par niveau si spécifié
        if (request('level_id')) {
            $query->where('level_id', request('level_id'));
        }

        // Filtre par année académique si spécifié
        if (request('academic_year_id')) {
            $query->where('academic_year_id', request('academic_year_id'));
        }

        $classes = $query->orderBy('cycle_id')
                        ->orderBy('level_id')
                        ->orderBy('name')
                        ->paginate(20);

        $levels = Level::with('cycle')->orderBy('cycle_id')->orderBy('name')->get();
        $cycles = Cycle::orderBy('name')->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('academic.classes.index', array_merge(
            compact('classes', 'levels', 'cycles', 'academicYears'),
            $context
        ));
    }

    /**
     * Formulaire de création d'une classe
     */
    public function createClass()
    {
        $context = $this->getSchoolContext();
        $levels = Level::with('cycle')->where('is_active', true)->orderBy('cycle_id')->orderBy('name')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        
        return view('academic.classes.create', array_merge(
            compact('levels', 'academicYears'),
            $context
        ));
    }

    /**
     * Afficher les détails d'une classe
     */
    public function showClass(SchoolClass $class)
    {
        $context = $this->getSchoolContext();
        $class->load(['academicYear', 'cycle', 'level', 'students.user', 'teacherAssignments.teacher.user', 'teacherAssignments.subject']);
        
        return view('academic.classes.show', array_merge(compact('class'), $context));
    }

    /**
     * Formulaire d'édition d'une classe
     */
    public function editClass(SchoolClass $class)
    {
        $context = $this->getSchoolContext();
        $levels = Level::with('cycle')->where('is_active', true)->orderBy('cycle_id')->orderBy('name')->get();
        $academicYears = AcademicYear::where('is_active', true)->orderBy('start_date', 'desc')->get();
        $class->load(['academicYear', 'cycle', 'level']);
        return view('academic.classes.edit', array_merge(
            compact('class', 'levels', 'academicYears'),
            $context
        ));
    }

    /**
     * Créer une nouvelle classe
     */
    public function storeClass(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
        ]);

        $level = Level::findOrFail($request->level_id);

        // Vérifier l'unicité du nom pour l'année et le niveau
        $exists = SchoolClass::where('academic_year_id', $request->academic_year_id)
                           ->where('level_id', $request->level_id)
                           ->where('name', $request->name)
                           ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Cette classe existe déjà pour ce niveau et cette année.']);
        }

        SchoolClass::create([
            'academic_year_id' => $request->academic_year_id,
            'cycle_id' => $level->cycle_id,
            'level_id' => $request->level_id,
            'name' => $request->name,
            'capacity' => $request->capacity,
        ]);

        return redirect()->route('academic.classes')
            ->with('success', 'Classe créée avec succès.');
    }

    /**
     * Mettre à jour une classe
     */
    public function updateClass(Request $request, SchoolClass $class)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'level_id' => 'required|exists:levels,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1|max:100',
        ]);

        $level = Level::findOrFail($request->level_id);

        // Vérifier l'unicité du nom (sauf pour la classe actuelle)
        $exists = SchoolClass::where('academic_year_id', $request->academic_year_id)
                           ->where('level_id', $request->level_id)
                           ->where('name', $request->name)
                           ->where('id', '!=', $class->id)
                           ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'Cette classe existe déjà pour ce niveau et cette année.']);
        }

        $class->update([
            'academic_year_id' => $request->academic_year_id,
            'cycle_id' => $level->cycle_id,
            'level_id' => $request->level_id,
            'name' => $request->name,
            'capacity' => $request->capacity,
        ]);

        return redirect()->route('academic.classes')
            ->with('success', 'Classe mise à jour avec succès.');
    }

    /**
     * Supprimer une classe
     */
    public function destroyClass(SchoolClass $class)
    {
        // Vérifier s'il y a des étudiants inscrits
        if ($class->students()->count() > 0) {
            return back()->withErrors(['delete' => 'Impossible de supprimer cette classe, elle contient des étudiants.']);
        }

        $class->delete();

        return redirect()->route('academic.classes')
            ->with('success', 'Classe supprimée avec succès.');
    }

    // ================================
    // GESTION DES MATIÈRES
    // ================================

    /**
     * Afficher la liste des matières
     */
    public function subjects()
    {
        $context = $this->getSchoolContext();
        
        $subjects = Subject::with(['levels.cycle'])
            ->orderBy('name')
            ->get();

        $levels = Level::with('cycle')->orderBy('cycle_id')->orderBy('name')->get();
        $levelsBySubject = collect();

        // Organiser les niveaux par matière pour l'affichage
        foreach ($subjects as $subject) {
            $levelsBySubject[$subject->id] = $subject->levels;
        }

        return view('academic.subjects.index', array_merge(
            compact('subjects', 'levels', 'levelsBySubject'),
            $context
        ));
    }

    /**
     * Formulaire de création d'une matière
     */
    public function createSubject()
    {
        $context = $this->getSchoolContext();
        $levels = Level::with('cycle')->where('is_active', true)->orderBy('cycle_id')->orderBy('name')->get();
        
        return view('academic.subjects.create', array_merge(compact('levels'), $context));
    }

    /**
     * Afficher les détails d'une matière
     */
    public function showSubject(Subject $subject)
    {
        $subject->load(['levels.cycle', 'teacherAssignments.teacher.user', 'teacherAssignments.class']);
        return view('academic.subjects.show', compact('subject'));
    }

    /**
     * Formulaire d'édition d'une matière
     */
    public function editSubject(Subject $subject)
    {
        $levels = Level::with('cycle')->where('is_active', true)->orderBy('cycle_id')->orderBy('name')->get();
        $subject->load(['levels.cycle']);
        return view('academic.subjects.edit', compact('subject', 'levels'));
    }

    /**
     * Créer une nouvelle matière
     */
    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:subjects,code',
            'coefficient' => 'required|integer|min:1|max:10',
            'levels' => 'required|array|min:1',
            'levels.*' => 'exists:levels,id',
        ]);

        $subject = Subject::create([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'coefficient' => $request->coefficient,
        ]);

        // Associer les niveaux
        $subject->levels()->sync($request->levels);

        return redirect()->route('academic.subjects')
            ->with('success', 'Matière créée avec succès.');
    }

    /**
     * Mettre à jour une matière
     */
    public function updateSubject(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:subjects,code,' . $subject->id,
            'coefficient' => 'required|integer|min:1|max:10',
            'levels' => 'required|array|min:1',
            'levels.*' => 'exists:levels,id',
        ]);

        $subject->update([
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'coefficient' => $request->coefficient,
        ]);

        // Mettre à jour les niveaux associés
        $subject->levels()->sync($request->levels);

        return redirect()->route('academic.subjects')
            ->with('success', 'Matière mise à jour avec succès.');
    }

    /**
     * Supprimer une matière
     */
    public function destroySubject(Subject $subject)
    {
        // Vérifier s'il y a des affectations d'enseignants
        if ($subject->teacherAssignments()->count() > 0) {
            return back()->withErrors(['delete' => 'Impossible de supprimer cette matière, elle est assignée à des enseignants.']);
        }

        $subject->delete();

        return redirect()->route('academic.subjects')
            ->with('success', 'Matière supprimée avec succès.');
    }

    // ================================
    // API ENDPOINTS POUR LES FILTRES
    // ================================

    /**
     * Récupérer les niveaux d'un cycle (AJAX)
     */
    public function getLevelsByCycle($cycleId)
    {
        $levels = Level::where('cycle_id', $cycleId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($levels);
    }

    /**
     * Récupérer les classes d'un niveau (AJAX)
     */
    public function getClassesByLevel($levelId)
    {
        $classes = SchoolClass::where('level_id', $levelId)
            ->with(['academicYear:id,name'])
            ->orderBy('academic_year_id', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'academic_year_id', 'capacity']);

        return response()->json($classes);
    }

    /**
     * Statistiques du module académique (Dashboard)
     */
    public function getStats()
    {
        $stats = [
            'total_cycles' => Cycle::count(),
            'total_levels' => Level::count(),
            'total_classes' => SchoolClass::count(),
            'total_subjects' => Subject::count(),
        ];

        return response()->json($stats);
    }
}