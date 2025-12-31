@extends('layouts.dashboard')

@section('title', 'Nouveau Paiement')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Enregistrer un Nouveau Paiement</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Saisir les détails d'un paiement d'étudiant</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('finance.payments.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour aux paiements
                </a>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('finance.payments.store') }}" class="space-y-6">
                    @csrf

                    <!-- Sélection de l'étudiant et des frais -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Étudiant et Frais</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Étudiant -->
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700">Étudiant *</label>
                                <select id="student_id" name="student_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="">Sélectionnez un étudiant</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->user->name }} - {{ $student->user->email }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('student_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Frais scolaire -->
                            <div>
                                <label for="school_fee_id" class="block text-sm font-medium text-gray-700">Type de Frais *</label>
                                <select id="school_fee_id" name="school_fee_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="">Sélectionnez un type de frais</option>
                                    @foreach($schoolFees as $fee)
                                        <option value="{{ $fee->id }}" 
                                                data-amount="{{ $fee->amount }}"
                                                {{ old('school_fee_id') == $fee->id ? 'selected' : '' }}>
                                            {{ $fee->name }} - {{ number_format($fee->amount, 0, ',', ' ') }} FCFA
                                            @if($fee->due_date)
                                                (Échéance: {{ $fee->due_date->format('d/m/Y') }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('school_fee_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Détails du paiement -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Détails du Paiement</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Montant -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Montant Payé (FCFA) *</label>
                                <input type="number" id="amount" name="amount" value="{{ old('amount') }}" 
                                       required min="0.01" step="0.01"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="0">
                                <p class="mt-1 text-sm text-gray-500">Peut être un paiement partiel ou total</p>
                                @error('amount')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date de paiement -->
                            <div>
                                <label for="payment_date" class="block text-sm font-medium text-gray-700">Date de Paiement *</label>
                                <input type="datetime-local" id="payment_date" name="payment_date" 
                                       value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                @error('payment_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- Méthode de paiement -->
                            <div>
                                <label for="payment_method" class="block text-sm font-medium text-gray-700">Méthode de Paiement *</label>
                                <select id="payment_method" name="payment_method" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    <option value="">Sélectionnez une méthode</option>
                                    <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Espèces</option>
                                    <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Virement bancaire</option>
                                    <option value="check" {{ old('payment_method') === 'check' ? 'selected' : '' }}>Chèque</option>
                                    <option value="card" {{ old('payment_method') === 'card' ? 'selected' : '' }}>Carte bancaire</option>
                                    <option value="mobile_money" {{ old('payment_method') === 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                </select>
                                @error('payment_method')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Référence de transaction -->
                            <div>
                                <label for="transaction_reference" class="block text-sm font-medium text-gray-700">Référence de Transaction</label>
                                <input type="text" id="transaction_reference" name="transaction_reference" 
                                       value="{{ old('transaction_reference') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                       placeholder="Numéro de chèque, référence virement...">
                                <p class="mt-1 text-sm text-gray-500">Pour les virements, chèques, etc.</p>
                                @error('transaction_reference')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mt-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                      placeholder="Informations supplémentaires sur ce paiement...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Information importante -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">
                                    Information importante
                                </h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Le paiement sera automatiquement confirmé après enregistrement</li>
                                        <li>Un reçu sera généré automatiquement</li>
                                        <li>Le solde de l'étudiant sera mis à jour automatiquement</li>
                                        <li>Vérifiez bien le montant avant de valider</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('finance.payments') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md text-sm font-medium">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                            Enregistrer le Paiement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-remplir le montant basé sur les frais sélectionnés
    document.getElementById('school_fee_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const amount = selectedOption.getAttribute('data-amount');
        if (amount) {
            document.getElementById('amount').value = amount;
        }
    });
</script>
@endpush
@endsection