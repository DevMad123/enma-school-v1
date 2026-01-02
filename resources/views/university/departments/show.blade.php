@extends('layouts.dashboard')

@section('title', $department->name)

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec boutons d'action -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $department->name }}</h2>
                        <span class="ml-3 px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
                            {{ $department->code }}
                        </span>
                        @if($department->is_active)
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded">
                                Actif
                            </span>
                        @else
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 rounded">
                                Inactif
                            </span>
                        @endif
                    </div>
                    
                    @if($department->short_name)
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">{{ $department->short_name }}</p>
                    @endif
                    
                    <!-- UFR de rattachement -->
                    <div class="mt-3">
                        <span class="text-sm text-gray-500">UFR de rattachement :</span>
                        <a href="{{ route('university.ufrs.show', $department->ufr) }}" 
                           class="text-purple-600 hover:text-purple-800 dark:text-purple-400 font-medium">
                            {{ $department->ufr->name }}
                        </a>
                    </div>
                    
                    @if($department->description)
                        <p class="text-gray-600 dark:text-gray-400 mt-3">{{ $department->description }}</p>
                    @endif
                </div>
                
                <div class="flex gap-3 ml-6">
                    <a href="{{ route('university.departments.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Liste départements
                    </a>
                    <a href="{{ route('university.departments.edit', $department) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_programs }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Programmes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_semesters }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Semestres</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_course_units }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Unités d'enseignement</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations du responsable -->
        @if($department->head_of_department || $department->contact_email || $department->contact_phone || $department->office_location)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Responsable du département</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if($department->head_of_department)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Chef de département</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $department->head_of_department }}</p>
                </div>
                @endif

                @if($department->office_location)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Localisation du bureau</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $department->office_location }}</p>
                </div>
                @endif

                @if($department->contact_email)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <p class="mt-1">
                        <a href="mailto:{{ $department->contact_email }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            {{ $department->contact_email }}
                        </a>
                    </p>
                </div>
                @endif

                @if($department->contact_phone)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                    <p class="mt-1">
                        <a href="tel:{{ $department->contact_phone }}" 
                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            {{ $department->contact_phone }}
                        </a>
                    </p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Liste des programmes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Programmes du département</h3>
                    <a href="{{ route('university.programs.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nouveau programme
                    </a>
                </div>
            </div>
            
            <div class="p-6">
                @if($department->programs->count() > 0)
                    <div class="space-y-4">
                        @foreach($department->programs as $program)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                                <a href="{{ route('university.programs.show', $program) }}" 
                                                   class="hover:text-green-600 dark:hover:text-green-400">
                                                    {{ $program->name }}
                                                </a>
                                            </h4>
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded">
                                                {{ $program->code }}
                                            </span>
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100 rounded">
                                                {{ ucfirst($program->level) }}
                                            </span>
                                        </div>
                                        
                                        @if($program->description)
                                            <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">{{ Str::limit($program->description, 150) }}</p>
                                        @endif
                                        
                                        <div class="flex items-center gap-4 mt-3 text-sm text-gray-500">
                                            <span>{{ $program->duration_semesters }} semestres</span>
                                            <span>{{ $program->total_credits }} crédits ECTS</span>
                                            <span>{{ $program->semesters->count() }} semestre(s) configuré(s)</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('university.programs.show', $program) }}" 
                                           class="inline-flex items-center px-3 py-1 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                            Détails
                                        </a>
                                        <a href="{{ route('university.programs.edit', $program) }}" 
                                           class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-gray-400">
                                            Modifier
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucun programme</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Ce département n'a encore aucun programme d'études.</p>
                        <a href="{{ route('university.programs.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Créer le premier programme
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Actions de suppression -->
        @if($department->programs->count() === 0)
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-medium text-red-800 dark:text-red-200">Zone de danger</h3>
                    <p class="text-red-600 dark:text-red-400 mt-1">Supprimer définitivement ce département</p>
                </div>
                <form action="{{ route('university.departments.destroy', $department) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce département ? Cette action est irréversible.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Supprimer le département
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection