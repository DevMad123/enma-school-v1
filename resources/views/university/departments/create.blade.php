@extends('layouts.dashboard')

@section('title', 'Créer un Département')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Créer un nouveau département</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Ajoutez un département à une UFR existante
                    </p>
                </div>
                <a href="{{ route('university.departments.index') }}" 
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
            <form action="{{ route('university.departments.store') }}" method="POST">
                @csrf
                
                <div class="p-6 space-y-6">
                    <!-- UFR -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Rattachement à l'UFR</h3>
                        <div>
                            <label for="ufr_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                UFR de rattachement <span class="text-red-500">*</span>
                            </label>
                            <select id="ufr_id" name="ufr_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                    required>
                                <option value="">Sélectionnez une UFR...</option>
                                @foreach($ufrs as $ufr)
                                    <option value="{{ $ufr->id }}" {{ old('ufr_id') == $ufr->id ? 'selected' : '' }}>
                                        {{ $ufr->name }} ({{ $ufr->code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('ufr_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Informations de base -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informations générales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom complet -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom complet du département <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Département d'Informatique"
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Code -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Code du département <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="code" name="code" value="{{ old('code') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: DEPT-INFO"
                                       required>
                                @error('code')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nom court -->
                            <div>
                                <label for="short_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom court (optionnel)
                                </label>
                                <input type="text" id="short_name" name="short_name" value="{{ old('short_name') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: INFO">
                                @error('short_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description (optionnel)
                                </label>
                                <textarea id="description" name="description" rows="3" 
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                          placeholder="Décrivez les activités et spécialités du département...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contact et responsable -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Contact et responsable</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Chef de département -->
                            <div>
                                <label for="head_of_department" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Chef de département
                                </label>
                                <input type="text" id="head_of_department" name="head_of_department" value="{{ old('head_of_department') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Dr. Marie Dupont">
                                @error('head_of_department')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Localisation bureau -->
                            <div>
                                <label for="office_location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Localisation du bureau
                                </label>
                                <input type="text" id="office_location" name="office_location" value="{{ old('office_location') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Bâtiment A, Bureau 201">
                                @error('office_location')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Email de contact
                                </label>
                                <input type="email" id="contact_email" name="contact_email" value="{{ old('contact_email') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: dept.info@universite.com">
                                @error('contact_email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Téléphone -->
                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Téléphone
                                </label>
                                <input type="tel" id="contact_phone" name="contact_phone" value="{{ old('contact_phone') }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: +225 XX XX XX XX">
                                @error('contact_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-between">
                    <a href="{{ route('university.departments.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Créer le département
                    </button>
                </div>
            </form>
        </div>

        <!-- Aide -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Organisation universitaire
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <p>
                            Les départements sont rattachés aux UFRs et organisent les programmes d'études par domaine de spécialisation.
                            Chaque département peut proposer plusieurs programmes (Licence, Master, Doctorat) dans son domaine.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection