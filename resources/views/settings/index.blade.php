@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paramètres et Configuration</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Configurez les paramètres généraux de votre établissement scolaire
            </p>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Informations de l'école -->
        <a href="{{ route('settings.school') }}" class="settings-card">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">École</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Informations générales</p>
                </div>
            </div>
        </a>

        <!-- Années scolaires -->
        <a href="{{ route('settings.years') }}" class="settings-card">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Années scolaires</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Gestion des périodes</p>
                </div>
            </div>
        </a>

        <!-- Système de notation -->
        <a href="{{ route('settings.grading') }}" class="settings-card">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Notation</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Barèmes et échelles</p>
                </div>
            </div>
        </a>

        <!-- Paramètres financiers -->
        <a href="{{ route('settings.financial') }}" class="settings-card">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Finances</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Configuration globale</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-dashboard.stat-card 
            title="Année courante"
            value="{{ \App\Models\AcademicYear::where('is_current', true)->first()?->name ?? 'Non définie' }}"
            color="blue"
        />

        <x-dashboard.stat-card 
            title="Devise par défaut"
            value="{{ \App\Models\Setting::get('default_currency', 'FCFA') }}"
            color="green"
        />

        <x-dashboard.stat-card 
            title="Échelle de notation"
            value="Sur {{ \App\Models\Setting::get('grading_scale', '20') }}"
            color="purple"
        />
    </div>

    <!-- Informations système -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations système</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Version Laravel:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ app()->version() }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Version PHP:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ PHP_VERSION }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Environnement:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ app()->environment() }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Timezone:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ config('app.timezone') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
.settings-card {
    @apply bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-lg hover:border-blue-300 dark:hover:border-blue-600 hover:scale-[1.02];
}
</style>
@endsection