<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;

class UniversityMiddleware
{
    /**
     * Vérification avancée des permissions universitaires
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        $school = School::getActiveSchool();

        // Vérifications de base
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Authentification requise');
        }

        if (!$school) {
            return redirect()->route('dashboard')
                ->with('error', 'Aucun établissement actif trouvé');
        }

        if (!$school->isUniversity()) {
            return redirect()->route('academic.levels')
                ->with('error', 'Cette section est réservée aux établissements universitaires');
        }

        // Super Admin a toujours accès - bypass toutes les autres vérifications
        if ($user->hasRole('super_admin')) {
            $request->merge([
                'university_context' => [
                    'school' => $school,
                    'user_role' => $user->roles->pluck('name'),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'is_super_admin' => true,
                ]
            ]);
            return $next($request);
        }

        // Vérifications des permissions utilisateur pour les autres rôles
        $hasUniversityAccess = $this->checkUniversityPermissions($user);
        
        if (!$hasUniversityAccess) {
            return redirect()->route('dashboard')
                ->with('error', 'Accès non autorisé au module universitaire');
        }

        // Ajouter le contexte universitaire à la requête
        $request->merge([
            'university_context' => [
                'school' => $school,
                'user_role' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'is_super_admin' => false,
            ]
        ]);

        return $next($request);
    }

    /**
     * Vérifier les permissions universitaires de l'utilisateur
     */
    private function checkUniversityPermissions($user): bool
    {
        // Super admin a toujours accès (vérification redondante pour sécurité)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Roles autorisés pour l'accès universitaire (noms corrects du système)
        $allowedRoles = ['admin', 'directeur', 'teacher', 'staff'];
        
        // Permissions spécifiques pour l'universitaire (mises à jour)
        $allowedPermissions = [
            'manage_university_system',
            'view_university_dashboard',
            'manage_ufrs', 'view_ufrs',
            'manage_departments', 'view_departments', 
            'manage_programs', 'view_programs',
            'manage_semesters', 'view_semesters',
            'manage_course_units', 'view_course_units',
            // Permissions générales qui donnent accès
            'manage_school_governance',
            'manage_school_settings',
            'manage_academic_settings'
        ];

        // Vérifier les rôles
        if ($user->hasAnyRole($allowedRoles)) {
            return true;
        }

        // Vérifier les permissions spécifiques
        if ($user->hasAnyPermission($allowedPermissions)) {
            return true;
        }

        return false;
    }
}