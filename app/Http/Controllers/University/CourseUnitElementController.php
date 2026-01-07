<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\CourseUnit;
use App\Models\CourseUnitElement;
use App\Services\CourseUnitElementService;
use App\Http\Requests\StoreECUERequest;
use App\Http\Requests\UpdateECUERequest;
use App\Traits\HasUniversityContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Contrôleur spécialisé pour la gestion des éléments constitutifs d'unités d'enseignement (ECUE)
 * Migré depuis UniversityController pour une meilleure séparation des responsabilités
 */
class CourseUnitElementController extends Controller
{
    use HasUniversityContext;

    public function __construct(
        protected CourseUnitElementService $courseUnitElementService
    ) {
        $this->middleware('auth');
        $this->middleware('can:manage-university');
    }

    /**
     * Afficher les éléments constitutifs d'une unité d'enseignement
     */
    public function index(CourseUnit $courseUnit): View
    {
        $courseUnit->load(['elements', 'semester.program']);

        $totalCredits = $courseUnit->elements->sum('credits');
        $totalHours = $courseUnit->elements->sum('volume_hours');
        
        $statistics = [
            'total_elements' => $courseUnit->elements->count(),
            'total_credits' => $totalCredits,
            'total_hours' => $totalHours,
            'completion' => $courseUnit->credits > 0 ? 
                min(100, ($totalCredits / $courseUnit->credits) * 100) : 0
        ];

        return view('university.course-unit-elements.index', [
            'courseUnit' => $courseUnit,
            'elements' => $courseUnit->elements,
            'statistics' => $statistics
        ]);
    }

    /**
     * Afficher un élément constitutif spécifique
     */
    public function show(CourseUnit $courseUnit, CourseUnitElement $element): View
    {
        $courseUnit->load('semester.program');

        $relatedData = [
            'courseUnit' => $courseUnit,
            'semester' => $courseUnit->semester,
            'program' => $courseUnit->semester->program,
            'total_elements' => $courseUnit->elements()->count(),
            'total_credits' => $courseUnit->elements()->sum('credits'),
        ];

        return view('university.course-unit-elements.show', [
            'element' => $element,
            'courseUnit' => $courseUnit,
            'relatedData' => $relatedData
        ]);
    }

    /**
     * Afficher le formulaire de création d'un ECUE
     */
    public function create(CourseUnit $courseUnit): View
    {
        $courseUnit->load('semester.program');

        $remainingCredits = $courseUnit->credits - $courseUnit->elements()->sum('credits');
        
        $context = [
            'courseUnit' => $courseUnit,
            'remainingCredits' => $remainingCredits,
            'canAddMore' => $remainingCredits > 0,
        ];

        return view('university.course-unit-elements.create', $context);
    }

    /**
     * Enregistrer un nouvel élément constitutif
     */
    public function store(StoreECUERequest $request, CourseUnit $courseUnit): RedirectResponse
    {
        try {
            $element = $this->courseUnitElementService->createElement(
                $courseUnit, 
                $request->validated()
            );

            $this->logUniversityActivity('create', 'course_unit_element', $element);

            return redirect()
                ->route('university.course-units.elements.index', $courseUnit)
                ->with('success', 'Élément constitutif créé avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire d'édition d'un ECUE
     */
    public function edit(CourseUnit $courseUnit, CourseUnitElement $element): View
    {
        $courseUnit->load('semester.program');

        $otherElementsCredits = $courseUnit->elements()
            ->where('id', '!=', $element->id)
            ->sum('credits');
        
        $availableCredits = $courseUnit->credits - $otherElementsCredits;

        $context = [
            'element' => $element,
            'courseUnit' => $courseUnit,
            'availableCredits' => $availableCredits,
            'currentCredits' => $element->credits,
        ];

        return view('university.course-unit-elements.edit', $context);
    }

    /**
     * Mettre à jour un élément constitutif
     */
    public function update(UpdateECUERequest $request, CourseUnit $courseUnit, CourseUnitElement $element): RedirectResponse
    {
        try {
            $this->courseUnitElementService->updateElement(
                $element, 
                $request->validated()
            );

            $this->logUniversityActivity('update', 'course_unit_element', $element);

            return redirect()
                ->route('university.course-units.elements.show', [$courseUnit, $element])
                ->with('success', 'Élément constitutif modifié avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un élément constitutif
     */
    public function destroy(CourseUnit $courseUnit, CourseUnitElement $element): RedirectResponse
    {
        try {
            $elementName = $element->name;
            $element->delete();

            $this->logUniversityActivity('delete', 'course_unit_element', null, [
                'element_name' => $elementName,
                'course_unit_id' => $courseUnit->id
            ]);

            return redirect()
                ->route('university.course-units.elements.index', $courseUnit)
                ->with('success', "Élément constitutif '{$elementName}' supprimé avec succès");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}