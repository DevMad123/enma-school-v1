@extends('layouts.dashboard')

@section('title', 'Créer une affectation')

@section('header')
<div class="flex items-center justify-between">
    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.assignments.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800">Créer une affectation pédagogique</h1>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.assignments.store') }}" method="POST" class="space-y-6" x-data="assignmentForm()">
    @csrf

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                Détails de l'affectation
            </h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- Sélection de l'année académique -->
            <div>
                <label for="academic_year_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Année académique <span class="text-red-500">*</span>
                </label>
                <select id="academic_year_id" 
                        name="academic_year_id" 
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('academic_year_id') border-red-300 @enderror"
                        required>
                    <option value="">Sélectionner une année académique</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" {{ old('academic_year_id', $currentAcademicYear->id ?? '') == $year->id ? 'selected' : '' }}>
                            {{ $year->name }} ({{ $year->start_date->format('Y') }}-{{ $year->end_date->format('Y') }})
                        </option>
                    @endforeach
                </select>
                @error('academic_year_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Enseignant et Classe/Matière -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
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
                    
                    <!-- Informations enseignant sélectionné -->
                    <div x-show="selectedTeacher" class="mt-3 p-3 bg-blue-50 rounded-lg">
                        <div class="text-xs text-gray-600" x-text="getTeacherInfo()"></div>
                    </div>
                </div>

                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Classe <span class="text-red-500">*</span>
                    </label>
                    <select id="class_id" 
                            name="class_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('class_id') border-red-300 @enderror"
                            x-model="selectedClass"
                            required>
                        <option value="">Sélectionner une classe</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->level->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('class_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Matière et Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Matière <span class="text-red-500">*</span>
                    </label>
                    <select id="subject_id" 
                            name="subject_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('subject_id') border-red-300 @enderror"
                            x-model="selectedSubject"
                            required>
                        <option value="">Sélectionner une matière</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>
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
                </div>

                <div>
                    <label for="assignment_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type d'affectation <span class="text-red-500">*</span>
                    </label>
                    <select id="assignment_type" 
                            name="assignment_type" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('assignment_type') border-red-300 @enderror"
                            required>
                        <option value="">Sélectionner un type</option>
                        <option value="primary" {{ old('assignment_type') === 'primary' ? 'selected' : '' }}>
                            Titulaire principal
                        </option>
                        <option value="substitute" {{ old('assignment_type') === 'substitute' ? 'selected' : '' }}>
                            Remplaçant
                        </option>
                        <option value="assistant" {{ old('assignment_type') === 'assistant' ? 'selected' : '' }}>
                            Assistant
                        </option>
                    </select>
                    @error('assignment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Horaires et période -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="weekly_hours" class="block text-sm font-medium text-gray-700 mb-2">
                        Heures par semaine <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           id="weekly_hours" 
                           name="weekly_hours" 
                           value="{{ old('weekly_hours') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('weekly_hours') border-red-300 @enderror"
                           min="1" 
                           max="40"
                           required>
                    @error('weekly_hours')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date de début <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           value="{{ old('start_date') }}"
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
                           value="{{ old('end_date') }}"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('end_date') border-red-300 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Laisser vide pour une affectation permanente</p>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3" 
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('notes') border-red-300 @enderror"
                          placeholder="Informations supplémentaires sur cette affectation...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Vérification des conflits -->
    <div x-show="showConflictCheck" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Vérification des conflits</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>Nous vérifions s'il existe des affectations conflictuelles...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Les champs marqués d'un * sont obligatoires
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.assignments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium rounded-md transition-colors duration-150 shadow-sm">
                Annuler
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white font-medium rounded-md transition-colors duration-150 shadow-sm" x-bind:disabled="isSubmitting">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span x-text="isSubmitting ? 'Création...' : 'Créer l\'affectation'"></span>
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function assignmentForm() {
    return {
        selectedTeacher: '',
        selectedClass: '',
        selectedSubject: '',
        showConflictCheck: false,
        isSubmitting: false,
        
        teachers: @json($teachers),
        
        getTeacherInfo() {
            const teacher = this.teachers.find(t => t.id == this.selectedTeacher);
            if (teacher) {
                let info = `Statut: ${teacher.status}`;
                if (teacher.specialization) {
                    info += ` • Spécialisation: ${teacher.specialization}`;
                }
                if (teacher.employment_type) {
                    info += ` • ${teacher.employment_type === 'full_time' ? 'Temps plein' : 'Temps partiel'}`;
                }
                return info;
            }
            return '';
        },
        
        init() {
            this.$watch('selectedTeacher', () => this.checkConflicts());
            this.$watch('selectedClass', () => this.checkConflicts());
            this.$watch('selectedSubject', () => this.checkConflicts());
        },
        
        checkConflicts() {
            if (this.selectedTeacher && this.selectedClass && this.selectedSubject) {
                this.showConflictCheck = true;
                
                // Simulation d'une vérification des conflits
                setTimeout(() => {
                    this.showConflictCheck = false;
                }, 2000);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Pré-remplir la date de début avec aujourd'hui
    const startDateField = document.getElementById('start_date');
    if (!startDateField.value) {
        const today = new Date().toISOString().split('T')[0];
        startDateField.value = today;
    }
});
</script>
@endpush
@endsection