<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Models\Student;
use App\Models\GradePeriod;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportCardController extends Controller
{
    /**
     * Afficher la liste des bulletins
     */
    public function index(Request $request)
    {
        $query = ReportCard::with(['student', 'gradePeriod', 'schoolClass'])
            ->orderBy('created_at', 'desc');

        // Filtres
        if ($request->student_id) {
            $query->forStudent($request->student_id);
        }

        if ($request->period_id) {
            $query->forPeriod($request->period_id);
        }

        if ($request->class_id) {
            $query->where('school_class_id', $request->class_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $reportCards = $query->paginate(20);

        // Données pour les filtres
        $students = Student::active()->orderBy('first_name')->get();
        $periods = GradePeriod::where('is_active', true)->get();
        $classes = SchoolClass::with('level')->orderBy('name')->get();

        return view('report-cards.index', compact(
            'reportCards', 
            'students', 
            'periods', 
            'classes'
        ));
    }

    /**
     * Afficher le formulaire de création de bulletin
     */
    public function create(Request $request)
    {
        $students = Student::active()->orderBy('first_name')->get();
        $periods = GradePeriod::where('is_active', true)->get();
        $classes = SchoolClass::with('level')->orderBy('name')->get();

        return view('report-cards.create', compact('students', 'periods', 'classes'));
    }

    /**
     * Stocker un nouveau bulletin
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'grade_period_id' => 'required|exists:grade_periods,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'school_class_id' => 'nullable|exists:classes,id',
        ]);

        $student = Student::findOrFail($request->student_id);
        
        $academicYearId = $request->academic_year_id ?? 
            AcademicYear::where('is_active', true)->first()?->id;
        
        $classId = $request->school_class_id ?? 
            $student->currentClass()?->id;

        try {
            $reportCard = $student->getOrCreateReportCard(
                $request->grade_period_id,
                $academicYearId,
                $classId
            );

            return redirect()
                ->route('report-cards.show', $reportCard)
                ->with('success', 'Bulletin généré avec succès');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Afficher un bulletin
     */
    public function show(ReportCard $reportCard)
    {
        $reportCard->load([
            'student',
            'gradePeriod',
            'academicYear',
            'schoolClass.level',
            'generatedBy'
        ]);

        $subjectAverages = $reportCard->getSubjectAverages();

        return view('report-cards.show', compact('reportCard', 'subjectAverages'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(ReportCard $reportCard)
    {
        $reportCard->load(['student', 'gradePeriod', 'schoolClass']);
        $subjectAverages = $reportCard->getSubjectAverages();

        return view('report-cards.edit', compact('reportCard', 'subjectAverages'));
    }

    /**
     * Mettre à jour un bulletin
     */
    public function update(Request $request, ReportCard $reportCard)
    {
        $request->validate([
            'observations' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'is_final' => 'boolean',
        ]);

        $reportCard->update($request->only([
            'observations',
            'status',
            'is_final'
        ]));

        // Recalculer si nécessaire
        if ($request->recalculate) {
            $reportCard->calculate();
        }

        return redirect()
            ->route('report-cards.show', $reportCard)
            ->with('success', 'Bulletin mis à jour avec succès');
    }

    /**
     * Supprimer un bulletin
     */
    public function destroy(ReportCard $reportCard)
    {
        if ($reportCard->is_final) {
            return back()->withErrors(['error' => 'Impossible de supprimer un bulletin finalisé']);
        }

        $reportCard->delete();

        return redirect()
            ->route('report-cards.index')
            ->with('success', 'Bulletin supprimé avec succès');
    }

    /**
     * Exporter un bulletin en PDF
     */
    public function exportPdf(ReportCard $reportCard)
    {
        $reportCard->load([
            'student',
            'gradePeriod',
            'academicYear',
            'schoolClass.level'
        ]);

        $subjectAverages = $reportCard->getSubjectAverages();

        $pdf = Pdf::loadView('report-cards.pdf', compact('reportCard', 'subjectAverages'))
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = sprintf(
            'bulletin_%s_%s_%s.pdf',
            str_replace(' ', '_', $reportCard->student->full_name),
            str_replace(' ', '_', $reportCard->gradePeriod->name),
            $reportCard->academicYear->name
        );

        return $pdf->download($filename);
    }

    /**
     * Générer des bulletins en masse pour une classe
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'period_id' => 'required|exists:grade_periods,id',
        ]);

        $class = SchoolClass::findOrFail($request->class_id);
        $period = GradePeriod::findOrFail($request->period_id);
        $academicYear = AcademicYear::where('is_active', true)->first();

        if (!$academicYear) {
            return back()->withErrors(['error' => 'Aucune année académique active trouvée']);
        }

        $students = $class->students()->active()->get();
        $generatedCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                try {
                    $student->getOrCreateReportCard(
                        $period->id,
                        $academicYear->id,
                        $class->id
                    );
                    $generatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Erreur pour {$student->full_name}: {$e->getMessage()}";
                }
            }

            DB::commit();

            $message = "Bulletins générés: {$generatedCount}/{$students->count()}";
            if (count($errors) > 0) {
                $message .= ". Erreurs: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= "...";
                }
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la génération: ' . $e->getMessage()]);
        }
    }

    /**
     * Recalculer un bulletin
     */
    public function recalculate(ReportCard $reportCard)
    {
        try {
            $reportCard->calculate();
            return back()->with('success', 'Bulletin recalculé avec succès');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors du recalcul: ' . $e->getMessage()]);
        }
    }

    /**
     * Publier un bulletin
     */
    public function publish(ReportCard $reportCard)
    {
        if ($reportCard->general_average === null) {
            return back()->withErrors(['error' => 'Bulletin non calculé, impossible de publier']);
        }

        $reportCard->update([
            'status' => 'published',
            'generated_at' => now(),
            'generated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Bulletin publié avec succès');
    }

    /**
     * Finaliser un bulletin
     */
    public function finalize(ReportCard $reportCard)
    {
        if ($reportCard->status !== 'published') {
            return back()->withErrors(['error' => 'Le bulletin doit être publié avant d\'être finalisé']);
        }

        $reportCard->update(['is_final' => true]);

        return back()->with('success', 'Bulletin finalisé avec succès');
    }
}
