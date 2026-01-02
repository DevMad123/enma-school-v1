@extends('layouts.dashboard')

@section('title', 'Gestion des Étudiants')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Gestion des Étudiants</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Administration centralisée des élèves</p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-3">
                <a href="{{ route('admin.students.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvel Étudiant
                </a>
                <button class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    Importer
                </button>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-dashboard.stat-card 
                title="Total Étudiants"
                :value="number_format($stats['total_students'])"
                color="blue"
                description="Tous statuts confondus"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'/></svg>'"
            />
            <x-dashboard.stat-card 
                title="Étudiants Actifs"
                :value="number_format($stats['active_students'])"
                color="green"
                description="Actuellement inscrits"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'"
            />
            <x-dashboard.stat-card 
                title="Nouveaux ce Mois"
                :value="number_format($stats['new_this_month'])"
                color="purple"
                description="Inscriptions récentes"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4v16m8-8H4\'/></svg>'"
            />
            <x-dashboard.stat-card 
                title="Inactifs"
                :value="number_format($stats['inactive_students'])"
                color="red"
                description="Nécessitent attention"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.768 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z\'/></svg>'"
            />
        </div>

        <!-- Filtres -->
        <x-dashboard.card title="Filtres de Recherche">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Nom, email, numéro..."
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cycle</label>
                    <select name="cycle_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les cycles</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle->id }}" {{ request('cycle_id') == $cycle->id ? 'selected' : '' }}>
                                {{ $cycle->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Niveau</label>
                    <select name="level_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les niveaux</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>
                                {{ $level->name }} ({{ $level->cycle->name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Classe</label>
                    <select name="class_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Toutes les classes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
                    <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les statuts</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactif</option>
                        <option value="graduated" {{ request('status') === 'graduated' ? 'selected' : '' }}>Diplômé</option>
                        <option value="transferred" {{ request('status') === 'transferred' ? 'selected' : '' }}>Transféré</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2 lg:col-span-5">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                        Filtrer
                    </button>
                    <a href="{{ route('admin.students.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </x-dashboard.card>

        <!-- Liste des étudiants -->
        <x-dashboard.card title="Liste des Étudiants" :noPadding="true">
            @if($students->count() > 0)
                @php
                    $headers = ['Étudiant', 'Numéro', 'Classe/Niveau', 'Contact', 'Statut', 'Inscription', 'Actions'];
                    
                    $rows = $students->map(function($student) {
                        $enrollment = $student->enrollments->first();
                        return [
                            '<div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold">' . 
                                    substr($student->user->name, 0, 1) . 
                                '</div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">' . $student->user->name . '</p>
                                    <p class="text-sm text-gray-500">' . ($student->user->email ?? 'N/A') . '</p>
                                </div>
                            </div>',
                            
                            '<span class="font-mono text-sm bg-gray-100 px-2 py-1 rounded">' . $student->student_number . '</span>',
                            
                            '<div>
                                <p class="font-medium">' . ($student->currentClass->name ?? 'Non affecté') . '</p>
                                <p class="text-sm text-gray-500">' . 
                                    ($student->currentClass ? $student->currentClass->level->name . ' - ' . $student->currentClass->level->cycle->name : 'N/A') . 
                                '</p>
                            </div>',
                            
                            '<div>
                                <p class="text-sm">' . ($student->user->phone ?? 'N/A') . '</p>
                                <p class="text-sm text-gray-500">' . ($student->guardian_phone ?? 'Tuteur: N/A') . '</p>
                            </div>',
                            
                            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . 
                            ($student->status === 'active' ? 'bg-green-100 text-green-800' : 
                             ($student->status === 'inactive' ? 'bg-red-100 text-red-800' : 
                              ($student->status === 'graduated' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'))) . '">' . 
                            ucfirst($student->status) . '</span>',
                            
                            '<span class="text-sm text-gray-500">' . 
                                ($enrollment ? $enrollment->created_at->format('d/m/Y') : 'N/A') . 
                            '</span>',
                            
                            '<div class="flex items-center space-x-2">
                                <a href="' . route('admin.students.show', $student) . '" 
                                   class="text-blue-600 hover:text-blue-800" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="' . route('admin.students.edit', $student) . '" 
                                   class="text-yellow-600 hover:text-yellow-800" title="Modifier">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="' . route('admin.students.toggle-status', $student) . '" class="inline">
                                    ' . csrf_field() . '
                                    <button type="submit" 
                                            class="text-purple-600 hover:text-purple-800" 
                                            title="Basculer statut"
                                            onclick="return confirm(\'Voulez-vous changer le statut de cet étudiant ?\')"> 
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>'
                        ];
                    })->toArray();
                @endphp
                
                <x-dashboard.table 
                    :headers="$headers" 
                    :rows="$rows"
                    empty="Aucun étudiant trouvé"
                />
                
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $students->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun étudiant</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Commencez par créer votre premier étudiant.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.students.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Créer un étudiant
                        </a>
                    </div>
                </div>
            @endif
        </x-dashboard.card>
    </div>
@endsection