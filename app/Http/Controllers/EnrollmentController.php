<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EnrollmentController extends Controller
{
    /**
     * Afficher la liste des inscriptions
     */
    public function index()
    {
        $enrollments = Enrollment::with(['student', 'academicYear', 'schoolClass'])
            ->orderBy('enrollment_date', 'desc')
            ->paginate(15);

        return view('enrollments.index', compact('enrollments'));
    }

    /**
     * Afficher le formulaire de création d'une nouvelle inscription
     */
    public function create()
    {
        $students = Student::with('user')->get();
        $academicYears = AcademicYear::all();
        $classes = SchoolClass::with(['level', 'cycle'])->get();

        return view('enrollments.create', compact('students', 'academicYears', 'classes'));
    }

    /**
     * Enregistrer une nouvelle inscription
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
            'enrollment_date' => 'required|date',
        ]);

        // Vérifier qu'il n'y a pas déjà une inscription active pour cet étudiant cette année
        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('status', 'active')
            ->first();

        if ($existingEnrollment) {
            return back()->withErrors([
                'student_id' => 'Cet étudiant est déjà inscrit pour cette année académique.'
            ]);
        }

        // Vérifier la capacité de la classe
        $class = SchoolClass::findOrFail($request->class_id);
        $activeEnrollments = $class->activeEnrollments()->count();

        if ($activeEnrollments >= $class->capacity) {
            return back()->withErrors([
                'class_id' => 'Cette classe a atteint sa capacité maximale.'
            ]);
        }

        DB::transaction(function () use ($request) {
            // Créer l'inscription
            Enrollment::create([
                'student_id' => $request->student_id,
                'academic_year_id' => $request->academic_year_id,
                'class_id' => $request->class_id,
                'enrollment_date' => $request->enrollment_date,
                'status' => 'active',
            ]);

            // Optionnel: Ajouter l'étudiant à la table pivot class_student
            $student = Student::findOrFail($request->student_id);
            $student->classes()->syncWithoutDetaching([
                $request->class_id => ['assigned_at' => now()]
            ]);
        });

        return redirect()->route('enrollments.index')
            ->with('success', 'Inscription créée avec succès.');
    }

    /**
     * Afficher les détails d'une inscription
     */
    public function show(Enrollment $enrollment)
    {
        $enrollment->load(['student.user', 'academicYear', 'schoolClass.level']);
        
        return view('enrollments.show', compact('enrollment'));
    }

    /**
     * Marquer une inscription comme terminée
     */
    public function complete(Enrollment $enrollment)
    {
        $enrollment->markAsCompleted();

        return back()->with('success', 'Inscription marquée comme terminée.');
    }

    /**
     * Marquer une inscription comme annulée
     */
    public function cancel(Enrollment $enrollment)
    {
        DB::transaction(function () use ($enrollment) {
            $enrollment->markAsCancelled();

            // Retirer l'étudiant de la classe dans la table pivot
            $enrollment->student->classes()->detach($enrollment->class_id);
        });

        return back()->with('success', 'Inscription annulée avec succès.');
    }

    /**
     * Supprimer une inscription
     */
    public function destroy(Enrollment $enrollment)
    {
        DB::transaction(function () use ($enrollment) {
            // Retirer l'étudiant de la classe dans la table pivot
            $enrollment->student->classes()->detach($enrollment->class_id);
            
            // Supprimer l'inscription
            $enrollment->delete();
        });

        return redirect()->route('enrollments.index')
            ->with('success', 'Inscription supprimée avec succès.');
    }

    /**
     * Inscrire un étudiant rapidement
     */
    public function quickEnroll(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $academicYear = AcademicYear::where('is_active', true)->first();
        
        if (!$academicYear) {
            return response()->json(['error' => 'Aucune année académique active'], 400);
        }

        // Vérifier qu'il n'y a pas déjà une inscription active
        $existingEnrollment = Enrollment::where('student_id', $request->student_id)
            ->where('academic_year_id', $academicYear->id)
            ->where('status', 'active')
            ->first();

        if ($existingEnrollment) {
            return response()->json(['error' => 'Étudiant déjà inscrit'], 400);
        }

        try {
            DB::transaction(function () use ($request, $academicYear) {
                Enrollment::create([
                    'student_id' => $request->student_id,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $request->class_id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);

                $student = Student::findOrFail($request->student_id);
                $student->classes()->syncWithoutDetaching([
                    $request->class_id => ['assigned_at' => now()]
                ]);
            });

            return response()->json(['success' => 'Inscription réussie']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'inscription'], 500);
        }
    }
}
