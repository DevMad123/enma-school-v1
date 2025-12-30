<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Routes pour les paramètres
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        
        // Informations de l'école
        Route::get('school', [SettingsController::class, 'school'])->name('school');
        Route::put('school', [SettingsController::class, 'updateSchool'])->name('school.update');
        
        // Années scolaires
        Route::get('years', [SettingsController::class, 'years'])->name('years');
        Route::post('years', [SettingsController::class, 'storeYear'])->name('years.store');
        Route::patch('years/{year}/archive', [SettingsController::class, 'archiveYear'])->name('years.archive');
        
        // Système de notation
        Route::get('grading', [SettingsController::class, 'grading'])->name('grading');
        Route::post('grading', [SettingsController::class, 'updateGrading'])->name('grading.update');
        
        // Paramètres financiers
        Route::get('financial', [SettingsController::class, 'financial'])->name('financial');
        Route::post('financial', [SettingsController::class, 'updateFinancial'])->name('financial.update');
    });

    // Routes pour le module Utilisateurs & Sécurité (middleware permission temporairement désactivé)
    Route::middleware(['auth'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
    });

    Route::middleware(['auth'])->prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('index');
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('{role}', [RoleController::class, 'show'])->name('show');
        Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('{role}', [RoleController::class, 'update'])->name('update');
        Route::delete('{role}', [RoleController::class, 'destroy'])->name('destroy');
        Route::post('{role}/permissions', [RoleController::class, 'storePermission'])->name('permissions.store');
        Route::delete('{role}/permissions/{permission}', [RoleController::class, 'destroyPermission'])->name('permissions.destroy');
    });

    Route::middleware(['auth'])->prefix('security')->name('security.')->group(function () {
        Route::get('settings', [SecurityController::class, 'settings'])->name('settings');
        Route::put('settings', [SecurityController::class, 'updateSettings'])->name('settings.update');
        Route::get('activity-log', [SecurityController::class, 'activityLog'])->name('activity-log');
        Route::get('audit', [SecurityController::class, 'securityAudit'])->name('audit');
    });

    // Routes pour les inscriptions
    Route::resource('enrollments', EnrollmentController::class);
    Route::post('enrollments/{enrollment}/complete', [EnrollmentController::class, 'complete'])->name('enrollments.complete');
    Route::post('enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel'])->name('enrollments.cancel');
    Route::post('quick-enroll', [EnrollmentController::class, 'quickEnroll'])->name('enrollments.quick');

    // Routes pour les affectations d'enseignants
    Route::resource('teacher-assignments', TeacherAssignmentController::class);
    Route::get('teacher/{teacher}/assignments', [TeacherAssignmentController::class, 'teacherAssignments'])->name('teacher.assignments');
    Route::get('class/{class}/teachers', [TeacherAssignmentController::class, 'classTeachers'])->name('class.teachers');
    Route::post('quick-assign-teacher', [TeacherAssignmentController::class, 'quickAssign'])->name('teacher-assignments.quick');

    // Routes pour les bulletins scolaires
    Route::resource('report-cards', \App\Http\Controllers\ReportCardController::class);
    Route::post('report-cards/{reportCard}/recalculate', [\App\Http\Controllers\ReportCardController::class, 'recalculate'])->name('report-cards.recalculate');
    Route::post('report-cards/{reportCard}/publish', [\App\Http\Controllers\ReportCardController::class, 'publish'])->name('report-cards.publish');
    Route::post('report-cards/{reportCard}/finalize', [\App\Http\Controllers\ReportCardController::class, 'finalize'])->name('report-cards.finalize');
    Route::get('report-cards/{reportCard}/pdf', [\App\Http\Controllers\ReportCardController::class, 'exportPdf'])->name('report-cards.pdf');
    Route::post('report-cards/bulk-generate', [\App\Http\Controllers\ReportCardController::class, 'bulkGenerate'])->name('report-cards.bulk-generate');

    // Routes pour la gestion financière
    Route::prefix('finance')->name('finance.')->group(function () {
        // Tableau de bord financier
        Route::get('/', [\App\Http\Controllers\FinanceController::class, 'index'])->name('index');
        
        // Gestion des frais scolaires
        Route::prefix('school-fees')->name('school-fees.')->group(function () {
            Route::get('/', [\App\Http\Controllers\FinanceController::class, 'schoolFees'])->name('index');
            Route::get('create', [\App\Http\Controllers\FinanceController::class, 'createSchoolFee'])->name('create');
            Route::post('/', [\App\Http\Controllers\FinanceController::class, 'storeSchoolFee'])->name('store');
            Route::get('{schoolFee}/edit', [\App\Http\Controllers\FinanceController::class, 'editSchoolFee'])->name('edit');
            Route::put('{schoolFee}', [\App\Http\Controllers\FinanceController::class, 'updateSchoolFee'])->name('update');
            Route::delete('{schoolFee}', [\App\Http\Controllers\FinanceController::class, 'destroySchoolFee'])->name('destroy');
        });
        
        // Gestion des paiements
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\FinanceController::class, 'payments'])->name('index');
            Route::get('create', [\App\Http\Controllers\FinanceController::class, 'createPayment'])->name('create');
            Route::post('/', [\App\Http\Controllers\FinanceController::class, 'storePayment'])->name('store');
            Route::post('{payment}/confirm', [\App\Http\Controllers\FinanceController::class, 'confirmPayment'])->name('confirm');
            Route::post('{payment}/cancel', [\App\Http\Controllers\FinanceController::class, 'cancelPayment'])->name('cancel');
        });
        
        // Consultation des soldes
        Route::get('student-balances', [\App\Http\Controllers\FinanceController::class, 'studentBalances'])->name('student-balances');
        Route::get('students/{student}/balance', [\App\Http\Controllers\FinanceController::class, 'studentBalance'])->name('student-balance');
        
        // Reçus
        Route::prefix('receipts')->name('receipts.')->group(function () {
            Route::get('{receipt}/download', [\App\Http\Controllers\FinanceController::class, 'downloadReceipt'])->name('download');
        });
        
        // Rapports
        Route::get('reports', [\App\Http\Controllers\FinanceController::class, 'generateReport'])->name('reports');
    });
});

require __DIR__.'/auth.php';
