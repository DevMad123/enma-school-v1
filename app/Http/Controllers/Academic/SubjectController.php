<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\EducationalSubject;
use App\Models\Level;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Contrôleur spécialisé pour la gestion des matières préuniversitaires
 * Utilise le modèle polymorphe EducationalSubject
 */
class SubjectController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-academic');
    }

    /**
     * Afficher la liste des matières
     */
    public function index(Request $request): View
    {
        $query = EducationalSubject::with(['level.cycle', 'teachers'])
            ->whereHas('level', function ($q) {
                $q->where('education_context', 'preuniversity');
            });

        // Filtres
        if ($request->filled('level_id')) {
            $query->where('level_id', $request->level_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        $subjects = $query->orderBy('name')->paginate(15);
        $levels = Level::where('education_context', 'preuniversity')
            ->orderBy('name')
            ->get();
        
        $categories = EducationalSubject::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        $statistics = [
            'total_subjects' => EducationalSubject::whereHas('level', function ($q) {
                $q->where('education_context', 'preuniversity');
            })->count(),
            'active_subjects' => EducationalSubject::whereHas('level', function ($q) {
                $q->where('education_context', 'preuniversity');
            })->where('is_active', true)->count(),
        ];

        return view('academic.subjects.index', [
            'subjects' => $subjects,
            'statistics' => $statistics,
            'levels' => $levels,
            'categories' => $categories,
            'filters' => $request->only(['level_id', 'category', 'search']),
        ]);
    }

    /**
     * Formulaire de création d'une matière
     */
    public function create(): View
    {
        $levels = Level::with('cycle')
            ->where('education_context', 'preuniversity')
            ->orderBy('name')
            ->get();
        
        $categories = EducationalSubject::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');
        
        return view('academic.subjects.create', [
            'levels' => $levels,
            'categories' => $categories,
            'defaultCoefficient' => 1.0,
        ]);
    }

    /**
     * Créer une nouvelle matière
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:educational_subjects,code',
            'level_id' => 'required|exists:levels,id',
            'coefficient' => 'required|numeric|min:0.5|max:10',
            'volume_hours' => 'required|integer|min:1|max:200',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'is_optional' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $subject = EducationalSubject::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'level_id' => $validated['level_id'],
                'coefficient' => $validated['coefficient'],
                'volume_hours' => $validated['volume_hours'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                'is_optional' => $validated['is_optional'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('academic.subjects.show', $subject)
                ->with('success', 'Matière créée avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une matière
     */
    public function show(EducationalSubject $subject): View
    {
        $subject->load(['level.cycle', 'teachers', 'evaluations']);
        
        $statistics = [
            'total_evaluations' => $subject->evaluations()->count(),
            'total_teachers' => $subject->teachers()->count(),
            'average_grade' => $subject->evaluations()
                ->whereHas('grades')
                ->with('grades')
                ->get()
                ->flatMap->grades
                ->avg('points'),
        ];

        return view('academic.subjects.show', [
            'subject' => $subject,
            'teachers' => $subject->teachers,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Formulaire d'édition d'une matière
     */
    public function edit(EducationalSubject $subject): View
    {
        $levels = Level::with('cycle')
            ->where('education_context', 'preuniversity')
            ->orderBy('name')
            ->get();
            
        $categories = EducationalSubject::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category');

        return view('academic.subjects.edit', [
            'subject' => $subject,
            'levels' => $levels,
            'categories' => $categories,
        ]);
    }

    /**
     * Mettre à jour une matière
     */
    public function update(Request $request, EducationalSubject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required', 
                'string', 
                'max:20',
                Rule::unique('educational_subjects', 'code')->ignore($subject->id)
            ],
            'level_id' => 'required|exists:levels,id',
            'coefficient' => 'required|numeric|min:0.5|max:10',
            'volume_hours' => 'required|integer|min:1|max:200',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'is_optional' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $subject->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'level_id' => $validated['level_id'],
                'coefficient' => $validated['coefficient'],
                'volume_hours' => $validated['volume_hours'],
                'category' => $validated['category'],
                'description' => $validated['description'] ?? null,
                'is_optional' => $validated['is_optional'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('academic.subjects.show', $subject)
                ->with('success', 'Matière mise à jour avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une matière
     */
    public function destroy(EducationalSubject $subject): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier si la matière a des évaluations
            if ($subject->evaluations()->exists()) {
                return back()->with('error', 'Impossible de supprimer une matière ayant des évaluations');
            }

            // Vérifier si la matière a des notes
            if ($subject->grades()->exists()) {
                return back()->with('error', 'Impossible de supprimer une matière ayant des notes');
            }

            $subject->delete();
            DB::commit();

            return redirect()
                ->route('academic.subjects.index')
                ->with('success', 'Matière supprimée avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Assigner un enseignant à une matière
     */
    public function assignTeacher(Request $request, EducationalSubject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        try {
            DB::beginTransaction();

            // Vérifier que l'enseignant n'est pas déjà assigné
            if (!$subject->teachers()->where('teacher_id', $validated['teacher_id'])->exists()) {
                $subject->teachers()->attach($validated['teacher_id'], [
                    'assigned_at' => now(),
                    'assigned_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return back()->with('success', 'Enseignant assigné avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'assignation: ' . $e->getMessage());
        }
    }

    /**
     * Retirer un enseignant d'une matière
     */
    public function removeTeacher(Request $request, EducationalSubject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        try {
            DB::beginTransaction();
            
            $subject->teachers()->detach($validated['teacher_id']);
            
            DB::commit();

            return back()->with('success', 'Enseignant retiré avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du retrait: ' . $e->getMessage());
        }
    }

    /**
     * Statistiques détaillées des matières
     */
    public function statistics(): View
    {
        $statistics = [
            'total_subjects' => EducationalSubject::whereHas('level', function ($q) {
                $q->where('education_context', 'preuniversity');
            })->count(),
            'active_subjects' => EducationalSubject::whereHas('level', function ($q) {
                $q->where('education_context', 'preuniversity');
            })->where('is_active', true)->count(),
            'subjects_by_level' => EducationalSubject::whereHas('level', function ($q) {
                $q->where('education_context', 'preuniversity');
            })
            ->selectRaw('level_id, count(*) as count')
            ->with('level:id,name')
            ->groupBy('level_id')
            ->get(),
            'subjects_by_category' => EducationalSubject::whereHas('level', function ($q) {
                $q->where('education_context', 'preuniversity');
            })
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->get(),
        ];

        return view('academic.subjects.statistics', [
            'statistics' => $statistics,
        ]);
    }
}