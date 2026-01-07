<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EducationalConfigurationService;
use App\Repositories\EducationalSettingsRepository;
use App\Services\Settings\PreUniversitySettingsService;
use App\Services\Settings\UniversitySettingsService;
use App\Http\Middleware\EducationalContextMiddleware;

class EducationalSettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository principal
        $this->app->singleton(EducationalSettingsRepository::class);
        
        // Service de configuration principal
        $this->app->singleton(EducationalConfigurationService::class, function ($app) {
            return new EducationalConfigurationService(
                $app->make(EducationalSettingsRepository::class)
            );
        });

        // Services spécialisés (créés à la demande)
        $this->app->bind(PreUniversitySettingsService::class, function ($app) {
            return new PreUniversitySettingsService(
                $app->make(EducationalSettingsRepository::class)
            );
        });

        $this->app->bind(UniversitySettingsService::class, function ($app) {
            return new UniversitySettingsService(
                $app->make(EducationalSettingsRepository::class)
            );
        });

        // Alias pratiques
        $this->app->alias(EducationalConfigurationService::class, 'educational.configuration');
        $this->app->alias(EducationalSettingsRepository::class, 'educational.repository');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Enregistrement du middleware
        $this->app['router']->aliasMiddleware('educational.context', EducationalContextMiddleware::class);
        
        // Configuration du cache pour les paramètres éducatifs
        config([
            'cache.stores.educational_settings' => [
                'driver' => 'redis',
                'connection' => 'default',
                'prefix' => 'edu_settings:',
            ]
        ]);
    }
}