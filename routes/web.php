<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\TeacherAssignmentController;
use App\Http\Controllers\DashboardController;
// Dashboard Controllers - Phase 2 Refactoring
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Staff\StaffDashboardController; 
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Student\UniversityStudentDashboardController;
use App\Http\Controllers\Student\PreUniversityStudentDashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\Admin\AcademicYearController;
// MODULE A4 - Contrôleurs du personnel
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\UniversityTeacherController;
use App\Http\Controllers\Admin\TeacherAssignmentController as AdminTeacherAssignmentController;
// MODULE A5 - Contrôleur des paramètres pédagogiques
use App\Http\Controllers\Admin\PedagogicalSettingsController;
// MODULE A6 - Contrôleur de supervision et audits
use App\Http\Controllers\Admin\SupervisionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// DEBUG ROUTE - Pour vérifier les rôles utilisateur
Route::get('/debug-user', function () {
    $user = \App\Models\User::where('email', 'superadmin@enmaschool.com')->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found']);
    }
    
    return response()->json([
        'user' => $user->name,
        'email' => $user->email,
        'roles' => $user->getRoleNames()->toArray(),
        'has_super_admin' => $user->hasRole('super_admin'),
        'has_any_admin_role' => $user->hasAnyRole(['super_admin', 'admin', 'directeur']),
        'user_id' => $user->id,
        'permissions_count' => $user->getAllPermissions()->count(),
    ]);
})->middleware(['auth', 'verified']);

// ===================================================================
// DASHBOARD REFACTORED ROUTES - Phase 2 Architecture
// ===================================================================

// Dashboard principal et redirections intelligentes
Route::middleware(['auth', 'verified', 'school.context'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Route de redirection automatique vers le dashboard approprié
    Route::get('/redirect', [DashboardController::class, 'redirect'])->name('redirect');
    
    // Dashboard par défaut pour utilisateurs sans dashboard spécifique
    Route::get('/default', [DashboardController::class, 'defaultDashboard'])->name('default');
    
    // API pour obtenir les informations du dashboard courant
    Route::get('/current-info', [DashboardController::class, 'currentDashboardInfo'])->name('current.info');
});

// Dashboard Administration (super_admin, admin, directeur)
Route::middleware(['auth', 'verified', 'dashboard.access:admin'])->prefix('admin/dashboard')->name('admin.dashboard.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
    Route::get('/governance', [AdminDashboardController::class, 'governance'])->name('governance');
    Route::get('/supervision', [AdminDashboardController::class, 'supervision'])->name('supervision');
    Route::get('/statistics', [AdminDashboardController::class, 'statistics'])->name('statistics');
    Route::get('/quick-actions', [AdminDashboardController::class, 'quickActions'])->name('quick-actions');
    
    // API Routes pour données dynamiques
    Route::get('/api/stats', [AdminDashboardController::class, 'getStats'])->name('api.stats');
    Route::get('/api/activities', [AdminDashboardController::class, 'getRecentActivities'])->name('api.activities');
});

// Dashboard Personnel (staff, accountant, supervisor)
Route::middleware(['auth', 'verified', 'dashboard.access:staff'])->prefix('staff/dashboard')->name('staff.dashboard.')->group(function () {
    Route::get('/', [StaffDashboardController::class, 'index'])->name('index');
    Route::get('/financial', [StaffDashboardController::class, 'financial'])->name('financial');
    Route::get('/supervision', [StaffDashboardController::class, 'supervision'])->name('supervision');
    Route::get('/operations', [StaffDashboardController::class, 'operations'])->name('operations');
    
    // API Routes
    Route::get('/api/financial-data', [StaffDashboardController::class, 'getFinancialData'])->name('api.financial');
    Route::get('/api/operations-data', [StaffDashboardController::class, 'getOperationsData'])->name('api.operations');
});

// Dashboard Enseignant (teacher)
Route::middleware(['auth', 'verified', 'dashboard.access:teacher'])->prefix('teacher/dashboard')->name('teacher.dashboard.')->group(function () {
    Route::get('/', [TeacherDashboardController::class, 'index'])->name('index');
    Route::get('/schedule', [TeacherDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/evaluations', [TeacherDashboardController::class, 'evaluations'])->name('evaluations');
    Route::get('/classes', [TeacherDashboardController::class, 'classes'])->name('classes');
    Route::get('/pedagogical', [TeacherDashboardController::class, 'pedagogical'])->name('pedagogical');
    
    // API Routes
    Route::get('/api/schedule-data', [TeacherDashboardController::class, 'getScheduleData'])->name('api.schedule');
    Route::get('/api/classes-data', [TeacherDashboardController::class, 'getClassesData'])->name('api.classes');
});

// Dashboard Étudiant Universitaire
Route::middleware(['auth', 'verified', 'dashboard.access:university_student', 'university'])->prefix('student/university/dashboard')->name('student.university.dashboard.')->group(function () {
    Route::get('/', [UniversityStudentDashboardController::class, 'index'])->name('index');
    Route::get('/academic-path', [UniversityStudentDashboardController::class, 'academicPath'])->name('academic-path');
    Route::get('/grades', [UniversityStudentDashboardController::class, 'grades'])->name('grades');
    Route::get('/enrollment', [UniversityStudentDashboardController::class, 'enrollment'])->name('enrollment');
    Route::get('/documents', [UniversityStudentDashboardController::class, 'documents'])->name('documents');
    Route::get('/calendar', [UniversityStudentDashboardController::class, 'calendar'])->name('calendar');
    
    // API Routes
    Route::get('/api/ue-progress', [UniversityStudentDashboardController::class, 'getUEProgress'])->name('api.ue-progress');
    Route::get('/api/semester-data', [UniversityStudentDashboardController::class, 'getSemesterData'])->name('api.semester-data');
});

// Dashboard Étudiant Préuniversitaire
Route::middleware(['auth', 'verified', 'dashboard.access:preuniversity_student', 'pre_university'])->prefix('student/preuniversity/dashboard')->name('student.preuniversity.dashboard.')->group(function () {
    Route::get('/', [PreUniversityStudentDashboardController::class, 'index'])->name('index');
    Route::get('/bulletin', [PreUniversityStudentDashboardController::class, 'bulletin'])->name('bulletin');
    Route::get('/subjects', [PreUniversityStudentDashboardController::class, 'subjects'])->name('subjects');
    Route::get('/school-life', [PreUniversityStudentDashboardController::class, 'schoolLife'])->name('school-life');
    Route::get('/parent-communication', [PreUniversityStudentDashboardController::class, 'parentCommunication'])->name('parent-communication');
    Route::get('/homework', [PreUniversityStudentDashboardController::class, 'homework'])->name('homework');
    
    // API Routes
    Route::get('/api/grades-data', [PreUniversityStudentDashboardController::class, 'getGradesData'])->name('api.grades-data');
    Route::get('/api/bulletin-data', [PreUniversityStudentDashboardController::class, 'getBulletinData'])->name('api.bulletin-data');
});

// ===================================================================
// FIN DES ROUTES DASHBOARD REFACTORISÉES
// ===================================================================

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Routes pour les paramètres
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        
        // Informations de l'école (redirection vers gouvernance)
        Route::get('school', [SettingsController::class, 'school'])->name('school');
        Route::put('school', [SettingsController::class, 'updateSchool'])->name('school.update');
        
        // Années scolaires (redirection vers gouvernance)
        Route::get('years', [SettingsController::class, 'years'])->name('years');
        Route::post('years', [SettingsController::class, 'storeYear'])->name('years.store');
        
        // Système de notation (redirection vers gouvernance)
        Route::get('grading', [SettingsController::class, 'grading'])->name('grading');
        Route::post('grading', [SettingsController::class, 'updateGrading'])->name('grading.update');
        
        // Paramètres financiers
        Route::get('financial', [SettingsController::class, 'financial'])->name('financial');
        Route::post('financial', [SettingsController::class, 'updateFinancial'])->name('financial.update');
        
        // Paramètres système
        Route::get('system', [SettingsController::class, 'system'])->name('system');
        Route::post('system', [SettingsController::class, 'updateSystem'])->name('system.update');
        
        // Paramètres de notifications
        Route::get('notifications', [SettingsController::class, 'notifications'])->name('notifications');
        Route::post('notifications', [SettingsController::class, 'updateNotifications'])->name('notifications.update');
    });

    // Routes pour le module Utilisateurs & Sécurité avec rate limiting
    Route::middleware(['auth', 'rate.limit.custom:user_creation'])->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('{user}', [UserController::class, 'show'])->name('show');
        Route::get('{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('{user}', [UserController::class, 'update'])->name('update');
        Route::delete('{user}', [UserController::class, 'destroy'])->name('destroy');
        
        // Routes sensibles avec rate limiting strict
        Route::middleware(['rate.limit.custom:auth'])->group(function () {
            Route::post('{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        });
    });

    Route::middleware(['auth', 'rate.limit.custom:default'])->prefix('roles')->name('roles.')->group(function () {
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

    Route::middleware(['auth', 'rate.limit.custom:default'])->prefix('security')->name('security.')->group(function () {
        Route::get('settings', [SecurityController::class, 'settings'])->name('settings');
        Route::put('settings', [SecurityController::class, 'updateSettings'])->name('settings.update');
        Route::get('activity-log', [SecurityController::class, 'activityLog'])->name('activity-log');
        Route::get('audit', [SecurityController::class, 'securityAudit'])->name('audit');
    });

    // Routes pour les inscriptions
    Route::middleware(['auth', 'pre_university'])->group(function () {
        Route::resource('enrollments', EnrollmentController::class);
        Route::post('enrollments/{enrollment}/complete', [EnrollmentController::class, 'complete'])->name('enrollments.complete');
        Route::post('enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel'])->name('enrollments.cancel');
        Route::post('quick-enroll', [EnrollmentController::class, 'quickEnroll'])->name('enrollments.quick');
    });

    // Routes pour les affectations d'enseignants
    Route::middleware(['auth', 'pre_university'])->group(function () {
        Route::resource('teacher-assignments', TeacherAssignmentController::class);
        Route::get('teacher/{teacher}/assignments', [TeacherAssignmentController::class, 'teacherAssignments'])->name('teacher.assignments');
        Route::get('class/{class}/teachers', [TeacherAssignmentController::class, 'classTeachers'])->name('class.teachers');
        Route::post('quick-assign-teacher', [TeacherAssignmentController::class, 'quickAssign'])->name('teacher-assignments.quick');
    });

    // Routes pour les bulletins scolaires
    Route::middleware(['auth', 'pre_university'])->group(function () {
        Route::resource('report-cards', \App\Http\Controllers\ReportCardController::class);
        Route::post('report-cards/{reportCard}/recalculate', [\App\Http\Controllers\ReportCardController::class, 'recalculate'])->name('report-cards.recalculate');
        Route::post('report-cards/{reportCard}/publish', [\App\Http\Controllers\ReportCardController::class, 'publish'])->name('report-cards.publish');
        Route::post('report-cards/{reportCard}/finalize', [\App\Http\Controllers\ReportCardController::class, 'finalize'])->name('report-cards.finalize');
        Route::get('report-cards/{reportCard}/pdf', [\App\Http\Controllers\ReportCardController::class, 'exportPdf'])->name('report-cards.pdf');
        Route::post('report-cards/bulk-generate', [\App\Http\Controllers\ReportCardController::class, 'bulkGenerate'])->name('report-cards.bulk-generate');
    });

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

    // Routes pour le module académique (Structure Académique)
    Route::prefix('academic')->name('academic.')->middleware(['auth', 'pre_university'])->group(function () {
        // Gestion des niveaux
        Route::get('levels', [\App\Http\Controllers\AcademicController::class, 'levels'])->name('levels');
        Route::get('levels/create', [\App\Http\Controllers\AcademicController::class, 'createLevel'])->name('levels.create');
        Route::post('levels', [\App\Http\Controllers\AcademicController::class, 'storeLevel'])->name('levels.store');
        Route::get('levels/{level}', [\App\Http\Controllers\AcademicController::class, 'showLevel'])->name('levels.show');
        Route::get('levels/{level}/edit', [\App\Http\Controllers\AcademicController::class, 'editLevel'])->name('levels.edit');
        Route::put('levels/{level}', [\App\Http\Controllers\AcademicController::class, 'updateLevel'])->name('levels.update');
        Route::delete('levels/{level}', [\App\Http\Controllers\AcademicController::class, 'destroyLevel'])->name('levels.destroy');
        
        // Gestion des classes
        Route::get('classes', [\App\Http\Controllers\AcademicController::class, 'classes'])->name('classes');
        Route::get('classes/create', [\App\Http\Controllers\AcademicController::class, 'createClass'])->name('classes.create');
        Route::post('classes', [\App\Http\Controllers\AcademicController::class, 'storeClass'])->name('classes.store');
        Route::get('classes/{class}', [\App\Http\Controllers\AcademicController::class, 'showClass'])->name('classes.show');
        Route::get('classes/{class}/edit', [\App\Http\Controllers\AcademicController::class, 'editClass'])->name('classes.edit');
        Route::put('classes/{class}', [\App\Http\Controllers\AcademicController::class, 'updateClass'])->name('classes.update');
        Route::delete('classes/{class}', [\App\Http\Controllers\AcademicController::class, 'destroyClass'])->name('classes.destroy');
        
        // Gestion des matières
        Route::get('subjects', [\App\Http\Controllers\AcademicController::class, 'subjects'])->name('subjects');
        Route::get('subjects/create', [\App\Http\Controllers\AcademicController::class, 'createSubject'])->name('subjects.create');
        Route::post('subjects', [\App\Http\Controllers\AcademicController::class, 'storeSubject'])->name('subjects.store');
        Route::get('subjects/{subject}', [\App\Http\Controllers\AcademicController::class, 'showSubject'])->name('subjects.show');
        Route::get('subjects/{subject}/edit', [\App\Http\Controllers\AcademicController::class, 'editSubject'])->name('subjects.edit');
        Route::put('subjects/{subject}', [\App\Http\Controllers\AcademicController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('subjects/{subject}', [\App\Http\Controllers\AcademicController::class, 'destroySubject'])->name('subjects.destroy');
        
        // API endpoints pour les filtres dynamiques
        Route::get('api/cycles/{cycle}/levels', [\App\Http\Controllers\AcademicController::class, 'getLevelsByCycle'])->name('api.cycles.levels');
        Route::get('api/levels/{level}/classes', [\App\Http\Controllers\AcademicController::class, 'getClassesByLevel'])->name('api.levels.classes');
        Route::get('api/stats', [\App\Http\Controllers\AcademicController::class, 'getStats'])->name('api.stats');
    });

    // ==========================================
    // UNIVERSITÉ - GESTION UNIVERSITAIRE OPTIMISÉE
    // ==========================================
    Route::prefix('university')->name('university.')->middleware(['auth', 'university'])->group(function () {
        // Tableau de bord universitaire
        Route::get('/', [\App\Http\Controllers\UniversityController::class, 'dashboard'])->name('dashboard');

        // Resources optimisées avec nested routes
        Route::resource('ufrs', \App\Http\Controllers\UniversityController::class, [
            'names' => [
                'index' => 'ufrs.index',
                'create' => 'ufrs.create', 
                'store' => 'ufrs.store',
                'show' => 'ufrs.show',
                'edit' => 'ufrs.edit',
                'update' => 'ufrs.update',
                'destroy' => 'ufrs.destroy',
            ],
            'parameters' => ['ufrs' => 'ufr']
        ])->except(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
        
        // Routes UFR personnalisées
        Route::get('ufrs', [\App\Http\Controllers\UniversityController::class, 'ufrs'])->name('ufrs.index');
        Route::get('ufrs/create', [\App\Http\Controllers\UniversityController::class, 'createUFR'])->name('ufrs.create');
        Route::post('ufrs', [\App\Http\Controllers\UniversityController::class, 'storeUFR'])->name('ufrs.store');
        Route::get('ufrs/{ufr}', [\App\Http\Controllers\UniversityController::class, 'showUFR'])->name('ufrs.show');
        Route::get('ufrs/{ufr}/edit', [\App\Http\Controllers\UniversityController::class, 'editUFR'])->name('ufrs.edit');
        Route::put('ufrs/{ufr}', [\App\Http\Controllers\UniversityController::class, 'updateUFR'])->name('ufrs.update');
        Route::delete('ufrs/{ufr}', [\App\Http\Controllers\UniversityController::class, 'destroyUFR'])->name('ufrs.destroy');

        // Routes Départements personnalisées
        Route::get('departments', [\App\Http\Controllers\UniversityController::class, 'departments'])->name('departments.index');
        Route::get('departments/create', [\App\Http\Controllers\UniversityController::class, 'createDepartment'])->name('departments.create');
        Route::post('departments', [\App\Http\Controllers\UniversityController::class, 'storeDepartment'])->name('departments.store');
        Route::get('departments/{department}', [\App\Http\Controllers\UniversityController::class, 'showDepartment'])->name('departments.show');
        Route::get('departments/{department}/edit', [\App\Http\Controllers\UniversityController::class, 'editDepartment'])->name('departments.edit');
        Route::put('departments/{department}', [\App\Http\Controllers\UniversityController::class, 'updateDepartment'])->name('departments.update');
        Route::delete('departments/{department}', [\App\Http\Controllers\UniversityController::class, 'destroyDepartment'])->name('departments.destroy');

        // Routes Programmes personnalisées
        Route::get('programs', [\App\Http\Controllers\UniversityController::class, 'programs'])->name('programs.index');
        Route::get('programs/create', [\App\Http\Controllers\UniversityController::class, 'createProgram'])->name('programs.create');
        Route::post('programs', [\App\Http\Controllers\UniversityController::class, 'storeProgram'])->name('programs.store');
        Route::get('programs/{program}', [\App\Http\Controllers\UniversityController::class, 'showProgram'])->name('programs.show');
        Route::get('programs/{program}/edit', [\App\Http\Controllers\UniversityController::class, 'editProgram'])->name('programs.edit');
        Route::put('programs/{program}', [\App\Http\Controllers\UniversityController::class, 'updateProgram'])->name('programs.update');
        Route::delete('programs/{program}', [\App\Http\Controllers\UniversityController::class, 'destroyProgram'])->name('programs.destroy');

        // Routes Semestres avec hiérarchie optimisée
        Route::prefix('programs/{program}')->name('programs.')->group(function () {
            Route::get('semesters', [\App\Http\Controllers\UniversityController::class, 'semesters'])->name('semesters.index');
            Route::get('semesters/create', [\App\Http\Controllers\UniversityController::class, 'createSemester'])->name('semesters.create');
            Route::post('semesters', [\App\Http\Controllers\UniversityController::class, 'storeSemester'])->name('semesters.store');
            Route::get('semesters/{semester}', [\App\Http\Controllers\UniversityController::class, 'showSemester'])->name('semesters.show');
            Route::get('semesters/{semester}/edit', [\App\Http\Controllers\UniversityController::class, 'editSemester'])->name('semesters.edit');
            Route::put('semesters/{semester}', [\App\Http\Controllers\UniversityController::class, 'updateSemester'])->name('semesters.update');
            Route::delete('semesters/{semester}', [\App\Http\Controllers\UniversityController::class, 'destroySemester'])->name('semesters.destroy');
        });

        // Routes Semestres sans hiérarchie pour les templates existants
        Route::get('programs/{program}/semesters', [\App\Http\Controllers\UniversityController::class, 'semesters'])->name('programs.semesters.index');
        Route::get('programs/{program}/semesters/create', [\App\Http\Controllers\UniversityController::class, 'createSemester'])->name('programs.semesters.create');
        Route::post('programs/{program}/semesters', [\App\Http\Controllers\UniversityController::class, 'storeSemester'])->name('programs.semesters.store');
        Route::delete('programs/{program}/semesters/{semester}', [\App\Http\Controllers\UniversityController::class, 'destroySemester'])->name('semesters.destroy');

        // Routes Unités d'Enseignement optimisées
        Route::prefix('semesters/{semester}')->name('semesters.')->group(function () {
            Route::get('course-units', [\App\Http\Controllers\UniversityController::class, 'courseUnits'])->name('course-units.index');
            Route::get('course-units/create', [\App\Http\Controllers\UniversityController::class, 'createCourseUnit'])->name('course-units.create');
            Route::post('course-units', [\App\Http\Controllers\UniversityController::class, 'storeCourseUnit'])->name('course-units.store');
        });
        
        // Routes Course Units globales (sans semestre parent)
        Route::get('course-units/{courseUnit}', [\App\Http\Controllers\UniversityController::class, 'showCourseUnit'])->name('course-units.show');
        Route::get('course-units/{courseUnit}/edit', [\App\Http\Controllers\UniversityController::class, 'editCourseUnit'])->name('course-units.edit');
        Route::put('course-units/{courseUnit}', [\App\Http\Controllers\UniversityController::class, 'updateCourseUnit'])->name('course-units.update');
        Route::delete('course-units/{courseUnit}', [\App\Http\Controllers\UniversityController::class, 'destroyCourseUnit'])->name('course-units.destroy');

        // Routes ECUE (Éléments Constitutifs d'Unités d'Enseignement)
        Route::prefix('course-units/{courseUnit}')->name('course-units.')->group(function () {
            Route::get('elements', [\App\Http\Controllers\UniversityController::class, 'showCourseUnitElements'])->name('elements.index');
            Route::get('elements/create', [\App\Http\Controllers\UniversityController::class, 'createCourseUnitElement'])->name('elements.create');
            Route::post('elements', [\App\Http\Controllers\UniversityController::class, 'storeCourseUnitElement'])->name('elements.store');
            Route::get('elements/{element}', [\App\Http\Controllers\UniversityController::class, 'showCourseUnitElement'])->name('elements.show');
            Route::get('elements/{element}/edit', [\App\Http\Controllers\UniversityController::class, 'editCourseUnitElement'])->name('elements.edit');
            Route::put('elements/{element}', [\App\Http\Controllers\UniversityController::class, 'updateCourseUnitElement'])->name('elements.update');
            Route::delete('elements/{element}', [\App\Http\Controllers\UniversityController::class, 'destroyCourseUnitElement'])->name('elements.destroy');
        });

        // Routes Enseignants Universitaires - MODULE UNIVERSITAIRE A4
        Route::prefix('teachers')->name('teachers.')->group(function () {
            Route::get('/', [UniversityTeacherController::class, 'index'])->name('index');
            Route::get('create', [UniversityTeacherController::class, 'create'])->name('create');
            Route::post('/', [UniversityTeacherController::class, 'store'])->name('store');
            Route::get('{teacher}', [UniversityTeacherController::class, 'show'])->name('show');
            Route::get('{teacher}/edit', [UniversityTeacherController::class, 'edit'])->name('edit');
            Route::put('{teacher}', [UniversityTeacherController::class, 'update'])->name('update');
            Route::delete('{teacher}', [UniversityTeacherController::class, 'destroy'])->name('destroy');
            Route::post('{teacher}/toggle-status', [UniversityTeacherController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

    // ==========================================
    // MODULE A4 - GESTION DU PERSONNEL & AFFECTATIONS PÉDAGOGIQUES
    // ==========================================
    
    // Routes pour la gestion des enseignants
    Route::prefix('admin/teachers')->name('admin.teachers.')->middleware(['auth', 'pre_university'])->group(function () {
        Route::get('/', [TeacherController::class, 'index'])->name('index');
        Route::get('create', [TeacherController::class, 'create'])->name('create');
        Route::post('/', [TeacherController::class, 'store'])->name('store');
        Route::get('{teacher}', [TeacherController::class, 'show'])->name('show');
        Route::get('{teacher}/edit', [TeacherController::class, 'edit'])->name('edit');
        Route::put('{teacher}', [TeacherController::class, 'update'])->name('update');
        Route::delete('{teacher}', [TeacherController::class, 'destroy'])->name('destroy');
        Route::post('{teacher}/toggle-status', [TeacherController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('export', [TeacherController::class, 'export'])->name('export');
    });

    // Routes pour la gestion des affectations pédagogiques 
    Route::prefix('admin/assignments')->name('admin.assignments.')->middleware(['auth', 'pre_university'])->group(function () {
        Route::get('/', [AdminTeacherAssignmentController::class, 'index'])->name('index');
        Route::get('create', [AdminTeacherAssignmentController::class, 'create'])->name('create');
        Route::get('schedule', [AdminTeacherAssignmentController::class, 'schedule'])->name('schedule');
        Route::post('duplicate', [AdminTeacherAssignmentController::class, 'duplicate'])->name('duplicate');
        Route::post('/', [AdminTeacherAssignmentController::class, 'store'])->name('store');
        Route::get('{assignment}', [AdminTeacherAssignmentController::class, 'show'])->name('show');
        Route::get('{assignment}/edit', [AdminTeacherAssignmentController::class, 'edit'])->name('edit');
        Route::put('{assignment}', [AdminTeacherAssignmentController::class, 'update'])->name('update');
        Route::delete('{assignment}', [AdminTeacherAssignmentController::class, 'destroy'])->name('destroy');
        Route::post('{assignment}/toggle-status', [AdminTeacherAssignmentController::class, 'toggleStatus'])->name('toggle-status');
    });

    // Routes pour la gestion des étudiants (MODULE A3 - Structure académique)
    Route::prefix('admin/students')->name('admin.students.')->middleware(['auth', 'pre_university'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\StudentController::class, 'index'])->name('index');
        Route::get('create', [\App\Http\Controllers\Admin\StudentController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\StudentController::class, 'store'])->name('store');
        Route::get('{student}', [\App\Http\Controllers\Admin\StudentController::class, 'show'])->name('show');
        Route::get('{student}/edit', [\App\Http\Controllers\Admin\StudentController::class, 'edit'])->name('edit');
        Route::put('{student}', [\App\Http\Controllers\Admin\StudentController::class, 'update'])->name('update');
        Route::delete('{student}', [\App\Http\Controllers\Admin\StudentController::class, 'destroy'])->name('destroy');
        Route::post('{student}/toggle-status', [\App\Http\Controllers\Admin\StudentController::class, 'toggleStatus'])->name('toggle-status');
        
        // Routes utilitaires
        Route::get('export', [\App\Http\Controllers\Admin\StudentController::class, 'export'])->name('export');
        Route::post('import', [\App\Http\Controllers\Admin\StudentController::class, 'import'])->name('import');
        Route::get('api/by-class', [\App\Http\Controllers\Admin\StudentController::class, 'getByClass'])->name('api.by-class');
    });

    // Routes pour les bulletins scolaires
    Route::resource('report-cards', \App\Http\Controllers\ReportCardController::class);
    Route::post('report-cards/{reportCard}/recalculate', [\App\Http\Controllers\ReportCardController::class, 'recalculate'])->name('report-cards.recalculate');
    Route::post('report-cards/{reportCard}/publish', [\App\Http\Controllers\ReportCardController::class, 'publish'])->name('report-cards.publish');
    Route::post('report-cards/{reportCard}/finalize', [\App\Http\Controllers\ReportCardController::class, 'finalize'])->name('report-cards.finalize');

    // Routes Admin - MODULE A1 Gouvernance de l'établissement
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin.access'])->group(function () {
        // MODULE A1 - Écoles
        Route::resource('schools', \App\Http\Controllers\Admin\SchoolController::class)->except(['show', 'destroy']);
        Route::get('schools/{school}/settings', [\App\Http\Controllers\Admin\SchoolController::class, 'settings'])->name('schools.settings');
        Route::put('schools/{school}/settings', [\App\Http\Controllers\Admin\SchoolController::class, 'updateSettings'])->name('schools.settings.update');
        
        // MODULE A2 - Années académiques & Périodes
        Route::resource('academic-years', AcademicYearController::class);
        Route::patch('academic-years/{academicYear}/toggle-active', [AcademicYearController::class, 'toggleActive'])->name('academic-years.toggle-active');
        Route::patch('academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
        Route::get('academic-years/{academicYear}/periods', [AcademicYearController::class, 'managePeriods'])->name('academic-years.manage-periods');
        Route::post('academic-years/{academicYear}/generate-periods', [AcademicYearController::class, 'generatePeriods'])->name('academic-years.generate-periods');
        
        // Routes pour la gestion des périodes individuelles (si nécessaire)
        Route::prefix('academic-periods')->name('academic-periods.')->group(function () {
            Route::post('/', [AcademicYearController::class, 'storePeriod'])->name('store');
            Route::put('{period}', [AcademicYearController::class, 'updatePeriod'])->name('update');
            Route::delete('{period}', [AcademicYearController::class, 'destroyPeriod'])->name('destroy');
            Route::patch('{period}/toggle-active', [AcademicYearController::class, 'togglePeriodActive'])->name('toggle-active');
        });
        
        // MODULE A5 - Paramètres pédagogiques
        Route::prefix('pedagogy-settings')->name('pedagogy-settings.')->group(function () {
            Route::get('/', [PedagogicalSettingsController::class, 'index'])->name('index');
            Route::put('/global', [PedagogicalSettingsController::class, 'updateGlobal'])->name('global.update');
            Route::put('/level/{level}/threshold', [PedagogicalSettingsController::class, 'updateLevelThreshold'])->name('level.threshold');
            Route::put('/subject/{subject}/threshold', [PedagogicalSettingsController::class, 'updateSubjectThreshold'])->name('subject.threshold');
            Route::delete('/level/{level}/threshold', [PedagogicalSettingsController::class, 'resetLevelThreshold'])->name('level.threshold.reset');
            Route::delete('/subject/{subject}/threshold', [PedagogicalSettingsController::class, 'resetSubjectThreshold'])->name('subject.threshold.reset');
        });

        // MODULE A6 - Supervision & Audits
        Route::prefix('supervision')->name('supervision.')->group(function () {
            Route::get('/', [SupervisionController::class, 'index'])->name('index');
            Route::get('/teacher-activities', [SupervisionController::class, 'teacherActivities'])->name('teacher-activities');
            Route::get('/student-activities', [SupervisionController::class, 'studentActivities'])->name('student-activities');
            Route::get('/user-logs', [SupervisionController::class, 'userLogs'])->name('user-logs');
            Route::get('/chart-data', [SupervisionController::class, 'getDashboardChartData'])->name('chart-data');
        });
    });
});

require __DIR__.'/auth.php';
