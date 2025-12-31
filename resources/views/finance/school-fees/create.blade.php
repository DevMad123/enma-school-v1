@extends('layouts.dashboard')

@section('title', 'Nouveau Frais Scolaire')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Créer un Nouveau Frais Scolaire</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Définir les frais de scolarité pour les étudiants</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('finance.school-fees.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour aux frais scolaires
                </a>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="max-w-5xl mx-auto">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="POST" action="{{ route('finance.school-fees.store') }}" class="space-y-6">
                    @csrf

                    <!-- Informations générales -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Informations Générales</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom des frais -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nom des Frais *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ex: Frais de scolarité, Frais d'inscription...">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Montant -->
                            <div>
                                <label for="amount" class="block text-sm font-medium text-gray-700">Montant (FCFA) *</label>
                                <input type="number" id="amount" name="amount" value="{{ old('amount') }}" required min="0" step="0.01"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0">
                                @error('amount')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea id="description" name="description" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Description détaillée des frais...">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Ciblage -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Ciblage des Frais</h3>
                        <p class="text-sm text-gray-600 mb-4">Sélectionnez à qui s'appliquent ces frais. Laissez vide pour tous les étudiants.</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Classe spécifique -->
                            <div>
                                <label for="school_class_id" class="block text-sm font-medium text-gray-700">Classe Spécifique</label>
                                <select id="school_class_id" name="school_class_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Toutes les classes</option>
                                    @foreach($schoolClasses as $class)
                                        <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('school_class_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Niveau -->
                            <div>
                                <label for="level_id" class="block text-sm font-medium text-gray-700">Niveau</label>
                                <select id="level_id" name="level_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tous les niveaux</option>
                                    @foreach($levels as $level)
                                        <option value="{{ $level->id }}" {{ old('level_id') == $level->id ? 'selected' : '' }}>
                                            {{ $level->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('level_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cycle -->
                            <div>
                                <label for="cycle_id" class="block text-sm font-medium text-gray-700">Cycle</label>
                                <select id="cycle_id" name="cycle_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Tous les cycles</option>
                                    @foreach($cycles as $cycle)
                                        <option value="{{ $cycle->id }}" {{ old('cycle_id') == $cycle->id ? 'selected' : '' }}>
                                            {{ $cycle->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cycle_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Configuration -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Configuration</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Année académique -->
                            <div>
                                <label for="academic_year_id" class="block text-sm font-medium text-gray-700">Année Académique *</label>
                                <select id="academic_year_id" name="academic_year_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Sélectionnez une année</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                            {{ $year->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('academic_year_id')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Date limite -->
                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700">Date Limite de Paiement</label>
                                <input type="date" id="due_date" name="due_date" value="{{ old('due_date') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('due_date')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Statut -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Statut *</label>
                                <select id="status" name="status" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                                </select>
                                @error('status')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Options -->
                        <div class="mt-6">
                            <div class="flex items-center">
                                <input type="checkbox" id="is_mandatory" name="is_mandatory" value="1" 
                                       {{ old('is_mandatory', '1') ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="is_mandatory" class="ml-2 block text-sm text-gray-900">
                                    Frais obligatoires
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Les frais obligatoires doivent être payés par tous les étudiants concernés.</p>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('finance.school-fees') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md text-sm font-medium">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                            Créer le Frais
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection