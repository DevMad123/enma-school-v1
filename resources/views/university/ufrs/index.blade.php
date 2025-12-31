@extends('layouts.dashboard')

@section('title', 'Gestion des UFR')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec boutons d'action -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <div class="flex items-center">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Unités de Formation et de Recherche (UFR)</h2>
                        <span class="ml-3 px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100 rounded-full">
                            Universitaire
                        </span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Gérez les grandes divisions académiques de votre université
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('university.dashboard') }}" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('university.ufrs.create') }}" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer une UFR
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ufrs->count() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">UFR Total</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ufrs->sum('departments_count') }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Départements</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $ufrs->where('is_active', true)->count() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">UFR Actives</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalPrograms ?? 0 }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Programmes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des UFR -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Liste des UFR</h3>
            </div>
            
            @if($ufrs->isEmpty())
                <div class="p-6 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucune UFR créée</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">Commencez par créer votre première Unité de Formation et de Recherche.</p>
                    <a href="{{ route('university.ufrs.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer une UFR
                    </a>
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($ufrs as $ufr)
                        <div class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white">{{ $ufr->name }}</h4>
                                        <span class="ml-2 px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded">
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
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            <strong>Nom court :</strong> {{ $ufr->short_name }}
                                        </p>
                                    @endif
                                    
                                    @if($ufr->description)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $ufr->description }}</p>
                                    @endif
                                    
                                    <div class="flex items-center mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        {{ $ufr->departments_count }} département(s)
                                        
                                        @if($ufr->dean_name)
                                            <span class="ml-4">👨‍🎓 {{ $ufr->dean_name }}</span>
                                        @endif
                                        
                                        @if($ufr->building)
                                            <span class="ml-4">🏢 {{ $ufr->building }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-2 ml-4">
                                    <a href="{{ route('university.ufrs.show', $ufr) }}" 
                                       class="inline-flex items-center px-3 py-1 border border-purple-300 text-purple-700 hover:bg-purple-50 dark:border-purple-600 dark:text-purple-400 dark:hover:bg-gray-700 rounded text-sm font-medium transition-colors">
                                        Voir
                                    </a>
                                    <a href="{{ route('university.ufrs.edit', $ufr) }}" 
                                       class="inline-flex items-center px-3 py-1 border border-blue-300 text-blue-700 hover:bg-blue-50 dark:border-blue-600 dark:text-blue-400 dark:hover:bg-gray-700 rounded text-sm font-medium transition-colors">
                                        Modifier
                                    </a>
                                    <form action="{{ route('university.ufrs.destroy', $ufr) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette UFR ?')"
                                                class="inline-flex items-center px-3 py-1 border border-red-300 text-red-700 hover:bg-red-50 dark:border-red-600 dark:text-red-400 dark:hover:bg-gray-700 rounded text-sm font-medium transition-colors">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection