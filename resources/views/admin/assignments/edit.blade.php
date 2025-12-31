@extends('layouts.dashboard')

@section('title', 'Modifier l\'affectation')

@section('header')
<div class="flex items-center justify-between">
    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.assignments.show', $assignment) }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800">Modifier l'affectation</h1>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('admin.assignments.show', $assignment) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium rounded-md transition-colors duration-150 shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Voir les détails
        </a>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.assignments.update', $assignment) }}" method="POST" class="space-y-6" x-data="assignmentEditForm()">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Informations de l'affectation
            </h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- Année académique (lecture seule) -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Année académique</p>
                        <p class="text-lg font-semibold text-blue-900">{{ $assignment->academicYear->name }}</p>
                        <p class="text-sm text-blue-600">
                            {{ $assignment->academicYear->start_date->format('Y') }} - {{ $assignment->academicYear->end_date->format('Y') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Enseignant, Classe et Matière -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Enseignant <span class="text-red-500">*</span>
                    </label>
                    <select id="teacher_id" 
                            name="teacher_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('teacher_id') border-red-300 @enderror"
                            x-model="selectedTeacher"
                            required>
                        <option value="">Sélectionner un enseignant</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id', $assignment->teacher_id) == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name }}
                                @if($teacher->specialization)
                                    ({{ $teacher->specialization }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <!-- Info enseignant actuel -->
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600">
                            <span class="font-medium">Actuel:</span> {{ $assignment->teacher->user->name }}
                            @if($assignment->teacher->specialization)
                                ({{ $assignment->teacher->specialization }})
                            @endif
                        </p>
                    </div>
                </div>

                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Classe <span class="text-red-500">*</span>
                    </label>
                    <select id="class_id" 
                            name="class_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('class_id') border-red-300 @enderror"
                            required>
                        <option value="">Sélectionner une classe</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id', $assignment->class_id) == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->level->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <!-- Info classe actuelle -->
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600">
                            <span class="font-medium">Actuelle:</span> {{ $assignment->schoolClass->name }} - {{ $assignment->schoolClass->level->name }}
                        </p>
                    </div>
                </div>

                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Matière <span class="text-red-500">*</span>
                    </label>
                    <select id="subject_id" 
                            name="subject_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('subject_id') border-red-300 @enderror"
                            required>
                        <option value="">Sélectionner une matière</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $assignment->subject_id) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                                @if($subject->code)
                                    ({{ $subject->code }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    
                    <!-- Info matière actuelle -->
                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs text-gray-600">
                            <span class="font-medium">Actuelle:</span> {{ $assignment->subject->name }}
                            @if($assignment->subject->code)
                                ({{ $assignment->subject->code }})
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Type et horaires -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="assignment_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type d'affectation <span class="text-red-500">*</span>
                    </label>
                    <select id="assignment_type" 
                            name="assignment_type" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('assignment_type') border-red-300 @enderror"
                            required>
                        <option value="">Sélectionner un type</option>
                        <option value="primary" {{ old('assignment_type', $assignment->assignment_type) === 'primary' ? 'selected' : '' }}>
                            Titulaire principal
                        </option>
                        <option value="substitute" {{ old('assignment_type', $assignment->assignment_type) === 'substitute' ? 'selected' : '' }}>
                            Remplaçant
                        </option>
                        <option value="assistant" {{ old('assignment_type', $assignment->assignment_type) === 'assistant' ? 'selected' : '' }}>
                            Assistant
                        </option>
                    </select>
                    @error('assignment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="weekly_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Heures par semaine <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="weekly_hours" 
                           name="weekly_hours" 
                           value="{{ old('weekly_hours', $assignment->weekly_hours) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('weekly_hours') border-red-300 @enderror"
                           min="1" 
                           max="40"
                           required>
                    @error('weekly_hours')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date de début <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date', $assignment->start_date->format('Y-m-d')) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('start_date') border-red-300 @enderror"
                           required>
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date de fin
                    </label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           value="{{ old('end_date', $assignment->end_date?->format('Y-m-d')) }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('end_date') border-red-300 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Laisser vide pour une affectation permanente</p>
                </div>
            </div>

            <!-- Statut -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Statut de l'affectation</label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $assignment->is_active ? '1' : '0') === '1' ? 'checked' : '' }}
                               class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 focus:ring-green-500 focus:ring-2">
                        <span class="ml-3 text-sm text-gray-900 flex items-center">
                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Active - L'affectation est en cours et opérationnelle
                        </span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" 
                               name="is_active" 
                               value="0" 
                               {{ old('is_active', $assignment->is_active ? '1' : '0') === '0' ? 'checked' : '' }}
                               class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500 focus:ring-2">
                        <span class="ml-3 text-sm text-gray-900 flex items-center">
                            <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Inactive - L'affectation est suspendue ou terminée
                        </span>
                    </label>
                </div>
                @error('is_active')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="4" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror"
                          placeholder="Informations supplémentaires, raisons de modification, etc...">{{ old('notes', $assignment->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Autres affectations de l'enseignant (si modification) -->
    <div x-show="selectedTeacher && selectedTeacher != '{{ $assignment->teacher_id }}'" 
         class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Changement d'enseignant détecté</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Vous changez l'enseignant de cette affectation. Vérifiez que le nouvel enseignant n'a pas de conflits d'horaires.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Affectation créée le {{ $assignment->created_at->format('d/m/Y à H:i') }}
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.assignments.show', $assignment) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium rounded-md transition-colors duration-150 shadow-sm">
                Annuler
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium rounded-md transition-colors duration-150 shadow-sm" x-bind:disabled="isSubmitting">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="isSubmitting ? 'Mise à jour...' : 'Mettre à jour'"></span>
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function assignmentEditForm() {
    return {
        selectedTeacher: '{{ $assignment->teacher_id }}',
        isSubmitting: false,
        
        init() {
            // Surveillance des changements pour détecter les conflits
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Validation des dates
    const startDateField = document.getElementById('start_date');
    const endDateField = document.getElementById('end_date');
    
    function validateDates() {
        if (startDateField.value && endDateField.value) {
            const startDate = new Date(startDateField.value);
            const endDate = new Date(endDateField.value);
            
            if (endDate <= startDate) {
                endDateField.setCustomValidity('La date de fin doit être postérieure à la date de début');
            } else {
                endDateField.setCustomValidity('');
            }
        } else {
            endDateField.setCustomValidity('');
        }
    }
    
    startDateField.addEventListener('change', validateDates);
    endDateField.addEventListener('change', validateDates);
});
</script>
@endpush
@endsection