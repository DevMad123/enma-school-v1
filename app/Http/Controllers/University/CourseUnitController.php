<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use App\Models\CourseUnit;
use App\Services\UniversityService;
use App\Traits\HasUniversityContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Contrôleur spécialisé pour la gestion des unités d'enseignement (UE)
 * Migré depuis UniversityController pour une meilleure séparation des responsabilités
 */
class CourseUnitController extends Controller
{
    use HasUniversityContext;

    public function __construct(
        protected UniversityService $universityService
    ) {
        $this->middleware('auth');
        $this->middleware('can:manage-university');
    }

    /**
     * Afficher les unités d'enseignement d'un semestre
     */
    public function index(Semester $semester): View
    {
        return view('university.course-units.index', compact('semester'));
    }

    /**
     * Afficher le formulaire de création d'une UE
     */
    public function create(Semester $semester): View
    {
        return view('university.course-units.create', compact('semester'));
    }

    /**
     * Enregistrer une nouvelle unité d'enseignement
     */
    public function store(Request $request, Semester $semester): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                'unique:course_units,code'
            ],
            'credits' => 'required|integer|min:1|max:20',
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'type' => 'required|in:fundamental,specialization,transversal,optional',
            'volume_hours_cm' => 'nullable|integer|min:0',
            'volume_hours_td' => 'nullable|integer|min:0',
            'volume_hours_tp' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            $courseUnitData = $request->all();
            $courseUnitData['semester_id'] = $semester->id;
            $courseUnitData['school_id'] = $this->getUniversityContext()['school_id'];
            
            // Calcul du volume horaire total
            $courseUnitData['volume_hours_total'] = 
                ($courseUnitData['volume_hours_cm'] ?? 0) +
                ($courseUnitData['volume_hours_td'] ?? 0) +
                ($courseUnitData['volume_hours_tp'] ?? 0);

            $courseUnit = CourseUnit::create($courseUnitData);

            $this->logUniversityActivity('create', 'course_unit', $courseUnit);

            return redirect()
                ->route('university.semesters.show', [$semester->program, $semester])
                ->with('success', 'Unité d\'enseignement créée avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher une unité d'enseignement spécifique
     */
    public function show(CourseUnit $courseUnit): View
    {
        $courseUnit->load(['semester.program', 'elements']);
        
        return view('university.course-units.show', compact('courseUnit'));
    }

    /**
     * Afficher le formulaire d'édition d'une UE
     */
    public function edit(CourseUnit $courseUnit): View
    {
        $courseUnit->load('semester.program');
        
        return view('university.course-units.edit', compact('courseUnit'));
    }

    /**
     * Mettre à jour une unité d'enseignement
     */
    public function update(Request $request, CourseUnit $courseUnit): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:20',
                'unique:course_units,code,' . $courseUnit->id
            ],
            'credits' => 'required|integer|min:1|max:20',
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'type' => 'required|in:fundamental,specialization,transversal,optional',
            'volume_hours_cm' => 'nullable|integer|min:0',
            'volume_hours_td' => 'nullable|integer|min:0',
            'volume_hours_tp' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        try {
            $updateData = $request->all();
            
            // Calcul du volume horaire total
            $updateData['volume_hours_total'] = 
                ($updateData['volume_hours_cm'] ?? 0) +
                ($updateData['volume_hours_td'] ?? 0) +
                ($updateData['volume_hours_tp'] ?? 0);

            $courseUnit->update($updateData);

            $this->logUniversityActivity('update', 'course_unit', $courseUnit);

            return redirect()
                ->route('university.course-units.show', $courseUnit)
                ->with('success', 'Unité d\'enseignement modifiée avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une unité d'enseignement
     */
    public function destroy(CourseUnit $courseUnit): RedirectResponse
    {
        try {
            if ($courseUnit->elements()->count() > 0) {
                return back()->with('error', 'Impossible de supprimer une UE qui contient des ECUE.');
            }

            $semester = $courseUnit->semester;
            $courseUnitName = $courseUnit->name;
            
            $courseUnit->delete();

            $this->logUniversityActivity('delete', 'course_unit', null, [
                'course_unit_name' => $courseUnitName,
                'semester_id' => $semester->id
            ]);

            return redirect()
                ->route('university.semesters.show', [$semester->program, $semester])
                ->with('success', "Unité d'enseignement '{$courseUnitName}' supprimée avec succès");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}