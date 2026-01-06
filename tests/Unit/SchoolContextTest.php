<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SchoolContextService;
use App\Models\User;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

/**
 * Tests unitaires pour le système de contexte école
 * 
 * @package Tests\Unit
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class SchoolContextTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Instance du service de contexte école
     * 
     * @var SchoolContextService
     */
    protected SchoolContextService $schoolContextService;

    /**
     * École de test
     * 
     * @var School
     */
    protected School $school;

    /**
     * Utilisateur de test
     * 
     * @var User
     */
    protected User $user;

    /**
     * Configuration initiale pour chaque test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->schoolContextService = app(SchoolContextService::class);

        // Créer une école de test
        $this->school = School::factory()->create([
            'name' => 'École de Test',
            'type' => 'university',
            'is_active' => true,
        ]);

        // Créer un utilisateur de test
        $this->user = User::factory()->create([
            'name' => 'Utilisateur Test',
            'email' => 'test@enmaschool.com',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_user_can_be_assigned_to_school()
    {
        // Assigner l'école à l'utilisateur
        $this->schoolContextService->setUserSchoolContext($this->user, $this->school);

        // Vérifier l'assignation
        $this->assertEquals($this->school->id, $this->user->fresh()->school_id);
        
        // Vérifier que l'école est récupérable
        $retrievedSchool = $this->schoolContextService->getSchoolForUser($this->user);
        $this->assertInstanceOf(School::class, $retrievedSchool);
        $this->assertEquals($this->school->id, $retrievedSchool->id);
    }

    /** @test */
    public function test_user_cannot_be_assigned_to_inactive_school()
    {
        // Désactiver l'école
        $this->school->update(['is_active' => false]);

        // Tenter d'assigner l'école à l'utilisateur doit lever une exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("L'école 'École de Test' n'est pas active.");

        $this->schoolContextService->setUserSchoolContext($this->user, $this->school);
    }

    /** @test */
    public function test_validate_school_access_with_active_user_and_school()
    {
        // Utilisateur actif + École active = accès autorisé
        $this->assertTrue(
            $this->schoolContextService->validateSchoolAccess($this->user, $this->school)
        );
    }

    /** @test */
    public function test_validate_school_access_with_inactive_user()
    {
        // Désactiver l'utilisateur
        $this->user->update(['is_active' => false]);

        // L'accès doit être refusé
        $this->assertFalse(
            $this->schoolContextService->validateSchoolAccess($this->user, $this->school)
        );
    }

    /** @test */
    public function test_validate_school_access_with_inactive_school()
    {
        // Désactiver l'école
        $this->school->update(['is_active' => false]);

        // L'accès doit être refusé
        $this->assertFalse(
            $this->schoolContextService->validateSchoolAccess($this->user, $this->school)
        );
    }

    /** @test */
    public function test_get_current_school_context_with_authenticated_user()
    {
        // Assigner l'école à l'utilisateur
        $this->user->update(['school_id' => $this->school->id]);

        // Simuler l'authentification
        Auth::login($this->user);

        // Récupérer le contexte école courant
        $currentSchool = $this->schoolContextService->getCurrentSchoolContext();

        $this->assertInstanceOf(School::class, $currentSchool);
        $this->assertEquals($this->school->id, $currentSchool->id);
    }

    /** @test */
    public function test_get_current_school_context_without_authenticated_user()
    {
        // S'assurer qu'aucun utilisateur n'est connecté
        Auth::logout();

        // Le contexte école doit être null
        $currentSchool = $this->schoolContextService->getCurrentSchoolContext();
        $this->assertNull($currentSchool);
    }

    /** @test */
    public function test_has_valid_school_context_with_valid_user()
    {
        // Assigner l'école à l'utilisateur et l'authentifier
        $this->user->update(['school_id' => $this->school->id]);
        Auth::login($this->user);

        // Le contexte doit être valide
        $this->assertTrue($this->schoolContextService->hasValidSchoolContext());
    }

    /** @test */
    public function test_has_valid_school_context_without_school()
    {
        // Utilisateur connecté sans école assignée
        Auth::login($this->user);

        // Le contexte ne doit pas être valide
        $this->assertFalse($this->schoolContextService->hasValidSchoolContext());
    }

    /** @test */
    public function test_school_type_detection()
    {
        // École universitaire
        $this->user->update(['school_id' => $this->school->id]);
        Auth::login($this->user);

        $this->assertEquals('university', $this->schoolContextService->getCurrentSchoolType());
        $this->assertTrue($this->schoolContextService->isUniversityContext());
        $this->assertFalse($this->schoolContextService->isPreUniversityContext());

        // École pré-universitaire
        $this->school->update(['type' => 'pre_university']);
        $this->schoolContextService->resetContext(); // Reset cache

        $this->assertEquals('pre_university', $this->schoolContextService->getCurrentSchoolType());
        $this->assertFalse($this->schoolContextService->isUniversityContext());
        $this->assertTrue($this->schoolContextService->isPreUniversityContext());
    }

    /** @test */
    public function test_get_accessible_schools_for_user()
    {
        // Créer une école supplémentaire
        $school2 = School::factory()->create([
            'name' => 'École 2',
            'type' => 'pre_university',
            'is_active' => true,
        ]);

        // Créer une école inactive
        $inactiveSchool = School::factory()->create([
            'name' => 'École Inactive',
            'is_active' => false,
        ]);

        // Récupérer les écoles accessibles (pour V1 : toutes les écoles actives)
        $accessibleSchools = $this->schoolContextService->getAccessibleSchoolsForUser($this->user);

        // Vérifier que seules les écoles actives sont retournées
        $this->assertCount(2, $accessibleSchools);
        $this->assertTrue($accessibleSchools->contains('id', $this->school->id));
        $this->assertTrue($accessibleSchools->contains('id', $school2->id));
        $this->assertFalse($accessibleSchools->contains('id', $inactiveSchool->id));
    }

    /** @test */
    public function test_reset_context()
    {
        // Configurer un contexte
        $this->user->update(['school_id' => $this->school->id]);
        Auth::login($this->user);

        // Vérifier que le contexte est configuré
        $this->assertNotNull($this->schoolContextService->getCurrentSchoolContext());

        // Réinitialiser le contexte
        $this->schoolContextService->resetContext();

        // Le contexte doit être réinitialisé (mais l'utilisateur reste connecté)
        // Le contexte sera re-initialisé au prochain appel
        $newContext = $this->schoolContextService->getCurrentSchoolContext();
        $this->assertInstanceOf(School::class, $newContext);
        $this->assertEquals($this->school->id, $newContext->id);
    }

    /** @test */
    public function test_user_model_school_relationship()
    {
        // Tester la relation au niveau du modèle User
        $this->user->update(['school_id' => $this->school->id]);

        $this->assertInstanceOf(School::class, $this->user->fresh()->school);
        $this->assertEquals($this->school->id, $this->user->school->id);
    }

    /** @test */
    public function test_user_has_school_access_method()
    {
        // Assigner l'école à l'utilisateur
        $this->user->update(['school_id' => $this->school->id]);

        // L'utilisateur doit avoir accès à son école
        $this->assertTrue($this->user->hasSchoolAccess($this->school));

        // Créer une autre école
        $otherSchool = School::factory()->create(['is_active' => true]);

        // L'utilisateur ne doit pas avoir accès à une autre école
        $this->assertFalse($this->user->hasSchoolAccess($otherSchool));
    }

    /** @test */
    public function test_user_get_school_context_method()
    {
        // Utilisateur sans école
        $this->assertNull($this->user->getSchoolContext());

        // Assigner une école active
        $this->user->update(['school_id' => $this->school->id]);
        $this->assertInstanceOf(School::class, $this->user->fresh()->getSchoolContext());

        // École inactive
        $this->school->update(['is_active' => false]);
        $this->assertNull($this->user->fresh()->getSchoolContext());
    }

    /** @test */
    public function test_school_model_context_methods()
    {
        // Tester getForUser
        $this->user->update(['school_id' => $this->school->id]);
        $schoolForUser = School::getForUser($this->user);
        $this->assertEquals($this->school->id, $schoolForUser->id);

        // Tester getCurrentContext avec injection
        app()->instance('current_school', $this->school);
        $currentContext = School::getCurrentContext();
        $this->assertEquals($this->school->id, $currentContext->id);

        // Tester getDefaultActiveSchool
        $defaultSchool = School::getDefaultActiveSchool();
        $this->assertInstanceOf(School::class, $defaultSchool);
    }
}