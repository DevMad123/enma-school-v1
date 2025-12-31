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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
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

        <!-- Paramètres système -->
        <a href="{{ route('settings.system') }}" class="settings-card">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gray-100 dark:bg-gray-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Système</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sécurité & Performance</p>
                </div>
            </div>
        </a>

        <!-- Paramètres de notifications -->
        <a href="{{ route('settings.notifications') }}" class="settings-card">
            <div class="flex items-center space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Notifications</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email & SMS</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Statistiques rapides -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-dashboard.stat-card 
            title="Devise par défaut"
            value="{{ \App\Models\Setting::get('default_currency', 'FCFA') }}"
            color="green"
        />

        <x-dashboard.stat-card 
            title="Mode maintenance"
            value="{{ \App\Models\Setting::get('maintenance_mode', false) ? 'Activé' : 'Désactivé' }}"
            color="{{ \App\Models\Setting::get('maintenance_mode', false) ? 'red' : 'blue' }}"
        />

        <x-dashboard.stat-card 
            title="Notifications email"
            value="{{ \App\Models\Setting::get('enable_email_notifications', true) ? 'Activées' : 'Désactivées' }}"
            color="{{ \App\Models\Setting::get('enable_email_notifications', true) ? 'blue' : 'gray' }}"
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