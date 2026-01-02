@extends('layouts.dashboard')

@section('title', 'UE - ' . $courseUnit->name)

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec navigation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <!-- Fil d'Ariane -->
                    <nav class="flex mb-4" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('university.dashboard') }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                                    </svg>
                                    Université
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <a href="{{ route('university.programs.show', $courseUnit->semester->program) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                        {{ $courseUnit->semester->program->name }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <a href="{{ route('university.programs.semesters.show', [$courseUnit->semester->program, $courseUnit->semester]) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                        {{ $courseUnit->semester->name }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <a href="{{ route('university.course-units', $courseUnit->semester) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                        Unités d'enseignement
                                    </a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $courseUnit->code }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $courseUnit->name }}</h2>
                        <span class="ml-3 px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100 rounded-full">
                            {{ $courseUnit->code }}
                        </span>
                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded">
                            @switch($courseUnit->type)
                                @case('mandatory') Obligatoire @break
                                @case('optional') Optionnel @break
                                @case('specialization') Spécialisation @break
                                @case('project') Projet @break
                                @case('internship') Stage @break
                                @default {{ ucfirst($courseUnit->type) }}
                            @endswitch
                        </span>
                        @if($courseUnit->is_active)
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded">
                                Actif
                            </span>
                        @else
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 rounded">
                                Inactif
                            </span>
                        @endif
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ $courseUnit->semester->program->department->ufr->name }} • {{ $courseUnit->semester->program->department->name }}
                    </p>
                </div>
                
                <div class="flex gap-3 ml-6">
                    <a href="{{ route('university.course-units.edit', $courseUnit) }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    
                    <a href="{{ route('university.course-units.elements.index', $courseUnit) }}" 
                       class="inline-flex items-center px-4 py-2 border border-purple-600 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Gérer les ECUE
                    </a>
                    
                    <a href="{{ route('university.course-units', $courseUnit->semester) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour au semestre
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques et informations -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">{{ $courseUnit->credits }}</h3>
                        <p class="text-blue-700 dark:text-blue-300">Crédits ECTS</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-green-900 dark:text-green-100">{{ $courseUnit->hours_total }}h</h3>
                        <p class="text-green-700 dark:text-green-300">Volume horaire</p>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100">{{ $courseUnit->coefficient ?? 1 }}</h3>
                        <p class="text-yellow-700 dark:text-yellow-300">Coefficient</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-100">{{ $courseUnit->elements->count() ?? 0 }}</h3>
                        <p class="text-purple-700 dark:text-purple-300">ECUE</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Détails de l'UE -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informations principales -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Description -->
                @if($courseUnit->description)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Description</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $courseUnit->description }}</p>
                </div>
                @endif

                <!-- Répartition horaire -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Répartition horaire</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $courseUnit->hours_cm ?? 0 }}h</div>
                            <div class="text-sm text-blue-700 dark:text-blue-300">Cours Magistral</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $courseUnit->hours_td ?? 0 }}h</div>
                            <div class="text-sm text-green-700 dark:text-green-300">Travaux Dirigés</div>
                        </div>
                        <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $courseUnit->hours_tp ?? 0 }}h</div>
                            <div class="text-sm text-orange-700 dark:text-orange-300">Travaux Pratiques</div>
                        </div>
                    </div>

                    <!-- Barre de progression -->
                    <div class="mt-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                            <span>Répartition</span>
                            <span>{{ $courseUnit->hours_total }}h total</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            @php
                                $total = $courseUnit->hours_total;
                                $cm_pct = $total > 0 ? ($courseUnit->hours_cm / $total) * 100 : 0;
                                $td_pct = $total > 0 ? ($courseUnit->hours_td / $total) * 100 : 0;
                                $tp_pct = $total > 0 ? ($courseUnit->hours_tp / $total) * 100 : 0;
                            @endphp
                            <div class="flex h-2 rounded-full overflow-hidden">
                                @if($cm_pct > 0)
                                    <div class="bg-blue-500" style="width: {{ $cm_pct }}%"></div>
                                @endif
                                @if($td_pct > 0)
                                    <div class="bg-green-500" style="width: {{ $td_pct }}%"></div>
                                @endif
                                @if($tp_pct > 0)
                                    <div class="bg-orange-500" style="width: {{ $tp_pct }}%"></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prérequis -->
                @if($courseUnit->prerequisites)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Prérequis</h3>
                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ $courseUnit->prerequisites }}</p>
                </div>
                @endif
            </div>

            <!-- Informations contextuelles -->
            <div class="space-y-6">
                <!-- Informations générales -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Semestre :</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $courseUnit->semester->name }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Programme :</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $courseUnit->semester->program->name }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Département :</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $courseUnit->semester->program->department->name }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">UFR :</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $courseUnit->semester->program->department->ufr->name }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Année académique :</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $courseUnit->semester->academicYear->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Dates importantes -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dates</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Créé le :</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $courseUnit->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div>
                            <span class="font-medium text-gray-900 dark:text-white">Modifié le :</span>
                            <p class="text-gray-600 dark:text-gray-400">{{ $courseUnit->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('university.course-units.elements.index', $courseUnit) }}" 
                           class="block w-full text-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                            Gérer les ECUE
                        </a>
                        <a href="{{ route('university.course-units.edit', $courseUnit) }}" 
                           class="block w-full text-center px-4 py-2 border border-purple-600 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors">
                            Modifier l'UE
                        </a>
                        @if($courseUnit->elements->count() == 0)
                        <button onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cette unité d\'enseignement ? Cette action est irréversible.')) { document.getElementById('delete-course-unit-form').submit(); }"
                                class="block w-full text-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Supprimer l'UE
                        </button>
                        
                        <form id="delete-course-unit-form" action="{{ route('university.course-units.destroy', $courseUnit) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection