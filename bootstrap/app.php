<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Enregistrer les middlewares Spatie Permission et de sécurité
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'school.context' => \App\Http\Middleware\SchoolContextMiddleware::class, // ✅ Nouveau middleware central
            'dashboard.access' => \App\Http\Middleware\DashboardAccessMiddleware::class, // ✅ Dashboard access control
            'educational_context' => \App\Http\Middleware\EducationalContextMiddleware::class, // ✅ Contexte éducatif unifié
            'admin.access' => \App\Http\Middleware\AdminAccess::class,
            'university' => \App\Http\Middleware\UniversityContextMiddleware::class, // ✅ Contexte universitaire
            'pre_university' => \App\Http\Middleware\PreUniversityContextMiddleware::class, // ✅ Contexte préuniversitaire
            'rate.limit.custom' => \App\Http\Middleware\CustomRateLimit::class,
        ]);
        
        // Appliquer le middleware school.context à toutes les routes web authentifiées
        // Note : L'ordre est important - auth PUIS school.context
        $middleware->web([
            'school.context', // ✅ Remplace EnsureSchoolExists
        ]);
        
        // Groupes de middleware pour la sécurité
        $middleware->group('secure', [
            'auth',
            'verified',
            'rate.limit.custom:default'
        ]);
        
        $middleware->group('financial', [
            'auth',
            'verified',
            'permission:manage_finances',
            'rate.limit.custom:financial'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
