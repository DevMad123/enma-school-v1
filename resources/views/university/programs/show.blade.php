@extends('layouts.dashboard')

@section('title', $program->name)

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec boutons d'action -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $program->name }}</h2>
                        <span class="ml-3 px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
                            {{ $program->code }}
                        </span>
                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded">
                            {{ ucfirst($program->level) }}
                        </span>
                        @if($program->is_active)
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 rounded">
                                Actif
                            </span>
                        @else
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 rounded">
                                Inactif
                            </span>
                        @endif
                    </div>
                    
                    @if($program->short_name)
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">{{ $program->short_name }}</p>
                    @endif
                    
                    <!-- Rattachements -->
                    <div class="mt-3 text-sm">
                        <span class="text-gray-500">Département :</span>
                        <a href="{{ route('university.departments.show', $program->department) }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 font-medium">
                            {{ $program->department->name }}
                        </a>
                        <span class="text-gray-400 mx-2">•</span>
                        <span class="text-gray-500">UFR :</span>
                        <a href="{{ route('university.ufrs.show', $program->department->ufr) }}" 
                           class="text-purple-600 hover:text-purple-800 dark:text-purple-400 font-medium">
                            {{ $program->department->ufr->name }}
                        </a>
                    </div>
                    
                    @if($program->description)
                        <p class="text-gray-600 dark:text-gray-400 mt-3">{{ $program->description }}</p>
                    @endif
                </div>
                
                <div class="flex gap-3 ml-6">
                    <a href="{{ route('university.programs.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Liste programmes
                    </a>
                    <a href="{{ route('university.programs.edit', $program) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                </div>
            </div>
        </div>

        <!-- Informations du programme -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalSemesters }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Semestres</p>
                        <p class="text-xs text-gray-500">sur {{ $program->duration_semesters }} prévus</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCourseUnits }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Unités d'enseignement</p>
                        <p class="text-xs text-gray-500">configurées</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalCredits }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Crédits ECTS</p>
                        <p class="text-xs text-gray-500">sur {{ $program->total_credits }} requis</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails du programme -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations détaillées</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau d'études</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ ucfirst($program->level) }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Durée totale</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $program->duration_semesters }} semestres</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total crédits ECTS</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $program->total_credits }} crédits</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Titre du diplôme</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $program->diploma_title }}</p>
                </div>
            </div>
        </div>

        <!-- Objectifs pédagogiques -->
        @if($program->objectives && count($program->objectives) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Objectifs pédagogiques</h3>
            <ul class="space-y-2">
                @foreach($program->objectives as $objective)
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-green-500 mt-1 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-gray-700 dark:text-gray-300">{{ $objective }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Semestres du programme -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Structure semestrielle</h3>
                    <a href="{{ route('university.programs.semesters.create', $program) }}" 
                       class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouveau semestre
                    </a>
                </div>
            </div>
            
            <div class="p-6">
                @if($program->semesters->count() > 0)
                    <div class="space-y-4">
                        @foreach($program->semesters->sortBy('semester_number') as $semester)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                                <a href="{{ route('university.semesters.course-units.index', $semester) }}" 
                                                   class="hover:text-orange-600 dark:hover:text-orange-400">
                                                    {{ $semester->name }}
                                                </a>
                                            </h4>
                                            <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-100 rounded">
                                                Semestre {{ $semester->semester_number }}
                                            </span>
                                        </div>
                                        
                                        @if($semester->description)
                                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">{{ Str::limit($semester->description, 150) }}</p>
                                        @endif
                                        
                                        <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                                            <span>{{ $semester->credits_required }} crédits requis</span>
                                            <span>{{ $semester->courseUnits->count() }} UE configurée(s)</span>
                                            <span>{{ $semester->courseUnits->sum('credits') }} crédits totaux</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('university.semesters.course-units.index', $semester) }}" 
                                           class="inline-flex items-center px-3 py-1 text-sm font-medium text-orange-600 hover:text-orange-800 dark:text-orange-400">
                                            Gérer les UE
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucun semestre configuré</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Ce programme n'a encore aucun semestre défini.</p>
                        <a href="{{ route('university.programs.semesters.create', $program) }}" 
                           class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Créer le premier semestre
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions de suppression -->
        @if($program->semesters->count() === 0)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-red-800 dark:text-red-200">Zone de danger</h3>
                    <p class="text-red-600 dark:text-red-400 mt-1">Supprimer définitivement ce programme</p>
                </div>
                <form action="{{ route('university.programs.destroy', $program) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce programme ? Cette action est irréversible.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Supprimer le programme
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection