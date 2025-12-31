<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AcademicYearController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // TODO: Ajouter middleware pour roles admin/directeur
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schoolId = $request->get('school_id');
        
        $query = AcademicYear::with(['school', 'academicPeriods'])
            ->orderBy('created_at', 'desc');
            
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }
        
        $academicYears = $query->paginate(10);
        $schools = School::active()->get();
        
        return view('admin.academic-years.index', compact('academicYears', 'schools', 'schoolId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $schools = School::active()->get();
        $selectedSchoolId = $request->get('school_id');
        
        return view('admin.academic-years.create', compact('schools', 'selectedSchoolId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'create_periods' => 'boolean',
        ]);

        // Vérifier qu'il n'y a pas déjà une année active pour cette école si on veut activer celle-ci
        if ($request->boolean('is_active')) {
            $activeYear = AcademicYear::where('school_id', $request->school_id)
                ->where('is_active', true)
                ->first();
                
            if ($activeYear) {
                return back()->withErrors(['is_active' => 'Il y a déjà une année académique active pour cette école.']);
            }
        }

        $academicYear = AcademicYear::create([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Créer les périodes par défaut si demandé
        if ($request->boolean('create_periods')) {
            $academicYear->createDefaultPeriods();
        }

        return redirect()
            ->route('admin.academic-years.index', ['school_id' => $request->school_id])
            ->with('success', 'Année académique créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AcademicYear $academicYear)
    {
        $academicYear->load(['school', 'academicPeriods' => function($query) {
            $query->orderBy('order');
        }]);
        
        return view('admin.academic-years.show', compact('academicYear'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AcademicYear $academicYear)
    {
        $schools = School::active()->get();
        
        return view('admin.academic-years.edit', compact('academicYear', 'schools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        // Vérifier qu'il n'y a pas déjà une année active pour cette école si on veut activer celle-ci
        if ($request->boolean('is_active') && !$academicYear->is_active) {
            $activeYear = AcademicYear::where('school_id', $request->school_id)
                ->where('is_active', true)
                ->where('id', '!=', $academicYear->id)
                ->first();
                
            if ($activeYear) {
                return back()->withErrors(['is_active' => 'Il y a déjà une année académique active pour cette école.']);
            }
        }

        $academicYear->update([
            'school_id' => $request->school_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.academic-years.index', ['school_id' => $academicYear->school_id])
            ->with('success', 'Année académique mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AcademicYear $academicYear)
    {
        $schoolId = $academicYear->school_id;
        
        if ($academicYear->is_active) {
            return back()->withErrors(['delete' => 'Impossible de supprimer une année académique active.']);
        }

        $academicYear->delete();

        return redirect()
            ->route('admin.academic-years.index', ['school_id' => $schoolId])
            ->with('success', 'Année académique supprimée avec succès.');
    }

    /**
     * Activer/désactiver une année académique
     */
    public function toggleActive(AcademicYear $academicYear)
    {
        if (!$academicYear->is_active) {
            $academicYear->activate();
            $message = 'Année académique activée avec succès.';
        } else {
            $academicYear->update(['is_active' => false]);
            $message = 'Année académique désactivée avec succès.';
        }

        return back()->with('success', $message);
    }

    /**
     * Gérer les périodes d'une année académique
     */
    public function managePeriods(AcademicYear $academicYear)
    {
        $academicYear->load(['school', 'academicPeriods' => function($query) {
            $query->orderBy('order');
        }]);
        
        return view('admin.academic-years.periods', compact('academicYear'));
    }

    /**
     * Générer automatiquement les périodes selon le système académique
     */
    public function generatePeriods(AcademicYear $academicYear)
    {
        try {
            $academicYear->createDefaultPeriods();
            
            return response()->json([
                'success' => true,
                'message' => 'Périodes générées avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération des périodes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajouter une nouvelle période à une année académique
     */
    public function storePeriod(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'order' => 'required|integer|min:1',
        ]);

        $academicYear = AcademicYear::findOrFail($request->academic_year_id);
        
        // Déterminer le type selon le système académique de l'école
        $type = $academicYear->school ? $academicYear->school->academic_system : 'trimestre';

        $period = $academicYear->academicPeriods()->create([
            'name' => $request->name,
            'type' => $type,
            'order' => $request->order,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => false,
        ]);

        return redirect()
            ->route('admin.academic-years.manage-periods', $academicYear)
            ->with('success', 'Période ajoutée avec succès.');
    }

    /**
     * Mettre à jour une période académique
     */
    public function updatePeriod(Request $request, $periodId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $period = \App\Models\GradePeriod::findOrFail($periodId);

        $period->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return redirect()
            ->route('admin.academic-years.manage-periods', $period->academicYear)
            ->with('success', 'Période mise à jour avec succès.');
    }

    /**
     * Supprimer une période académique
     */
    public function destroyPeriod($periodId)
    {
        $period = \App\Models\GradePeriod::findOrFail($periodId);
        $academicYear = $period->academicYear;

        if ($period->is_active) {
            return back()->withErrors(['delete' => 'Impossible de supprimer une période active.']);
        }

        $period->delete();

        return redirect()
            ->route('admin.academic-years.manage-periods', $academicYear)
            ->with('success', 'Période supprimée avec succès.');
    }

    /**
     * Activer/désactiver une période académique
     */
    public function togglePeriodActive($periodId)
    {
        $period = \App\Models\GradePeriod::findOrFail($periodId);

        if (!$period->is_active) {
            // Désactiver les autres périodes de la même année académique
            \App\Models\GradePeriod::where('academic_year_id', $period->academic_year_id)
                ->where('id', '!=', $period->id)
                ->update(['is_active' => false]);

            $period->update(['is_active' => true]);
            $message = 'Période activée avec succès.';
        } else {
            $period->update(['is_active' => false]);
            $message = 'Période désactivée avec succès.';
        }

        return back()->with('success', $message);
    }
}
