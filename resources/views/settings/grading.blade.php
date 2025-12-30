@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Système de Notation</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Configurez les barèmes et échelles de notation pour votre établissement
            </p>
        </div>
        <a href="{{ route('settings.index') }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour
        </a>
    </div>

    <!-- Formulaire -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <form action="{{ route('settings.grading.update') }}" method="POST" class="space-y-6 p-6">
            @csrf

            <!-- Échelle de notation -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Échelle de notation</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Type d'échelle -->
                    <div>
                        <label for="grading_scale" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Échelle de base
                        </label>
                        <select id="grading_scale" 
                                name="grading_scale" 
                                onchange="toggleGradingOptions()"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="20" {{ old('grading_scale', $gradingSettings['grading_scale']) == '20' ? 'selected' : '' }}>
                                Sur 20 points
                            </option>
                            <option value="100" {{ old('grading_scale', $gradingSettings['grading_scale']) == '100' ? 'selected' : '' }}>
                                Sur 100 points
                            </option>
                            <option value="letter" {{ old('grading_scale', $gradingSettings['grading_scale']) == 'letter' ? 'selected' : '' }}>
                                Lettres (A, B, C, D, F)
                            </option>
                        </select>
                        @error('grading_scale')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note de passage -->
                    <div id="passing-grade-section">
                        <label for="passing_grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Note de passage
                        </label>
                        <input type="number" 
                               id="passing_grade" 
                               name="passing_grade" 
                               step="0.01"
                               value="{{ old('passing_grade', $gradingSettings['passing_grade']) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('passing_grade')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Note d'excellence -->
                    <div id="excellence-grade-section">
                        <label for="excellence_grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Note d'excellence
                        </label>
                        <input type="number" 
                               id="excellence_grade" 
                               name="excellence_grade" 
                               step="0.01"
                               value="{{ old('excellence_grade', $gradingSettings['excellence_grade']) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('excellence_grade')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Précision des notes -->
                <div class="mt-6">
                    <label for="grade_precision" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nombre de décimales
                    </label>
                    <select id="grade_precision" 
                            name="grade_precision" 
                            class="w-full max-w-xs px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="0" {{ old('grade_precision', $gradingSettings['grade_precision']) == '0' ? 'selected' : '' }}>0 décimale (ex: 15)</option>
                        <option value="1" {{ old('grade_precision', $gradingSettings['grade_precision']) == '1' ? 'selected' : '' }}>1 décimale (ex: 15.5)</option>
                        <option value="2" {{ old('grade_precision', $gradingSettings['grade_precision']) == '2' ? 'selected' : '' }}>2 décimales (ex: 15.75)</option>
                        <option value="3" {{ old('grade_precision', $gradingSettings['grade_precision']) == '3' ? 'selected' : '' }}>3 décimales (ex: 15.753)</option>
                    </select>
                    @error('grade_precision')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Affichage des lettres -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Affichage des notes</h2>
                
                <div class="flex items-center mb-4">
                    <input type="checkbox" 
                           id="display_letter_grades" 
                           name="display_letter_grades" 
                           value="1"
                           {{ old('display_letter_grades', $gradingSettings['display_letter_grades']) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                    <label for="display_letter_grades" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                        Afficher les équivalences en lettres (A, B, C, etc.)
                    </label>
                </div>

                <!-- Barème des lettres -->
                <div id="letter-scale-section" class="{{ old('display_letter_grades', $gradingSettings['display_letter_grades']) ? '' : 'hidden' }}">
                    <h3 class="text-md font-medium text-gray-900 dark:text-white mb-3">Barème des lettres</h3>
                    
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                            @php
                                $letterGradeScale = $gradingSettings['letter_grade_scale'];
                            @endphp
                            
                            @foreach($letterGradeScale as $letter => $range)
                                <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-600 rounded border">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $letter }}</span>
                                    <span class="text-gray-600 dark:text-gray-300">
                                        {{ $range['min'] }} - {{ $range['max'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        
                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            Les barèmes s'ajustent automatiquement selon l'échelle choisie (20 ou 100 points).
                        </p>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Exemples visuels -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Aperçu du système</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Note excellente -->
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="font-medium text-green-800 dark:text-green-300">Excellence</span>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-green-900 dark:text-green-200">
                            {{ $gradingSettings['excellence_grade'] }}{{ $gradingSettings['grading_scale'] != 'letter' ? '/'.$gradingSettings['grading_scale'] : '' }}
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-400">
                            Mention Très Bien
                        </p>
                    </div>

                    <!-- Note de passage -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="font-medium text-blue-800 dark:text-blue-300">Passage</span>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-blue-900 dark:text-blue-200">
                            {{ $gradingSettings['passing_grade'] }}{{ $gradingSettings['grading_scale'] != 'letter' ? '/'.$gradingSettings['grading_scale'] : '' }}
                        </p>
                        <p class="text-sm text-blue-700 dark:text-blue-400">
                            Note minimum requise
                        </p>
                    </div>

                    <!-- Précision -->
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                            <span class="font-medium text-purple-800 dark:text-purple-300">Précision</span>
                        </div>
                        <p class="mt-2 text-2xl font-bold text-purple-900 dark:text-purple-200">
                            {{ $gradingSettings['grade_precision'] }}
                        </p>
                        <p class="text-sm text-purple-700 dark:text-purple-400">
                            Décimale(s)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('settings.index') }}" 
                   class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleGradingOptions() {
    const scale = document.getElementById('grading_scale').value;
    const passingSection = document.getElementById('passing-grade-section');
    const excellenceSection = document.getElementById('excellence-grade-section');
    
    if (scale === 'letter') {
        passingSection.style.display = 'none';
        excellenceSection.style.display = 'none';
    } else {
        passingSection.style.display = 'block';
        excellenceSection.style.display = 'block';
    }
}

// Toggle letter scale visibility
document.getElementById('display_letter_grades').addEventListener('change', function() {
    const letterSection = document.getElementById('letter-scale-section');
    if (this.checked) {
        letterSection.classList.remove('hidden');
    } else {
        letterSection.classList.add('hidden');
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleGradingOptions();
});
</script>
@endsection