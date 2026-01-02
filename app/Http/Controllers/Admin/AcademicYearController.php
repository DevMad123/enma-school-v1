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
        // Pour la V1, se limiter à l'école ID 1
        $mainSchool = School::find(1);
        
        if (!$mainSchool) {
            abort(404, 'École principale non trouvée');
        }
        
        $query = AcademicYear::with(['school', 'academicPeriods'])
            ->where('school_id', 1) // Fixer à l'école ID 1
            ->orderBy('created_at', 'desc');
        
        // Recherche par nom
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filtres par statut
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true);
                    break;
                case 'current':
                    $query->where('is_current', true);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }
            
        $academicYears = $query->paginate(10)->withQueryString();
        
        return view('admin.academic-years.index', compact('academicYears', 'mainSchool'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Pour la V1, se limiter à l'école ID 1
        $mainSchool = School::find(1);
        
        if (!$mainSchool) {
            abort(404, 'École principale non trouvée');
        }
        
        return view('admin.academic-years.create', compact('mainSchool'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'is_current' => 'boolean',
            'create_periods' => 'boolean',
        ]);

        // Pour la V1, forcer l'école ID 1
        $schoolId = 1;
        $mainSchool = School::find($schoolId);
        
        if (!$mainSchool) {
            return back()->withErrors(['school' => 'École principale non trouvée']);
        }

        // Gérer l'année active : une seule année active par école
        if ($request->boolean('is_active')) {
            AcademicYear::where('school_id', $schoolId)->update(['is_active' => false]);
        }
        
        // Gérer l'année courante : une seule année courante par école
        if ($request->boolean('is_current')) {
            AcademicYear::where('school_id', $schoolId)->update(['is_current' => false]);
        }

        $academicYear = AcademicYear::create([
            'school_id' => $schoolId,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->boolean('is_active'),
            'is_current' => $request->boolean('is_current'),
        ]);

        // Créer les périodes par défaut si demandé
        if ($request->boolean('create_periods')) {
            $academicYear->createDefaultPeriods();
        }

        return redirect()
            ->route('admin.academic-years.index')
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
        // Vérifier que l'année appartient à l'école ID 1
        if ($academicYear->school_id !== 1) {
            return back()->withErrors(['error' => 'Action non autorisée.']);
        }

        if (!$academicYear->is_active) {
            // Désactiver toutes les autres années pour cette école
            AcademicYear::where('school_id', 1)->update(['is_active' => false]);
            $academicYear->update(['is_active' => true]);
            $message = 'Année académique activée avec succès.';
        } else {
            $academicYear->update(['is_active' => false]);
            $message = 'Année académique désactivée avec succès.';
        }

        return back()->with('success', $message);
    }

    /**
     * Définir comme année courante
     */
    public function setCurrent(AcademicYear $academicYear)
    {
        // Vérifier que l'année appartient à l'école ID 1
        if ($academicYear->school_id !== 1) {
            return back()->withErrors(['error' => 'Action non autorisée.']);
        }

        // Désactiver le statut courant pour toutes les autres années
        AcademicYear::where('school_id', 1)->update(['is_current' => false]);
        
        // Activer le statut courant pour cette année
        $academicYear->update(['is_current' => true]);

        return back()->with('success', 'Année académique définie comme courante avec succès.');
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
