@extends('layouts.dashboard')

@section('title', 'Modifier Année Académique')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier l'Année Académique</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $academicYear->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.academic-years.show', $academicYear) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Voir
                </a>
                <a href="{{ route('admin.academic-years.index', ['school_id' => $academicYear->school_id]) }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Form -->
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PUT')
            
            <!-- École -->
            <div>
                <label for="school_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    École <span class="text-red-500">*</span>
                </label>
                <select name="school_id" id="school_id" required
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('school_id') border-red-500 @enderror">
                    <option value="">Sélectionner une école</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" 
                                {{ old('school_id', $academicYear->school_id) == $school->id ? 'selected' : '' }}
                                data-academic-system="{{ $school->academic_system }}">
                            {{ $school->name }}
                            <span class="text-sm text-gray-500">({{ ucfirst($school->academic_system) }})</span>
                        </option>
                    @endforeach
                </select>
                @error('school_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                
                @if($academicYear->school && $academicYear->school_id != old('school_id', $academicYear->school_id))
                    <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                Attention : Changer d'école affectera les périodes académiques existantes.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Nom de l'année -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nom de l'année académique <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $academicYear->name) }}" required
                       placeholder="ex: 2024-2025"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de début <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $academicYear->start_date->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de fin <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $academicYear->end_date->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Statut -->
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Statut</h3>
                
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_active" id="is_active" value="1" 
                               {{ old('is_active', $academicYear->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 @error('is_active') border-red-500 @enderror">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">
                            Activer cette année académique
                        </label>
                        <p class="text-gray-500 dark:text-gray-400">
                            Si cochée, cette année sera définie comme l'année académique active de l'école (désactive les autres).
                        </p>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @if($academicYear->is_active)
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-green-800 dark:text-green-200">
                                Cette année académique est actuellement active pour l'école {{ $academicYear->school->name ?? 'inconnue' }}.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Informations sur les périodes -->
            @if($academicYear->academicPeriods->count() > 0)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Périodes existantes</h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($academicYear->academicPeriods->sortBy('order') as $period)
                                <div class="flex items-center justify-between p-2 bg-white dark:bg-gray-800 rounded border">
                                    <div>
                                        <div class="font-medium text-sm text-gray-900 dark:text-white">{{ $period->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    @if($period->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                            Active
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                            <a href="{{ route('admin.academic-years.manage-periods', $academicYear) }}" 
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Gérer les périodes
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <div class="text-center py-6 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune période définie</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Cette année académique n'a pas encore de périodes définies.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('admin.academic-years.manage-periods', $academicYear) }}" 
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Créer des périodes
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.academic-years.index', ['school_id' => $academicYear->school_id]) }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Optional: Add any JavaScript functionality for the edit form
    console.log('Edit academic year form loaded');
});
</script>
@endpush