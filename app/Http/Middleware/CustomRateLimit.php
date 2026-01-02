<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CustomRateLimit
{
    /**
     * Gérer une demande entrante avec rate limiting adaptatif
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $limits = $this->getLimits($type);
        $identifier = $this->getIdentifier($request);
        
        foreach ($limits as $window => $maxAttempts) {
            $key = "rate_limit:{$type}:{$identifier}:{$window}";
            $attempts = Cache::get($key, 0);
            
            if ($attempts >= $maxAttempts) {
                $this->logRateLimitExceeded($request, $type, $window);
                return $this->buildRateLimitResponse($window);
            }
            
            // Incrémenter le compteur
            Cache::put($key, $attempts + 1, $this->getWindowInSeconds($window));
        }
        
        return $next($request);
    }

    /**
     * Obtenir les limites selon le type d'opération
     */
    private function getLimits(string $type): array
    {
        $baseLimits = [
            'auth' => [
                '1min' => 5,    // 5 tentatives par minute
                '15min' => 15,  // 15 tentatives par 15 minutes
                '1hour' => 50   // 50 tentatives par heure
            ],
            'user_creation' => [
                '1min' => 2,    // 2 créations par minute
                '1hour' => 10,  // 10 créations par heure
                '1day' => 50    // 50 créations par jour
            ],
            'financial' => [
                '1min' => 3,    // 3 opérations financières par minute
                '15min' => 20,  // 20 par 15 minutes
                '1hour' => 100  // 100 par heure
            ],
            'password_reset' => [
                '1min' => 1,    // 1 reset par minute
                '1hour' => 5,   // 5 par heure
                '1day' => 10    // 10 par jour
            ],
            'api' => [
                '1min' => 60,   // 60 requêtes API par minute
                '1hour' => 1000 // 1000 par heure
            ],
            'default' => [
                '1min' => 10,   // 10 requêtes par minute
                '1hour' => 200  // 200 par heure
            ]
        ];

        // Ajuster les limites selon le rôle utilisateur
        $limits = $baseLimits[$type] ?? $baseLimits['default'];
        
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasRole(['super_admin', 'admin'])) {
                // Les admins ont des limites plus élevées
                foreach ($limits as $window => $limit) {
                    $limits[$window] = $limit * 3;
                }
            } elseif ($user->hasRole('teacher')) {
                // Les enseignants ont des limites légèrement plus élevées
                foreach ($limits as $window => $limit) {
                    $limits[$window] = $limit * 1.5;
                }
            }
        }

        return $limits;
    }

    /**
     * Obtenir l'identifiant unique pour le rate limiting
     */
    private function getIdentifier(Request $request): string
    {
        if (Auth::check()) {
            return 'user_' . Auth::id();
        }
        
        // Pour les utilisateurs non connectés, utiliser IP + User-Agent
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? '');
        
        return "guest_{$ip}_{$userAgent}";
    }

    /**
     * Convertir la fenêtre en secondes
     */
    private function getWindowInSeconds(string $window): int
    {
        return match($window) {
            '1min' => 60,
            '15min' => 900,
            '1hour' => 3600,
            '1day' => 86400,
            default => 3600
        };
    }

    /**
     * Logger les dépassements de limite
     */
    private function logRateLimitExceeded(Request $request, string $type, string $window): void
    {
        try {
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                \Spatie\Activitylog\Models\Activity::create([
                    'log_name' => 'security',
                    'description' => 'Rate limit dépassé',
                    'causer_type' => Auth::check() ? get_class(Auth::user()) : null,
                    'causer_id' => Auth::id(),
                    'properties' => [
                        'type' => $type,
                        'window' => $window,
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method()
                    ]
                ]);
            } else {
                \Log::warning('Rate limit dépassé', [
                    'type' => $type,
                    'window' => $window,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => Auth::id()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors du logging de rate limit', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);
        }
    }

    /**
     * Construire la réponse de rate limit dépassé
     */
    private function buildRateLimitResponse(string $window): Response
    {
        $message = "Trop de tentatives. Veuillez réessayer plus tard.";
        $retryAfter = $this->getWindowInSeconds($window);
        
        $response = [
            'error' => 'Too Many Requests',
            'message' => $message,
            'retry_after' => $retryAfter
        ];

        return response()->json($response, 429)
            ->header('Retry-After', $retryAfter)
            ->header('X-RateLimit-Limit', 'Exceeded')
            ->header('X-RateLimit-Remaining', '0');
    }
}