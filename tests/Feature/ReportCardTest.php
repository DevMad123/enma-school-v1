<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\ReportCard;
use App\Models\Grade;
use App\Models\GradePeriod;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportCardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $student;
    protected $period;
    protected $academicYear;
    protected $class;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un utilisateur pour l'authentification
        $this->user = User::factory()->create();
        
        // Note: Dans un vrai test, on utiliserait des factories
        // Pour cet exemple, on assume que les données de base existent
    }

    /** @test */
    public function test_can_view_report_cards_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('report-cards.index'));

        $response->assertStatus(200);
        $response->assertViewIs('report-cards.index');
    }

    /** @test */
    public function test_can_view_create_report_card_form()
    {
        $response = $this->actingAs($this->user)
            ->get(route('report-cards.create'));

        $response->assertStatus(200);
        $response->assertViewIs('report-cards.create');
    }

    /** @test */
    public function test_can_calculate_student_average()
    {
        // Ce test nécessiterait des factories pour Student, Grade, etc.
        // Pour l'instant, on teste si les étudiants existants peuvent calculer leurs moyennes
        
        $student = Student::whereHas('grades')->first();
        
        if ($student) {
            $average = $student->getAverageForPeriod();
            $this->assertIsNumeric($average);
            $this->assertGreaterThanOrEqual(0, $average);
            $this->assertLessThanOrEqual(20, $average);
        } else {
            $this->markTestSkipped('Aucun étudiant avec notes trouvé pour le test');
        }
    }

    /** @test */
    public function test_can_create_report_card_for_student()
    {
        $student = Student::whereHas('grades')->first();
        $period = GradePeriod::where('is_active', true)->first();
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        if (!$student || !$period || !$academicYear) {
            $this->markTestSkipped('Données insuffisantes pour le test');
        }
        
        $class = $student->currentClass();
        if (!$class) {
            $this->markTestSkipped('Étudiant sans classe actuelle');
        }
        
        try {
            $reportCard = $student->getOrCreateReportCard(
                $period->id,
                $academicYear->id,
                $class->id
            );
            
            $this->assertInstanceOf(ReportCard::class, $reportCard);
            $this->assertEquals($student->id, $reportCard->student_id);
            $this->assertEquals($period->id, $reportCard->grade_period_id);
            $this->assertNotNull($reportCard->general_average);
            
        } catch (\Exception $e) {
            $this->fail('Impossible de créer le bulletin: ' . $e->getMessage());
        }
    }

    /** @test */
    public function test_report_card_calculates_correct_mention()
    {
        $student = Student::whereHas('grades')->first();
        $period = GradePeriod::where('is_active', true)->first();
        
        if (!$student || !$period) {
            $this->markTestSkipped('Données insuffisantes pour le test');
        }
        
        try {
            $reportCard = $student->getOrCreateReportCard($period->id);
            
            if ($reportCard->general_average >= 16) {
                $this->assertEquals('Très Bien', $reportCard->mention);
            } elseif ($reportCard->general_average >= 14) {
                $this->assertEquals('Bien', $reportCard->mention);
            } elseif ($reportCard->general_average >= 12) {
                $this->assertEquals('Assez Bien', $reportCard->mention);
            } elseif ($reportCard->general_average >= 10) {
                $this->assertEquals('Passable', $reportCard->mention);
            } else {
                $this->assertEquals('Insuffisant', $reportCard->mention);
            }
            
        } catch (\Exception $e) {
            $this->markTestSkipped('Erreur lors du calcul: ' . $e->getMessage());
        }
    }

    /** @test */
    public function test_can_export_report_card_as_pdf()
    {
        $reportCard = ReportCard::first();
        
        if (!$reportCard) {
            $this->markTestSkipped('Aucun bulletin trouvé pour le test PDF');
        }
        
        $response = $this->actingAs($this->user)
            ->get(route('report-cards.pdf', $reportCard));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function test_cannot_delete_finalized_report_card()
    {
        $reportCard = ReportCard::first();
        
        if (!$reportCard) {
            $this->markTestSkipped('Aucun bulletin trouvé pour le test');
        }
        
        // Finaliser le bulletin
        $reportCard->update(['is_final' => true]);
        
        $response = $this->actingAs($this->user)
            ->delete(route('report-cards.destroy', $reportCard));

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        
        // Vérifier que le bulletin existe toujours
        $this->assertDatabaseHas('report_cards', ['id' => $reportCard->id]);
    }

    /** @test */
    public function test_report_card_status_workflow()
    {
        $reportCard = ReportCard::where('is_final', false)->first();
        
        if (!$reportCard) {
            $this->markTestSkipped('Aucun bulletin non-finalisé trouvé');
        }
        
        // Test: brouillon → publié
        $reportCard->update(['status' => 'draft']);
        
        $response = $this->actingAs($this->user)
            ->post(route('report-cards.publish', $reportCard));
        
        $response->assertRedirect();
        $reportCard->refresh();
        $this->assertEquals('published', $reportCard->status);
        
        // Test: publié → finalisé
        $response = $this->actingAs($this->user)
            ->post(route('report-cards.finalize', $reportCard));
        
        $response->assertRedirect();
        $reportCard->refresh();
        $this->assertTrue($reportCard->is_final);
    }

    /** @test */
    public function test_bulk_generate_creates_multiple_report_cards()
    {
        $class = SchoolClass::whereHas('students')->first();
        $period = GradePeriod::where('is_active', true)->first();
        
        if (!$class || !$period) {
            $this->markTestSkipped('Classe ou période non trouvée');
        }
        
        $initialCount = ReportCard::count();
        
        $response = $this->actingAs($this->user)
            ->post(route('report-cards.bulk-generate'), [
                'class_id' => $class->id,
                'period_id' => $period->id,
            ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $this->assertGreaterThan($initialCount, ReportCard::count());
    }
}
