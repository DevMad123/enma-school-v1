@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Informations de l'École</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Configurez les informations générales de votre établissement scolaire
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
        <form action="{{ route('settings.school.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 p-6">
            @csrf
            @method('PUT')

            <!-- Informations de base -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations de base</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom de l'école -->
                    <div>
                        <label for="school_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom de l'école
                        </label>
                        <input type="text" 
                               id="school_name" 
                               name="school_name" 
                               value="{{ old('school_name', $settings['school_name']) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                               required>
                        @error('school_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email de l'école -->
                    <div>
                        <label for="school_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email de contact
                        </label>
                        <input type="email" 
                               id="school_email" 
                               name="school_email" 
                               value="{{ old('school_email', $settings['school_email']) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('school_email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label for="school_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Téléphone
                        </label>
                        <input type="text" 
                               id="school_phone" 
                               name="school_phone" 
                               value="{{ old('school_phone', $settings['school_phone']) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('school_phone')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Site web -->
                    <div>
                        <label for="school_website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Site web
                        </label>
                        <input type="url" 
                               id="school_website" 
                               name="school_website" 
                               value="{{ old('school_website', $settings['school_website']) }}"
                               placeholder="https://exemple.com"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('school_website')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Adresse -->
                <div class="mt-6">
                    <label for="school_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Adresse complète
                    </label>
                    <textarea id="school_address" 
                              name="school_address" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                              placeholder="Adresse de l'établissement...">{{ old('school_address', $settings['school_address']) }}</textarea>
                    @error('school_address')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Logo et image -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Logo de l'école</h2>
                
                <div class="flex items-center space-x-6">
                    @if($settings['school_logo'])
                        <div class="flex-shrink-0">
                            <img src="{{ Storage::url($settings['school_logo']) }}" 
                                 alt="Logo de l'école" 
                                 class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                        </div>
                    @endif
                    
                    <div class="flex-1">
                        <label for="school_logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ $settings['school_logo'] ? 'Changer le logo' : 'Ajouter un logo' }}
                        </label>
                        <input type="file" 
                               id="school_logo" 
                               name="school_logo" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Formats acceptés : JPEG, PNG, JPG, GIF. Taille maximale : 2MB.
                        </p>
                        @error('school_logo')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Paramètres régionaux -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Paramètres régionaux</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Devise -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Devise
                        </label>
                        <select id="currency" 
                                name="currency" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="FCFA" {{ old('currency', $settings['currency']) == 'FCFA' ? 'selected' : '' }}>FCFA (Franc CFA)</option>
                            <option value="EUR" {{ old('currency', $settings['currency']) == 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                            <option value="USD" {{ old('currency', $settings['currency']) == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                            <option value="MAD" {{ old('currency', $settings['currency']) == 'MAD' ? 'selected' : '' }}>MAD (Dirham)</option>
                        </select>
                        @error('currency')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fuseau horaire -->
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fuseau horaire
                        </label>
                        <select id="timezone" 
                                name="timezone" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="Africa/Abidjan" {{ old('timezone', $settings['timezone']) == 'Africa/Abidjan' ? 'selected' : '' }}>Abidjan (GMT)</option>
                            <option value="Africa/Casablanca" {{ old('timezone', $settings['timezone']) == 'Africa/Casablanca' ? 'selected' : '' }}>Casablanca (GMT+1)</option>
                            <option value="Europe/Paris" {{ old('timezone', $settings['timezone']) == 'Europe/Paris' ? 'selected' : '' }}>Paris (GMT+1)</option>
                            <option value="Africa/Tunis" {{ old('timezone', $settings['timezone']) == 'Africa/Tunis' ? 'selected' : '' }}>Tunis (GMT+1)</option>
                        </select>
                        @error('timezone')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
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
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection