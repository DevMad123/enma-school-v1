<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademicDashboardController extends Controller
{
    /**
     * Redirection intelligente vers le dashboard approprié selon le contexte
     */
    public function redirect(Request $request): RedirectResponse
    {
        $context = $request->attributes->get('educational_context');
        
        if (!$context) {
            return redirect()->route('dashboard')->with('error', 'Contexte éducatif non défini');
        }

        return match($context->school_type) {
            'preuniversity' => redirect()->route('academic.preuniversity.dashboard.index'),
            'university' => redirect()->route('academic.university.dashboard.index'),
            default => redirect()->route('dashboard')->with('error', 'Type d\'établissement non reconnu')
        };
    }
}