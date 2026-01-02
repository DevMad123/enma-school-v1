@extends('layouts.dashboard')

@section('title', 'Gestion Universitaire')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête universitaire -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-purple-200 dark:border-gray-600 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-6">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $school->name }}</h1>
                        <div class="flex items-center mt-2">
                            <span class="px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100 rounded-full">
                                {{ $school->getTypeLabel() }}
                            </span>
                            <p class="ml-4 text-gray-600 dark:text-gray-300">
                                Gestion universitaire avancée
                            </p>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Organisation</div>
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                        UFR → Départements → Programmes
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- UFRs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['ufrs'] ?? 0 }}</p>
                        <p class="text-gray-600 dark:text-gray-400">UFR</p>
                        <p class="text-xs text-purple-600">Unités Formation Recherche</p>
                    </div>
                </div>
            </div>

            <!-- Départements -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['departments'] ?? 0 }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Départements</p>
                        <p class="text-xs text-blue-600">Unités pédagogiques</p>
                    </div>
                </div>
            </div>

            <!-- Programmes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['programs'] ?? 0 }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Programmes</p>
                        <p class="text-xs text-green-600">Filières d'études</p>
                    </div>
                </div>
            </div>

            <!-- Semestres actifs -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['current_semester'] ?? 0 }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Semestre actuel</p>
                        <p class="text-xs text-orange-600">Périodes en cours</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation rapide -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- UFR -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Gestion UFR</h3>
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Gérez les Unités de Formation et de Recherche</p>
                <div class="flex gap-2">
                    <a href="{{ route('university.ufrs.index') }}" 
                       class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Gérer les UFR
                    </a>
                    <a href="{{ route('university.ufrs.create') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-purple-600 text-purple-600 hover:bg-purple-50 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition-colors">
                        + Créer
                    </a>
                </div>
            </div>

            <!-- Départements -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Départements</h3>
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Organisez les départements par UFR</p>
                <div class="flex gap-2">
                    <a href="{{ route('university.departments.index') }}" 
                       class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Gérer
                    </a>
                    <a href="{{ route('university.departments.create') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-blue-600 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition-colors">
                        + Créer
                    </a>
                </div>
            </div>

            <!-- Programmes -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Programmes</h3>
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Créez des filières et programmes d'études</p>
                <div class="flex gap-2">
                    <a href="{{ route('university.programs.index') }}" 
                       class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors">
                        Gérer
                    </a>
                    <a href="{{ route('university.programs.create') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 border border-green-600 text-green-600 hover:bg-green-50 dark:hover:bg-gray-700 rounded-lg text-sm font-medium transition-colors">
                        + Créer
                    </a>
                </div>
            </div>
        </div>

        <!-- Guide rapide -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">🎓 Système Universitaire</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">Structure Hiérarchique</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <strong>UFR</strong> : Unités de Formation et de Recherche</li>
                        <li>• <strong>Départements</strong> : Divisions spécialisées par domaine</li>
                        <li>• <strong>Programmes</strong> : Filières d'études (Licence, Master, Doctorat)</li>
                        <li>• <strong>Semestres</strong> : Périodes d'enseignement</li>
                        <li>• <strong>UE</strong> : Unités d'Enseignement avec crédits ECTS</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">Système de Crédits ECTS</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <strong>1 semestre</strong> = 30 crédits ECTS</li>
                        <li>• <strong>Licence</strong> = 180 crédits (6 semestres)</li>
                        <li>• <strong>Master</strong> = 120 crédits (4 semestres)</li>
                        <li>• <strong>Doctorat</strong> = 180 crédits (6 semestres)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection