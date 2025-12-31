<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Vérifier que l'utilisateur est connecté
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si aucun rôle spécifique n'est demandé, vérifier les rôles admin par défaut
        if (empty($roles)) {
            $roles = ['super_admin', 'admin', 'directeur'];
        }

        // Vérifier si l'utilisateur a un des rôles autorisés
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Si l'utilisateur n'a pas les permissions, retourner une erreur 403
        abort(403, 'Accès refusé. Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.');
    }
}
