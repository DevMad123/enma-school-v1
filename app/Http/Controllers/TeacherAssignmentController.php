<?php

namespace App\Http\Controllers;

use App\Models\TeacherAssignment;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherAssignmentController extends Controller
{
    /**
     * Afficher la liste des affectations
     */
    public function index()
    {
        $assignments = TeacherAssignment::with(['teacher', 'academicYear', 'schoolClass.level'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('teacher-assignments.index', compact('assignments'));
    }

    /**
     * Afficher le formulaire de création d'une nouvelle affectation
     */
    public function create()
    {
        $teachers = Teacher::with('user')->where('status', 'active')->get();
        $academicYears = AcademicYear::all();
        $classes = SchoolClass::with(['level', 'cycle'])->get();

        return view('teacher-assignments.create', compact('teachers', 'academicYears', 'classes'));
    }

    /**
     * Enregistrer une nouvelle affectation
     */
    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        // Vérifier qu'il n'y a pas déjà une affectation pour cet enseignant dans cette classe cette année
        $existingAssignment = TeacherAssignment::where('teacher_id', $request->teacher_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->whereNull('subject_id') // Pour l'instant, sans matière spécifique
            ->first();

        if ($existingAssignment) {
            return back()->withErrors([
                'teacher_id' => 'Cet enseignant est déjà affecté à cette classe pour cette année académique.'
            ])->withInput();
        }

        TeacherAssignment::create([
            'teacher_id' => $request->teacher_id,
            'academic_year_id' => $request->academic_year_id,
            'class_id' => $request->class_id,
            'subject_id' => null, // Sera géré dans le module suivant
        ]);

        return redirect()->route('teacher-assignments.index')
            ->with('success', 'Affectation créée avec succès.');
    }

    /**
     * Afficher les détails d'une affectation
     */
    public function show(TeacherAssignment $teacherAssignment)
    {
        $teacherAssignment->load(['teacher.user', 'academicYear', 'schoolClass.level']);
        
        return view('teacher-assignments.show', compact('teacherAssignment'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(TeacherAssignment $teacherAssignment)
    {
        $teachers = Teacher::with('user')->where('status', 'active')->get();
        $academicYears = AcademicYear::all();
        $classes = SchoolClass::with(['level', 'cycle'])->get();

        return view('teacher-assignments.edit', compact('teacherAssignment', 'teachers', 'academicYears', 'classes'));
    }

    /**
     * Mettre à jour une affectation
     */
    public function update(Request $request, TeacherAssignment $teacherAssignment)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        // Vérifier qu'il n'y a pas de conflit (sauf pour l'affectation actuelle)
        $existingAssignment = TeacherAssignment::where('teacher_id', $request->teacher_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->where('class_id', $request->class_id)
            ->whereNull('subject_id')
            ->where('id', '!=', $teacherAssignment->id)
            ->first();

        if ($existingAssignment) {
            return back()->withErrors([
                'teacher_id' => 'Cet enseignant est déjà affecté à cette classe pour cette année académique.'
            ])->withInput();
        }

        $teacherAssignment->update([
            'teacher_id' => $request->teacher_id,
            'academic_year_id' => $request->academic_year_id,
            'class_id' => $request->class_id,
        ]);

        return redirect()->route('teacher-assignments.index')
            ->with('success', 'Affectation mise à jour avec succès.');
    }

    /**
     * Supprimer une affectation
     */
    public function destroy(TeacherAssignment $teacherAssignment)
    {
        $teacherAssignment->delete();

        return redirect()->route('teacher-assignments.index')
            ->with('success', 'Affectation supprimée avec succès.');
    }

    /**
     * Afficher les affectations d'un enseignant
     */
    public function teacherAssignments($teacherId)
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);
        $assignments = $teacher->assignments()->with(['academicYear', 'schoolClass.level'])->get();

        return view('teacher-assignments.teacher-view', compact('teacher', 'assignments'));
    }

    /**
     * Afficher les enseignants d'une classe
     */
    public function classTeachers($classId)
    {
        $class = SchoolClass::with(['level', 'cycle'])->findOrFail($classId);
        $assignments = $class->teacherAssignments()->with(['teacher.user', 'academicYear'])->get();

        return view('teacher-assignments.class-view', compact('class', 'assignments'));
    }

    /**
     * Affecter rapidement un enseignant à une classe pour l'année en cours
     */
    public function quickAssign(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();
        
        if (!$activeAcademicYear) {
            return response()->json(['error' => 'Aucune année académique active'], 400);
        }

        // Vérifier qu'il n'y a pas déjà une affectation
        $existingAssignment = TeacherAssignment::where('teacher_id', $request->teacher_id)
            ->where('academic_year_id', $activeAcademicYear->id)
            ->where('class_id', $request->class_id)
            ->whereNull('subject_id')
            ->first();

        if ($existingAssignment) {
            return response()->json(['error' => 'Enseignant déjà affecté'], 400);
        }

        try {
            TeacherAssignment::create([
                'teacher_id' => $request->teacher_id,
                'academic_year_id' => $activeAcademicYear->id,
                'class_id' => $request->class_id,
                'subject_id' => null,
            ]);

            return response()->json(['success' => 'Affectation réussie']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de l\'affectation'], 500);
        }
    }
}
