<?php

/*
 * ✅ CONTRÔLEUR MIGRÉ ET NETTOYÉ
 *
 * Ce contrôleur a été refactorisé et ses fonctionnalités ont été migrées vers des controllers spécialisés.
 * Les routes utilisent maintenant les nouveaux controllers dans le namespace University.
 *
 * ✅ CONTROLLERS SPÉCIALISÉS ACTIFS :
 * • App\Http\Controllers\University\DashboardController
 * • App\Http\Controllers\University\UFRController  
 * • App\Http\Controllers\University\DepartmentController
 * • App\Http\Controllers\University\ProgramController
 * • App\Http\Controllers\University\SemesterController
 * • App\Http\Controllers\University\CourseUnitController
 * • App\Http\Controllers\University\CourseUnitElementController
 *
 * 🎯 STATUT : NETTOYÉ - PRÊT POUR SUPPRESSION
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

/**
 * @deprecated Ce contrôleur a été migré vers des controllers spécialisés.
 * Toutes les fonctionnalités sont maintenant dans le namespace App\Http\Controllers\University.
 */
class UniversityController extends Controller
{
    /**
     * Redirection vers le nouveau dashboard universitaire
     * Cette méthode sera supprimée une fois toutes les références mises à jour.
     */
    public function dashboard(Request $request): RedirectResponse
    {
        return redirect()->route('university.dashboard')
            ->with('info', 'Redirection automatique vers le nouveau dashboard universitaire');
    }
}

