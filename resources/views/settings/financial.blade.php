@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paramètres Financiers</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Configurez les paramètres financiers globaux de votre établissement
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
        <form action="{{ route('settings.financial.update') }}" method="POST" class="space-y-6 p-6">
            @csrf

            <!-- Paramètres généraux -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Paramètres généraux</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Devise par défaut -->
                    <div>
                        <label for="default_currency" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Devise par défaut
                        </label>
                        <select id="default_currency" 
                                name="default_currency" 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                            <option value="FCFA" {{ old('default_currency', $financialSettings['default_currency']) == 'FCFA' ? 'selected' : '' }}>
                                FCFA (Franc CFA)
                            </option>
                            <option value="EUR" {{ old('default_currency', $financialSettings['default_currency']) == 'EUR' ? 'selected' : '' }}>
                                EUR (Euro)
                            </option>
                            <option value="USD" {{ old('default_currency', $financialSettings['default_currency']) == 'USD' ? 'selected' : '' }}>
                                USD (Dollar américain)
                            </option>
                            <option value="MAD" {{ old('default_currency', $financialSettings['default_currency']) == 'MAD' ? 'selected' : '' }}>
                                MAD (Dirham marocain)
                            </option>
                            <option value="TND" {{ old('default_currency', $financialSettings['default_currency']) == 'TND' ? 'selected' : '' }}>
                                TND (Dinar tunisien)
                            </option>
                        </select>
                        @error('default_currency')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Délai de paiement -->
                    <div>
                        <label for="payment_terms_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Délai de paiement (jours)
                        </label>
                        <input type="number" 
                               id="payment_terms_days" 
                               name="payment_terms_days" 
                               min="1" 
                               max="365"
                               value="{{ old('payment_terms_days', $financialSettings['payment_terms_days']) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Nombre de jours accordés pour le paiement des frais scolaires
                        </p>
                        @error('payment_terms_days')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Frais de retard -->
                    <div>
                        <label for="late_payment_fee_percentage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Frais de retard (%)
                        </label>
                        <input type="number" 
                               id="late_payment_fee_percentage" 
                               name="late_payment_fee_percentage" 
                               min="0" 
                               max="100"
                               step="0.01"
                               value="{{ old('late_payment_fee_percentage', $financialSettings['late_payment_fee_percentage']) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Pourcentage appliqué sur les paiements en retard
                        </p>
                        @error('late_payment_fee_percentage')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Paiements en ligne -->
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="enable_online_payments" 
                               name="enable_online_payments" 
                               value="1"
                               {{ old('enable_online_payments', $financialSettings['enable_online_payments']) ? 'checked' : '' }}
                               class="mt-1 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                        <div class="ml-3">
                            <label for="enable_online_payments" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Activer les paiements en ligne
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Permettre aux parents de payer en ligne via des plateformes de paiement
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Rappels de paiement -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Rappels de paiement</h2>
                
                <div class="space-y-4">
                    <!-- Activer les rappels -->
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="enable_payment_reminders" 
                               name="enable_payment_reminders" 
                               value="1"
                               {{ old('enable_payment_reminders', $financialSettings['enable_payment_reminders']) ? 'checked' : '' }}
                               onchange="toggleReminderSettings()"
                               class="mt-1 rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                        <div class="ml-3">
                            <label for="enable_payment_reminders" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Activer les rappels automatiques
                            </label>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Envoyer des rappels automatiques avant l'échéance des paiements
                            </p>
                        </div>
                    </div>

                    <!-- Jours de rappel -->
                    <div id="reminder-settings" class="{{ old('enable_payment_reminders', $financialSettings['enable_payment_reminders']) ? '' : 'hidden' }}">
                        <label for="reminder_days_before" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Jours avant échéance pour les rappels
                        </label>
                        <input type="text" 
                               id="reminder_days_before" 
                               name="reminder_days_before" 
                               value="{{ old('reminder_days_before', $financialSettings['reminder_days_before']) }}"
                               placeholder="7,3,1"
                               class="w-full max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Séparez les jours par des virgules (ex: 7,3,1 pour des rappels à 7, 3 et 1 jour avant)
                        </p>
                        @error('reminder_days_before')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Informations bancaires -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informations bancaires</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom de la banque -->
                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom de la banque
                        </label>
                        <input type="text" 
                               id="bank_name" 
                               name="bank_name" 
                               value="{{ old('bank_name', $financialSettings['bank_name']) }}"
                               placeholder="Ex: Banque Centrale Populaire"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('bank_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Numéro de compte -->
                    <div>
                        <label for="bank_account_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Numéro de compte
                        </label>
                        <input type="text" 
                               id="bank_account_number" 
                               name="bank_account_number" 
                               value="{{ old('bank_account_number', $financialSettings['bank_account_number']) }}"
                               placeholder="Ex: 123456789012345"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        @error('bank_account_number')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code SWIFT -->
                    <div class="md:col-span-2">
                        <label for="bank_swift_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Code SWIFT/BIC
                        </label>
                        <input type="text" 
                               id="bank_swift_code" 
                               name="bank_swift_code" 
                               value="{{ old('bank_swift_code', $financialSettings['bank_swift_code']) }}"
                               placeholder="Ex: BCMAMAMA"
                               class="w-full max-w-md px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Code d'identification internationale de la banque (pour les virements internationaux)
                        </p>
                        @error('bank_swift_code')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            <!-- Résumé des paramètres -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Résumé de la configuration</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div class="bg-white dark:bg-gray-600 rounded p-3">
                        <span class="block text-gray-500 dark:text-gray-400">Devise</span>
                        <span class="font-medium text-gray-900 dark:text-white" id="summary-currency">
                            {{ $financialSettings['default_currency'] }}
                        </span>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-600 rounded p-3">
                        <span class="block text-gray-500 dark:text-gray-400">Délai de paiement</span>
                        <span class="font-medium text-gray-900 dark:text-white" id="summary-terms">
                            {{ $financialSettings['payment_terms_days'] }} jours
                        </span>
                    </div>
                    
                    <div class="bg-white dark:bg-gray-600 rounded p-3">
                        <span class="block text-gray-500 dark:text-gray-400">Frais de retard</span>
                        <span class="font-medium text-gray-900 dark:text-white" id="summary-late-fee">
                            {{ $financialSettings['late_payment_fee_percentage'] }}%
                        </span>
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
function toggleReminderSettings() {
    const checkbox = document.getElementById('enable_payment_reminders');
    const settings = document.getElementById('reminder-settings');
    
    if (checkbox.checked) {
        settings.classList.remove('hidden');
    } else {
        settings.classList.add('hidden');
    }
}

// Update summary when values change
document.getElementById('default_currency').addEventListener('change', function() {
    document.getElementById('summary-currency').textContent = this.value;
});

document.getElementById('payment_terms_days').addEventListener('input', function() {
    document.getElementById('summary-terms').textContent = this.value + ' jours';
});

document.getElementById('late_payment_fee_percentage').addEventListener('input', function() {
    document.getElementById('summary-late-fee').textContent = this.value + '%';
});
</script>
@endsection