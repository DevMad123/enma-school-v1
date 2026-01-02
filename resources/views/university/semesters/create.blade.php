@extends('layouts.dashboard')

@section('title', 'Nouveau Semestre - ' . $program->name)

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
                            <a href="{{ route('university.ufrs.show', $program->department->ufr) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                {{ $program->department->ufr->short_name ?: $program->department->ufr->name }}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('university.departments.show', $program->department) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                {{ $program->department->short_name ?: $program->department->name }}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('university.programs.show', $program) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                {{ $program->short_name ?: $program->name }}
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <a href="{{ route('university.semesters', $program) }}" class="text-gray-700 hover:text-purple-600 dark:text-gray-300">
                                Semestres
                            </a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-gray-500 dark:text-gray-400">Nouveau Semestre</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Créer un Nouveau Semestre</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Programme : {{ $program->name }}
                    </p>
                    @php
                        $currentAcademicYear = \App\Models\AcademicYear::currentForSchool(\App\Models\School::getActiveSchool()->id)->first();
                    @endphp
                    @if($currentAcademicYear)
                        <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                            📅 Année académique : {{ $currentAcademicYear->name }}
                        </p>
                    @else
                        <p class="text-sm text-red-600 dark:text-red-400 mt-1">
                            ⚠️ Aucune année académique courante définie
                        </p>
                    @endif
                </div>
                
                <a href="{{ route('university.semesters', $program) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour aux Semestres
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
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Information sur le Programme</h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Durée totale prévue : {{ $program->duration_semesters }} semestre(s)</li>
                            <li>Semestres déjà créés : {{ $program->semesters->count() }}</li>
                            @if($program->semesters->count() > 0)
                                <li>Numéro suggéré : S{{ $program->semesters->max('semester_number') + 1 }}</li>
                            @else
                                <li>Ce sera le premier semestre du programme</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de création -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Détails du Semestre</h3>
            </div>
            
            <form action="{{ route('university.semesters.store', $program) }}" method="POST" class="p-6 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Numéro de semestre -->
                    <div>
                        <label for="semester_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Numéro de Semestre <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="semester_number" 
                               id="semester_number"
                               value="{{ old('semester_number', $program->semesters->count() > 0 ? $program->semesters->max('semester_number') + 1 : 1) }}"
                               min="1" 
                               max="20"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('semester_number') border-red-500 @enderror" 
                               required>
                        @error('semester_number')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Numéro d'ordre du semestre dans le programme (1, 2, 3...)
                        </p>
                    </div>

                    <!-- Nom du semestre -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom du Semestre <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name') }}"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('name') border-red-500 @enderror" 
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Exemple : "Semestre 1", "Premier Semestre", "Fondamentaux"...
                        </p>
                    </div>

                    <!-- Crédits requis -->
                    <div>
                        <label for="required_credits" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Crédits ECTS Requis <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="required_credits" 
                               id="required_credits"
                               value="{{ old('required_credits', 30) }}"
                               min="1" 
                               max="90"
                               step="0.5"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('required_credits') border-red-500 @enderror" 
                               required>
                        @error('required_credits')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Nombre de crédits ECTS nécessaires pour valider le semestre (généralement 30)
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

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description"
                              rows="4"
                              class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white @error('description') border-red-500 @enderror"
                              placeholder="Description optionnelle du semestre, objectifs pédagogiques, prérequis...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons d'action -->
                <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="text-red-500">*</span> Champs obligatoires
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('university.semesters', $program) }}" 
                           class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Annuler
                        </a>
                        
                        <button type="submit" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            Créer le Semestre
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
                            <li>Un semestre universitaire fait généralement 30 crédits ECTS</li>
                            <li>Utilisez une numérotation séquentielle (S1, S2, S3...) pour faciliter le suivi</li>
                            <li>La description peut inclure les objectifs pédagogiques et les prérequis</li>
                            <li>Vous pourrez ajouter les Unités d'Enseignement après création du semestre</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-génération du nom basé sur le numéro
document.getElementById('semester_number').addEventListener('input', function() {
    const number = this.value;
    const nameField = document.getElementById('name');
    
    // Si le champ nom est vide, suggérer un nom
    if (!nameField.value || nameField.value.startsWith('Semestre ')) {
        if (number) {
            nameField.value = `Semestre ${number}`;
        } else {
            nameField.value = '';
        }
    }
});
</script>
@endsection