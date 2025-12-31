@extends('layouts.dashboard')

@section('title', $ufr->name)

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec boutons d'action -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ufr->name }}</h2>
                        <span class="ml-3 px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
                            {{ $ufr->code }}
                        </span>
                        @if($ufr->is_active)
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded">
                                Active
                            </span>
                        @else
                            <span class="ml-2 px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100 rounded">
                                Inactive
                            </span>
                        @endif
                    </div>
                    
                    @if($ufr->short_name)
                        <p class="text-lg text-gray-600 dark:text-gray-400 mt-1">{{ $ufr->short_name }}</p>
                    @endif
                    
                    @if($ufr->description)
                        <p class="text-gray-600 dark:text-gray-400 mt-3">{{ $ufr->description }}</p>
                    @endif
                </div>
                
                <div class="flex gap-3 ml-6">
                    <a href="{{ route('university.ufrs.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Liste UFR
                    </a>
                    <a href="{{ route('university.ufrs.edit', $ufr) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifier
                    </a>
                    <a href="{{ route('university.departments.create', ['ufr' => $ufr->id]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouveau Département
                    </a>
                </div>
            </div>
        </div>

        <!-- Informations détaillées -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informations générales -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Statistiques -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Statistiques</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $ufr->departments->count() }}</div>
                            <div class="text-sm text-blue-700 dark:text-blue-300">Départements</div>
                        </div>
                        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $totalPrograms }}</div>
                            <div class="text-sm text-green-700 dark:text-green-300">Programmes</div>
                        </div>
                        <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $totalSemesters }}</div>
                            <div class="text-sm text-purple-700 dark:text-purple-300">Semestres</div>
                        </div>
                        <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $totalCourseUnits }}</div>
                            <div class="text-sm text-orange-700 dark:text-orange-300">UE</div>
                        </div>
                    </div>
                </div>

                <!-- Départements -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Départements</h3>
                        <a href="{{ route('university.departments.create', ['ufr' => $ufr->id]) }}" 
                           class="inline-flex items-center px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded transition-colors">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Ajouter
                        </a>
                    </div>
                    
                    @if($ufr->departments->isEmpty())
                        <div class="p-6 text-center">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">Aucun département</h4>
                            <p class="text-gray-600 dark:text-gray-400 mt-1">Commencez par créer le premier département de cette UFR.</p>
                            <a href="{{ route('university.departments.create', ['ufr' => $ufr->id]) }}" 
                               class="inline-flex items-center mt-3 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                Créer un département
                            </a>
                        </div>
                    @else
                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($ufr->departments as $department)
                                <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ $department->name }}</h4>
                                                <span class="ml-2 px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
                                                    {{ $department->code }}
                                                </span>
                                            </div>
                                            
                                            @if($department->description)
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $department->description }}</p>
                                            @endif
                                            
                                            <div class="flex items-center mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                {{ $department->programs->count() }} programme(s)
                                                
                                                @if($department->head_name)
                                                    <span class="ml-4">👨‍💼 {{ $department->head_name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2 ml-4">
                                            <a href="{{ route('university.departments.show', $department) }}" 
                                               class="inline-flex items-center px-3 py-1 border border-purple-300 text-purple-700 hover:bg-purple-50 dark:border-purple-600 dark:text-purple-400 dark:hover:bg-gray-700 rounded text-sm font-medium transition-colors">
                                                Voir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informations de contact -->
            <div class="space-y-6">
                <!-- Direction -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Direction</h3>
                    
                    @if($ufr->dean_name || $ufr->dean_email)
                        <div class="space-y-3">
                            @if($ufr->dean_name)
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $ufr->dean_name }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Doyen</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if($ufr->dean_email)
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <a href="mailto:{{ $ufr->dean_email }}" class="text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                        {{ $ufr->dean_email }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Informations de direction non renseignées</p>
                    @endif
                </div>

                <!-- Contact -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Contact</h3>
                    
                    <div class="space-y-3">
                        @if($ufr->email)
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:{{ $ufr->email }}" class="text-purple-600 hover:text-purple-700 dark:text-purple-400">
                                    {{ $ufr->email }}
                                </a>
                            </div>
                        @endif
                        
                        @if($ufr->phone)
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="text-gray-900 dark:text-white">{{ $ufr->phone }}</span>
                            </div>
                        @endif
                        
                        @if(!$ufr->email && !$ufr->phone)
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Informations de contact non renseignées</p>
                        @endif
                    </div>
                </div>

                <!-- Localisation -->
                @if($ufr->building || $ufr->address)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Localisation</h3>
                        
                        <div class="space-y-3">
                            @if($ufr->building)
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span class="text-gray-900 dark:text-white">{{ $ufr->building }}</span>
                                </div>
                            @endif
                            
                            @if($ufr->address)
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 text-gray-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-gray-900 dark:text-white">{{ $ufr->address }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Actions rapides -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions rapides</h3>
                    
                    <div class="space-y-2">
                        <a href="{{ route('university.departments.index', ['ufr' => $ufr->id]) }}" 
                           class="flex items-center w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Voir tous les départements
                        </a>
                        
                        <a href="{{ route('university.programs.index', ['ufr' => $ufr->id]) }}" 
                           class="flex items-center w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Voir tous les programmes
                        </a>
                        
                        <a href="{{ route('university.ufrs.edit', $ufr) }}" 
                           class="flex items-center w-full px-3 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier l'UFR
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection