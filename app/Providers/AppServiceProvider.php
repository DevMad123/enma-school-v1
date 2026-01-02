<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Models\CourseUnitElement;
use App\Observers\CourseUnitElementObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix pour les erreurs MySQL avec utf8mb4
        Schema::defaultStringLength(191);
        
        // Charger les helpers personnalisés
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }

        // Enregistrement des Observers
        CourseUnitElement::observe(CourseUnitElementObserver::class);
    }
}
