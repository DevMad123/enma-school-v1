@extends('layouts.dashboard')

@section('title', 'Créer un Établissement')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Configuration de l'Établissement</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Créez votre établissement pour commencer à utiliser l'application</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto">
    <form action="{{ route('admin.schools.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        
        <!-- Informations Générales -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informations Générales</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom de l'établissement -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom de l'établissement <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name"
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Ex: Lycée Moderne de Cocody"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nom abrégé -->
                    <div>
                        <label for="short_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom abrégé
                        </label>
                        <input type="text" 
                               name="short_name" 
                               id="short_name"
                               value="{{ old('short_name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('short_name') border-red-500 @enderror"
                               placeholder="Ex: LMC">
                        @error('short_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type d'établissement -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Type d'établissement <span class="text-red-500">*</span>
                        </label>
                        <select name="type" 
                                id="type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror"
                                required
                                onchange="toggleEducationalLevels()">
                            <option value="">Sélectionner un type</option>
                            <option value="pre_university" {{ old('type') === 'pre_university' ? 'selected' : '' }}>Pré-universitaire</option>
                            <option value="university" {{ old('type') === 'university' ? 'selected' : '' }}>Universitaire</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            <strong>Pré-universitaire :</strong> Écoles primaires, secondaires, techniques<br>
                            <strong>Universitaire :</strong> Établissements d'enseignement supérieur
                        </p>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Niveaux éducatifs (visible uniquement pour pré-universitaire) -->
                    <div id="educational-levels-section" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Niveaux éducatifs gérés <span class="text-red-500">*</span>
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="educational_levels[]" 
                                       value="primary"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                       {{ in_array('primary', old('educational_levels', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    <strong>Primaire</strong> - CP1 à CM2
                                </span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="educational_levels[]" 
                                       value="secondary"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                       {{ in_array('secondary', old('educational_levels', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    <strong>Secondaire</strong> - 6e à Terminale
                                </span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="educational_levels[]" 
                                       value="technical"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                       {{ in_array('technical', old('educational_levels', [])) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                    <strong>Technique/Professionnel</strong> - Formations techniques
                                </span>
                            </label>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Sélectionnez tous les niveaux que votre établissement gère
                        </p>
                        @error('educational_levels')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Adresse email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               id="email"
                               value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="contact@etablissement.edu.ci"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Téléphone
                        </label>
                        <input type="tel" 
                               name="phone" 
                               id="phone"
                               value="{{ old('phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-500 @enderror"
                               placeholder="+225 XX XX XX XX XX">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Ville -->
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Ville
                        </label>
                        <input type="text" 
                               name="city" 
                               id="city"
                               value="{{ old('city') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('city') border-red-500 @enderror"
                               placeholder="Ex: Abidjan">
                        @error('city')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pays -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pays
                        </label>
                        <input type="text" 
                               name="country" 
                               id="country"
                               value="{{ old('country', 'Côte d\'Ivoire') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('country') border-red-500 @enderror">
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Adresse -->
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Adresse
                        </label>
                        <textarea name="address" 
                                  id="address"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-500 @enderror"
                                  placeholder="Adresse complète de l'établissement">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Académique -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Configuration Académique</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Système académique -->
                    <div>
                        <label for="academic_system" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Système académique <span class="text-red-500">*</span>
                        </label>
                        <select name="academic_system" 
                                id="academic_system"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('academic_system') border-red-500 @enderror"
                                required>
                            <option value="">Sélectionner un système</option>
                            <option value="trimestre" {{ old('academic_system') === 'trimestre' ? 'selected' : '' }}>Trimestre</option>
                            <option value="semestre" {{ old('academic_system') === 'semestre' ? 'selected' : '' }}>Semestre</option>
                        </select>
                        @error('academic_system')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Système de notation -->
                    <div>
                        <label for="grading_system" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Système de notation <span class="text-red-500">*</span>
                        </label>
                        <select name="grading_system" 
                                id="grading_system"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('grading_system') border-red-500 @enderror"
                                required>
                            <option value="">Sélectionner un système</option>
                            <option value="20" {{ old('grading_system') === '20' ? 'selected' : '' }}>Sur 20</option>
                            <option value="100" {{ old('grading_system') === '100' ? 'selected' : '' }}>Sur 100</option>
                            <option value="custom" {{ old('grading_system') === 'custom' ? 'selected' : '' }}>Personnalisé</option>
                        </select>
                        @error('grading_system')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Officiels -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Documents Officiels</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Ces documents seront utilisés dans les bulletins et documents officiels.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Logo -->
                    <div>
                        <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Logo de l'établissement
                        </label>
                        <input type="file" 
                               name="logo" 
                               id="logo"
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('logo') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF jusqu'à 2MB</p>
                        @error('logo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tampon -->
                    <div>
                        <label for="stamp" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tampon officiel
                        </label>
                        <input type="file" 
                               name="stamp" 
                               id="stamp"
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('stamp') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF jusqu'à 2MB</p>
                        @error('stamp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Signature -->
                    <div>
                        <label for="signature" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Signature officielle
                        </label>
                        <input type="file" 
                               name="signature" 
                               id="signature"
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('signature') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF jusqu'à 2MB</p>
                        @error('signature')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('dashboard') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150">
                Annuler
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                Créer l'établissement
            </button>
        </div>
    </form>
</div>

<script>
function toggleEducationalLevels() {
    const typeSelect = document.getElementById('type');
    const educationalLevelsSection = document.getElementById('educational-levels-section');
    
    if (typeSelect.value === 'pre_university') {
        educationalLevelsSection.classList.remove('hidden');
    } else {
        educationalLevelsSection.classList.add('hidden');
        // Décocher toutes les cases
        const checkboxes = educationalLevelsSection.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => checkbox.checked = false);
    }
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    toggleEducationalLevels();
});
</script>
@endsection