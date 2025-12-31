<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

/**
 * MODULE A4 - Contrôleur pour la gestion des enseignants
 */
class TeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin|directeur|responsable_pedagogique']);
    }

    /**
     * Afficher la liste des enseignants
     */
    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'school'])
            ->when($request->school_id, function ($q, $schoolId) {
                $q->forSchool($schoolId);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->specialization, function ($q, $specialization) {
                $q->withSpecialization($specialization);
            })
            ->when($request->search, function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('employee_id', 'like', "%{$search}%")
                          ->orWhereHas('user', function ($userQuery) use ($search) {
                              $userQuery->where('email', 'like', "%{$search}%");
                          });
                });
            });

        $teachers = $query->paginate(20);
        $schools = School::active()->get();
        $specializations = Teacher::distinct('specialization')
            ->whereNotNull('specialization')
            ->pluck('specialization');

        return view('admin.teachers.index', compact('teachers', 'schools', 'specializations'));
    }

    /**
     * Afficher le formulaire de création d'un enseignant
     */
    public function create()
    {
        $schools = School::active()->get();
        $availableUsers = User::doesntHave('teacher')
            ->doesntHave('student')
            ->doesntHave('parentProfile')
            ->doesntHave('staff')
            ->get();

        return view('admin.teachers.create', compact('schools', 'availableUsers'));
    }

    /**
     * Enregistrer un nouvel enseignant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id', 'unique:teachers,user_id'],
            'school_id' => ['required', 'exists:schools,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:50', 'unique:teachers,employee_id'],
            'hire_date' => ['nullable', 'date'],
            'qualifications' => ['nullable', 'string'],
            'teaching_subjects' => ['nullable', 'array'],
            'status' => ['required', 'in:active,inactive,retired'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $teacher = Teacher::create($validated);
            
            // Assigner le rôle enseignant à l'utilisateur
            $teacher->user->assignRole('enseignant');
            
            // Log de l'action
            Log::info('Nouvel enseignant créé', [
                'teacher_id' => $teacher->id,
                'user_id' => auth()->id(),
                'teacher_name' => $teacher->user->name
            ]);
        });

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Enseignant créé avec succès.');
    }

    /**
     * Afficher les détails d'un enseignant
     */
    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'school', 'assignments.subject', 'assignments.schoolClass.level', 'assignments.academicYear']);
        
        // Statistiques de l'enseignant
        $stats = [
            'total_assignments' => $teacher->assignments->count(),
            'active_assignments' => $teacher->activeAssignments()->count(),
            'total_weekly_hours' => $teacher->getTotalWeeklyHours(),
            'subjects_count' => $teacher->subjects->count(),
            'classes_count' => $teacher->classes->count(),
        ];

        return view('admin.teachers.show', compact('teacher', 'stats'));
    }

    /**
     * Afficher le formulaire de modification d'un enseignant
     */
    public function edit(Teacher $teacher)
    {
        $schools = School::active()->get();
        
        return view('admin.teachers.edit', compact('teacher', 'schools'));
    }

    /**
     * Mettre à jour un enseignant
     */
    public function update(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($teacher->user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('teachers')->ignore($teacher->id)],
            'specialization' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive,on_leave,suspended'],
            'employment_type' => ['nullable', 'in:full_time,part_time,contract,substitute'],
            'qualifications' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            // Mise à jour des informations utilisateur
            $teacher->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);

            // Mise à jour des informations enseignant
            $teacher->update([
                'employee_id' => $validated['employee_id'],
                'specialization' => $validated['specialization'] ?? null,
                'hire_date' => $validated['hire_date'],
                'status' => $validated['status'],
                'employment_type' => $validated['employment_type'] ?? null,
                'qualifications' => $validated['qualifications'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            // Log de l'action
            Log::info('Enseignant mis à jour', [
                'teacher_id' => $teacher->id,
                'user_id' => auth()->id(),
                'teacher_name' => $teacher->user->name
            ]);

            return redirect()->route('admin.teachers.show', $teacher)
                ->with('success', 'Enseignant mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un enseignant
     */
    public function destroy(Teacher $teacher)
    {
        if ($teacher->assignments()->exists()) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer un enseignant qui a des affectations.');
        }

        $teacher->delete();

        // Log de l'action
        Log::info('Enseignant supprimé', [
            'teacher_id' => $teacher->id,
            'user_id' => auth()->id(),
            'teacher_name' => $teacher->user->name
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Enseignant supprimé avec succès.');
    }

    /**
     * Activer/désactiver un enseignant
     */
    public function toggleStatus(Teacher $teacher)
    {
        $newStatus = $teacher->status === 'active' ? 'inactive' : 'active';
        $teacher->update(['status' => $newStatus]);

        $message = $newStatus === 'active' ? 'Enseignant activé' : 'Enseignant désactivé';

        return redirect()->back()
            ->with('success', $message . ' avec succès.');
    }

    /**
     * Afficher le formulaire d'édition d'un enseignant
     */
    // public function edit(Teacher $teacher)
    // {
    //     return view('admin.teachers.edit', compact('teacher'));
    // }

    /**
     * Exporter la liste des enseignants
     */
    public function export(Request $request)
    {
        // Implementation pour export Excel/PDF
        // Peut utiliser Laravel Excel ou autre
        
        return response()->json(['message' => 'Export en cours de développement']);
    }
}