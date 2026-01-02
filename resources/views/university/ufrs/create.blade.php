@extends('layouts.dashboard')

@section('title', 'Créer une UFR')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Créer une nouvelle UFR</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Définissez une nouvelle Unité de Formation et de Recherche
                    </p>
                </div>
                <a href="{{ route('university.ufrs.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la liste
                </a>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <form action="{{ route('university.ufrs.store') }}" method="POST">
                @csrf
                
                <div class="p-6 space-y-6">
                    <!-- Informations de base -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informations générales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom complet -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom complet de l'UFR <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Unité de Formation et de Recherche en Sciences et Technologies"
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nom court -->
                            <div>
                                <label for="short_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom court (optionnel)
                                </label>
                                <input type="text" id="short_name" name="short_name" value="{{ old('short_name') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: UFR-ST">
                                @error('short_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Code -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Code UFR <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="code" name="code" value="{{ old('code') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: UFR01, ST2024"
                                       required>
                                @error('code')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Code unique d'identification de l'UFR</p>
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="3" 
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                          placeholder="Description des missions et domaines d'expertise de l'UFR...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Direction et Administration -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Direction et Administration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom du Doyen -->
                            <div>
                                <label for="dean_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom du Doyen
                                </label>
                                <input type="text" id="dean_name" name="dean_name" value="{{ old('dean_name') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Pr. Jean DUPONT">
                                @error('dean_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email de contact -->
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email de contact
                                </label>
                                <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="contact@ufr.universite.edu">
                                @error('contact_email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Téléphone -->
                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Téléphone
                                </label>
                                <input type="text" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="+33 1 23 45 67 89">
                                @error('contact_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Localisation -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Localisation</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Bâtiment -->
                            <div>
                                <label for="building" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Bâtiment
                                </label>
                                <input type="text" id="building" name="building" value="{{ old('building') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Bâtiment A, Campus Nord">
                                @error('building')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Statut -->
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Statut
                                </label>
                                <select id="is_active" name="is_active" 
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white">
                                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Adresse complète -->
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Adresse complète
                                </label>
                                <textarea id="address" name="address" rows="2" 
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-purple-500 focus:ring-purple-500 dark:bg-gray-700 dark:text-white" 
                                          placeholder="Adresse physique de l'UFR...">{{ old('address') }}</textarea>
                                @error('address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Informations académiques -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informations académiques</h3>
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h4 class="font-medium text-blue-800 dark:text-blue-200">Informations importantes</h4>
                                    <ul class="text-sm text-blue-700 dark:text-blue-300 mt-1 list-disc list-inside space-y-1">
                                        <li>Une UFR peut contenir plusieurs départements</li>
                                        <li>Chaque département peut proposer différents programmes d'études</li>
                                        <li>Le code UFR doit être unique dans l'université</li>
                                        <li>Vous pourrez ajouter des départements après la création de l'UFR</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-600 rounded-b-lg">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('university.ufrs.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Créer l'UFR
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-generation du code basé sur le nom
document.getElementById('name').addEventListener('input', function(e) {
    const name = e.target.value;
    const codeInput = document.getElementById('code');
    
    if (!codeInput.value || codeInput.dataset.autoGenerated === 'true') {
        // Générer un code automatique
        const words = name.split(' ').filter(word => word.length > 2);
        let code = '';
        
        if (words.length >= 2) {
            code = words.slice(0, 3).map(word => word.charAt(0).toUpperCase()).join('');
        } else if (words.length === 1) {
            code = words[0].substring(0, 3).toUpperCase();
        }
        
        if (code) {
            codeInput.value = code + new Date().getFullYear().toString().slice(-2);
            codeInput.dataset.autoGenerated = 'true';
        }
    }
});

document.getElementById('code').addEventListener('input', function(e) {
    // Marquer que l'utilisateur a modifié le code manuellement
    e.target.dataset.autoGenerated = 'false';
});
</script>
@endsection