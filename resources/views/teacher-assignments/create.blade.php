@extends('layouts.dashboard')

@section('title', 'Nouvelle Affectation Enseignant')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nouvelle Affectation Enseignant</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Affecter un enseignant à une classe</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('teacher-assignments.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                            
                            @if ($errors->any())
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('teacher-assignments.store') }}" class="space-y-6">
                                @csrf

                                <!-- Sélection de l'enseignant -->
                                <div>
                                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Enseignant
                                    </label>
                                    <select name="teacher_id" id="teacher_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required>
                                        <option value="">Sélectionner un enseignant</option>
                                        @foreach($teachers as $teacher)
                                            <option value="{{ $teacher->id }}" 
                                                    {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}
                                                    data-specialization="{{ $teacher->specialization }}">
                                                {{ $teacher->full_name }} 
                                                @if($teacher->specialization)
                                                    ({{ $teacher->specialization }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sélection de l'année académique -->
                                <div>
                                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Année Académique
                                    </label>
                                    <select name="academic_year_id" id="academic_year_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required>
                                        <option value="">Sélectionner une année académique</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}" 
                                                    {{ old('academic_year_id') == $year->id || $year->is_active ? 'selected' : '' }}>
                                                {{ $year->name }} {{ $year->is_active ? '(Actuelle)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Sélection de la classe -->
                                <div>
                                    <label for="class_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Classe
                                    </label>
                                    <select name="class_id" id="class_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required>
                                        <option value="">Sélectionner une classe</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}
                                                    data-capacity="{{ $class->capacity }}" 
                                                    data-enrolled="{{ $class->students_count }}"
                                                    data-level="{{ $class->level->name }}"
                                                    data-cycle="{{ $class->cycle->name }}">
                                                {{ $class->level->name }} {{ $class->name }} 
                                                ({{ $class->cycle->name }} - {{ $class->capacity }} places)
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Les classes affichent le cycle, niveau et capacité
                                    </p>
                                </div>

                                <!-- Informations sur l'enseignant sélectionné -->
                                <div id="teacher-info" class="hidden p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Information sur l'enseignant</h4>
                                    <div class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                                        <p>Spécialisation: <span id="teacher-specialization"></span></p>
                                    </div>
                                </div>

                                <!-- Informations sur la classe sélectionnée -->
                                <div id="class-info" class="hidden p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                                    <h4 class="text-sm font-medium text-green-800 dark:text-green-200">Information sur la classe</h4>
                                    <div class="mt-2 text-sm text-green-600 dark:text-green-400">
                                        <p>Cycle: <span id="class-cycle"></span></p>
                                        <p>Niveau: <span id="class-level"></span></p>
                                        <p>Capacité: <span id="class-capacity"></span> élèves</p>
                                        <p>Inscrits: <span id="class-enrolled"></span> élèves</p>
                                    </div>
                                </div>

                                <!-- Note importante -->
                                <div class="p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                        <strong>Note:</strong> La gestion des matières spécifiques sera disponible dans le prochain module. 
                                        Pour l'instant, cette affectation concerne l'enseignant dans la classe de manière générale.
                                    </p>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="flex items-center justify-end space-x-4">
                                    <a href="{{ route('teacher-assignments.index') }}" 
                                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-medium transition duration-150">
                                        Annuler
                                    </a>
                                    <button type="submit" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Créer l'affectation
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const teacherSelect = document.getElementById('teacher_id');
            const teacherInfo = document.getElementById('teacher-info');
            const teacherSpecialization = document.getElementById('teacher-specialization');

            const classSelect = document.getElementById('class_id');
            const classInfo = document.getElementById('class-info');
            const classCycle = document.getElementById('class-cycle');
            const classLevel = document.getElementById('class-level');
            const classCapacity = document.getElementById('class-capacity');
            const classEnrolled = document.getElementById('class-enrolled');

            teacherSelect.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    const specialization = selectedOption.dataset.specialization || 'Non spécifiée';

                    teacherSpecialization.textContent = specialization;
                    teacherInfo.classList.remove('hidden');
                } else {
                    teacherInfo.classList.add('hidden');
                }
            });

            classSelect.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    const capacity = selectedOption.dataset.capacity;
                    const enrolled = selectedOption.dataset.enrolled;
                    const level = selectedOption.dataset.level;
                    const cycle = selectedOption.dataset.cycle;

                    classCycle.textContent = cycle;
                    classLevel.textContent = level;
                    classCapacity.textContent = capacity;
                    classEnrolled.textContent = enrolled;

                    classInfo.classList.remove('hidden');
                } else {
                    classInfo.classList.add('hidden');
                }
            });
        });
    </script>
@endsection