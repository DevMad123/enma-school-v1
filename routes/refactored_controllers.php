<?php

/**
 * Routes pour les contrôleurs universitaires refactorisés
 * 
 * Organisées par domaine selon l'architecture DDD:
 * - University/ProgramController
 * - University/UFRController  
 * - University/DepartmentController
 * - Academic/ClassController
 * - Academic/SubjectController
 * - Academic/LevelController
 */

use App\Http\Controllers\University\ProgramController;
use App\Http\Controllers\University\UFRController;
use App\Http\Controllers\University\DepartmentController;
use App\Http\Controllers\Academic\ClassController;
use App\Http\Controllers\Academic\SubjectController;
use App\Http\Controllers\Academic\LevelController;
use Illuminate\Support\Facades\Route;

// ===========================================
// ROUTES UNIVERSITAIRES (LMD)
// ===========================================

Route::prefix('university')->name('university.')->group(function () {
    
    // Gestion des UFR
    Route::controller(UFRController::class)->prefix('ufrs')->name('ufrs.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{ufr}', 'show')->name('show');
        Route::get('{ufr}/edit', 'edit')->name('edit');
        Route::put('{ufr}', 'update')->name('update');
        Route::delete('{ufr}', 'destroy')->name('destroy');
        Route::patch('{ufr}/toggle-status', 'toggleStatus')->name('toggle-status');
        Route::get('export', 'export')->name('export');
    });

    // Gestion des Départements
    Route::controller(DepartmentController::class)->prefix('departments')->name('departments.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{department}', 'show')->name('show');
        Route::get('{department}/edit', 'edit')->name('edit');
        Route::put('{department}', 'update')->name('update');
        Route::delete('{department}', 'destroy')->name('destroy');
        Route::patch('{department}/toggle-status', 'toggleStatus')->name('toggle-status');
        Route::post('{department}/assign-head', 'assignHead')->name('assign-head');
        Route::get('{department}/statistics', 'statistics')->name('statistics');
    });

    // Gestion des Programmes
    Route::controller(ProgramController::class)->prefix('programs')->name('programs.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{program}', 'show')->name('show');
        Route::get('{program}/edit', 'edit')->name('edit');
        Route::put('{program}', 'update')->name('update');
        Route::delete('{program}', 'destroy')->name('destroy');
        Route::post('{program}/validate-lmd', 'validateLMD')->name('validate-lmd');
        Route::post('{program}/duplicate', 'duplicate')->name('duplicate');
        Route::get('export', 'export')->name('export');
        Route::get('statistics', 'statistics')->name('statistics');
    });

});

// ===========================================
// ROUTES ACADÉMIQUES (PRÉUNIVERSITAIRE)
// ===========================================

Route::prefix('academic')->name('academic.')->group(function () {
    
    // Gestion des Niveaux
    Route::controller(LevelController::class)->prefix('levels')->name('levels.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{level}', 'show')->name('show');
        Route::get('{level}/edit', 'edit')->name('edit');
        Route::put('{level}', 'update')->name('update');
        Route::delete('{level}', 'destroy')->name('destroy');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::get('validate-structure', 'validateStructure')->name('validate-structure');
        Route::post('apply-recommended', 'applyRecommendedStructure')->name('apply-recommended');
        Route::get('export', 'export')->name('export');
        Route::get('cycle-statistics', 'statisticsByCycle')->name('cycle-statistics');
        Route::get('student-flow', 'analyzeStudentFlow')->name('student-flow');
        Route::get('compare', 'compareWithOthers')->name('compare');
    });

    // Gestion des Classes
    Route::controller(ClassController::class)->prefix('classes')->name('classes.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{schoolClass}', 'show')->name('show');
        Route::get('{schoolClass}/edit', 'edit')->name('edit');
        Route::put('{schoolClass}', 'update')->name('update');
        Route::delete('{schoolClass}', 'destroy')->name('destroy');
        
        // Gestion des inscriptions
        Route::get('{schoolClass}/enrollments', 'manageEnrollments')->name('manage-enrollments');
        Route::post('{schoolClass}/add-student', 'addStudent')->name('add-student');
        Route::delete('{schoolClass}/remove-student', 'removeStudent')->name('remove-student');
        Route::post('{schoolClass}/close-enrollments', 'closeEnrollments')->name('close-enrollments');
        Route::post('{schoolClass}/reopen-enrollments', 'reopenEnrollments')->name('reopen-enrollments');
        
        // Fonctionnalités avancées
        Route::post('{schoolClass}/duplicate', 'duplicate')->name('duplicate');
        Route::get('{schoolClass}/schedule', 'schedule')->name('schedule');
        Route::get('{schoolClass}/export-students', 'exportStudents')->name('export-students');
        Route::get('{schoolClass}/progression', 'progression')->name('progression');
    });

    // Gestion des Matières
    Route::controller(SubjectController::class)->prefix('subjects')->name('subjects.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{subject}', 'show')->name('show');
        Route::get('{subject}/edit', 'edit')->name('edit');
        Route::put('{subject}', 'update')->name('update');
        Route::delete('{subject}', 'destroy')->name('destroy');
        
        // Gestion des enseignants
        Route::post('{subject}/assign-teacher', 'assignTeacher')->name('assign-teacher');
        Route::delete('{subject}/remove-teacher', 'removeTeacher')->name('remove-teacher');
        
        // Fonctionnalités avancées
        Route::post('{subject}/duplicate', 'duplicate')->name('duplicate');
        Route::post('import', 'import')->name('import');
        Route::get('export', 'export')->name('export');
        Route::post('bulk-update-coefficients', 'bulkUpdateCoefficients')->name('bulk-update-coefficients');
        Route::get('workload-analysis', 'analyzeWorkload')->name('workload-analysis');
        Route::get('statistics', 'statistics')->name('statistics');
        Route::get('pedagogical-report', 'pedagogicalReport')->name('pedagogical-report');
    });

});

// ===========================================
// REDIRECTIONS POUR COMPATIBILITÉ
// ===========================================

/**
 * Routes de redirection pour maintenir la compatibilité avec l'ancien système
 * À supprimer après migration complète des vues
 */
Route::redirect('/university', '/university/programs');
Route::redirect('/academic', '/academic/levels');

// Redirection des anciennes routes monolithiques vers les nouvelles
Route::get('/university-old/{any}', function () {
    return redirect('/university/programs')->with('info', 'Interface mise à jour. Vous avez été redirigé.');
})->where('any', '.*');

Route::get('/academic-old/{any}', function () {
    return redirect('/academic/levels')->with('info', 'Interface mise à jour. Vous avez été redirigé.');
})->where('any', '.*');