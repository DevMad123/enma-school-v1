<?php

namespace App\Http\Controllers;

use App\Models\SchoolFee;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Level;
use App\Models\Cycle;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class FinanceController extends Controller
{
    /**
     * Affichage principal du module financier
     */
    public function index()
    {
        $schoolFees = SchoolFee::with(['schoolClass', 'level', 'cycle', 'academicYear'])
                               ->paginate(10);
        
        $recentPayments = Payment::with(['student.user', 'schoolFee'])
                                ->latest()
                                ->limit(5)
                                ->get();
        
        $totalFeesAmount = SchoolFee::where('status', 'active')->sum('amount');
        $totalPayments = Payment::where('status', 'confirmed')->sum('amount');
        $pendingPayments = Payment::where('status', 'pending')->count();
        
        return view('finance.index', compact(
            'schoolFees', 
            'recentPayments', 
            'totalFeesAmount', 
            'totalPayments', 
            'pendingPayments'
        ));
    }

    /**
     * Gestion des frais scolaires
     */
    public function schoolFees()
    {
        $schoolFees = SchoolFee::with(['schoolClass', 'level', 'cycle', 'academicYear'])
                               ->orderBy('created_at', 'desc')
                               ->paginate(15);
        
        return view('finance.school-fees.index', compact('schoolFees'));
    }

    /**
     * Formulaire de création de frais scolaires
     */
    public function createSchoolFee()
    {
        $schoolClasses = SchoolClass::all();
        $levels = Level::all();
        $cycles = Cycle::all();
        $academicYears = AcademicYear::all();
        
        return view('finance.school-fees.create', compact(
            'schoolClasses', 
            'levels', 
            'cycles', 
            'academicYears'
        ));
    }

    /**
     * Enregistrement d'un nouveau frais scolaire
     */
    public function storeSchoolFee(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'level_id' => 'nullable|exists:levels,id',
            'cycle_id' => 'nullable|exists:cycles,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_mandatory' => 'boolean',
            'due_date' => 'nullable|date',
            'status' => 'required|in:active,inactive'
        ]);

        SchoolFee::create($validated);

        return redirect()->route('finance.school-fees')
                        ->with('success', 'Frais scolaire créé avec succès.');
    }

    /**
     * Édition d'un frais scolaire
     */
    public function editSchoolFee(SchoolFee $schoolFee)
    {
        $schoolClasses = SchoolClass::all();
        $levels = Level::all();
        $cycles = Cycle::all();
        $academicYears = AcademicYear::all();
        
        return view('finance.school-fees.edit', compact(
            'schoolFee',
            'schoolClasses', 
            'levels', 
            'cycles', 
            'academicYears'
        ));
    }

    /**
     * Mise à jour d'un frais scolaire
     */
    public function updateSchoolFee(Request $request, SchoolFee $schoolFee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'school_class_id' => 'nullable|exists:school_classes,id',
            'level_id' => 'nullable|exists:levels,id',
            'cycle_id' => 'nullable|exists:cycles,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'is_mandatory' => 'boolean',
            'due_date' => 'nullable|date',
            'status' => 'required|in:active,inactive'
        ]);

        $schoolFee->update($validated);

        return redirect()->route('finance.school-fees')
                        ->with('success', 'Frais scolaire mis à jour avec succès.');
    }

    /**
     * Suppression d'un frais scolaire
     */
    public function destroySchoolFee(SchoolFee $schoolFee)
    {
        // Vérifier s'il y a des paiements associés
        if ($schoolFee->payments()->exists()) {
            return redirect()->route('finance.school-fees')
                           ->with('error', 'Impossible de supprimer ce frais : des paiements y sont associés.');
        }

        $schoolFee->delete();

        return redirect()->route('finance.school-fees')
                        ->with('success', 'Frais scolaire supprimé avec succès.');
    }

    /**
     * Gestion des paiements
     */
    public function payments()
    {
        $payments = Payment::with(['student.user', 'schoolFee', 'receipt'])
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);
        
        return view('finance.payments.index', compact('payments'));
    }

    /**
     * Formulaire d'enregistrement de paiement
     */
    public function createPayment()
    {
        $students = Student::with('user')->get();
        $schoolFees = SchoolFee::where('status', 'active')->get();
        
        return view('finance.payments.create', compact('students', 'schoolFees'));
    }

    /**
     * Enregistrement d'un nouveau paiement
     */
    public function storePayment(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'school_fee_id' => 'required|exists:school_fees,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank_transfer,check,card,mobile_money',
            'transaction_reference' => 'nullable|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        // Vérifier que le montant ne dépasse pas le solde restant
        $schoolFee = SchoolFee::find($validated['school_fee_id']);
        $remainingBalance = $schoolFee->remaining_balance;

        if ($validated['amount'] > $remainingBalance) {
            return back()->withErrors([
                'amount' => "Le montant ne peut pas dépasser le solde restant ({$remainingBalance} FCFA)"
            ]);
        }

        $validated['processed_by'] = Auth::id();
        $payment = Payment::create($validated);

        // Auto-confirmer le paiement et générer le reçu
        $payment->confirm();

        return redirect()->route('finance.payments')
                        ->with('success', 'Paiement enregistré avec succès. Un reçu a été généré.');
    }

    /**
     * Confirmation d'un paiement
     */
    public function confirmPayment(Payment $payment)
    {
        $payment->confirm();
        
        return redirect()->route('finance.payments')
                        ->with('success', 'Paiement confirmé et reçu généré avec succès.');
    }

    /**
     * Annulation d'un paiement
     */
    public function cancelPayment(Payment $payment)
    {
        $payment->update(['status' => 'cancelled']);
        
        return redirect()->route('finance.payments')
                        ->with('success', 'Paiement annulé avec succès.');
    }

    /**
     * Consultation des soldes des étudiants
     */
    public function studentBalances()
    {
        $students = Student::with(['user', 'enrollments.schoolClass'])
                          ->get()
                          ->map(function ($student) {
                              $totalFees = $this->calculateStudentTotalFees($student);
                              $totalPaid = $this->calculateStudentTotalPaid($student);
                              $balance = $totalFees - $totalPaid;
                              
                              $student->total_fees = $totalFees;
                              $student->total_paid = $totalPaid;
                              $student->balance = $balance;
                              
                              return $student;
                          });
        
        return view('finance.student-balances', compact('students'));
    }

    /**
     * Détail du solde d'un étudiant
     */
    public function studentBalance(Student $student)
    {
        $enrollment = $student->enrollments()->with('schoolClass')->latest()->first();
        
        if (!$enrollment) {
            return redirect()->route('finance.student-balances')
                           ->with('error', 'Aucune inscription trouvée pour cet étudiant.');
        }

        // Récupérer les frais applicables à cet étudiant
        $applicableFees = SchoolFee::where('status', 'active')
                                  ->where(function ($query) use ($enrollment) {
                                      $query->whereNull('school_class_id')
                                           ->orWhere('school_class_id', $enrollment->school_class_id);
                                  })
                                  ->get();

        $payments = Payment::where('student_id', $student->id)
                          ->with(['schoolFee', 'receipt'])
                          ->orderBy('payment_date', 'desc')
                          ->get();

        $totalFees = $applicableFees->sum('amount');
        $totalPaid = $payments->where('status', 'confirmed')->sum('amount');
        $balance = $totalFees - $totalPaid;
        
        return view('finance.student-balance-detail', compact(
            'student', 
            'enrollment', 
            'applicableFees', 
            'payments', 
            'totalFees', 
            'totalPaid', 
            'balance'
        ));
    }

    /**
     * Téléchargement d'un reçu en PDF
     */
    public function downloadReceipt(Receipt $receipt)
    {
        $pdf = PDF::loadView('finance.receipts.pdf', [
            'receipt' => $receipt,
            'payment' => $receipt->payment,
            'student' => $receipt->payment->student,
            'schoolFee' => $receipt->payment->schoolFee
        ]);

        return $pdf->download("recu-{$receipt->receipt_number}.pdf");
    }

    /**
     * Génération de rapport financier
     */
    public function generateReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());
        
        $payments = Payment::whereBetween('payment_date', [$startDate, $endDate])
                          ->where('status', 'confirmed')
                          ->with(['student.user', 'schoolFee'])
                          ->get();
        
        $totalAmount = $payments->sum('amount');
        $paymentsByMethod = $payments->groupBy('payment_method');
        $paymentsByFee = $payments->groupBy('school_fee_id');
        
        return view('finance.reports.index', compact(
            'payments', 
            'totalAmount', 
            'paymentsByMethod', 
            'paymentsByFee',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Calcul du total des frais pour un étudiant
     */
    private function calculateStudentTotalFees(Student $student): float
    {
        $enrollment = $student->enrollments()->latest()->first();
        if (!$enrollment) return 0;

        return SchoolFee::where('status', 'active')
                       ->where(function ($query) use ($enrollment) {
                           $query->whereNull('school_class_id')
                                ->orWhere('school_class_id', $enrollment->school_class_id);
                       })
                       ->sum('amount');
    }

    /**
     * Calcul du total payé par un étudiant
     */
    private function calculateStudentTotalPaid(Student $student): float
    {
        return Payment::where('student_id', $student->id)
                     ->where('status', 'confirmed')
                     ->sum('amount');
    }
}
