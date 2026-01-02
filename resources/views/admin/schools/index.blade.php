@extends('layouts.dashboard')

@section('title', 'Gouvernance de l\'Établissement')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gouvernance de l'Établissement</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configuration et paramètres de votre établissement</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.pedagogy-settings.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Paramètres Pédagogiques
                </a>
                <a href="{{ route('admin.schools.settings', $school) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Paramètres
                </a>
                <a href="{{ route('admin.schools.edit', $school) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>
    </div>
</div>

<!-- School Information Cards -->
<div class="space-y-6">
    
    <!-- Basic Information -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Informations Générales</h3>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $school->is_active ? 'Actif' : 'Inactif' }}
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom de l'établissement</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->name }}</dd>
                </div>
                
                @if($school->short_name)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom abrégé</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->short_name }}</dd>
                </div>
                @endif
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Type d'établissement</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->type === 'pre_university' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                            {{ $school->type === 'pre_university' ? 'Pré Universitaire' : 'Universitaire' }}
                        </span>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->email }}</dd>
                </div>
                
                @if($school->phone)
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->phone }}</dd>
                </div>
                @endif
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pays</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->country }}</dd>
                </div>
            </div>
            
            @if($school->address)
            <div class="mt-4">
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Adresse</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $school->address }}</dd>
            </div>
            @endif
        </div>
    </div>

    <!-- Academic Configuration -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Configuration Académique</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Système académique</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ ucfirst($school->academic_system) }}
                        </span>
                    </dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Système de notation</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ $school->grading_system === '20' ? 'Sur 20' : ($school->grading_system === '100' ? 'Sur 100' : 'Personnalisé') }}
                        </span>
                    </dd>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Documents Officiels</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Logo -->
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Logo de l'établissement</dt>
                    @if($school->logo_path)
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ \Storage::url($school->logo_path) }}" alt="Logo" class="w-full h-full object-contain">
                        </div>
                        <p class="text-xs text-green-600 mt-2">✓ Configuré</p>
                    @else
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Non configuré</p>
                    @endif
                </div>

                <!-- Stamp -->
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Tampon officiel</dt>
                    @if($school->stamp_path)
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ \Storage::url($school->stamp_path) }}" alt="Tampon" class="w-full h-full object-contain">
                        </div>
                        <p class="text-xs text-green-600 mt-2">✓ Configuré</p>
                    @else
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Non configuré</p>
                    @endif
                </div>

                <!-- Signature -->
                <div class="text-center">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Signature officielle</dt>
                    @if($school->signature_path)
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-lg overflow-hidden">
                            <img src="{{ \Storage::url($school->signature_path) }}" alt="Signature" class="w-full h-full object-contain">
                        </div>
                        <p class="text-xs text-green-600 mt-2">✓ Configuré</p>
                    @else
                        <div class="w-20 h-20 mx-auto bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Non configuré</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    @if($school->settings->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Paramètres Configurés</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $school->settings->count() }} paramètre(s) personnalisé(s) configuré(s).
                <a href="{{ route('admin.schools.settings', $school) }}" class="text-blue-600 hover:text-blue-800">Voir tous les paramètres →</a>
            </p>
        </div>
    </div>
    @endif

</div>
@endsection