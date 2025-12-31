@extends('layouts.dashboard')

@section('title', 'Planning des Affectations')

@section('header')
<div class="flex items-center justify-between">
    <div class="flex items-center space-x-3">
        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800">Planning des Affectations</h1>
    </div>
    <div class="flex items-center space-x-2">
        <select id="yearFilter" class="form-select rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}" {{ request('year_id') == $year->id ? 'selected' : '' }}>
                    {{ $year->name }}
                </option>
            @endforeach
        </select>
        <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-150 shadow-sm" onclick="printSchedule()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer
        </button>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Vue d'ensemble des statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Enseignants actifs</h3>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['total_teachers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Affectations actives</h3>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['active_assignments'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Classes concernées</h3>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['total_classes'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-900">Matières enseignées</h3>
                    <p class="text-2xl font-bold text-purple-600">{{ $stats['total_subjects'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et options d'affichage -->
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div>
                    <label for="levelFilter" class="text-sm font-medium text-gray-700">Niveau</label>
                    <select id="levelFilter" class="ml-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous les niveaux</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="cycleFilter" class="text-sm font-medium text-gray-700">Cycle</label>
                    <select id="cycleFilter" class="ml-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Tous les cycles</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subjectFilter" class="text-sm font-medium text-gray-700">Matière</label>
                    <select id="subjectFilter" class="ml-2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Toutes les matières</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button type="button" id="toggleView" class="inline-flex items-center px-3 py-2 border border-blue-300 text-blue-700 bg-white hover:bg-blue-50 font-medium rounded-md transition-colors duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Vue tableau
                </button>
                <button type="button" id="toggleWeekView" class="inline-flex items-center px-3 py-2 border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium rounded-md transition-colors duration-150">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Vue hebdomadaire
                </button>
            </div>
        </div>
    </div>

    <!-- Vue par Enseignant (par défaut) -->
    <div id="teacherView" class="space-y-6">
        @foreach($teacherAssignments as $teacher => $assignments)
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($teacher, 0, 2)) }}
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $teacher }}</h3>
                                <p class="text-sm text-gray-600">
                                    {{ count($assignments) }} affectation{{ count($assignments) > 1 ? 's' : '' }} •
                                    {{ $assignments->sum('weekly_hours') }}h/semaine
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $assignments->sum('weekly_hours') >= 20 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $assignments->sum('weekly_hours') >= 20 ? 'Plein temps' : 'Temps partiel' }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Matière
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Classe
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Heures/sem
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Période
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statut
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($assignments as $assignment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-purple-100 rounded flex items-center justify-center mr-3">
                                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">{{ $assignment->subject->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $assignment->schoolClass->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $assignment->assignment_type === 'primary' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($assignment->assignment_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $assignment->weekly_hours }}h
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $assignment->start_date->format('d/m') }} - 
                                        {{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : 'En cours' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $assignment->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $assignment->is_active ? 'Actif' : 'Inactif' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        
        @if($teacherAssignments->isEmpty())
            <div class="bg-white rounded-lg shadow-sm border p-12">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Aucune affectation trouvée</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Aucune affectation ne correspond aux critères sélectionnés.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('admin.assignments.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors duration-150 shadow-sm">
                            Créer une nouvelle affectation
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Vue en tableau (masquée par défaut) -->
    <div id="tableView" class="hidden">
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Enseignant
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Matière
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Classe
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Heures
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Période
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($allAssignments as $assignment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                            {{ strtoupper(substr($assignment->teacher->user->name, 0, 2)) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $assignment->teacher->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->subject->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->schoolClass->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $assignment->weekly_hours }}h
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $assignment->assignment_type === 'primary' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($assignment->assignment_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $assignment->start_date->format('d/m') }} - 
                                    {{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : 'En cours' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $assignment->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $assignment->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleViewBtn = document.getElementById('toggleView');
    const teacherView = document.getElementById('teacherView');
    const tableView = document.getElementById('tableView');
    
    let currentView = 'teacher';
    
    toggleViewBtn.addEventListener('click', function() {
        if (currentView === 'teacher') {
            teacherView.classList.add('hidden');
            tableView.classList.remove('hidden');
            this.innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Vue enseignant
            `;
            currentView = 'table';
        } else {
            tableView.classList.add('hidden');
            teacherView.classList.remove('hidden');
            this.innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                Vue tableau
            `;
            currentView = 'teacher';
        }
    });
    
    // Gestion des filtres
    const filters = ['yearFilter', 'levelFilter', 'cycleFilter', 'subjectFilter'];
    filters.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('change', function() {
                updateScheduleView();
            });
        }
    });
});

function updateScheduleView() {
    const filters = {
        year_id: document.getElementById('yearFilter').value,
        level_id: document.getElementById('levelFilter').value,
        cycle_id: document.getElementById('cycleFilter').value,
        subject_id: document.getElementById('subjectFilter').value
    };
    
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
        if (value) params.append(key, value);
    });
    
    const url = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    window.location.href = url;
}

function printSchedule() {
    window.print();
}
</script>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    
    @page {
        size: A4 landscape;
        margin: 1cm;
    }
    
    body {
        font-size: 12px;
    }
    
    .bg-gray-50,
    .bg-blue-50,
    .bg-green-50,
    .bg-yellow-50,
    .bg-purple-50 {
        background-color: #f9fafb !important;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endpush
@endsection