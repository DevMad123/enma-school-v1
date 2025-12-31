@extends('layouts.dashboard')

@section('title', 'Gestion des Frais Scolaires')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Frais Scolaires</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Définition et gestion des frais de scolarité</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('finance.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Tableau de bord
                </a>
                <a href="{{ route('finance.school-fees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouveau Frais
                </a>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="max-w-7xl mx-auto">
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                @if($schoolFees->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom des Frais</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cible</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Année Académique</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Échéance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($schoolFees as $fee)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $fee->name }}</div>
                                        @if($fee->description)
                                        <div class="text-sm text-gray-500">{{ Str::limit($fee->description, 50) }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($fee->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($fee->schoolClass)
                                        Classe: {{ $fee->schoolClass->name }}
                                    @elseif($fee->level)
                                        Niveau: {{ $fee->level->name }}
                                    @elseif($fee->cycle)
                                        Cycle: {{ $fee->cycle->name }}
                                    @else
                                        Tous les étudiants
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $fee->academicYear->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($fee->due_date)
                                        <div class="@if($fee->isOverdue()) text-red-600 @endif">
                                            {{ $fee->due_date->format('d/m/Y') }}
                                            @if($fee->isOverdue())
                                                <span class="text-xs">(Échu)</span>
                                            @endif
                                        </div>
                                    @else
                                        Pas d'échéance
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($fee->status === 'active') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($fee->status === 'active') Actif @else Inactif @endif
                                    </span>
                                    @if($fee->is_mandatory)
                                        <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Obligatoire
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('finance.school-fees.edit', $fee) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        
                                        <form method="POST" action="{{ route('finance.school-fees.destroy', $fee) }}" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce frais ?')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $schoolFees->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">🏫</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun frais scolaire configuré</h3>
                    <p class="text-gray-500 mb-6">Commencez par créer les premiers frais scolaires pour votre établissement.</p>
                    <a href="{{ route('finance.school-fees.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                        Créer le premier frais scolaire
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection