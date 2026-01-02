@extends('layouts.dashboard')

@section('title', 'ECUE - ' . $element->name)

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
                                        {{ $courseUnit->semester->program->short_name ?: $courseUnit->semester->program->name }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <a href="{{ route('university.course-units.elements.index', $courseUnit) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                        ECUE - {{ $courseUnit->code }}
                                    </a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $element->code }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $element->name }}</h2>
                        <span class="ml-3 px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100 rounded-full">
                            {{ $element->code }}
                        </span>
                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded">
                            {{ $element->status_formatted }}
                        </span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Élément Constitutif de {{ $courseUnit->code }} - {{ $courseUnit->name }}
                    </p>
                </div>
                
                <div class="flex gap-3 ml-6">
                    <a href="{{ route('university.course-units.elements.index', $courseUnit) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour aux ECUE
                    </a>
                    
                    <a href="{{ route('university.course-units.elements.edit', [$courseUnit, $element]) }}"
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    
                    @if($element->canBeDeleted())
                        <button onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet ECUE ? Cette action est irréversible.')) { document.getElementById('delete-element-form').submit(); }"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Supprimer
                        </button>
                        
                        <form id="delete-element-form" action="{{ route('university.course-units.elements.destroy', [$courseUnit, $element]) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations principales -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Colonne principale - Informations détaillées -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations générales -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Code ECUE</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $element->code }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Statut</dt>
                            <dd class="mt-1">
                                @if($element->status == 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        <svg class="w-1.5 h-1.5 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                        <svg class="w-1.5 h-1.5 mr-1" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        {{ ucfirst($element->status) }}
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Crédits ECTS</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $element->credits }} crédits</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Coefficient</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $element->coefficient ?: 1 }}</dd>
                        </div>

                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom complet</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $element->name }}</dd>
                        </div>

                        @if($element->description)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $element->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Répartition des heures -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Répartition des heures</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="text-center">
                            <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                <dt class="text-sm font-medium text-purple-600 dark:text-purple-400">Cours Magistraux</dt>
                                <dd class="mt-1 text-2xl font-bold text-purple-900 dark:text-purple-100">{{ $element->hours_cm ?: 0 }}h</dd>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                                <dt class="text-sm font-medium text-green-600 dark:text-green-400">Travaux Dirigés</dt>
                                <dd class="mt-1 text-2xl font-bold text-green-900 dark:text-green-100">{{ $element->hours_td ?: 0 }}h</dd>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <div class="p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                                <dt class="text-sm font-medium text-orange-600 dark:text-orange-400">Travaux Pratiques</dt>
                                <dd class="mt-1 text-2xl font-bold text-orange-900 dark:text-orange-100">{{ $element->hours_tp ?: 0 }}h</dd>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <dt class="text-sm font-medium text-blue-600 dark:text-blue-400">Total</dt>
                                <dd class="mt-1 text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $element->hours_total }}h</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique de répartition -->
                    @if($element->hours_total > 0)
                    <div class="mt-4">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Répartition visuelle</h4>
                        <div class="flex w-full h-4 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            @php
                                $cmPercent = ($element->hours_cm / $element->hours_total) * 100;
                                $tdPercent = ($element->hours_td / $element->hours_total) * 100;
                                $tpPercent = ($element->hours_tp / $element->hours_total) * 100;
                            @endphp
                            
                            @if($element->hours_cm > 0)
                                <div class="bg-purple-500 h-full" style="width: {{ $cmPercent }}%" title="CM: {{ $element->hours_cm }}h ({{ round($cmPercent) }}%)"></div>
                            @endif
                            
                            @if($element->hours_td > 0)
                                <div class="bg-green-500 h-full" style="width: {{ $tdPercent }}%" title="TD: {{ $element->hours_td }}h ({{ round($tdPercent) }}%)"></div>
                            @endif
                            
                            @if($element->hours_tp > 0)
                                <div class="bg-orange-500 h-full" style="width: {{ $tpPercent }}%" title="TP: {{ $element->hours_tp }}h ({{ round($tpPercent) }}%)"></div>
                            @endif
                        </div>
                        
                        <div class="flex flex-wrap gap-4 mt-2 text-xs">
                            @if($element->hours_cm > 0)
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-1"></div>
                                    <span class="text-gray-600 dark:text-gray-400">CM: {{ round($cmPercent) }}%</span>
                                </div>
                            @endif
                            
                            @if($element->hours_td > 0)
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-1"></div>
                                    <span class="text-gray-600 dark:text-gray-400">TD: {{ round($tdPercent) }}%</span>
                                </div>
                            @endif
                            
                            @if($element->hours_tp > 0)
                                <div class="flex items-center">
                                    <div class="w-3 h-3 bg-orange-500 rounded-full mr-1"></div>
                                    <span class="text-gray-600 dark:text-gray-400">TP: {{ round($tpPercent) }}%</span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Modalités d'évaluation -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Modalités d'évaluation</h3>
                    
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type d'évaluation</dt>
                            <dd class="mt-1">
                                @php
                                    $evaluationTypes = [
                                        'controle_continu' => 'Contrôle Continu',
                                        'examen_final' => 'Examen Final',
                                        'mixte' => 'Mixte (CC + Examen)',
                                        'projet' => 'Projet',
                                        'rapport' => 'Rapport',
                                        'oral' => 'Oral'
                                    ];
                                    $typeLabel = $evaluationTypes[$element->evaluation_type] ?? ucfirst(str_replace('_', ' ', $element->evaluation_type));
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100">
                                    {{ $typeLabel }}
                                </span>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Coefficient</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $element->coefficient ?: 1 }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Colonne de droite - Informations complémentaires -->
            <div class="space-y-6">
                <!-- Relation avec l'UE parente -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">Unité d'Enseignement</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Code UE</p>
                            <p class="text-blue-900 dark:text-blue-100 font-mono">{{ $courseUnit->code }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Nom</p>
                            <p class="text-blue-900 dark:text-blue-100">{{ $courseUnit->name }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div>
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Crédits UE</p>
                                <p class="text-blue-900 dark:text-blue-100">{{ $courseUnit->credits }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Heures UE</p>
                                <p class="text-blue-900 dark:text-blue-100">{{ $courseUnit->hours_total }}h</p>
                            </div>
                        </div>
                        
                        <div class="pt-2">
                            <a href="{{ route('university.course-units.show', $courseUnit) }}" 
                               class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 underline">
                                Voir les détails de l'UE →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Métadonnées -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations système</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Créé le</p>
                            <p class="text-gray-900 dark:text-white">{{ $element->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Dernière modification</p>
                            <p class="text-gray-900 dark:text-white">{{ $element->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        
                        @if($element->created_at != $element->updated_at)
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Modifié il y a</p>
                            <p class="text-gray-900 dark:text-white">{{ $element->updated_at->diffForHumans() }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Statistiques d'utilisation -->
                @php
                    $evaluationsCount = 0;
                    $gradesCount = 0;
                    try {
                        $evaluationsCount = method_exists($element, 'evaluations') ? $element->evaluations()->count() : 0;
                        $gradesCount = method_exists($element, 'grades') ? $element->grades()->count() : 0;
                    } catch (\Exception $e) {
                        // Les relations n'existent peut-être pas encore
                    }
                @endphp

                @if($evaluationsCount > 0 || $gradesCount > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Utilisation</h3>
                    
                    <div class="space-y-3">
                        @if($evaluationsCount > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Évaluations</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $evaluationsCount }}</span>
                        </div>
                        @endif
                        
                        @if($gradesCount > 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Notes saisies</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $gradesCount }}</span>
                        </div>
                        @endif
                    </div>
                    
                    @if(!$element->canBeDeleted())
                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-xs text-yellow-800 dark:text-yellow-200">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Cet ECUE ne peut pas être supprimé car il contient des données d'évaluation.
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection