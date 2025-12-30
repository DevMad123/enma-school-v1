<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Nouvelle Inscription
                </h2>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <div class="py-12">
                <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
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

                            <form method="POST" action="{{ route('enrollments.store') }}" class="space-y-6">
                                @csrf

                                <!-- Sélection de l'étudiant -->
                                <div>
                                    <label for="student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Étudiant
                                    </label>
                                    <select name="student_id" id="student_id" 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                            required>
                                        <option value="">Sélectionner un étudiant</option>
                                        @foreach($students as $student)
                                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                                {{ $student->full_name }} ({{ $student->user->email }})
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
                                                    data-enrolled="{{ $class->students_count }}">
                                                {{ $class->full_name }} 
                                                ({{ $class->students_count }}/{{ $class->capacity }} étudiants)
                                            </option>
                                        @endforeach
                                    </select>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Les classes affichent le nombre d'étudiants actuels / capacité maximale
                                    </p>
                                </div>

                                <!-- Date d'inscription -->
                                <div>
                                    <label for="enrollment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Date d'inscription
                                    </label>
                                    <input type="date" name="enrollment_date" id="enrollment_date" 
                                           value="{{ old('enrollment_date', date('Y-m-d')) }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                           required>
                                </div>

                                <!-- Informations sur la classe sélectionnée -->
                                <div id="class-info" class="hidden p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Information sur la classe</h4>
                                    <div class="mt-2 text-sm text-blue-600 dark:text-blue-400">
                                        <p>Capacité: <span id="class-capacity"></span> étudiants</p>
                                        <p>Inscrits actuellement: <span id="class-enrolled"></span> étudiants</p>
                                        <p>Places disponibles: <span id="class-available"></span> étudiants</p>
                                    </div>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="flex items-center justify-end space-x-4">
                                    <a href="{{ route('enrollments.index') }}" 
                                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                        Annuler
                                    </a>
                                    <button type="submit" 
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Créer l'inscription
                                    </button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.getElementById('class_id');
            const classInfo = document.getElementById('class-info');
            const capacitySpan = document.getElementById('class-capacity');
            const enrolledSpan = document.getElementById('class-enrolled');
            const availableSpan = document.getElementById('class-available');

            classSelect.addEventListener('change', function() {
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    const capacity = parseInt(selectedOption.dataset.capacity);
                    const enrolled = parseInt(selectedOption.dataset.enrolled);
                    const available = capacity - enrolled;

                    capacitySpan.textContent = capacity;
                    enrolledSpan.textContent = enrolled;
                    availableSpan.textContent = available;

                    classInfo.classList.remove('hidden');

                    // Avertissement si la classe est pleine
                    if (available <= 0) {
                        classInfo.className = 'p-4 bg-red-50 dark:bg-red-900 rounded-lg';
                        classInfo.querySelector('h4').className = 'text-sm font-medium text-red-800 dark:text-red-200';
                        classInfo.querySelector('div').className = 'mt-2 text-sm text-red-600 dark:text-red-400';
                        availableSpan.parentElement.innerHTML = '<strong>⚠️ Classe pleine!</strong>';
                    } else {
                        classInfo.className = 'p-4 bg-blue-50 dark:bg-blue-900 rounded-lg';
                        classInfo.querySelector('h4').className = 'text-sm font-medium text-blue-800 dark:text-blue-200';
                        classInfo.querySelector('div').className = 'mt-2 text-sm text-blue-600 dark:text-blue-400';
                    }
                } else {
                    classInfo.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>