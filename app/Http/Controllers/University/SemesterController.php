<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Semester;
use App\Services\SemesterService;
use App\Traits\HasUniversityContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Contrôleur spécialisé pour la gestion des semestres universitaires
 * Migré depuis UniversityController pour une meilleure séparation des responsabilités
 */
class SemesterController extends Controller
{
    use HasUniversityContext;

    public function __construct(
        protected SemesterService $semesterService
    ) {
        $this->middleware('auth');
        $this->middleware('can:manage-university');
    }

    /**
     * Afficher les semestres d'un programme
     */
    public function index(Program $program): View
    {
        return view('university.semesters.index', compact('program'));
    }

    /**
     * Afficher le formulaire de création d'un semestre
     */
    public function create(Program $program): View
    {
        return view('university.semesters.create', compact('program'));
    }

    /**
     * Enregistrer un nouveau semestre
     */
    public function store(Request $request, Program $program): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => [
                'required',
                'integer',
                'min:1',
                'max:' . ($program->total_semesters ?? 8),
                function ($attribute, $value, $fail) use ($program) {
                    if (Semester::where('program_id', $program->id)
                               ->where('semester_number', $value)
                               ->exists()) {
                        $fail("Le semestre n°{$value} existe déjà pour ce programme.");
                    }
                }
            ],
            'is_active' => 'boolean',
            'total_credits' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $semester = $this->semesterService->createSemester($program, $request->all());

            $this->logUniversityActivity('create', 'semester', $semester);

            return redirect()
                ->route('university.programs.show', $program)
                ->with('success', 'Semestre créé avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du semestre : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un semestre spécifique
     */
    public function show(Program $program, Semester $semester): View
    {
        $semester->load(['courseUnits' => function ($query) {
            $query->with('elements')->withCount('elements');
        }]);

        return view('university.semesters.show', compact('program', 'semester'));
    }

    /**
     * Route alternative pour afficher un semestre
     */
    public function showAlt(Semester $semester): View
    {
        $semester->load(['program', 'courseUnits' => function ($query) {
            $query->with('elements')->withCount('elements');
        }]);
        
        return view('university.semesters.show', [
            'semester' => $semester,
            'program' => $semester->program
        ]);
    }

    /**
     * Afficher le formulaire d'édition d'un semestre
     */
    public function edit(Program $program, Semester $semester): View
    {
        return view('university.semesters.edit', compact('program', 'semester'));
    }

    /**
     * Route alternative pour éditer un semestre
     */
    public function editAlt(Semester $semester): View
    {
        $semester->load('program');
        return view('university.semesters.edit', [
            'semester' => $semester,
            'program' => $semester->program
        ]);
    }

    /**
     * Mettre à jour un semestre
     */
    public function update(Request $request, Program $program, Semester $semester): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'semester_number' => [
                'required',
                'integer',
                'min:1',
                'max:' . ($program->total_semesters ?? 8),
                function ($attribute, $value, $fail) use ($program, $semester) {
                    if (Semester::where('program_id', $program->id)
                               ->where('semester_number', $value)
                               ->where('id', '!=', $semester->id)
                               ->exists()) {
                        $fail("Le semestre n°{$value} existe déjà pour ce programme.");
                    }
                }
            ],
            'is_active' => 'boolean',
            'total_credits' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $this->semesterService->updateSemester($semester, $request->all());

            $this->logUniversityActivity('update', 'semester', $semester);

            return redirect()
                ->route('university.semesters.show', [$program, $semester])
                ->with('success', 'Semestre modifié avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Route alternative pour mettre à jour un semestre
     */
    public function updateAlt(Request $request, Semester $semester): RedirectResponse
    {
        return $this->update($request, $semester->program, $semester);
    }

    /**
     * Supprimer un semestre
     */
    public function destroy(Program $program, Semester $semester): RedirectResponse
    {
        try {
            if ($semester->courseUnits()->count() > 0) {
                return back()->with('error', 'Impossible de supprimer un semestre qui contient des UE.');
            }

            $semesterName = $semester->name;
            $semester->delete();

            $this->logUniversityActivity('delete', 'semester', null, [
                'semester_name' => $semesterName,
                'program_id' => $program->id
            ]);

            return redirect()
                ->route('university.programs.show', $program)
                ->with('success', "Semestre '{$semesterName}' supprimé avec succès");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Route alternative pour supprimer un semestre
     */
    public function destroyAlt(Semester $semester): RedirectResponse
    {
        return $this->destroy($semester->program, $semester);
    }
}