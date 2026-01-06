<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\UFR;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

/**
 * Contrôleur spécialisé pour la gestion des départements universitaires
 */
class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:manage-university');
    }

    /**
     * Afficher la liste des départements
     */
    public function index(Request $request): View
    {
        $school = School::getActiveSchool();
        
        $departments = Department::with(['ufr', 'programs', 'teachers'])
            ->whereHas('ufr', function ($query) use ($school) {
                $query->where('school_id', $school->id);
            })
            ->withCount(['programs', 'teachers'])
            ->when($request->ufr_id, function ($query, $ufrId) {
                return $query->where('ufr_id', $ufrId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->orderBy('ufr_id')
            ->orderBy('name')
            ->paginate(15);

        $ufrs = UFR::where('school_id', $school->id)
            ->orderBy('name')
            ->get();

        $statistics = [
            'total_departments' => $departments->total(),
            'total_programs' => $departments->sum('programs_count'),
            'total_teachers' => $departments->sum('teachers_count'),
            'active_departments' => $departments->where('is_active', true)->count(),
        ];

        return view('university.departments.index', [
            'departments' => $departments,
            'ufrs' => $ufrs,
            'statistics' => $statistics,
            'school' => $school,
        ]);
    }

    /**
     * Formulaire de création d'un département
     */
    public function create(): View
    {
        $school = School::getActiveSchool();
        $ufrs = UFR::where('school_id', $school->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('university.departments.create', [
            'ufrs' => $ufrs,
            'school' => $school,
        ]);
    }

    /**
     * Créer un nouveau département
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ufr_id' => 'required|exists:ufrs,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments,code',
            'description' => 'nullable|string',
            'head_of_department' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Vérifier que l'UFR appartient à l'école active
            $ufr = UFR::where('id', $validated['ufr_id'])
                ->where('school_id', School::getActiveSchool()->id)
                ->firstOrFail();

            $validated['slug'] = \Str::slug($validated['name']);
            $department = Department::create($validated);

            DB::commit();

            return redirect()
                ->route('university.departments.show', $department)
                ->with('success', 'Département créé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création: ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un département
     */
    public function show(Department $department): View
    {
        $department->load([
            'ufr.school',
            'programs.semesters.courseUnits',
            'teachers.user'
        ]);

        $statistics = [
            'programs_count' => $department->programs->count(),
            'active_programs' => $department->programs->where('is_active', true)->count(),
            'teachers_count' => $department->teachers->count(),
            'course_units_count' => $department->programs->flatMap->semesters->flatMap->courseUnits->count(),
            'total_credits' => $department->programs->sum('total_credits'),
        ];

        return view('university.departments.show', [
            'department' => $department,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Formulaire d'édition d'un département
     */
    public function edit(Department $department): View
    {
        $school = School::getActiveSchool();
        $ufrs = UFR::where('school_id', $school->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('university.departments.edit', [
            'department' => $department,
            'ufrs' => $ufrs,
        ]);
    }

    /**
     * Mettre à jour un département
     */
    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'ufr_id' => 'required|exists:ufrs,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments,code,' . $department->id,
            'description' => 'nullable|string',
            'head_of_department' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Vérifier que l'UFR appartient à l'école active
            $ufr = UFR::where('id', $validated['ufr_id'])
                ->where('school_id', School::getActiveSchool()->id)
                ->firstOrFail();

            $validated['slug'] = \Str::slug($validated['name']);
            $department->update($validated);

            return redirect()
                ->route('university.departments.show', $department)
                ->with('success', 'Département mis à jour avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un département
     */
    public function destroy(Department $department): RedirectResponse
    {
        try {
            // Vérifier si le département peut être supprimé
            if ($department->programs()->count() > 0) {
                return back()->with('error', 'Impossible de supprimer un département contenant des programmes');
            }

            if ($department->teachers()->count() > 0) {
                return back()->with('error', 'Impossible de supprimer un département ayant des enseignants assignés');
            }

            $department->delete();

            return redirect()
                ->route('university.departments.index')
                ->with('success', 'Département supprimé avec succès');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Activer/désactiver un département
     */
    public function toggleStatus(Department $department): RedirectResponse
    {
        try {
            $department->update(['is_active' => !$department->is_active]);
            
            $status = $department->is_active ? 'activé' : 'désactivé';
            
            return back()->with('success', "Département {$status} avec succès");

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du changement de statut: ' . $e->getMessage());
        }
    }

    /**
     * Assigner un chef de département
     */
    public function assignHead(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        try {
            // Vérifier que l'enseignant appartient au département
            $teacher = $department->teachers()->findOrFail($validated['teacher_id']);

            // Désactiver l'ancien chef s'il existe
            $department->headAssignments()->where('is_active', true)->update(['is_active' => false]);

            // Créer la nouvelle assignation
            $department->headAssignments()->create([
                'teacher_id' => $validated['teacher_id'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_active' => true,
            ]);

            return back()->with('success', 'Chef de département assigné avec succès');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'assignation: ' . $e->getMessage());
        }
    }

    /**
     * Statistiques d'un département
     */
    public function statistics(Department $department): View
    {
        $department->load([
            'programs.semesters.courseUnits',
            'programs.students',
            'teachers'
        ]);

        $stats = [
            'academic' => [
                'programs_count' => $department->programs->count(),
                'active_programs' => $department->programs->where('is_active', true)->count(),
                'total_semesters' => $department->programs->flatMap->semesters->count(),
                'total_course_units' => $department->programs->flatMap->semesters->flatMap->courseUnits->count(),
                'total_credits' => $department->programs->flatMap->semesters->flatMap->courseUnits->sum('credits'),
            ],
            'human_resources' => [
                'teachers_count' => $department->teachers->count(),
                'active_teachers' => $department->teachers->where('is_active', true)->count(),
                'professors_count' => $department->teachers->where('grade', 'Professor')->count(),
                'lecturers_count' => $department->teachers->where('grade', 'Lecturer')->count(),
            ],
            'students' => [
                'total_enrolled' => $department->programs->flatMap->students->count(),
                'by_program' => $department->programs->mapWithKeys(function ($program) {
                    return [$program->name => $program->students->count()];
                }),
            ],
        ];

        return view('university.departments.statistics', [
            'department' => $department,
            'statistics' => $stats,
        ]);
    }
}