@extends('layouts.dashboard')

@section('title', 'Modifier ECUE - ' . $element->name)

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
                                    <a href="{{ route('university.course-units.elements.index', $courseUnit) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                        ECUE - {{ $courseUnit->name }}
                                    </a>
                                </div>
                            </li>
                            <li aria-current="page">
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-gray-500 dark:text-gray-400">Modifier {{ $element->code }}</span>
                                </div>
                            </li>
                        </ol>
                    </nav>

                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier l'ECUE</h2>
                        <span class="ml-3 px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100 rounded-full">
                            {{ $element->code }}
                        </span>
                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded">
                            {{ $element->status_formatted }}
                        </span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Élément de {{ $courseUnit->code }} - {{ $courseUnit->name }}
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

        <!-- Historique et informations -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4">Unité d'Enseignement parente</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Code UE</p>
                        <p class="text-blue-900 dark:text-blue-100">{{ $courseUnit->code }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Crédits disponibles</p>
                        <p class="text-blue-900 dark:text-blue-100" id="available-credits">{{ $courseUnit->credits - $courseUnit->elements->where('id', '!=', $element->id)->sum('credits') }}/{{ $courseUnit->credits }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Heures disponibles</p>
                        <p class="text-blue-900 dark:text-blue-100" id="available-hours">{{ $courseUnit->hours_total - $courseUnit->elements->where('id', '!=', $element->id)->sum('hours_total') }}/{{ $courseUnit->hours_total }}h</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">ECUE total</p>
                        <p class="text-blue-900 dark:text-blue-100">{{ $courseUnit->elements->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Créé le</p>
                        <p class="text-gray-900 dark:text-white">{{ $element->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-400">Dernière modification</p>
                        <p class="text-gray-900 dark:text-white">{{ $element->updated_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    @if($element->evaluations()->exists())
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Évaluations</p>
                            <p class="text-gray-900 dark:text-white">{{ $element->evaluations()->count() }} enregistrées</p>
                        </div>
                    @endif
                    @if($element->grades()->exists())
                        <div>
                            <p class="text-gray-600 dark:text-gray-400">Notes</p>
                            <p class="text-gray-900 dark:text-white">{{ $element->grades()->count() }} saisies</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <form action="{{ route('university.course-units.elements.update', [$courseUnit, $element]) }}" method="POST" id="ecue-edit-form" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Colonne principale - Formulaire -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Informations générales -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations générales</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Code ECUE <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       name="code" 
                                       id="code"
                                       value="{{ old('code', $element->code) }}"
                                       placeholder="Ex: {{ $courseUnit->code }}-ECUE01"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('code') border-red-500 @enderror"
                                       required>
                                @error('code')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Format recommandé: [CODE_UE]-ECUE[NN]</p>
                            </div>

                            <div>
                                <label for="credits" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Crédits ECTS <span class="text-red-500">*</span>
                                </label>
                                <input type="number" 
                                       name="credits" 
                                       id="credits"
                                       value="{{ old('credits', $element->credits) }}"
                                       min="0.5" 
                                       max="{{ $courseUnit->credits - $courseUnit->elements->where('id', '!=', $element->id)->sum('credits') }}"
                                       step="0.5"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('credits') border-red-500 @enderror"
                                       required>
                                @error('credits')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Maximum {{ $courseUnit->credits - $courseUnit->elements->where('id', '!=', $element->id)->sum('credits') }} crédits disponibles</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nom de l'ECUE <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="{{ old('name', $element->name) }}"
                                   placeholder="Ex: Travaux dirigés de programmation"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror"
                                   required>
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      rows="3"
                                      placeholder="Description optionnelle des objectifs et contenus de l'ECUE"
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror">{{ old('description', $element->description) }}</textarea>
                            @error('description')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Statut
                            </label>
                            <select name="status" 
                                    id="status"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status', $element->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ old('status', $element->status) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                <option value="archived" {{ old('status', $element->status) == 'archived' ? 'selected' : '' }}>Archivé</option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Répartition des heures -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Répartition des heures d'enseignement</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div>
                                <label for="hours_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Cours Magistral (CM)
                                </label>
                                <input type="number" 
                                       name="hours_cm" 
                                       id="hours_cm"
                                       value="{{ old('hours_cm', $element->hours_cm ?? 0) }}"
                                       min="0"
                                       step="0.5"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('hours_cm') border-red-500 @enderror">
                                @error('hours_cm')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="hours_td" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Travaux Dirigés (TD)
                                </label>
                                <input type="number" 
                                       name="hours_td" 
                                       id="hours_td"
                                       value="{{ old('hours_td', $element->hours_td ?? 0) }}"
                                       min="0"
                                       step="0.5"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('hours_td') border-red-500 @enderror">
                                @error('hours_td')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="hours_tp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Travaux Pratiques (TP)
                                </label>
                                <input type="number" 
                                       name="hours_tp" 
                                       id="hours_tp"
                                       value="{{ old('hours_tp', $element->hours_tp ?? 0) }}"
                                       min="0"
                                       step="0.5"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('hours_tp') border-red-500 @enderror">
                                @error('hours_tp')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="hours_total_display" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Total calculé
                                </label>
                                <input type="text" 
                                       id="hours_total_display"
                                       readonly
                                       value="{{ $element->hours_total }}h"
                                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-600 text-gray-600 dark:text-gray-400">
                                <p class="text-xs text-gray-500 mt-1">Maximum {{ $courseUnit->hours_total - $courseUnit->elements->where('id', '!=', $element->id)->sum('hours_total') }}h disponibles</p>
                            </div>
                        </div>

                        <!-- Barre de progression des heures -->
                        <div class="mt-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Répartition horaire</span>
                                <span class="text-sm text-gray-500" id="hours-percentage">{{ round(($element->hours_total / max($courseUnit->hours_total - $courseUnit->elements->where('id', '!=', $element->id)->sum('hours_total'), 1)) * 100) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-purple-600 h-2.5 rounded-full transition-all duration-300" id="hours-progress" style="width: {{ min(round(($element->hours_total / max($courseUnit->hours_total - $courseUnit->elements->where('id', '!=', $element->id)->sum('hours_total'), 1)) * 100), 100) }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Évaluation et coefficient -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Modalités d'évaluation</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="evaluation_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Type d'évaluation <span class="text-red-500">*</span>
                                </label>
                                <select name="evaluation_type" 
                                        id="evaluation_type"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('evaluation_type') border-red-500 @enderror"
                                        required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="controle_continu" {{ old('evaluation_type', $element->evaluation_type) == 'controle_continu' ? 'selected' : '' }}>Contrôle Continu</option>
                                    <option value="examen_final" {{ old('evaluation_type', $element->evaluation_type) == 'examen_final' ? 'selected' : '' }}>Examen</option>
                                    <option value="mixte" {{ old('evaluation_type', $element->evaluation_type) == 'mixte' ? 'selected' : '' }}>Mixte (CC + Examen)</option>
                                    <option value="projet" {{ old('evaluation_type', $element->evaluation_type) == 'projet' ? 'selected' : '' }}>Projet</option>
                                    <option value="pratique" {{ old('evaluation_type', $element->evaluation_type) == 'pratique' ? 'selected' : '' }}>Évaluation Pratique</option>
                                </select>
                                @error('evaluation_type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="coefficient" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Coefficient
                                </label>
                                <input type="number" 
                                       name="coefficient" 
                                       id="coefficient"
                                       value="{{ old('coefficient', $element->coefficient ?? 1) }}"
                                       min="0.5" 
                                       max="3"
                                       step="0.5"
                                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:text-white @error('coefficient') border-red-500 @enderror">
                                @error('coefficient')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-1">Entre 0.5 et 3 (défaut: 1)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne de droite - Prévisualisation et contraintes -->
                <div class="space-y-6">
                    <!-- Impact des modifications -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Impact des modifications</h3>
                        
                        <div class="space-y-4">
                            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">Valeurs actuelles</h4>
                                <div class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                                    <div class="flex justify-between">
                                        <span>Crédits:</span>
                                        <span>{{ $element->credits }} ECTS</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Heures totales:</span>
                                        <span>{{ $element->hours_total }}h</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Évaluation:</span>
                                        <span>{{ $element->evaluation_type_formatted }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h4 class="font-medium text-gray-900 dark:text-white mb-2">Après modification</h4>
                                
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Crédits UE:</span>
                                        <span class="font-medium" id="preview-ue-credits">{{ $courseUnit->elements->sum('credits') }}/{{ $courseUnit->credits }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Heures UE:</span>
                                        <span class="font-medium" id="preview-ue-hours">{{ $courseUnit->elements->sum('hours_total') }}/{{ $courseUnit->hours_total }}h</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Cohérence:</span>
                                        <span class="font-medium" id="preview-consistency">
                                            <span class="text-green-600">✓ Valide</span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Alertes de modification -->
                            <div id="modification-alerts" class="space-y-2">
                                <!-- Les alertes seront ajoutées dynamiquement -->
                            </div>
                        </div>
                    </div>

                    <!-- Contraintes de suppression -->
                    @if(!$element->canBeDeleted())
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-4">Contraintes de suppression</h3>
                        
                        <div class="space-y-2 text-sm text-red-700 dark:text-red-300">
                            @if($element->evaluations()->exists())
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $element->evaluations()->count() }} évaluation(s) liée(s)
                                </div>
                            @endif
                            @if($element->grades()->exists())
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 mt-0.5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $element->grades()->count() }} note(s) saisie(s)
                                </div>
                            @endif
                        </div>
                        
                        <div class="mt-4 p-3 bg-red-100 dark:bg-red-900/40 rounded text-xs text-red-800 dark:text-red-200">
                            Pour supprimer cet ECUE, vous devez d'abord supprimer toutes les évaluations et notes associées.
                        </div>
                    </div>
                    @endif

                    <!-- Contraintes LMD -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-4">Contraintes LMD</h3>
                        
                        <ul class="space-y-2 text-sm text-yellow-700 dark:text-yellow-300">
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Modifications limitées par les contraintes UE
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Code unique requis dans l'UE
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Synchronisation automatique avec UE
                            </li>
                            <li class="flex items-start">
                                <svg class="w-4 h-4 mr-2 mt-0.5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Validation des contraintes en temps réel
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Les modifications seront appliquées immédiatement
                    </div>
                    
                    <div class="flex space-x-3">
                        <a href="{{ route('university.course-units.elements.index', $courseUnit) }}" 
                           class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition-colors">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                id="submit-button">
                            <span id="submit-text">Enregistrer les modifications</span>
                            <span id="submit-spinner" class="hidden">
                                <svg class="w-4 h-4 animate-spin inline-block" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Enregistrement...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Éléments DOM
    const creditsInput = document.getElementById('credits');
    const hoursCmInput = document.getElementById('hours_cm');
    const hoursTdInput = document.getElementById('hours_td');
    const hoursTpInput = document.getElementById('hours_tp');
    const hoursDisplay = document.getElementById('hours_total_display');
    const hoursProgress = document.getElementById('hours-progress');
    const hoursPercentage = document.getElementById('hours-percentage');
    const alertsContainer = document.getElementById('modification-alerts');
    const previewUeCredits = document.getElementById('preview-ue-credits');
    const previewUeHours = document.getElementById('preview-ue-hours');
    const previewConsistency = document.getElementById('preview-consistency');
    
    // Constantes pour validation (sans l'élément actuel)
    const maxCredits = {{ $courseUnit->credits - $courseUnit->elements->where('id', '!=', $element->id)->sum('credits') }};
    const maxHours = {{ $courseUnit->hours_total - $courseUnit->elements->where('id', '!=', $element->id)->sum('hours_total') }};
    const currentUeCredits = {{ $courseUnit->elements->where('id', '!=', $element->id)->sum('credits') }};
    const currentUeHours = {{ $courseUnit->elements->where('id', '!=', $element->id)->sum('hours_total') }};
    const totalUeCredits = {{ $courseUnit->credits }};
    const totalUeHours = {{ $courseUnit->hours_total }};
    const originalCredits = {{ $element->credits }};
    const originalHours = {{ $element->hours_total }};

    // Fonction de calcul des heures totales
    function calculateTotalHours() {
        const cm = parseFloat(hoursCmInput.value) || 0;
        const td = parseFloat(hoursTdInput.value) || 0;
        const tp = parseFloat(hoursTpInput.value) || 0;
        return cm + td + tp;
    }

    // Fonction de mise à jour de la prévisualisation
    function updatePreview() {
        const newCredits = parseFloat(creditsInput.value) || 0;
        const newHours = calculateTotalHours();
        
        // Calcul des totaux UE après modification
        const finalUeCredits = currentUeCredits + newCredits;
        const finalUeHours = currentUeHours + newHours;
        
        // Mise à jour de l'affichage
        previewUeCredits.textContent = `${finalUeCredits}/${totalUeCredits}`;
        previewUeHours.textContent = `${finalUeHours}/${totalUeHours}h`;
        hoursDisplay.value = `${newHours}h`;
        
        // Mise à jour de la barre de progression
        const percentage = maxHours > 0 ? (newHours / maxHours) * 100 : 0;
        hoursProgress.style.width = Math.min(percentage, 100) + '%';
        hoursPercentage.textContent = Math.round(percentage) + '%';
        
        // Validation et détection des changements
        validateModifications(newCredits, newHours, finalUeCredits, finalUeHours);
    }

    // Fonction de validation des modifications
    function validateModifications(newCredits, newHours, finalUeCredits, finalUeHours) {
        const alerts = [];
        const changes = [];

        // Détection des changements
        if (newCredits !== originalCredits) {
            changes.push(`Crédits: ${originalCredits} → ${newCredits}`);
        }
        if (newHours !== originalHours) {
            changes.push(`Heures: ${originalHours}h → ${newHours}h`);
        }

        // Validation des limites
        if (newCredits > maxCredits) {
            alerts.push({
                type: 'error',
                message: `Crédits dépassent la limite UE (${maxCredits} disponibles)`
            });
        }

        if (newHours > maxHours) {
            alerts.push({
                type: 'error',
                message: `Heures dépassent la limite UE (${maxHours}h disponibles)`
            });
        }

        // Validation de cohérence
        const creditsOverflow = finalUeCredits > totalUeCredits;
        const hoursDeviation = Math.abs(finalUeHours - totalUeHours) > (totalUeHours * 0.05);
        
        if (creditsOverflow) {
            alerts.push({
                type: 'error',
                message: 'Total crédits UE dépassé'
            });
        }

        if (hoursDeviation && finalUeHours > totalUeHours) {
            alerts.push({
                type: 'warning',
                message: 'Dépassement heures UE détecté'
            });
        }

        // Informations sur les changements
        if (changes.length > 0) {
            alerts.push({
                type: 'info',
                message: `Modifications: ${changes.join(', ')}`
            });
        }

        // Mise à jour de l'indicateur de cohérence
        const isConsistent = !creditsOverflow && !hoursDeviation;
        previewConsistency.innerHTML = isConsistent 
            ? '<span class="text-green-600">✓ Valide</span>'
            : '<span class="text-orange-600">⚠ Attention</span>';

        // Affichage des alertes
        updateAlerts(alerts);
        
        // Gestion du bouton
        const hasErrors = alerts.some(alert => alert.type === 'error');
        const hasChanges = changes.length > 0;
        const submitButton = document.getElementById('submit-button');
        
        submitButton.disabled = hasErrors || (!hasChanges && newCredits <= 0) || newHours <= 0;
        
        if (!hasChanges && !hasErrors) {
            submitButton.textContent = 'Aucune modification';
        } else if (hasErrors) {
            submitButton.textContent = 'Erreurs à corriger';
        } else {
            document.getElementById('submit-text').textContent = 'Enregistrer les modifications';
        }
    }

    // Fonction d'affichage des alertes
    function updateAlerts(alerts) {
        alertsContainer.innerHTML = '';
        
        alerts.forEach(alert => {
            const alertElement = document.createElement('div');
            let bgColor, textColor, iconColor, icon;
            
            switch(alert.type) {
                case 'error':
                    bgColor = 'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-700';
                    textColor = 'text-red-700 dark:text-red-300';
                    iconColor = 'text-red-500';
                    icon = `<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>`;
                    break;
                case 'warning':
                    bgColor = 'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-700';
                    textColor = 'text-yellow-700 dark:text-yellow-300';
                    iconColor = 'text-yellow-500';
                    icon = `<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>`;
                    break;
                default:
                    bgColor = 'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-700';
                    textColor = 'text-blue-700 dark:text-blue-300';
                    iconColor = 'text-blue-500';
                    icon = `<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>`;
            }
            
            alertElement.className = `${bgColor} border rounded-lg p-3`;
            alertElement.innerHTML = `
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="w-4 h-4 ${iconColor}" fill="currentColor" viewBox="0 0 20 20">${icon}</svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm ${textColor}">${alert.message}</p>
                    </div>
                </div>
            `;
            alertsContainer.appendChild(alertElement);
        });
    }

    // Event listeners
    [creditsInput, hoursCmInput, hoursTdInput, hoursTpInput].forEach(input => {
        input.addEventListener('input', updatePreview);
    });

    // Animation du formulaire
    const form = document.getElementById('ecue-edit-form');
    form.addEventListener('submit', function() {
        const submitButton = document.getElementById('submit-button');
        const submitText = document.getElementById('submit-text');
        const submitSpinner = document.getElementById('submit-spinner');
        
        submitButton.disabled = true;
        submitText.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
    });

    // Initialisation
    updatePreview();
});
</script>
@endpush
@endsection