<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Student;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Level;
use App\Models\Cycle;
use App\Models\Enrollment;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Contrôleur pour la gestion administrative des étudiants
 * 
 * Ce contrôleur gère :
 * - CRUD des étudiants via l'interface admin
 * - Affectation aux classes
 * - Gestion des inscriptions
 * - Import/Export des étudiants
 * - Statistiques et rapports
 * 
 * @package App\Http\Controllers\Admin
 * @author ENMA School
 * @version 1.0
 * @since 2026-01-02
 */
class StudentController extends BaseController
{
    /**
     * Afficher la liste des étudiants
     */
    public function index(Request $request): View
    {
        $query = Student::with([
            'user', 
            'currentClass.level.cycle', 
            'enrollments' => function($q) {
                $q->latest()->limit(1);
            }
        ]);

        // Filtres
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('student_number', 'like', "%{$search}%");
        }

        if ($request->filled('class_id')) {
            $query->where('current_class_id', $request->get('class_id'));
        }

        if ($request->filled('level_id')) {
            $query->whereHas('currentClass', function($q) use ($request) {
                $q->where('level_id', $request->get('level_id'));
            });
        }

        if ($request->filled('cycle_id')) {
            $query->whereHas('currentClass.level', function($q) use ($request) {
                $q->where('cycle_id', $request->get('cycle_id'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(15);

        // Données pour les filtres
        $classes = SchoolClass::with('level.cycle')->orderBy('name')->get();
        $levels = Level::with('cycle')->orderBy('name')->get();
        $cycles = Cycle::orderBy('name')->get();

        // Statistiques
        $stats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'inactive_students' => Student::where('status', 'inactive')->count(),
            'new_this_month' => Student::where('created_at', '>=', now()->startOfMonth())->count(),
        ];

        return view('admin.students.index', compact(
            'students', 'classes', 'levels', 'cycles', 'stats'
        ));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(): View
    {
        $classes = SchoolClass::with('level.cycle')->orderBy('name')->get();
        $academicYear = AcademicYear::current();
        
        return view('admin.students.create', compact('classes', 'academicYear'));
    }

    /**
     * Enregistrer un nouvel étudiant
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_info' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make('student123'), // Mot de passe temporaire
                'email_verified_at' => now(),
            ]);

            // Assigner le rôle étudiant
            $studentRole = Role::where('name', 'student')->first();
            if ($studentRole) {
                $user->assignRole($studentRole);
            }

            // Générer le numéro étudiant
            $studentNumber = $this->generateStudentNumber();

            // Créer le profil étudiant
            $student = Student::create([
                'user_id' => $user->id,
                'student_number' => $studentNumber,
                'current_class_id' => $validated['class_id'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'place_of_birth' => $validated['place_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_phone' => $validated['guardian_phone'] ?? null,
                'guardian_email' => $validated['guardian_email'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'medical_info' => $validated['medical_info'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'active',
            ]);

            // Créer l'inscription pour l'année académique courante
            $academicYear = AcademicYear::current();
            if ($academicYear) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'class_id' => $validated['class_id'],
                    'academic_year_id' => $academicYear->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', "L'étudiant {$user->name} a été créé avec succès. Numéro: {$studentNumber}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'un étudiant
     */
    public function show(Student $student): View
    {
        $student->load([
            'user',
            'currentClass.level.cycle',
            'enrollments.academicYear',
            'enrollments.class.level',
            'payments.schoolFee',
            'grades.subject',
            'parentProfile'
        ]);

        return view('admin.students.show', compact('student'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Student $student): View
    {
        $student->load('user', 'currentClass');
        $classes = SchoolClass::with('level.cycle')->orderBy('name')->get();
        
        return view('admin.students.edit', compact('student', 'classes'));
    }

    /**
     * Mettre à jour un étudiant
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->user->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'place_of_birth' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'address' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_email' => 'nullable|email|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_info' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive,graduated,transferred',
        ]);

        DB::beginTransaction();
        
        try {
            // Mettre à jour l'utilisateur
            $student->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);

            // Mettre à jour le profil étudiant
            $student->update([
                'current_class_id' => $validated['class_id'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'place_of_birth' => $validated['place_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'address' => $validated['address'] ?? null,
                'guardian_name' => $validated['guardian_name'] ?? null,
                'guardian_phone' => $validated['guardian_phone'] ?? null,
                'guardian_email' => $validated['guardian_email'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'medical_info' => $validated['medical_info'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')
                ->with('success', "L'étudiant {$student->user->name} a été mis à jour avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Supprimer un étudiant (soft delete)
     */
    public function destroy(Student $student): RedirectResponse
    {
        try {
            $studentName = $student->user->name;
            $student->delete(); // Soft delete
            
            return redirect()->route('admin.students.index')
                ->with('success', "L'étudiant {$studentName} a été supprimé avec succès.");
                
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Basculer le statut d'un étudiant
     */
    public function toggleStatus(Student $student): RedirectResponse
    {
        $newStatus = $student->status === 'active' ? 'inactive' : 'active';
        
        $student->update(['status' => $newStatus]);
        
        return back()->with('success', 
            "Le statut de {$student->user->name} a été changé en " . 
            ($newStatus === 'active' ? 'actif' : 'inactif') . "."
        );
    }

    /**
     * Générer un numéro d'étudiant unique
     */
    private function generateStudentNumber(): string
    {
        $year = date('Y');
        $sequence = Student::whereYear('created_at', $year)->count() + 1;
        
        return 'ENM' . $year . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Export des étudiants
     */
    public function export(Request $request)
    {
        // TODO: Implémenter l'export Excel/CSV
        return back()->with('info', 'Fonctionnalité d\'export en cours de développement.');
    }

    /**
     * Import des étudiants
     */
    public function import(Request $request)
    {
        // TODO: Implémenter l'import Excel/CSV
        return back()->with('info', 'Fonctionnalité d\'import en cours de développement.');
    }

    /**
     * Obtenir les étudiants par classe (API)
     */
    public function getByClass(Request $request)
    {
        $classId = $request->get('class_id');
        
        $students = Student::where('current_class_id', $classId)
            ->where('status', 'active')
            ->with('user')
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'student_number' => $student->student_number,
                    'email' => $student->user->email,
                ];
            });

        return response()->json($students);
    }
}