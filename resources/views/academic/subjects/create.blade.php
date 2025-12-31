@extends('layouts.dashboard')

@section('title', 'Créer une matière')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- Breadcrumb -->
        <nav class="flex mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                        <svg class="w-3 h-3 mr-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                        <a href="{{ route('academic.subjects') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Matières</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Nouvelle matière</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- En-tête -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Créer une nouvelle matière</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">Ajoutez une nouvelle matière d'enseignement</p>
                </div>
                <a href="{{ route('academic.subjects') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <form action="{{ route('academic.subjects.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Nom de la matière -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom de la matière *
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           placeholder="Ex: Mathématiques, Français, Anglais"
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Code de la matière -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Code de la matière *
                    </label>
                    <input type="text" name="code" id="code" value="{{ old('code') }}" required maxlength="10"
                           placeholder="Ex: MATH, FR, ANG"
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Code unique pour identifier la matière (max 10 caractères)</p>
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coefficient -->
                <div>
                    <label for="coefficient" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Coefficient *
                    </label>
                    <input type="number" name="coefficient" id="coefficient" value="{{ old('coefficient', 1) }}" required min="1" max="10"
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Coefficient de la matière pour le calcul des moyennes (1-10)</p>
                    @error('coefficient')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Niveaux associés -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Niveaux concernés *
                    </label>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">Sélectionnez les niveaux où cette matière sera enseignée</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        @foreach($levels->groupBy('cycle.name') as $cycleName => $cyclelevels)
                        <div class="space-y-2">
                            <h4 class="font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-1">
                                {{ $cycleName }}
                            </h4>
                            @foreach($cyclelevels as $level)
                            <label class="flex items-center">
                                <input type="checkbox" name="levels[]" value="{{ $level->id }}" 
                                       {{ in_array($level->id, old('levels', [])) ? 'checked' : '' }}
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $level->name }}</span>
                            </label>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @error('levels')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    @error('levels.*')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <a href="{{ route('academic.subjects') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg transition-colors">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer la matière
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection