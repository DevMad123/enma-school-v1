<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;

class PreUniversityMiddleware
{
    /**
     * Vérification avancée des permissions pré-universitaires
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

        if (!$school->isPreUniversity()) {
            return redirect()->route('university.dashboard')
                ->with('error', 'Cette section est réservée aux établissements pré-universitaires');
        }

        // Super Admin a toujours accès - bypass toutes les autres vérifications
        if ($user->hasRole('super_admin')) {
            $request->merge([
                'pre_university_context' => [
                    'school' => $school,
                    'user_role' => $user->roles->pluck('name'),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'is_super_admin' => true,
                ]
            ]);
            return $next($request);
        }

        // Vérifications des permissions utilisateur pour les autres rôles
        $hasPreUniversityAccess = $this->checkPreUniversityPermissions($user);
        
        if (!$hasPreUniversityAccess) {
            return redirect()->route('dashboard')
                ->with('error', 'Accès non autorisé au module pré-universitaire');
        }

        // Ajouter le contexte pré-universitaire à la requête
        $request->merge([
            'pre_university_context' => [
                'school' => $school,
                'user_role' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'is_super_admin' => false,
            ]
        ]);

        return $next($request);
    }

    /**
     * Vérifier les permissions pré-universitaires de l'utilisateur
     */
    private function checkPreUniversityPermissions($user): bool
    {
        // Super admin a toujours accès (vérification redondante pour sécurité)
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Roles autorisés pour l'accès pré-universitaire
        $allowedRoles = ['admin', 'directeur', 'teacher', 'staff', 'secretary'];
        
        // Permissions spécifiques pour le pré-universitaire
        $allowedPermissions = [
            'manage_pre_university_system',
            'view_pre_university_dashboard',
            'manage_levels', 'view_levels',
            'manage_classes', 'view_classes', 
            'manage_subjects', 'view_subjects',
            'manage_students', 'view_students',
            'manage_enrollments', 'view_enrollments',
            'manage_teachers', 'view_teachers',
            'manage_assignments', 'view_assignments',
            // Permissions générales qui donnent accès
            'manage_school_governance',
            'manage_school_settings',
            'manage_academic_settings',
            'manage_financial_settings'
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