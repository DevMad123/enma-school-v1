@extends('layouts.dashboard')

@section('title', 'Modifier la classe')

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
                        <a href="{{ route('academic.classes') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Classes</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Modifier {{ $class->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- En-tête -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier la classe "{{ $class->name }}"</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ $class->level->cycle->name ?? '' }} - {{ $class->level->name ?? '' }} • 
                        {{ $class->academicYear->name ?? '' }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('academic.classes') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour
                    </a>
                    <button type="button" onclick="confirmDelete()" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>

        <!-- Formulaire de modification -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <form action="{{ route('academic.classes.update', $class) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Année académique -->
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Année académique *
                    </label>
                    <select name="academic_year_id" id="academic_year_id" required
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        <option value="">Sélectionnez une année académique</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id', $class->academic_year_id) == $year->id ? 'selected' : '' }}>
                                {{ $year->name }} ({{ $year->start_date->format('Y') }} - {{ $year->end_date->format('Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Niveau -->
                <div>
                    <label for="level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Niveau *
                    </label>
                    <select name="level_id" id="level_id" required
                            class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                        <option value="">Sélectionnez un niveau</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" {{ old('level_id', $class->level_id) == $level->id ? 'selected' : '' }}>
                                {{ $level->cycle->name ?? '' }} - {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('level_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nom de la classe -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nom de la classe *
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $class->name) }}" required
                           placeholder="Ex: 6ème A, CP1 B, Terminale C1"
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Capacité -->
                <div>
                    <label for="capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Capacité maximale *
                    </label>
                    <input type="number" name="capacity" id="capacity" value="{{ old('capacity', $class->capacity) }}" required min="1" max="100"
                           class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-3 text-gray-900 focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Nombre maximum d'étudiants dans cette classe</p>
                    @error('capacity')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <a href="{{ route('academic.classes') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg transition-colors">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-2">Confirmer la suppression</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Êtes-vous sûr de vouloir supprimer la classe "{{ $class->name }}" ?
                    Cette action est irréversible.
                </p>
                @if($class->students && $class->students->count() > 0)
                <p class="text-sm text-red-600 mt-2">
                    ⚠️ Cette classe contient {{ $class->students->count() }} étudiant(s). Supprimez d'abord les étudiants.
                </p>
                @endif
            </div>
            <div class="items-center px-4 py-3 space-x-3 flex justify-center">
                <button onclick="hideDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400">
                    Annuler
                </button>
                @if(!$class->students || $class->students->count() == 0)
                <form action="{{ route('academic.classes.destroy', $class) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-700">
                        Supprimer
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endsection