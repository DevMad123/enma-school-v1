@extends('layouts.dashboard')

@section('title', 'Nouvelle UE - ' . $semester->name)

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec navigation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
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
                            <a href="{{ route('university.ufrs.show', $semester->program->department->ufr) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                {{ $semester->program->department->ufr->short_name ?: $semester->program->department->ufr->name }}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('university.departments.show', $semester->program->department) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                {{ $semester->program->department->short_name ?: $semester->program->department->name }}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('university.programs.show', $semester->program) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                {{ $semester->program->short_name ?: $semester->program->name }}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('university.programs.semesters.index', $semester->program) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                Semestres
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('university.course-units', $semester) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                UE - {{ $semester->name }}
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-500 dark:text-gray-400">Nouvelle UE</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Créer une Nouvelle Unité d'Enseignement</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Semestre : {{ $semester->name }} - {{ $semester->program->name }}
                    </p>
                </div>
                
                <a href="{{ route('university.course-units', $semester) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour aux UE
                </a>
            </div>
        </div>

        <!-- Informations contextuelles -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Information sur le Semestre</h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Crédits requis pour le semestre : {{ $semester->required_credits }} ECTS</li>
                            <li>Crédits déjà attribués : {{ $semester->courseUnits->sum('credits') }} ECTS</li>
                            <li>Crédits restants : {{ $semester->required_credits - $semester->courseUnits->sum('credits') }} ECTS</li>
                            <li>Nombre d'UE existantes : {{ $semester->courseUnits->count() }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de création -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails de l'Unité d'Enseignement</h3>
            </div>
            
            <form action="{{ route('university.semesters.course-units.store', $semester) }}" method="POST" class="p-6 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Code UE -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Code UE <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="code" 
                               id="code"
                               value="{{ old('code') }}"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('code') border-red-500 @enderror" 
                               required
                               placeholder="Ex: INF101, MAT201, PHY301...">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Identifiant unique pour l'unité d'enseignement
                        </p>
                    </div>

                    <!-- Nom UE -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom de l'UE <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name') }}"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror" 
                               required
                               placeholder="Ex: Programmation Orientée Objet, Mathématiques Appliquées...">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type UE -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type d'UE <span class="text-red-500">*</span>
                        </label>
                        <select name="type" 
                                id="type"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('type') border-red-500 @enderror" 
                                required>
                            <option value="">Sélectionnez un type</option>
                            <option value="mandatory" {{ old('type', 'mandatory') == 'mandatory' ? 'selected' : '' }}>Obligatoire</option>
                            <option value="optional" {{ old('type') == 'optional' ? 'selected' : '' }}>Optionnelle</option>
                            <option value="specialization" {{ old('type') == 'specialization' ? 'selected' : '' }}>Spécialisation</option>
                            <option value="project" {{ old('type') == 'project' ? 'selected' : '' }}>Projet</option>
                            <option value="internship" {{ old('type') == 'internship' ? 'selected' : '' }}>Stage</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Crédits ECTS -->
                    <div>
                        <label for="credits" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Crédits ECTS <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="credits" 
                               id="credits"
                               value="{{ old('credits') }}"
                               min="0.5" 
                               max="30"
                               step="0.5"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('credits') border-red-500 @enderror" 
                               required>
                        @error('credits')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Nombre de crédits ECTS attribués à cette UE
                        </p>
                    </div>

                    <!-- Coefficient -->
                    <div>
                        <label for="coefficient" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Coefficient
                        </label>
                        <input type="number" 
                               name="coefficient" 
                               id="coefficient"
                               value="{{ old('coefficient', 1) }}"
                               min="0.5" 
                               max="10"
                               step="0.5"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('coefficient') border-red-500 @enderror">
                        @error('coefficient')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Coefficient pour le calcul de la moyenne
                        </p>
                    </div>

                    <!-- Statut -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Statut <span class="text-red-500">*</span>
                        </label>
                        <select name="status" 
                                id="status"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('status') border-red-500 @enderror" 
                                required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                            <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Volume horaire -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Volume Horaire</h4>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Heures CM -->
                        <div>
                            <label for="hours_cm" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Cours Magistraux (CM)
                            </label>
                            <input type="number" 
                                   name="hours_cm" 
                                   id="hours_cm"
                                   value="{{ old('hours_cm', 0) }}"
                                   min="0" 
                                   max="200"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('hours_cm') border-red-500 @enderror">
                            @error('hours_cm')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Heures</p>
                        </div>

                        <!-- Heures TD -->
                        <div>
                            <label for="hours_td" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Travaux Dirigés (TD)
                            </label>
                            <input type="number" 
                                   name="hours_td" 
                                   id="hours_td"
                                   value="{{ old('hours_td', 0) }}"
                                   min="0" 
                                   max="200"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('hours_td') border-red-500 @enderror">
                            @error('hours_td')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Heures</p>
                        </div>

                        <!-- Heures TP -->
                        <div>
                            <label for="hours_tp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Travaux Pratiques (TP)
                            </label>
                            <input type="number" 
                                   name="hours_tp" 
                                   id="hours_tp"
                                   value="{{ old('hours_tp', 0) }}"
                                   min="0" 
                                   max="200"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('hours_tp') border-red-500 @enderror">
                            @error('hours_tp')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Heures</p>
                        </div>

                        <!-- Total automatique -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Total Heures
                            </label>
                            <div id="hours_total_display" class="w-full px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium">
                                0 h
                            </div>
                            <input type="hidden" name="hours_total" id="hours_total" value="{{ old('hours_total', 0) }}">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Calculé automatiquement</p>
                        </div>
                    </div>
                </div>

                <!-- Description et Prérequis -->
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description
                            </label>
                            <textarea name="description" 
                                      id="description"
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror"
                                      placeholder="Description du contenu de l'UE, objectifs pédagogiques...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prérequis -->
                        <div>
                            <label for="prerequisites" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Prérequis
                            </label>
                            <textarea name="prerequisites" 
                                      id="prerequisites"
                                      rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('prerequisites') border-red-500 @enderror"
                                      placeholder="UE ou connaissances préalables requises...">{{ old('prerequisites') }}</textarea>
                            @error('prerequisites')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="text-red-500">*</span> Champs obligatoires
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('university.course-units', $semester) }}" 
                           class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Annuler
                        </a>
                        
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            Créer l'UE
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Conseils -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
            <div class="flex">
                <svg class="w-5 h-5 text-yellow-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Conseils de configuration</h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-400">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Une UE fait généralement entre 3 et 6 crédits ECTS</li>
                            <li>Utilisez des codes courts et explicites (ex: INF101, MAT201)</li>
                            <li>Le volume horaire total doit être cohérent avec les crédits attribués</li>
                            <li>Vérifiez que le total ne dépasse pas les crédits requis du semestre</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Calcul automatique du total des heures
function calculateTotalHours() {
    const cm = parseFloat(document.getElementById('hours_cm').value) || 0;
    const td = parseFloat(document.getElementById('hours_td').value) || 0;
    const tp = parseFloat(document.getElementById('hours_tp').value) || 0;
    const total = cm + td + tp;
    
    document.getElementById('hours_total').value = total;
    document.getElementById('hours_total_display').textContent = total + ' h';
}

// Événements pour le calcul automatique
document.getElementById('hours_cm').addEventListener('input', calculateTotalHours);
document.getElementById('hours_td').addEventListener('input', calculateTotalHours);
document.getElementById('hours_tp').addEventListener('input', calculateTotalHours);

// Calcul initial au chargement de la page
document.addEventListener('DOMContentLoaded', calculateTotalHours);

// Auto-génération du code UE basé sur le nom
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const codeField = document.getElementById('code');
    
    // Si le champ code est vide, suggérer un code basé sur le nom
    if (!codeField.value && name.length > 3) {
        const words = name.split(' ');
        let code = '';
        
        // Prendre les premières lettres de chaque mot
        words.forEach((word, index) => {
            if (index < 2) { // Maximum 2 mots
                code += word.substring(0, 3).toUpperCase();
            }
        });
        
        // Ajouter un numéro par défaut
        code += '01';
        codeField.value = code;
    }
});
</script>
@endsection