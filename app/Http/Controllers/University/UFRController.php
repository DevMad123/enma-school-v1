<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\UFR;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur spécialisé pour la gestion des UFR
 * (Unités de Formation et de Recherche)
 */
class UFRController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-university');
    }

    /**
     * Afficher la liste des UFR
     */
    public function index(Request $request): View
    {
        $school = School::getActiveSchool();
        
        $ufrs = UFR::with(['departments.programs', 'school'])
            ->where('school_id', $school->id)
            ->withCount(['departments', 'programs'])
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        $statistics = [
            'total_ufrs' => $ufrs->total(),
            'total_departments' => $ufrs->sum('departments_count'),
            'total_programs' => $ufrs->sum('programs_count'),
            'active_ufrs' => $ufrs->where('is_active', true)->count(),
        ];

        return view('university.ufrs.index', [
            'ufrs' => $ufrs,
            'statistics' => $statistics,
            'school' => $school,
        ]);
    }

    /**
     * Formulaire de création d'une UFR
     */
    public function create(): View
    {
        $school = School::getActiveSchool();
        
        return view('university.ufrs.create', [
            'school' => $school,
        ]);
    }

    /**
     * Créer une nouvelle UFR
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:ufrs,code',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'dean_name' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $validated['school_id'] = School::getActiveSchool()->id;
            $validated['slug'] = \Str::slug($validated['name']);

            $ufr = UFR::create($validated);

            DB::commit();

            return redirect()
                ->route('university.ufrs.show', $ufr)
                ->with('success', 'UFR créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'une UFR
     */
    public function show(UFR $ufr): View
    {
        $ufr->load([
            'departments.programs.semesters',
            'departments.teachers',
            'school'
        ]);

        $statistics = [
            'departments_count' => $ufr->departments->count(),
            'programs_count' => $ufr->departments->sum(fn($dept) => $dept->programs->count()),
            'teachers_count' => $ufr->departments->sum(fn($dept) => $dept->teachers->count()),
            'active_programs' => $ufr->departments->flatMap->programs->where('is_active', true)->count(),
        ];

        return view('university.ufrs.show', [
            'ufr' => $ufr,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Formulaire d'édition d'une UFR
     */
    public function edit(UFR $ufr): View
    {
        return view('university.ufrs.edit', [
            'ufr' => $ufr,
        ]);
    }

    /**
     * Mettre à jour une UFR
     */
    public function update(Request $request, UFR $ufr): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:ufrs,code,' . $ufr->id,
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'dean_name' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $validated['slug'] = \Str::slug($validated['name']);
            $ufr->update($validated);

            return redirect()
                ->route('university.ufrs.show', $ufr)
                ->with('success', 'UFR mise à jour avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une UFR
     */
    public function destroy(UFR $ufr): RedirectResponse
    {
        try {
            // Vérifier si l'UFR peut être supprimée
            if ($ufr->departments()->count() > 0) {
                return back()->with('error', 'Impossible de supprimer une UFR contenant des départements');
            }

            $ufr->delete();

            return redirect()
                ->route('university.ufrs.index')
                ->with('success', 'UFR supprimée avec succès');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Activer/désactiver une UFR
     */
    public function toggleStatus(UFR $ufr): RedirectResponse
    {
        try {
            $ufr->update(['is_active' => !$ufr->is_active]);
            
            $status = $ufr->is_active ? 'activée' : 'désactivée';
            
            return back()->with('success', "UFR {$status} avec succès");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du changement de statut: ' . $e->getMessage());
        }
    }

    /**
     * Exporter les UFR
     */
    public function export(Request $request)
    {
        try {
            $school = School::getActiveSchool();
            
            $ufrs = UFR::with(['departments.programs'])
                ->where('school_id', $school->id)
                ->get();

            $filename = 'ufrs_' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            
            return response()->streamDownload(function () use ($ufrs) {
                echo "UFR;Code;Départements;Programmes;Statut\n";
                
                foreach ($ufrs as $ufr) {
                    $departments = $ufr->departments->pluck('name')->join(', ');
                    $programsCount = $ufr->departments->sum(fn($dept) => $dept->programs->count());
                    $status = $ufr->is_active ? 'Actif' : 'Inactif';
                    
                    echo "\"{$ufr->name}\";\"{$ufr->code}\";\"{$departments}\";{$programsCount};\"{$status}\"\n";
                }
            }, $filename, [
                'Content-Type' => 'text/csv',
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }
}