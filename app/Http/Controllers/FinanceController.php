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
use App\Services\FinanceService;
use App\Traits\HasSchoolContext;
use App\Traits\HasCrudOperations;
use App\Http\Requests\StoreSchoolFeeRequest;
use App\Exceptions\BusinessRuleException;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/**
 * Contrôleur pour la gestion financière de l'école
 * 
 * Ce contrôleur gère :
 * - Les frais scolaires et leur configuration
 * - Les paiements des étudiants
 * - Les reçus et documents financiers
 * - Les rapports et statistiques financières
 * - Les soldes des étudiants
 * 
 * @package App\Http\Controllers
 * @author ENMA School
 * @version 1.0
 * @since 2026-01-02
 */
class FinanceController extends BaseController
{
    use HasSchoolContext, HasCrudOperations;
    
    /**
     * Service financier pour la logique métier
     * 
     * @var FinanceService
     */
    protected FinanceService $financeService;
    
    /**
     * Constructeur du contrôleur
     * 
     * @param FinanceService $financeService Service financier injecté
     */
    public function __construct(FinanceService $financeService)
    {
        parent::__construct();
        $this->financeService = $financeService;
    }
    
    /**
     * Affichage principal du module financier avec statistiques
     * 
     * @return View|JsonResponse|RedirectResponse Vue du tableau de bord financier
     */
    public function index()
    {
        try {
            $context = $this->getSchoolContext();
            $dashboardData = $this->financeService->getDashboardStatistics();
            
            return view('finance.index', array_merge($dashboardData, $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement du tableau de bord financier: ' . $e->getMessage(),
                $e,
                'dashboard'
            );
        }
    }
    
    /**
     * Gestion des frais scolaires avec filtres et pagination
     * 
     * @return View|JsonResponse|RedirectResponse Vue de la liste des frais scolaires
     */
    public function schoolFees()
    {
        try {
            $context = $this->getSchoolContext();
            $schoolFees = SchoolFee::with(['schoolClass', 'level', 'cycle', 'academicYear'])
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(15);
            
            return view('finance.school-fees.index', array_merge(compact('schoolFees'), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement des frais scolaires: ' . $e->getMessage(),
                $e,
                'finance.index'
            );
        }
    }

    /**
     * Formulaire de création de frais scolaires
     * 
     * @return View|JsonResponse|RedirectResponse Vue du formulaire de création
     */
    public function createSchoolFee()
    {
        try {
            $this->authorizeAction('create', SchoolFee::class);
            
            $context = $this->getSchoolContext();
            $schoolClasses = SchoolClass::where('is_active', true)->orderBy('name')->get();
            $levels = Level::where('is_active', true)->orderBy('order')->get();
            $cycles = Cycle::where('is_active', true)->orderBy('order')->get();
            $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
            
            return view('finance.school-fees.create', array_merge(compact(
                'schoolClasses', 
                'levels', 
                'cycles', 
                'academicYears'
            ), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement du formulaire: ' . $e->getMessage(),
                $e,
                'finance.school-fees'
            );
        }
    }

    /**
     * Enregistrement d'un nouveau frais scolaire avec validation robuste
     * 
     * @param StoreSchoolFeeRequest $request Requête validée
     * @return RedirectResponse|Response Redirection avec message
     */
    public function storeSchoolFee(StoreSchoolFeeRequest $request)
    {
        try {
            $this->authorizeAction('create', SchoolFee::class);
            
            // Créer le frais via le service
            $schoolFee = $this->financeService->createSchoolFee($request->validated());

            return $this->createCrudResponse(
                $request,
                true,
                'Frais scolaire créé avec succès',
                'finance.school-fees'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->handleContextualError(
                $request,
                'Erreur lors de la création du frais scolaire: ' . $e->getMessage(),
                $e
            );
        }
    }

    /**
     * Édition d'un frais scolaire
     * 
     * @param SchoolFee $schoolFee Frais à éditer
     * @return View|JsonResponse|RedirectResponse Vue du formulaire d'édition
     */
    public function editSchoolFee(SchoolFee $schoolFee)
    {
        try {
            $this->authorizeAction('update', $schoolFee);
            
            $context = $this->getSchoolContext();
            $schoolClasses = SchoolClass::where('is_active', true)->orderBy('name')->get();
            $levels = Level::where('is_active', true)->orderBy('order')->get();
            $cycles = Cycle::where('is_active', true)->orderBy('order')->get();
            $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
            
            return view('finance.school-fees.edit', array_merge(compact(
                'schoolFee',
                'schoolClasses', 
                'levels', 
                'cycles', 
                'academicYears'
            ), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement du formulaire d\'édition: ' . $e->getMessage(),
                $e,
                'finance.school-fees'
            );
        }
    }

    /**
     * Mise à jour d'un frais scolaire
     * 
     * @param Request $request Requête avec nouvelles données
     * @param SchoolFee $schoolFee Frais à mettre à jour
     * @return RedirectResponse|Response Redirection avec message
     */
    public function updateSchoolFee(Request $request, SchoolFee $schoolFee)
    {
        try {
            $this->authorizeAction('update', $schoolFee);
            
            // Validation des données
            $validated = $this->validateData($request->all(), [
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

            // Mettre à jour via le service
            $updatedSchoolFee = $this->financeService->updateSchoolFee($schoolFee, $validated);

            return $this->createCrudResponse(
                $request,
                true,
                'Frais scolaire mis à jour avec succès',
                'finance.school-fees'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->handleContextualError(
                $request,
                'Erreur lors de la mise à jour du frais scolaire: ' . $e->getMessage(),
                $e
            );
        }
    }

    /**
     * Suppression d'un frais scolaire
     * 
     * @param SchoolFee $schoolFee Frais à supprimer
     * @return RedirectResponse|Response Redirection avec message
     */
    public function destroySchoolFee(SchoolFee $schoolFee)
    {
        try {
            $this->authorizeAction('delete', $schoolFee);
            
            $schoolFeeName = $schoolFee->name;
            
            // Supprimer via le service qui gère les validations
            $this->financeService->deleteSchoolFee($schoolFee);

            return $this->createCrudResponse(
                request(),
                true,
                "Le frais scolaire '{$schoolFeeName}' a été supprimé avec succès",
                'finance.school-fees'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->route('finance.school-fees')
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors de la suppression du frais scolaire: ' . $e->getMessage(),
                $e,
                'finance.school-fees'
            );
        }
    }

    /**
     * Gestion des paiements
     * 
     * @return View|JsonResponse|RedirectResponse Vue de la liste des paiements
     */
    public function payments()
    {
        try {
            $context = $this->getSchoolContext();
            $payments = Payment::with(['student.user', 'schoolFee', 'receipt'])
                              ->orderBy('created_at', 'desc')
                              ->paginate(15);
            
            return view('finance.payments.index', array_merge(compact('payments'), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement des paiements: ' . $e->getMessage(),
                $e,
                'finance.index'
            );
        }
    }

    /**
     * Formulaire d'enregistrement de paiement
     * 
     * @return View|JsonResponse|RedirectResponse Vue du formulaire de création de paiement
     */
    public function createPayment()
    {
        try {
            $this->authorizeAction('create', Payment::class);
            
            $context = $this->getSchoolContext();
            $students = Student::with('user')->get();
            $schoolFees = SchoolFee::where('status', 'active')->get();
            
            return view('finance.payments.create', array_merge(compact('students', 'schoolFees'), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement du formulaire de paiement: ' . $e->getMessage(),
                $e,
                'finance.payments'
            );
        }
    }

    /**
     * Enregistrement d'un nouveau paiement
     * 
     * @param Request $request Requête avec données du paiement
     * @return RedirectResponse|Response Redirection avec message
     */
    public function storePayment(Request $request)
    {
        try {
            $this->authorizeAction('create', Payment::class);
            
            // Validation des données
            $validated = $this->validateData($request->all(), [
                'student_id' => 'required|exists:students,id',
                'school_fee_id' => 'required|exists:school_fees,id',
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,bank_transfer,check,card,mobile_money',
                'transaction_reference' => 'nullable|string',
                'payment_date' => 'required|date',
                'notes' => 'nullable|string'
            ]);

            // Créer le paiement via le service
            $payment = $this->financeService->createPayment($validated);

            return $this->createCrudResponse(
                $request,
                true,
                'Paiement enregistré avec succès. Un reçu a été généré.',
                'finance.payments'
            );
            
        } catch (BusinessRuleException $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
                
        } catch (\Exception $e) {
            return $this->handleContextualError(
                $request,
                'Erreur lors de l\'enregistrement du paiement: ' . $e->getMessage(),
                $e
            );
        }
    }

    /**
     * Confirmation d'un paiement
     * 
     * @param Payment $payment Paiement à confirmer
     * @return RedirectResponse Redirection avec message
     */
    public function confirmPayment(Payment $payment)
    {
        try {
            $this->authorizeAction('update', $payment);
            
            $receipt = $this->financeService->confirmPayment($payment);
            
            return redirect()->route('finance.payments')
                           ->with('success', 'Paiement confirmé et reçu généré avec succès.');
                           
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors de la confirmation du paiement: ' . $e->getMessage(),
                $e,
                'finance.payments'
            );
        }
    }

    /**
     * Annulation d'un paiement
     * 
     * @param Payment $payment Paiement à annuler
     * @return RedirectResponse Redirection avec message
     */
    public function cancelPayment(Payment $payment)
    {
        try {
            $this->authorizeAction('update', $payment);
            
            $payment->update(['status' => 'cancelled']);
            
            return redirect()->route('finance.payments')
                           ->with('success', 'Paiement annulé avec succès.');
                           
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors de l\'annulation du paiement: ' . $e->getMessage(),
                $e,
                'finance.payments'
            );
        }
    }

    /**
     * Consultation des soldes des étudiants
     * 
     * @return View|JsonResponse|RedirectResponse Vue des soldes étudiants
     */
    public function studentBalances()
    {
        try {
            $context = $this->getSchoolContext();
            $students = Student::with(['user', 'enrollments.schoolClass'])
                              ->get()
                              ->map(function ($student) {
                                  $totalFees = $this->financeService->calculateStudentTotalFees($student);
                                  $totalPaid = $this->financeService->calculateStudentTotalPaid($student);
                                  $balance = $totalFees - $totalPaid;
                                  
                                  $student->total_fees = $totalFees;
                                  $student->total_paid = $totalPaid;
                                  $student->balance = $balance;
                                  
                                  return $student;
                              });
            
            return view('finance.student-balances', array_merge(compact('students'), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement des soldes étudiants: ' . $e->getMessage(),
                $e,
                'finance.index'
            );
        }
    }

    /**
     * Détail du solde d'un étudiant
     * 
     * @param Student $student Étudiant
     * @return View|JsonResponse|RedirectResponse Vue du détail du solde
     */
    public function studentBalance(Student $student)
    {
        try {
            $context = $this->getSchoolContext();
            $enrollment = $student->enrollments()->with('schoolClass')->latest()->first();
            
            if (!$enrollment) {
                return redirect()->route('finance.student-balances')
                               ->with('error', 'Aucune inscription trouvée pour cet étudiant.');
            }

            // Récupérer les frais applicables via le service
            $applicableFees = $this->financeService->getApplicableFeesForStudent($student);

            $payments = Payment::where('student_id', $student->id)
                              ->with(['schoolFee', 'receipt'])
                              ->orderBy('payment_date', 'desc')
                              ->get();

            $totalFees = $applicableFees->sum('amount');
            $totalPaid = $payments->where('status', 'confirmed')->sum('amount');
            $balance = $totalFees - $totalPaid;
            
            return view('finance.student-balance-detail', array_merge(compact(
                'student', 
                'enrollment', 
                'applicableFees', 
                'payments', 
                'totalFees', 
                'totalPaid', 
                'balance'
            ), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du chargement du détail du solde: ' . $e->getMessage(),
                $e,
                'finance.student-balances'
            );
        }
    }

    /**
     * Téléchargement d'un reçu en PDF
     * 
     * @param Receipt $receipt Reçu à télécharger
     * @return Response|JsonResponse|RedirectResponse PDF response
     */
    public function downloadReceipt(Receipt $receipt)
    {
        try {
            $this->authorizeAction('view', $receipt);
            
            $pdf = PDF::loadView('finance.receipts.pdf', [
                'receipt' => $receipt,
                'payment' => $receipt->payment,
                'student' => $receipt->payment->student,
                'schoolFee' => $receipt->payment->schoolFee
            ]);

            return $pdf->download("recu-{$receipt->receipt_number}.pdf");
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors du téléchargement du reçu: ' . $e->getMessage(),
                $e,
                'finance.payments'
            );
        }
    }

    /**
     * Génération de rapport financier
     * 
     * @param Request $request Requête avec filtres
     * @return View|JsonResponse|RedirectResponse Vue du rapport financier
     */
    public function generateReport(Request $request)
    {
        try {
            $context = $this->getSchoolContext();
            $startDate = $request->input('start_date', now()->startOfMonth());
            $endDate = $request->input('end_date', now()->endOfMonth());
            
            $payments = Payment::whereBetween('payment_date', [$startDate, $endDate])
                              ->where('status', 'confirmed')
                              ->with(['student.user', 'schoolFee'])
                              ->get();
            
            $totalAmount = $payments->sum('amount');
            $paymentsByMethod = $payments->groupBy('payment_method');
            $paymentsByFee = $payments->groupBy('school_fee_id');
            
            return view('finance.reports.index', array_merge(compact(
                'payments', 
                'totalAmount', 
                'paymentsByMethod', 
                'paymentsByFee',
                'startDate',
                'endDate'
            ), $context));
            
        } catch (\Exception $e) {
            return $this->handleContextualError(
                request(),
                'Erreur lors de la génération du rapport: ' . $e->getMessage(),
                $e,
                'finance.index'
            );
        }
    }
}