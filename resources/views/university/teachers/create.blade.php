@extends('layouts.dashboard')

@section('title', 'Ajouter un enseignant universitaire')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Ajouter un enseignant universitaire
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Créer un nouveau profil d'enseignant universitaire</p>
            </div>
            <div>
                <a href="{{ route('university.teachers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto">
    <form action="{{ route('university.teachers.store') }}" method="POST" class="bg-white dark:bg-gray-800 shadow rounded-lg">
        @csrf
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Informations personnelles -->
                <div>
                    <h3 class="text-lg font-medium text-blue-600 dark:text-blue-400 flex items-center mb-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Informations personnelles
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Prénom <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="first_name" 
                                   id="first_name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('first_name') border-red-500 @enderror"
                                   value="{{ old('first_name') }}" 
                                   required>
                            @error('first_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="last_name" 
                                   id="last_name" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('last_name') border-red-500 @enderror"
                                   value="{{ old('last_name') }}" 
                                   required>
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('email') border-red-500 @enderror"
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Téléphone
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('phone') border-red-500 @enderror"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Informations académiques -->
                <div>
                    <h3 class="text-lg font-medium text-blue-600 dark:text-blue-400 flex items-center mb-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Informations académiques
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="ufr_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                UFR <span class="text-red-500">*</span>
                            </label>
                            <select name="ufr_id" 
                                    id="ufr_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('ufr_id') border-red-500 @enderror" 
                                    required>
                                <option value="">-- Sélectionner une UFR --</option>
                                @foreach($ufrs as $ufr)
                                    <option value="{{ $ufr->id }}" {{ old('ufr_id') == $ufr->id ? 'selected' : '' }}>
                                        {{ $ufr->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ufr_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Département
                            </label>
                            <select name="department_id" 
                                    id="department_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('department_id') border-red-500 @enderror">
                                <option value="">-- Sélectionner un département --</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" 
                                            data-ufr="{{ $department->ufr_id }}"
                                            {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="academic_rank" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Rang académique
                            </label>
                            <select name="academic_rank" 
                                    id="academic_rank" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('academic_rank') border-red-500 @enderror">
                                <option value="">-- Sélectionner un rang --</option>
                                <option value="assistant" {{ old('academic_rank') == 'assistant' ? 'selected' : '' }}>
                                    Assistant
                                </option>
                                <option value="maitre_assistant" {{ old('academic_rank') == 'maitre_assistant' ? 'selected' : '' }}>
                                    Maître Assistant
                                </option>
                                <option value="maitre_de_conferences" {{ old('academic_rank') == 'maitre_de_conferences' ? 'selected' : '' }}>
                                    Maître de Conférences
                                </option>
                                <option value="professeur" {{ old('academic_rank') == 'professeur' ? 'selected' : '' }}>
                                    Professeur
                                </option>
                                <option value="professeur_titulaire" {{ old('academic_rank') == 'professeur_titulaire' ? 'selected' : '' }}>
                                    Professeur Titulaire
                                </option>
                            </select>
                            @error('academic_rank')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="specialization" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Spécialisation
                            </label>
                            <input type="text" 
                                   name="specialization" 
                                   id="specialization" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('specialization') border-red-500 @enderror"
                                   value="{{ old('specialization') }}">
                            @error('specialization')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Separator -->
            <div class="border-t border-gray-200 dark:border-gray-600 my-6"></div>

            <!-- Professional and Additional Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Informations professionnelles -->
                <div>
                    <h3 class="text-lg font-medium text-blue-600 dark:text-blue-400 flex items-center mb-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6M8 8v10a2 2 0 002 2h4a2 2 0 002-2V8M8 8V6a2 2 0 012-2h4a2 2 0 012 2v2M8 8h8"></path>
                        </svg>
                        Informations professionnelles
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Numéro d'employé
                            </label>
                            <input type="text" 
                                   name="employee_id" 
                                   id="employee_id" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('employee_id') border-red-500 @enderror"
                                   value="{{ old('employee_id') }}">
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hire_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Date d'embauche
                            </label>
                            <input type="date" 
                                   name="hire_date" 
                                   id="hire_date" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('hire_date') border-red-500 @enderror"
                                   value="{{ old('hire_date') }}">
                            @error('hire_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="office_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Bureau
                            </label>
                            <input type="text" 
                                   name="office_location" 
                                   id="office_location" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('office_location') border-red-500 @enderror"
                                   value="{{ old('office_location') }}">
                            @error('office_location')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="salary" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Salaire
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="salary" 
                                       id="salary" 
                                       class="w-full px-3 py-2 pr-16 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('salary') border-red-500 @enderror"
                                       value="{{ old('salary') }}" 
                                       step="0.01">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">FCFA</span>
                                </div>
                            </div>
                            @error('salary')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Informations complémentaires -->
                <div>
                    <h3 class="text-lg font-medium text-blue-600 dark:text-blue-400 flex items-center mb-4">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Informations complémentaires
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="research_interests" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Intérêts de recherche
                            </label>
                            <textarea name="research_interests" 
                                      id="research_interests" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('research_interests') border-red-500 @enderror">{{ old('research_interests') }}</textarea>
                            @error('research_interests')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="qualifications" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Qualifications
                            </label>
                            <textarea name="qualifications" 
                                      id="qualifications" 
                                      rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('qualifications') border-red-500 @enderror">{{ old('qualifications') }}</textarea>
                            @error('qualifications')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Statut
                            </label>
                            <select name="status" 
                                    id="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white @error('status') border-red-500 @enderror">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                    Actif
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                    Inactif
                                </option>
                                <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>
                                    Retraité
                                </option>
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
                    </div>

        </div>
        
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-600">
            <a href="{{ route('university.teachers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Annuler
            </a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h2m0 0h9a2 2 0 002-2V9a2 2 0 00-2-2h-2m0 0V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0h4v4m0 0v6m0-6h6"></path>
                </svg>
                Enregistrer
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Filtrer les départements selon l'UFR sélectionnée
    $('#ufr_id').on('change', function() {
        const ufrId = $(this).val();
        const departmentSelect = $('#department_id');
        
        departmentSelect.find('option:not(:first)').hide();
        
        if (ufrId) {
            departmentSelect.find('option[data-ufr="' + ufrId + '"]').show();
        } else {
            departmentSelect.find('option:not(:first)').show();
        }
        
        departmentSelect.val('');
    });
    
    // Déclencher le filtrage au chargement si une UFR est déjà sélectionnée
    $('#ufr_id').trigger('change');
});
</script>
@endpush
@endsection