<?php

/*
 * ⚠️  FICHIER OBSOLÈTE - NE PLUS UTILISER
 *
 * Ce contrôleur monolithique a été refactorisé en controllers spécialisés :
 * 
 * ✅ Dashboard                → App\Http\Controllers\University\DashboardController
 * ✅ UFR Management          → App\Http\Controllers\University\UFRController  
 * ✅ Department Management   → App\Http\Controllers\University\DepartmentController
 * ✅ Program Management      → App\Http\Controllers\University\ProgramController
 * ✅ Semester Management     → App\Http\Controllers\University\SemesterController
 * ✅ Course Unit Management  → App\Http\Controllers\University\CourseUnitController
 * ✅ Course Elements Mgmt    → App\Http\Controllers\University\CourseUnitElementController
 *
 * 📊 MIGRATION STATUS : COMPLETED (January 2026)
 * 
 * 🗂️  ARCHITECTURE IMPACT :
 * • Séparation claire des responsabilités
 * • Réduction de 1358 lignes de code vers des controllers spécialisés
 * • Amélioration de la maintenabilité et testabilité
 * • Respect des principes SOLID et DDD
 *
 * 🚀 PROCHAINES ÉTAPES :
 * • Supprimer ce fichier après validation complète du nouveau système
 * • Nettoyer les imports et dépendances obsolètes
 * • Mettre à jour les tests unitaires
 *
 * 📝 MIGRATION LOG :
 * • 2026-01-07 : Migration complète vers controllers spécialisés
 * • 2026-01-07 : Mise à jour des routes web.php
 * • 2026-01-07 : Fichier marqué comme obsolète
 *
 * ⛔ Ce fichier sera supprimé dans la version suivante.
 *
 */

// L'ancien contenu du UniversityController reste temporairement pour référence
// mais toutes les routes ont été redirigées vers les nouveaux controllers

namespace App\Http\Controllers;

/**
 * @deprecated Utilisez les controllers spécialisés dans App\Http\Controllers\University\
 * @see App\Http\Controllers\University\DashboardController
 * @see App\Http\Controllers\University\UFRController
 * @see App\Http\Controllers\University\DepartmentController
 * @see App\Http\Controllers\University\ProgramController
 * @see App\Http\Controllers\University\SemesterController
 * @see App\Http\Controllers\University\CourseUnitController
 * @see App\Http\Controllers\University\CourseUnitElementController
 */
class UniversityController extends Controller
{
    public function __construct()
    {
        // Afficher un avertissement en mode développement
        if (config('app.env') === 'local') {
            \Log::warning('UniversityController obsolète utilisé. Migrez vers les controllers spécialisés.');
        }
    }

    /**
     * @deprecated Utilisez App\Http\Controllers\University\DashboardController::index()
     */
    public function dashboard()
    {
        abort(410, 'Cette méthode a été migrée vers University\DashboardController::index()');
    }

    /**
     * Méthodes UFR - @deprecated Utilisez App\Http\Controllers\University\UFRController
     */
    public function ufrs() { abort(410, 'Migré vers University\UFRController::index()'); }
    public function createUFR() { abort(410, 'Migré vers University\UFRController::create()'); }
    public function storeUFR() { abort(410, 'Migré vers University\UFRController::store()'); }
    public function showUFR() { abort(410, 'Migré vers University\UFRController::show()'); }
    public function editUFR() { abort(410, 'Migré vers University\UFRController::edit()'); }
    public function updateUFR() { abort(410, 'Migré vers University\UFRController::update()'); }
    public function destroyUFR() { abort(410, 'Migré vers University\UFRController::destroy()'); }

    /**
     * Méthodes Department - @deprecated Utilisez App\Http\Controllers\University\DepartmentController
     */
    public function departments() { abort(410, 'Migré vers University\DepartmentController::index()'); }
    public function createDepartment() { abort(410, 'Migré vers University\DepartmentController::create()'); }
    public function storeDepartment() { abort(410, 'Migré vers University\DepartmentController::store()'); }
    public function showDepartment() { abort(410, 'Migré vers University\DepartmentController::show()'); }
    public function editDepartment() { abort(410, 'Migré vers University\DepartmentController::edit()'); }
    public function updateDepartment() { abort(410, 'Migré vers University\DepartmentController::update()'); }
    public function destroyDepartment() { abort(410, 'Migré vers University\DepartmentController::destroy()'); }

    /**
     * Méthodes Program - @deprecated Utilisez App\Http\Controllers\University\ProgramController  
     */
    public function programs() { abort(410, 'Migré vers University\ProgramController::index()'); }
    public function createProgram() { abort(410, 'Migré vers University\ProgramController::create()'); }
    public function storeProgram() { abort(410, 'Migré vers University\ProgramController::store()'); }
    public function showProgram() { abort(410, 'Migré vers University\ProgramController::show()'); }
    public function editProgram() { abort(410, 'Migré vers University\ProgramController::edit()'); }
    public function updateProgram() { abort(410, 'Migré vers University\ProgramController::update()'); }
    public function destroyProgram() { abort(410, 'Migré vers University\ProgramController::destroy()'); }

    /**
     * Méthodes Semester - @deprecated Utilisez App\Http\Controllers\University\SemesterController
     */
    public function semesters() { abort(410, 'Migré vers University\SemesterController::index()'); }
    public function createSemester() { abort(410, 'Migré vers University\SemesterController::create()'); }
    public function storeSemester() { abort(410, 'Migré vers University\SemesterController::store()'); }
    public function showSemester() { abort(410, 'Migré vers University\SemesterController::show()'); }
    public function editSemester() { abort(410, 'Migré vers University\SemesterController::edit()'); }
    public function updateSemester() { abort(410, 'Migré vers University\SemesterController::update()'); }
    public function destroySemester() { abort(410, 'Migré vers University\SemesterController::destroy()'); }

    /**
     * Méthodes CourseUnit - @deprecated Utilisez App\Http\Controllers\University\CourseUnitController
     */
    public function courseUnits() { abort(410, 'Migré vers University\CourseUnitController::index()'); }
    public function createCourseUnit() { abort(410, 'Migré vers University\CourseUnitController::create()'); }
    public function storeCourseUnit() { abort(410, 'Migré vers University\CourseUnitController::store()'); }
    public function showCourseUnit() { abort(410, 'Migré vers University\CourseUnitController::show()'); }
    public function editCourseUnit() { abort(410, 'Migré vers University\CourseUnitController::edit()'); }
    public function updateCourseUnit() { abort(410, 'Migré vers University\CourseUnitController::update()'); }
    public function destroyCourseUnit() { abort(410, 'Migré vers University\CourseUnitController::destroy()'); }

    /**
     * Méthodes CourseUnitElement - @deprecated Utilisez App\Http\Controllers\University\CourseUnitElementController
     */
    public function showCourseUnitElements() { abort(410, 'Migré vers University\CourseUnitElementController::index()'); }
    public function createCourseUnitElement() { abort(410, 'Migré vers University\CourseUnitElementController::create()'); }
    public function storeCourseUnitElement() { abort(410, 'Migré vers University\CourseUnitElementController::store()'); }
    public function showCourseUnitElement() { abort(410, 'Migré vers University\CourseUnitElementController::show()'); }
    public function editCourseUnitElement() { abort(410, 'Migré vers University\CourseUnitElementController::edit()'); }
    public function updateCourseUnitElement() { abort(410, 'Migré vers University\CourseUnitElementController::update()'); }
    public function destroyCourseUnitElement() { abort(410, 'Migré vers University\CourseUnitElementController::destroy()'); }
}