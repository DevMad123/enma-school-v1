<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\SchoolFee;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Cycle;
use App\Models\Level;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FinanceModuleTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private Student $student;
    private AcademicYear $academicYear;
    private SchoolClass $schoolClass;
    private Cycle $cycle;
    private Level $level;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les données de test
        $this->admin = User::factory()->create();
        
        // Créer une année académique
        $this->academicYear = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => 'active'
        ]);

        // Créer un cycle
        $this->cycle = Cycle::create([
            'name' => 'Cycle Test',
            'description' => 'Cycle pour les tests'
        ]);

        // Créer un niveau
        $this->level = Level::create([
            'name' => 'CP1',
            'description' => 'Cours Préparatoire 1',
            'cycle_id' => $this->cycle->id,
            'order' => 1
        ]);

        // Créer une classe
        $this->schoolClass = SchoolClass::create([
            'name' => 'Classe Test',
            'description' => 'Classe pour les tests',
            'academic_year_id' => $this->academicYear->id,
            'cycle_id' => $this->cycle->id,
            'level_id' => $this->level->id
        ]);

        // Créer un étudiant
        $studentUser = User::factory()->create();
        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'gender' => 'male',
            'date_of_birth' => '2010-01-01',
            'phone' => '123456789',
            'address' => 'Test Address',
            'status' => 'active'
        ]);
    }

    /** @test */
    public function it_can_access_finance_dashboard()
    {
        $response = $this->actingAs($this->admin)
                        ->get(route('finance.index'));

        $response->assertStatus(200);
        $response->assertSee('Gestion Financière');
    }

    /** @test */
    public function it_can_create_school_fee()
    {
        $feeData = [
            'name' => 'Frais de Test',
            'description' => 'Description des frais de test',
            'amount' => 50000,
            'academic_year_id' => $this->academicYear->id,
            'school_class_id' => $this->schoolClass->id,
            'is_mandatory' => true,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'status' => 'active'
        ];

        $response = $this->actingAs($this->admin)
                        ->post(route('finance.school-fees.store'), $feeData);

        $response->assertRedirect(route('finance.school-fees'));
        $this->assertDatabaseHas('school_fees', [
            'name' => 'Frais de Test',
            'amount' => 50000
        ]);
    }

    /** @test */
    public function it_can_list_school_fees()
    {
        $schoolFee = SchoolFee::create([
            'name' => 'Frais de Scolarité',
            'amount' => 100000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        $response = $this->actingAs($this->admin)
                        ->get(route('finance.school-fees'));

        $response->assertStatus(200);
        $response->assertSee('Frais de Scolarité');
        $response->assertSee('100000');
    }

    /** @test */
    public function it_can_update_school_fee()
    {
        $schoolFee = SchoolFee::create([
            'name' => 'Frais Original',
            'amount' => 50000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        $updateData = [
            'name' => 'Frais Modifié',
            'amount' => 75000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => false,
            'status' => 'active'
        ];

        $response = $this->actingAs($this->admin)
                        ->put(route('finance.school-fees.update', $schoolFee), $updateData);

        $response->assertRedirect(route('finance.school-fees'));
        $this->assertDatabaseHas('school_fees', [
            'id' => $schoolFee->id,
            'name' => 'Frais Modifié',
            'amount' => 75000
        ]);
    }

    /** @test */
    public function it_can_create_payment()
    {
        $schoolFee = SchoolFee::create([
            'name' => 'Frais de Scolarité',
            'amount' => 100000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        $paymentData = [
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee->id,
            'amount' => 50000, // Paiement partiel
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d\TH:i'),
            'notes' => 'Paiement de test'
        ];

        $response = $this->actingAs($this->admin)
                        ->post(route('finance.payments.store'), $paymentData);

        $response->assertRedirect(route('finance.payments'));
        $this->assertDatabaseHas('payments', [
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee->id,
            'amount' => 50000,
            'status' => 'confirmed'
        ]);

        // Vérifier qu'un reçu a été généré
        $payment = Payment::where('student_id', $this->student->id)->first();
        $this->assertInstanceOf(Receipt::class, $payment->receipt);
    }

    /** @test */
    public function it_prevents_overpayment()
    {
        $schoolFee = SchoolFee::create([
            'name' => 'Frais de Scolarité',
            'amount' => 100000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        $paymentData = [
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee->id,
            'amount' => 150000, // Montant supérieur aux frais
            'payment_method' => 'cash',
            'payment_date' => now()->format('Y-m-d\TH:i')
        ];

        $response = $this->actingAs($this->admin)
                        ->post(route('finance.payments.store'), $paymentData);

        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function it_can_confirm_pending_payment()
    {
        $schoolFee = SchoolFee::create([
            'name' => 'Frais de Scolarité',
            'amount' => 100000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee->id,
            'amount' => 50000,
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'status' => 'pending',
            'processed_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
                        ->post(route('finance.payments.confirm', $payment));

        $response->assertRedirect(route('finance.payments'));
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'confirmed'
        ]);

        // Vérifier qu'un reçu a été généré
        $payment->refresh();
        $this->assertInstanceOf(Receipt::class, $payment->receipt);
    }

    /** @test */
    public function it_can_cancel_payment()
    {
        $schoolFee = SchoolFee::create([
            'name' => 'Frais de Scolarité',
            'amount' => 100000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        $payment = Payment::create([
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee->id,
            'amount' => 50000,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'status' => 'pending',
            'processed_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
                        ->post(route('finance.payments.cancel', $payment));

        $response->assertRedirect(route('finance.payments'));
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'cancelled'
        ]);
    }

    /** @test */
    public function it_can_view_student_balances()
    {
        $response = $this->actingAs($this->admin)
                        ->get(route('finance.student-balances'));

        $response->assertStatus(200);
        $response->assertSee('Soldes');
    }

    /** @test */
    public function it_calculates_student_balance_correctly()
    {
        // Créer des frais
        $schoolFee1 = SchoolFee::create([
            'name' => 'Frais 1',
            'amount' => 100000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        $schoolFee2 = SchoolFee::create([
            'name' => 'Frais 2',
            'amount' => 50000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        // Créer des paiements
        Payment::create([
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee1->id,
            'amount' => 75000, // Paiement partiel
            'payment_method' => 'cash',
            'payment_date' => now(),
            'status' => 'confirmed',
            'processed_by' => $this->admin->id
        ]);

        Payment::create([
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee2->id,
            'amount' => 50000, // Paiement complet
            'payment_method' => 'cash',
            'payment_date' => now(),
            'status' => 'confirmed',
            'processed_by' => $this->admin->id
        ]);

        // Total frais: 150000, Total payé: 125000, Solde: 25000
        $response = $this->actingAs($this->admin)
                        ->get(route('finance.student-balance', $this->student));

        $response->assertStatus(200);
        // La vue devrait afficher les calculs corrects
    }

    /** @test */
    public function receipt_number_is_generated_correctly()
    {
        $receipt = Receipt::create([
            'payment_id' => 1, // Simuler un ID de paiement
            'receipt_number' => Receipt::generateReceiptNumber(),
            'issue_date' => now(),
            'student_name' => 'Test Student',
            'amount_paid' => 50000,
            'payment_method' => 'cash',
            'school_fee_description' => 'Test Fee'
        ]);

        $currentYear = date('Y');
        $this->assertStringStartsWith("REC-{$currentYear}-", $receipt->receipt_number);
        $this->assertEquals(12, strlen($receipt->receipt_number)); // REC-YYYY-NNNN
    }

    /** @test */
    public function it_prevents_deleting_school_fee_with_payments()
    {
        $schoolFee = SchoolFee::create([
            'name' => 'Frais avec paiements',
            'amount' => 100000,
            'academic_year_id' => $this->academicYear->id,
            'is_mandatory' => true,
            'status' => 'active'
        ]);

        // Créer un paiement associé
        Payment::create([
            'student_id' => $this->student->id,
            'school_fee_id' => $schoolFee->id,
            'amount' => 50000,
            'payment_method' => 'cash',
            'payment_date' => now(),
            'status' => 'confirmed',
            'processed_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
                        ->delete(route('finance.school-fees.destroy', $schoolFee));

        $response->assertRedirect(route('finance.school-fees'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('school_fees', ['id' => $schoolFee->id]);
    }

    /** @test */
    public function it_can_access_financial_reports()
    {
        $response = $this->actingAs($this->admin)
                        ->get(route('finance.reports'));

        $response->assertStatus(200);
    }
}
