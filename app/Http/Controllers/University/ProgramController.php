<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Department;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Contrôleur spécialisé pour la gestion des programmes universitaires
 * Utilise les modèles directement pour la gestion des programmes
 */
class ProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-university');
    }

    /**
     * Afficher la liste des programmes
     */
    public function index(Request $request): View
    {
        $query = Program::with(['department']);

        // Filtres
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('cycle')) {
            $query->where('cycle', $request->cycle);
        }

        if ($request->boolean('active_only', true)) {
            $query->where('is_active', true);
        }

        $programs = $query->orderBy('name')->paginate(15);
        $departments = Department::orderBy('name')->get();
        
        $statistics = [
            'total_programs' => Program::count(),
            'active_programs' => Program::where('is_active', true)->count(),
            'programs_by_cycle' => Program::selectRaw('cycle, count(*) as count')
                ->groupBy('cycle')
                ->get(),
        ];

        return view('university.programs.index', [
            'programs' => $programs,
            'statistics' => $statistics,
            'departments' => $departments,
            'filters' => $request->only(['department_id', 'cycle', 'active_only']),
        ]);
    }

    /**
     * Formulaire de création d'un programme
     */
    public function create(): View
    {
        $departments = Department::with('ufr')->orderBy('name')->get();
        $programTypes = ['license' => 'Licence', 'master' => 'Master', 'doctorat' => 'Doctorat'];

        return view('university.programs.create', [
            'departments' => $departments,
            'programTypes' => $programTypes,
        ]);
    }

    /**
     * Créer un nouveau programme
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:programs',
            'type' => 'required|string|in:license,master,doctorat',
            'cycle' => 'required|string',
            'duration_semesters' => 'required|integer|min:2|max:12',
            'total_credits' => 'required|integer|min:60',
            'description' => 'nullable|string',
            'admission_requirements' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $program = Program::create([
                'department_id' => $validated['department_id'],
                'name' => $validated['name'],
                'code' => $validated['code'],
                'type' => $validated['type'],
                'cycle' => $validated['cycle'],
                'duration_semesters' => $validated['duration_semesters'],
                'total_credits' => $validated['total_credits'],
                'description' => $validated['description'] ?? null,
                'admission_requirements' => $validated['admission_requirements'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('university.programs.show', $program)
                ->with('success', 'Programme créé avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un programme
     */
    public function show(Program $program): View
    {
        $program->load(['department', 'semesters', 'enrollments']);
        
        $statistics = [
            'total_students' => $program->enrollments()->count(),
            'active_students' => $program->enrollments()
                ->where('status', 'active')
                ->count(),
            'completion_rate' => $program->enrollments()
                ->where('status', 'completed')
                ->count() / max($program->enrollments()->count(), 1) * 100,
        ];

        return view('university.programs.show', [
            'program' => $program,
            'statistics' => $statistics,
            'semesters' => $program->semesters,
            'enrollmentStats' => $statistics,
        ]);
    }

    /**
     * Formulaire d'édition d'un programme
     */
    public function edit(Program $program): View
    {
        $departments = Department::with('ufr')->orderBy('name')->get();
        $programTypes = ['license' => 'Licence', 'master' => 'Master', 'doctorat' => 'Doctorat'];
        
        return view('university.programs.edit', [
            'program' => $program,
            'departments' => $departments,
            'programTypes' => $programTypes,
        ]);
    }

    /**
     * Mettre à jour un programme
     */
    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('programs', 'code')->ignore($program->id)
            ],
            'type' => 'required|string|in:license,master,doctorat',
            'cycle' => 'required|string',
            'duration_semesters' => 'required|integer|min:2|max:12',
            'total_credits' => 'required|integer|min:60',
            'description' => 'nullable|string',
            'admission_requirements' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $program->update([
                'department_id' => $validated['department_id'],
                'name' => $validated['name'],
                'code' => $validated['code'],
                'type' => $validated['type'],
                'cycle' => $validated['cycle'],
                'duration_semesters' => $validated['duration_semesters'],
                'total_credits' => $validated['total_credits'],
                'description' => $validated['description'] ?? null,
                'admission_requirements' => $validated['admission_requirements'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return redirect()
                ->route('university.programs.show', $program)
                ->with('success', 'Programme mis à jour avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un programme
     */
    public function destroy(Program $program): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier si le programme a des étudiants inscrits
            if ($program->enrollments()->exists()) {
                return back()->with('error', 'Impossible de supprimer un programme ayant des étudiants inscrits');
            }

            // Vérifier si le programme a des semestres avec des cours
            if ($program->semesters()->whereHas('courses')->exists()) {
                return back()->with('error', 'Impossible de supprimer un programme ayant des cours configurés');
            }

            $program->delete();
            DB::commit();

            return redirect()
                ->route('university.programs.index')
                ->with('success', 'Programme supprimé avec succès');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Valider la conformité LMD d'un programme
     */
    public function validateLMD(Program $program): RedirectResponse
    {
        try {
            $issues = [];
            
            // Validation des crédits selon le type de programme
            $expectedCredits = [
                'license' => 180,
                'master' => 120,
                'doctorat' => 180,
            ];
            
            if (isset($expectedCredits[$program->type]) && 
                $program->total_credits < $expectedCredits[$program->type]) {
                $issues[] = "Crédits insuffisants: {$program->total_credits} au lieu de {$expectedCredits[$program->type]}";
            }
            
            // Validation de la durée selon le type
            $expectedDuration = [
                'license' => 6,
                'master' => 4,
                'doctorat' => 6,
            ];
            
            if (isset($expectedDuration[$program->type]) && 
                $program->duration_semesters < $expectedDuration[$program->type]) {
                $issues[] = "Durée insuffisante: {$program->duration_semesters} semestres au lieu de {$expectedDuration[$program->type]}";
            }

            if (empty($issues)) {
                return back()->with('success', 'Programme conforme LMD');
            } else {
                return back()
                    ->with('warning', 'Non-conformités détectées')
                    ->with('lmd_issues', $issues);
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la validation: ' . $e->getMessage());
        }
    }

    /**
     * Dupliquer un programme
     */
    public function duplicate(Program $program): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $newProgram = $program->replicate();
            $newProgram->name = $program->name . ' (Copie)';
            $newProgram->code = $program->code . '_COPY_' . time();
            $newProgram->created_by = auth()->id();
            $newProgram->save();

            DB::commit();

            return redirect()
                ->route('university.programs.edit', $newProgram)
                ->with('success', 'Programme dupliqué. Veuillez modifier les informations nécessaires.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la duplication: ' . $e->getMessage());
        }
    }

    /**
     * Statistiques des programmes
     */
    public function statistics(): View
    {
        $statistics = [
            'total_programs' => Program::count(),
            'active_programs' => Program::where('is_active', true)->count(),
            'programs_by_type' => Program::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get(),
            'programs_by_department' => Program::with('department')
                ->selectRaw('department_id, count(*) as count')
                ->groupBy('department_id')
                ->get(),
            'total_credits_distribution' => Program::selectRaw('
                CASE 
                    WHEN total_credits < 120 THEN "< 120"
                    WHEN total_credits < 180 THEN "120-179"
                    WHEN total_credits < 240 THEN "180-239"
                    ELSE "240+"
                END as range,
                count(*) as count
            ')
            ->groupBy('range')
            ->get(),
        ];
        
        return view('university.programs.statistics', [
            'statistics' => $statistics,
        ]);
    }
}