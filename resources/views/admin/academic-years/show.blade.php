@extends('layouts.dashboard')

@section('title', 'Année Académique - ' . $academicYear->name)

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $academicYear->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $academicYear->school ? $academicYear->school->name : 'École non définie' }}
                    @if($academicYear->is_active) - <span class="text-green-600 font-medium">Année Active</span> @endif
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.academic-years.manage-periods', $academicYear) }}" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Gérer les Périodes
                </a>
                @if(!$academicYear->is_active)
                    <form action="{{ route('admin.academic-years.toggle-active', $academicYear) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center"
                                onclick="return confirm('Activer cette année académique ?')">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Activer
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.academic-years.edit', $academicYear) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('admin.academic-years.index', ['school_id' => $academicYear->school_id]) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    
    <!-- Vue d'ensemble -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Durée -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Durée totale
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $academicYear->start_date->diffInDays($academicYear->end_date) + 1 }} jours
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Périodes -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Périodes définies
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $academicYear->academicPeriods->count() }}
                                @if($academicYear->academicPeriods->count() > 0)
                                    <span class="text-sm text-gray-500">
                                        ({{ $academicYear->academicPeriods->where('is_active', true)->count() }} active(s))
                                    </span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Classes associées
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $academicYear->classes->count() }}
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Étudiants -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                Étudiants inscrits
                            </dt>
                            <dd class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $academicYear->enrollments->count() }}
                                @if($academicYear->enrollments->count() > 0)
                                    <span class="text-sm text-gray-500">
                                        ({{ $academicYear->activeEnrollments()->count() }} actifs)
                                    </span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations détaillées -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Détails de l'année -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Détails de l'année académique
                </h3>
                
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">École</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            @if($academicYear->school)
                                <div class="flex items-center">
                                    <span class="font-medium">{{ $academicYear->school->name }}</span>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        {{ ucfirst($academicYear->school->academic_system) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $academicYear->school->short_name }} - {{ $academicYear->school->city }}
                                </div>
                            @else
                                <span class="text-red-600 dark:text-red-400">École non définie</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Période</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            <div class="flex items-center space-x-4">
                                <div>
                                    <span class="font-medium">Début :</span> 
                                    {{ $academicYear->start_date->format('d/m/Y') }}
                                    <span class="text-gray-500">({{ $academicYear->start_date->translatedFormat('l') }})</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 mt-1">
                                <div>
                                    <span class="font-medium">Fin :</span> 
                                    {{ $academicYear->end_date->format('d/m/Y') }}
                                    <span class="text-gray-500">({{ $academicYear->end_date->translatedFormat('l') }})</span>
                                </div>
                            </div>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Statut</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            <div class="flex flex-wrap gap-2">
                                @if($academicYear->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Active
                                    </span>
                                @endif
                                
                                @if($academicYear->is_current)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.414-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Courante
                                    </span>
                                @endif
                                
                                @if($academicYear->is_archived)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"/>
                                            <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Archivée
                                    </span>
                                @endif
                                
                                @if(!$academicYear->is_active && !$academicYear->is_current && !$academicYear->is_archived)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                        Inactive
                                    </span>
                                @endif
                            </div>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Créée le</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $academicYear->created_at->format('d/m/Y à H:i') }}
                            <span class="text-gray-500">({{ $academicYear->created_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Périodes académiques -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                        Périodes académiques
                    </h3>
                    <a href="{{ route('admin.academic-years.manage-periods', $academicYear) }}" 
                       class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 inline-flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Gérer
                    </a>
                </div>
                
                @if($academicYear->academicPeriods->count() > 0)
                    <div class="space-y-3">
                        @foreach($academicYear->academicPeriods->sortBy('order') as $period)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $period->name }}
                                            </h4>
                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $period->type === 'trimestre' ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' : 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' }}">
                                                {{ ucfirst($period->type) }} {{ $period->order }}
                                            </span>
                                            @if($period->is_active)
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    Active
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}
                                            <span class="ml-2">({{ $period->start_date->diffInDays($period->end_date) + 1 }} jours)</span>
                                        </div>
                                        
                                        <!-- Barre de progression -->
                                        @php
                                            $now = now();
                                            $total = $period->start_date->diffInDays($period->end_date) + 1;
                                            $elapsed = 0;
                                            $progress = 0;
                                            
                                            if ($now >= $period->start_date && $now <= $period->end_date) {
                                                $elapsed = $period->start_date->diffInDays($now) + 1;
                                                $progress = min(($elapsed / $total) * 100, 100);
                                            } elseif ($now > $period->end_date) {
                                                $progress = 100;
                                            }
                                        @endphp
                                        
                                        <div class="mt-2">
                                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                <span>Progression</span>
                                                <span>{{ round($progress) }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune période définie</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Les périodes académiques n'ont pas encore été créées.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('admin.academic-years.manage-periods', $academicYear) }}" 
                               class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Créer des périodes
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection