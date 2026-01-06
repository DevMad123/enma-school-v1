<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Domains\Academic\EducationalStructureService;
use App\Models\Level;
use App\Models\Cycle;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Contrôleur spécialisé pour la gestion des niveaux éducatifs
 * Utilise EducationalStructureService du domaine Academic
 */
class LevelController extends Controller
{
    protected EducationalStructureService $structureService;

    public function __construct(EducationalStructureService $structureService)
    {
        $this->structureService = $structureService;
        $this->middleware('auth');
        $this->middleware('can:manage-academic');
    }

    /**
     * Afficher la liste des niveaux
     */
    public function index(Request $request): View
    {
        $filters = [
            'cycle_id' => $request->get('cycle_id'),
            'search' => $request->get('search'),
        ];

        $levelsData = $this->structureService->getAllLevels($filters);
        $cycles = Cycle::orderBy('name')->get();

        return view('academic.levels.index', [
            'levels' => $levelsData['levels'],
            'statistics' => $levelsData['statistics'],
            'cycles' => $cycles,
            'filters' => $filters,
        ]);
    }

    /**
     * Formulaire de création d'un niveau
     */
    public function create(): View
    {
        $cycles = Cycle::orderBy('name')->get();
        $recommendedStructure = $this->structureService->getRecommendedEducationalStructure();

        return view('academic.levels.create', [
            'cycles' => $cycles,
            'recommendedStructure' => $recommendedStructure,
        ]);
    }

    /**
     * Créer un nouveau niveau
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cycle_id' => 'required|exists:cycles,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'order' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'min_age' => 'nullable|integer|min:3|max:25',
            'max_age' => 'nullable|integer|min:3|max:25|gte:min_age',
            'is_active' => 'boolean',
        ]);

        try {
            $result = $this->structureService->createEducationalLevel($validated);

            if ($result['success']) {
                return redirect()
                    ->route('academic.levels.show', $result['level'])
                    ->with('success', 'Niveau créé avec succès');
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
     * Afficher les détails d'un niveau
     */
    public function show(Level $level): View
    {
        $levelDetails = $this->structureService->getEducationalLevelDetails($level->id);

        return view('academic.levels.show', [
            'level' => $level,
            'details' => $levelDetails,
            'classes' => $levelDetails['classes'],
            'subjects' => $levelDetails['subjects'],
            'students' => $levelDetails['students'],
            'statistics' => $levelDetails['statistics'],
        ]);
    }

    /**
     * Formulaire d'édition d'un niveau
     */
    public function edit(Level $level): View
    {
        $cycles = Cycle::orderBy('name')->get();

        return view('academic.levels.edit', [
            'level' => $level,
            'cycles' => $cycles,
        ]);
    }

    /**
     * Mettre à jour un niveau
     */
    public function update(Request $request, Level $level): RedirectResponse
    {
        $validated = $request->validate([
            'cycle_id' => 'required|exists:cycles,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'order' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'min_age' => 'nullable|integer|min:3|max:25',
            'max_age' => 'nullable|integer|min:3|max:25|gte:min_age',
            'is_active' => 'boolean',
        ]);

        try {
            $result = $this->structureService->updateEducationalLevel($level->id, $validated);

            if ($result['success']) {
                return redirect()
                    ->route('academic.levels.show', $level)
                    ->with('success', 'Niveau mis à jour avec succès');
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
     * Supprimer un niveau
     */
    public function destroy(Level $level): RedirectResponse
    {
        try {
            $result = $this->structureService->deleteEducationalLevel($level->id);

            if ($result['success']) {
                return redirect()
                    ->route('academic.levels.index')
                    ->with('success', 'Niveau supprimé avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Réorganiser l'ordre des niveaux
     */
    public function reorder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cycle_id' => 'required|exists:cycles,id',
            'level_orders' => 'required|array',
            'level_orders.*' => 'integer|min:1',
        ]);

        try {
            $result = $this->structureService->reorderLevelsInCycle(
                $validated['cycle_id'],
                $validated['level_orders']
            );

            if ($result['success']) {
                return back()->with('success', 'Ordre des niveaux mis à jour avec succès');
            } else {
                return back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la réorganisation: ' . $e->getMessage());
        }
    }

    /**
     * Valider la structure éducative
     */
    public function validateStructure(): View
    {
        $validation = $this->structureService->validateEducationalStructure();

        return view('academic.levels.validation', [
            'validation' => $validation,
            'recommendations' => $validation['recommendations'],
            'warnings' => $validation['warnings'],
            'errors' => $validation['errors'],
        ]);
    }

    /**
     * Appliquer une structure recommandée
     */
    public function applyRecommendedStructure(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'structure_type' => 'required|string|in:ivorian_standard,french_system,custom',
            'confirm_overwrite' => 'boolean',
        ]);

        try {
            $result = $this->structureService->applyRecommendedStructure(
                $validated['structure_type'],
                $validated['confirm_overwrite'] ?? false
            );

            if ($result['success']) {
                return back()
                    ->with('success', 'Structure éducative appliquée avec succès')
                    ->with('applied_changes', $result['changes']);
            } else {
                return back()
                    ->with('error', $result['message'])
                    ->with('conflicts', $result['conflicts'] ?? []);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'application: ' . $e->getMessage());
        }
    }

    /**
     * Exporter la structure des niveaux
     */
    public function export(Request $request)
    {
        $filters = [
            'cycle_id' => $request->get('cycle_id'),
            'format' => $request->get('format', 'xlsx'),
            'include_statistics' => $request->boolean('include_statistics'),
        ];

        try {
            $export = $this->structureService->exportEducationalStructure($filters);

            return response()->download($export['file_path'], $export['file_name'])
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    /**
     * Statistiques par cycle
     */
    public function statisticsByCycle(): View
    {
        $statistics = $this->structureService->getStatisticsByCycle();

        return view('academic.levels.cycle-statistics', [
            'statistics' => $statistics,
            'charts_data' => $statistics['charts'],
            'comparisons' => $statistics['comparisons'],
        ]);
    }

    /**
     * Analyser les flux d'étudiants entre niveaux
     */
    public function analyzeStudentFlow(): View
    {
        $flowAnalysis = $this->structureService->analyzeStudentFlow();

        return view('academic.levels.student-flow', [
            'flow_analysis' => $flowAnalysis,
            'progression_matrix' => $flowAnalysis['progression_matrix'],
            'retention_rates' => $flowAnalysis['retention_rates'],
            'bottlenecks' => $flowAnalysis['bottlenecks'],
        ]);
    }

    /**
     * Comparer avec d'autres établissements
     */
    public function compareWithOthers(): View
    {
        $comparison = $this->structureService->compareStructureWithOthers();

        return view('academic.levels.structure-comparison', [
            'comparison' => $comparison,
            'benchmarks' => $comparison['benchmarks'],
            'recommendations' => $comparison['recommendations'],
        ]);
    }
}