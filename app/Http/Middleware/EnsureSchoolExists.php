<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\School;

class EnsureSchoolExists
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Liste des routes qui ne nécessitent pas d'école configurée
        $exemptRoutes = [
            'admin.schools.*',
            'login',
            'logout',
            'password.*',
            'register',
            'verification.*',
            'profile.*',
            'settings.*', // Permettre l'accès aux paramètres généraux
        ];

        // Vérifier si la route actuelle est exemptée
        foreach ($exemptRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Vérifier si une école active existe
        $school = School::getActiveSchool();
        
        if (!$school && auth()->check()) {
            // Si l'utilisateur a les permissions pour créer une école, rediriger vers la création
            if (auth()->user()->hasAnyPermission(['manage_school_governance', 'manage_school_info'])) {
                return redirect()->route('admin.schools.create')
                    ->with('info', 'Veuillez d\'abord configurer votre établissement pour accéder à cette fonctionnalité.');
            }
            
            // Sinon, afficher un message d'erreur et rediriger vers le dashboard
            return redirect()->route('dashboard')
                ->with('warning', 'L\'établissement n\'est pas encore configuré. Contactez votre administrateur.');
        }

        return $next($request);
    }
}
