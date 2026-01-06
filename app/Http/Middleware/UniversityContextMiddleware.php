<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SchoolContextService;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware pour vérifier le contexte universitaire
 * 
 * Ce middleware s'assure que l'utilisateur est dans un contexte
 * d'établissement universitaire pour accéder aux fonctionnalités spécifiques.
 * 
 * @package App\Http\Middleware
 * @author EnmaSchool Core Team
 * @version 1.0
 * @since 2026-01-06
 */
class UniversityContextMiddleware
{
    /**
     * Service de contexte école
     * 
     * @var SchoolContextService
     */
    protected SchoolContextService $schoolContextService;

    /**
     * Constructeur
     * 
     * @param SchoolContextService $schoolContextService
     */
    public function __construct(SchoolContextService $schoolContextService)
    {
        $this->schoolContextService = $schoolContextService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Vérifier le contexte école courant
        $currentSchoolType = $this->schoolContextService->getCurrentSchoolType();

        // Si pas de contexte défini, essayer de déterminer depuis l'utilisateur
        if (!$currentSchoolType) {
            $userSchool = $this->schoolContextService->getSchoolForUser($user);
            $currentSchoolType = $userSchool?->type;
        }

        // Vérifier que le contexte est universitaire
        if ($currentSchoolType !== 'university') {
            // Rediriger vers le dashboard approprié ou afficher une erreur
            return redirect()->route('dashboard')
                ->with('error', 'Accès réservé aux établissements universitaires.');
        }

        return $next($request);
    }
}