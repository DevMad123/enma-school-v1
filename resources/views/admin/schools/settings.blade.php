@extends('layouts.dashboard')

@section('title', 'Paramètres de l\'Établissement')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.schools.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paramètres Avancés</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $school->name }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto">
    <form action="{{ route('admin.schools.settings.update', $school) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Paramètres Généraux -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Paramètres Généraux</h3>
                
                <div class="space-y-4">
                    <!-- Devise de l'école -->
                    <div>
                        <label for="school_motto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Devise de l'établissement
                        </label>
                        <input type="text" 
                               name="settings[school_motto]" 
                               id="school_motto"
                               value="{{ old('settings.school_motto', $settings['school_motto']->value ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ex: Excellence et Innovation">
                        <p class="mt-1 text-xs text-gray-500">Cette devise apparaîtra sur les documents officiels</p>
                    </div>

                    <!-- Code établissement -->
                    <div>
                        <label for="school_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Code de l'établissement
                        </label>
                        <input type="text" 
                               name="settings[school_code]" 
                               id="school_code"
                               value="{{ old('settings.school_code', $settings['school_code']->value ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ex: ES001">
                        <p class="mt-1 text-xs text-gray-500">Code unique d'identification de l'établissement</p>
                    </div>

                    <!-- Site web -->
                    <div>
                        <label for="school_website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Site web
                        </label>
                        <input type="url" 
                               name="settings[school_website]" 
                               id="school_website"
                               value="{{ old('settings.school_website', $settings['school_website']->value ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="https://www.etablissement.edu.ci">
                    </div>
                </div>
            </div>
        </div>

        <!-- Année Scolaire -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Année Scolaire</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date de début -->
                    <div>
                        <label for="academic_year_start" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Date de début d'année
                        </label>
                        <input type="date" 
                               name="settings[academic_year_start]" 
                               id="academic_year_start"
                               value="{{ old('settings.academic_year_start', $settings['academic_year_start']->value ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Date de fin -->
                    <div>
                        <label for="academic_year_end" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Date de fin d'année
                        </label>
                        <input type="date" 
                               name="settings[academic_year_end]" 
                               id="academic_year_end"
                               value="{{ old('settings.academic_year_end', $settings['academic_year_end']->value ?? '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres Pédagogiques -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Paramètres Pédagogiques</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Effectif maximum par classe -->
                    <div>
                        <label for="max_students_per_class" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Effectif max par classe
                        </label>
                        <input type="number" 
                               name="settings[max_students_per_class]" 
                               id="max_students_per_class"
                               min="1"
                               max="100"
                               value="{{ old('settings.max_students_per_class', $settings['max_students_per_class']->value ?? '35') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Note de passage -->
                    <div>
                        <label for="passing_grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Note de passage
                        </label>
                        <input type="number" 
                               name="settings[passing_grade]" 
                               id="passing_grade"
                               step="0.1"
                               min="0"
                               max="{{ $school->grading_system }}"
                               value="{{ old('settings.passing_grade', $settings['passing_grade']->value ?? '10') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Sur {{ $school->grading_system }}</p>
                    </div>

                    <!-- Note d'excellence -->
                    <div>
                        <label for="excellence_grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Note d'excellence
                        </label>
                        <input type="number" 
                               name="settings[excellence_grade]" 
                               id="excellence_grade"
                               step="0.1"
                               min="0"
                               max="{{ $school->grading_system }}"
                               value="{{ old('settings.excellence_grade', $settings['excellence_grade']->value ?? '16') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Sur {{ $school->grading_system }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres des Bulletins -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Paramètres des Bulletins</h3>
                
                <div class="space-y-4">
                    <!-- Message du directeur -->
                    <div>
                        <label for="bulletin_director_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Message du directeur (sur les bulletins)
                        </label>
                        <textarea name="settings[bulletin_director_message]" 
                                  id="bulletin_director_message"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Message qui apparaîtra sur tous les bulletins...">{{ old('settings.bulletin_director_message', $settings['bulletin_director_message']->value ?? '') }}</textarea>
                    </div>

                    <!-- Pied de page des bulletins -->
                    <div>
                        <label for="bulletin_footer" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pied de page des bulletins
                        </label>
                        <textarea name="settings[bulletin_footer]" 
                                  id="bulletin_footer"
                                  rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Informations de contact ou message institutionnel...">{{ old('settings.bulletin_footer', $settings['bulletin_footer']->value ?? '') }}</textarea>
                    </div>

                    <!-- Affichage des appréciations -->
                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="settings[show_appreciation_on_bulletin]" 
                               id="show_appreciation_on_bulletin"
                               value="1"
                               {{ old('settings.show_appreciation_on_bulletin', $settings['show_appreciation_on_bulletin']->value ?? '') ? 'checked' : '' }}
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="show_appreciation_on_bulletin" class="ml-2 block text-sm text-gray-900 dark:text-white">
                            Afficher les appréciations sur les bulletins
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres Régionaux -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Paramètres Régionaux</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Fuseau horaire -->
                    <div>
                        <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fuseau horaire
                        </label>
                        <select name="settings[timezone]" 
                                id="timezone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="Africa/Abidjan" {{ old('settings.timezone', $settings['timezone']->value ?? 'Africa/Abidjan') === 'Africa/Abidjan' ? 'selected' : '' }}>Abidjan (GMT)</option>
                            <option value="Europe/Paris" {{ old('settings.timezone', $settings['timezone']->value ?? '') === 'Europe/Paris' ? 'selected' : '' }}>Paris (GMT+1)</option>
                            <option value="Africa/Cairo" {{ old('settings.timezone', $settings['timezone']->value ?? '') === 'Africa/Cairo' ? 'selected' : '' }}>Le Caire (GMT+2)</option>
                            <option value="America/New_York" {{ old('settings.timezone', $settings['timezone']->value ?? '') === 'America/New_York' ? 'selected' : '' }}>New York (GMT-5)</option>
                        </select>
                    </div>

                    <!-- Devise -->
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Devise
                        </label>
                        <select name="settings[currency]" 
                                id="currency"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="FCFA" {{ old('settings.currency', $settings['currency']->value ?? 'FCFA') === 'FCFA' ? 'selected' : '' }}>FCFA (Franc CFA)</option>
                            <option value="EUR" {{ old('settings.currency', $settings['currency']->value ?? '') === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                            <option value="USD" {{ old('settings.currency', $settings['currency']->value ?? '') === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                            <option value="MAD" {{ old('settings.currency', $settings['currency']->value ?? '') === 'MAD' ? 'selected' : '' }}>MAD (Dirham)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres de Notification -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Notifications</h3>
                
                <div class="space-y-4">
                    <!-- Email de notification -->
                    <div>
                        <label for="notification_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email de notification système
                        </label>
                        <input type="email" 
                               name="settings[notification_email]" 
                               id="notification_email"
                               value="{{ old('settings.notification_email', $settings['notification_email']->value ?? $school->email) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="notifications@etablissement.edu.ci">
                        <p class="mt-1 text-xs text-gray-500">Email qui recevra les notifications système importantes</p>
                    </div>

                    <!-- Fréquence des rapports -->
                    <div>
                        <label for="report_frequency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Fréquence des rapports automatiques
                        </label>
                        <select name="settings[report_frequency]" 
                                id="report_frequency"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="weekly" {{ old('settings.report_frequency', $settings['report_frequency']->value ?? '') === 'weekly' ? 'selected' : '' }}>Hebdomadaire</option>
                            <option value="monthly" {{ old('settings.report_frequency', $settings['report_frequency']->value ?? '') === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                            <option value="quarterly" {{ old('settings.report_frequency', $settings['report_frequency']->value ?? '') === 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
                            <option value="never" {{ old('settings.report_frequency', $settings['report_frequency']->value ?? '') === 'never' ? 'selected' : '' }}>Jamais</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres Personnalisés -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Paramètres Personnalisés</h3>
                    <button type="button" 
                            onclick="addCustomSetting()" 
                            class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded transition duration-150">
                        + Ajouter un paramètre
                    </button>
                </div>
                
                <div id="custom-settings" class="space-y-3">
                    @foreach($settings as $key => $setting)
                        @if(!in_array($key, ['school_motto', 'school_code', 'school_website', 'academic_year_start', 'academic_year_end', 'max_students_per_class', 'passing_grade', 'excellence_grade', 'bulletin_director_message', 'bulletin_footer', 'show_appreciation_on_bulletin', 'notification_email', 'report_frequency']))
                        <div class="flex items-center space-x-3">
                            <input type="text" 
                                   value="{{ $key }}" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50" 
                                   readonly>
                            <input type="text" 
                                   name="settings[{{ $key }}]" 
                                   value="{{ $setting->value }}"
                                   class="flex-2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Valeur">
                            <button type="button" 
                                    onclick="removeCustomSetting(this)" 
                                    class="text-red-600 hover:text-red-800 transition duration-150">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        @endif
                    @endforeach
                </div>
                
                @if($settings->whereNotIn('key', ['school_motto', 'school_code', 'school_website', 'academic_year_start', 'academic_year_end', 'max_students_per_class', 'passing_grade', 'excellence_grade', 'bulletin_director_message', 'bulletin_footer', 'show_appreciation_on_bulletin', 'notification_email', 'report_frequency'])->isEmpty())
                <p class="text-sm text-gray-500 text-center py-4">Aucun paramètre personnalisé configuré</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.schools.index') }}" 
               class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 transition duration-150">
                Annuler
            </a>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-150">
                Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let customSettingCounter = 0;

function addCustomSetting() {
    const container = document.getElementById('custom-settings');
    const settingDiv = document.createElement('div');
    settingDiv.className = 'flex items-center space-x-3';
    settingDiv.innerHTML = `
        <input type="text" 
               name="custom_key_${customSettingCounter}" 
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
               placeholder="Nom du paramètre">
        <input type="text" 
               name="custom_value_${customSettingCounter}" 
               class="flex-2 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
               placeholder="Valeur">
        <button type="button" 
                onclick="removeCustomSetting(this)" 
                class="text-red-600 hover:text-red-800 transition duration-150">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
        </button>
    `;
    
    // Remove empty message if exists
    const emptyMessage = container.querySelector('.text-center');
    if (emptyMessage) {
        emptyMessage.remove();
    }
    
    container.appendChild(settingDiv);
    customSettingCounter++;
}

function removeCustomSetting(button) {
    button.closest('.flex').remove();
    
    // Show empty message if no custom settings remain
    const container = document.getElementById('custom-settings');
    if (container.children.length === 0) {
        container.innerHTML = '<p class="text-sm text-gray-500 text-center py-4">Aucun paramètre personnalisé configuré</p>';
    }
}
</script>
@endpush
@endsection