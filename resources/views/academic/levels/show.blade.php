@extends('layouts.dashboard')

@section('title', 'Détails du niveau')

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
                        <a href="{{ route('academic.levels') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">Niveaux</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-3 h-3 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $level->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- En-tête -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $level->name }}</h2>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="text-gray-600 dark:text-gray-400">Cycle: {{ $level->cycle->name ?? 'Non défini' }}</span>
                        @if($level->code)
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">{{ $level->code }}</span>
                        @endif
                        @if($level->is_active)
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Actif</span>
                        @else
                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Inactif</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('academic.levels') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Retour
                    </a>
                    <a href="{{ route('academic.levels.edit', $level) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $level->classes->count() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Classes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $level->classes->sum(fn($class) => $class->students->count()) }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Étudiants</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $level->subjects->count() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Matières</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $level->classes->sum(fn($class) => $class->capacity ?? 0) }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Capacité totale</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes du niveau -->
        @if($level->classes->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Classes du niveau</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($level->classes as $class)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $class->name }}</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $class->academicYear->name ?? 'Année non définie' }}
                        </p>
                        <div class="flex justify-between items-center mt-3">
                            <span class="text-sm text-gray-500">
                                {{ $class->students->count() }}/{{ $class->capacity ?? 'N/A' }} étudiants
                            </span>
                            <a href="{{ route('academic.classes.show', $class) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Voir →
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Matières du niveau -->
        @if($level->subjects->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Matières enseignées</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($level->subjects as $subject)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <h4 class="font-medium text-gray-900 dark:text-white">{{ $subject->name }}</h4>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $subject->code }}</span>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded dark:bg-blue-900 dark:text-blue-300">
                                Coeff. {{ $subject->coefficient }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Informations détaillées -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informations détaillées</h3>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom complet</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $level->name }}</dd>
                    </div>
                    
                    @if($level->code)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Code</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $level->code }}</dd>
                    </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cycle</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $level->cycle->name ?? 'Non défini' }}</dd>
                    </div>
                    
                    @if($level->type)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ ucfirst($level->type) }}</dd>
                    </div>
                    @endif
                    
                    @if($level->order)
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Ordre dans le cycle</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $level->order }}</dd>
                    </div>
                    @endif
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Statut</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($level->is_active)
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Actif</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Inactif</span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date de création</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $level->created_at->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection