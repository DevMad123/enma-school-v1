<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\School;
use App\Models\UFR;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

/**
 * Contrôleur pour la gestion des enseignants universitaires
 * Intégré dans le module universitaire
 */
class UniversityTeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin|admin|directeur|responsable_pedagogique']);
    }

    /**
     * Afficher la liste des enseignants universitaires
     */
    public function index(Request $request)
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity()) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette fonctionnalité est réservée aux universités.');
        }

        $query = Teacher::with(['user', 'school'])
            ->where('school_id', $school->id)
            ->when($request->ufr_id, function ($q, $ufrId) {
                $q->where('ufr_id', $ufrId);
            })
            ->when($request->department_id, function ($q, $departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($request->academic_rank, function ($q, $rank) {
                $q->where('academic_rank', $rank);
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
        
        // Données pour les filtres
        $ufrs = UFR::where('school_id', $school->id)->active()->get();
        $departments = Department::where('school_id', $school->id)->active()->get();
        
        $stats = [
            'total' => Teacher::where('school_id', $school->id)->count(),
            'active' => Teacher::where('school_id', $school->id)->where('status', 'active')->count(),
            'professors' => Teacher::where('school_id', $school->id)->where('academic_rank', 'professeur')->count(),
            'lecturers' => Teacher::where('school_id', $school->id)->where('academic_rank', 'maitre_de_conferences')->count(),
        ];

        return view('university.teachers.index', compact('teachers', 'ufrs', 'departments', 'stats'));
    }

    /**
     * Afficher le formulaire de création d'un enseignant universitaire
     */
    public function create()
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity()) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette fonctionnalité est réservée aux universités.');
        }

        $ufrs = UFR::where('school_id', $school->id)->active()->get();
        $departments = Department::where('school_id', $school->id)->active()->get();
        
        $academicRanks = [
            'assistant' => 'Assistant',
            'maitre_assistant' => 'Maître Assistant',
            'maitre_de_conferences' => 'Maître de Conférences',
            'professeur' => 'Professeur',
            'professeur_titulaire' => 'Professeur Titulaire',
            'charge_cours' => 'Chargé de Cours',
            'vacataire' => 'Vacataire'
        ];

        return view('university.teachers.create', compact('ufrs', 'departments', 'academicRanks'));
    }

    /**
     * Enregistrer un nouvel enseignant universitaire
     */
    public function store(Request $request)
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity()) {
            return redirect()->route('dashboard')
                ->with('error', 'Cette fonctionnalité est réservée aux universités.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'ufr_id' => 'required|exists:ufrs,id',
            'department_id' => 'required|exists:departments,id',
            'academic_rank' => 'required|string|in:assistant,maitre_assistant,maitre_de_conferences,professeur,professeur_titulaire,charge_cours,vacataire',
            'specialization' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'qualifications' => 'nullable|string',
            'research_interests' => 'nullable|string',
            'office_location' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Créer l'utilisateur
            $user = User::create([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'password' => Hash::make('password123'), // Mot de passe temporaire
                'email_verified_at' => now(),
            ]);

            // Assigner le rôle d'enseignant
            $user->assignRole('teacher');

            // Générer un ID employé unique
            $employeeId = 'PROF-' . str_pad(Teacher::count() + 1, 4, '0', STR_PAD_LEFT);

            // Créer l'enseignant
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'employee_id' => $employeeId,
                'ufr_id' => $validated['ufr_id'],
                'department_id' => $validated['department_id'],
                'academic_rank' => $validated['academic_rank'],
                'specialization' => $validated['specialization'],
                'hire_date' => $validated['hire_date'],
                'qualifications' => $validated['qualifications'],
                'research_interests' => $validated['research_interests'] ?? null,
                'office_location' => $validated['office_location'] ?? null,
                'salary' => $validated['salary'] ?? null,
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()->route('university.teachers.index')
                ->with('success', 'Enseignant universitaire créé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la création de l\'enseignant universitaire: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Une erreur est survenue lors de la création de l\'enseignant.');
        }
    }

    /**
     * Afficher les détails d'un enseignant universitaire
     */
    public function show(Teacher $teacher)
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity() || $teacher->school_id !== $school->id) {
            return redirect()->route('university.teachers.index')
                ->with('error', 'Enseignant non trouvé.');
        }

        $teacher->load(['user', 'ufr', 'department', 'programs']);
        
        // Statistiques de l'enseignant
        $stats = [
            'programs_count' => $teacher->programs()->count(),
            'active_programs' => $teacher->programs()->where('is_active', true)->count(),
            'years_of_service' => $teacher->hire_date ? $teacher->hire_date->diffInYears(now()) : 0,
        ];

        return view('university.teachers.show', compact('teacher', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition d'un enseignant universitaire
     */
    public function edit(Teacher $teacher)
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity() || $teacher->school_id !== $school->id) {
            return redirect()->route('university.teachers.index')
                ->with('error', 'Enseignant non trouvé.');
        }

        $ufrs = UFR::where('school_id', $school->id)->active()->get();
        $departments = Department::where('school_id', $school->id)->active()->get();
        
        $academicRanks = [
            'assistant' => 'Assistant',
            'maitre_assistant' => 'Maître Assistant',
            'maitre_de_conferences' => 'Maître de Conférences',
            'professeur' => 'Professeur',
            'professeur_titulaire' => 'Professeur Titulaire',
            'charge_cours' => 'Chargé de Cours',
            'vacataire' => 'Vacataire'
        ];

        return view('university.teachers.edit', compact('teacher', 'ufrs', 'departments', 'academicRanks'));
    }

    /**
     * Mettre à jour un enseignant universitaire
     */
    public function update(Request $request, Teacher $teacher)
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity() || $teacher->school_id !== $school->id) {
            return redirect()->route('university.teachers.index')
                ->with('error', 'Enseignant non trouvé.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($teacher->user_id)],
            'phone' => 'required|string|max:20',
            'ufr_id' => 'required|exists:ufrs,id',
            'department_id' => 'required|exists:departments,id',
            'academic_rank' => 'required|string|in:assistant,maitre_assistant,maitre_de_conferences,professeur,professeur_titulaire,charge_cours,vacataire',
            'specialization' => 'required|string|max:255',
            'hire_date' => 'required|date',
            'qualifications' => 'nullable|string',
            'research_interests' => 'nullable|string',
            'office_location' => 'nullable|string|max:255',
            'salary' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        try {
            DB::beginTransaction();

            // Mettre à jour l'utilisateur
            $teacher->user->update([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
            ]);

            // Mettre à jour l'enseignant
            $teacher->update([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'phone' => $validated['phone'],
                'ufr_id' => $validated['ufr_id'],
                'department_id' => $validated['department_id'],
                'academic_rank' => $validated['academic_rank'],
                'specialization' => $validated['specialization'],
                'hire_date' => $validated['hire_date'],
                'qualifications' => $validated['qualifications'],
                'research_interests' => $validated['research_interests'],
                'office_location' => $validated['office_location'],
                'salary' => $validated['salary'],
                'status' => $validated['status'],
            ]);

            DB::commit();

            return redirect()->route('university.teachers.show', $teacher)
                ->with('success', 'Enseignant universitaire mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la mise à jour de l\'enseignant universitaire: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    /**
     * Supprimer un enseignant universitaire
     */
    public function destroy(Teacher $teacher)
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity() || $teacher->school_id !== $school->id) {
            return redirect()->route('university.teachers.index')
                ->with('error', 'Enseignant non trouvé.');
        }

        try {
            DB::beginTransaction();

            // Vérifier s'il y a des programmes actifs
            $activePrograms = $teacher->programs()->where('is_active', true)->count();
            if ($activePrograms > 0) {
                return back()->with('error', 'Impossible de supprimer cet enseignant car il est affecté à des programmes actifs.');
            }

            // Supprimer l'enseignant (l'utilisateur reste mais le rôle teacher sera retiré)
            $teacher->delete();

            DB::commit();

            return redirect()->route('university.teachers.index')
                ->with('success', 'Enseignant universitaire supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la suppression de l\'enseignant universitaire: ' . $e->getMessage());

            return back()->with('error', 'Une erreur est survenue lors de la suppression.');
        }
    }

    /**
     * Basculer le statut d'un enseignant universitaire
     */
    public function toggleStatus(Teacher $teacher)
    {
        $school = School::getActiveSchool();
        
        if (!$school || !$school->isUniversity() || $teacher->school_id !== $school->id) {
            return response()->json(['error' => 'Enseignant non trouvé.'], 404);
        }

        $newStatus = $teacher->status === 'active' ? 'inactive' : 'active';
        $teacher->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Statut mis à jour avec succès.'
        ]);
    }
}