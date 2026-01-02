@extends('layouts.dashboard')

@section('title', 'Modifier le département : ' . $department->name)

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier le département</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Modifiez les informations du département {{ $department->name }}</p>
                    
                    <div class="mt-3 text-sm">
                        <span class="text-gray-500">UFR :</span>
                        <a href="{{ route('university.ufrs.show', $department->ufr) }}" 
                           class="text-purple-600 hover:text-purple-800 dark:text-purple-400 font-medium">
                            {{ $department->ufr->name }}
                        </a>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('university.departments.show', $department) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Annuler
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <form action="{{ route('university.departments.update', $department) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Informations générales</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- UFR d'appartenance -->
                    <div class="col-span-2">
                        <label for="ufr_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            UFR d'appartenance *
                        </label>
                        <select name="ufr_id" id="ufr_id" required 
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionnez une UFR</option>
                            @foreach($ufrs as $ufr)
                                <option value="{{ $ufr->id }}" {{ $department->ufr_id == $ufr->id ? 'selected' : '' }}>
                                    {{ $ufr->name }} ({{ $ufr->code }})
                                </option>
                            @endforeach
                        </select>
                        @error('ufr_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nom du département -->
                    <div class="col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom du département *
                        </label>
                        <input type="text" name="name" id="name" required
                               value="{{ old('name', $department->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="ex: Département d'Informatique et Mathématiques Appliquées">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code du département -->
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Code du département *
                        </label>
                        <input type="text" name="code" id="code" required
                               value="{{ old('code', $department->code) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="ex: DIMA">
                        @error('code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nom abrégé -->
                    <div>
                        <label for="short_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Nom abrégé
                        </label>
                        <input type="text" name="short_name" id="short_name"
                               value="{{ old('short_name', $department->short_name) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="ex: Dépt. Info">
                        @error('short_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="4"
                                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                  placeholder="Description du département, ses domaines d'expertise...">{{ old('description', $department->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Informations de direction -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Direction et contacts</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Chef de département -->
                    <div class="col-span-2">
                        <label for="head_of_department" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Chef de département
                        </label>
                        <input type="text" name="head_of_department" id="head_of_department"
                               value="{{ old('head_of_department', $department->head_of_department) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="ex: Prof. Jean DUPONT">
                        @error('head_of_department')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email de contact -->
                    <div>
                        <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Email de contact
                        </label>
                        <input type="email" name="contact_email" id="contact_email"
                               value="{{ old('contact_email', $department->contact_email) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="chef.departement@universite.edu">
                        @error('contact_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Téléphone de contact -->
                    <div>
                        <label for="contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Téléphone de contact
                        </label>
                        <input type="tel" name="contact_phone" id="contact_phone"
                               value="{{ old('contact_phone', $department->contact_phone) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="+33 1 23 45 67 89">
                        @error('contact_phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Localisation -->
                    <div class="col-span-2">
                        <label for="office_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Localisation des bureaux
                        </label>
                        <input type="text" name="office_location" id="office_location"
                               value="{{ old('office_location', $department->office_location) }}"
                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                               placeholder="ex: Bâtiment B, 3ème étage, Aile Est">
                        @error('office_location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        * Champs obligatoires
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('university.departments.show', $department) }}" 
                           class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Annuler
                        </a>
                        
                        <button type="submit" 
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer les modifications
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
    // Auto-génération du code à partir du nom
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    const shortNameInput = document.getElementById('short_name');
    
    // Ne pas écraser les valeurs existantes lors de l'édition
    let originalCode = codeInput.value;
    let originalShortName = shortNameInput.value;
    
    nameInput.addEventListener('input', function() {
        const name = this.value;
        
        // Générer le code seulement si il n'y avait pas de valeur originale
        if (!originalCode) {
            const code = name
                .replace(/département\s*(de|d')?\s*/gi, '')
                .replace(/\s+/g, '')
                .substring(0, 8)
                .toUpperCase();
            
            if (code) {
                codeInput.value = code;
            }
        }
        
        // Générer le nom abrégé seulement si il n'y avait pas de valeur originale
        if (!originalShortName) {
            const shortName = name
                .replace(/département\s*(de|d')?\s*/gi, 'Dépt. ')
                .substring(0, 20);
            
            if (shortName && shortName !== 'Dépt. ') {
                shortNameInput.value = shortName;
            }
        }
    });
});
</script>
@endpush
@endsection