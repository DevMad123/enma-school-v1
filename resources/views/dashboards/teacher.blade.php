@extends('layouts.dashboard')

@section('title', 'Dashboard Enseignant')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mon Espace Enseignant</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Bienvenue {{ $teacher->user->name }}, gérez vos classes et évaluations</p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <button class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle évaluation
                </button>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-dashboard.stat-card 
                title="Mes Classes"
                :value="number_format($totalClasses)"
                color="blue"
                description="Classes assignées cette année"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Élèves Totaux"
                :value="number_format($totalStudents)"
                color="green"
                description="Dans toutes mes classes"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Évaluations"
                value="12"
                color="purple"
                :trend="['type' => 'up', 'value' => '+3']"
                description="Ce mois-ci"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Notes à saisir"
                value="8"
                color="orange"
                description="Évaluations en attente"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z\'/></svg>'"
            />
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- My Classes -->
            <div class="lg:col-span-2">
                <x-dashboard.card 
                    title="Mes Classes"
                    subtitle="Classes assignées pour cette année scolaire"
                >
                    <x-slot name="actions">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Voir toutes →
                        </a>
                    </x-slot>

                    <div class="space-y-4">
                        @forelse($classStats as $class)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                            <span class="text-white font-semibold text-sm">{{ substr($class['name'], 0, 2) }}</span>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $class['name'] }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $class['subject'] }} • {{ $class['level'] }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $class['students_count'] }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">élèves</p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            3 évaluations
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                            </svg>
                                            14.2 moyenne
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <button class="px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 font-medium">
                                            Voir détails
                                        </button>
                                        <button class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 text-sm font-medium rounded-lg transition-colors duration-200">
                                            Noter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Aucune classe assignée pour le moment</p>
                            </div>
                        @endforelse
                    </div>
                </x-dashboard.card>
            </div>

            <div class="space-y-6">
                <!-- Upcoming Deadlines -->
                <x-dashboard.card 
                    title="Prochaines Échéances"
                    subtitle="Deadlines importantes"
                >
                    <div class="space-y-4">
                        @foreach($upcomingDeadlines as $deadline)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-10 h-10 {{ $deadline['type'] === 'evaluation' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-600' }} rounded-lg flex items-center justify-center">
                                    @if($deadline['type'] === 'evaluation')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $deadline['title'] }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $deadline['class'] }} • {{ $deadline['date']->diffForHumans() }}
                                    </p>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $deadline['date']->diffInDays() <= 2 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ $deadline['date']->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Voir toutes les échéances →
                            </a>
                        </div>
                    </div>
                </x-dashboard.card>

                <!-- Quick Actions -->
                <x-dashboard.card 
                    title="Actions Rapides"
                    subtitle="Raccourcis fréquents"
                >
                    <div class="space-y-3">
                        <button class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Créer évaluation</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Nouvelle évaluation</p>
                            </div>
                        </button>

                        <button class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Saisir notes</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Évaluations récentes</p>
                            </div>
                        </button>

                        <button class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Générer bulletin</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Bulletins trimestriels</p>
                            </div>
                        </button>

                        <button class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Liste élèves</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Mes élèves</p>
                            </div>
                        </button>
                    </div>
                </x-dashboard.card>
            </div>
        </div>

        <!-- Recent Activity & Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Performance Chart -->
            <x-dashboard.card 
                title="Performance des Classes"
                subtitle="Moyennes par matière et classe"
            >
                <div class="space-y-6">
                    <!-- Chart simulation -->
                    <div class="space-y-4">
                        @foreach($classStats->take(4) as $class)
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $class['name'] }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ rand(12, 18) }}/20</span>
                                </div>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    @php $performance = rand(60, 90); @endphp
                                    <div 
                                        class="h-2 rounded-full {{ $performance > 75 ? 'bg-green-500' : ($performance > 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                        style="width: {{ $performance }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p class="text-lg font-bold text-green-600">{{ rand(75, 85) }}%</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Taux de réussite</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-blue-600">{{ number_format(rand(13, 17), 1) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Moyenne générale</p>
                            </div>
                            <div>
                                <p class="text-lg font-bold text-purple-600">{{ rand(85, 95) }}%</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Assiduité</p>
                            </div>
                        </div>
                    </div>
                </div>
            </x-dashboard.card>

            <!-- Recent Activity -->
            <x-dashboard.card 
                title="Activité Récente"
                subtitle="Dernières actions dans vos classes"
            >
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Notes saisies - CM2A Mathématiques</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Il y a 2 heures • 25 élèves notés</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Nouvelle évaluation créée - CP1B Français</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Il y a 1 jour • Planifiée pour demain</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Bulletins générés - CE1A</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Il y a 3 jours • 22 bulletins prêts</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 text-yellow-600 dark:text-yellow-400 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Rappel: Évaluation Sciences - CE2B</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Dans 2 jours • 28 élèves concernés</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Voir toute l'activité →
                        </a>
                    </div>
                </div>
            </x-dashboard.card>
        </div>
    </div>
@endsection