@extends('layouts.dashboard')

@section('title', 'Profil de ' . $user->name)

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profil de {{ $user->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Détails du compte utilisateur et permissions</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la liste
                </a>
                @can('edit_users')
                <a href="{{ route('users.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="mx-auto">
        <!-- Informations principales -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-start space-x-6">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="h-20 w-20 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </span>
                        </div>
                    </div>

                    <!-- Informations utilisateur -->
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-4">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                            @if($user->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-1.5 h-1.5 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Actif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="w-1.5 h-1.5 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    Inactif
                                </span>
                            @endif
                            @if(!$user->email_verified_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Email non vérifié
                                </span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Email</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->email }}</p>
                            </div>
                            
                            @if($user->phone)
                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Téléphone</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->phone }}</p>
                            </div>
                            @endif

                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Membre depuis</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('d/m/Y') }}</p>
                            </div>

                            <div>
                                <p class="text-gray-500 dark:text-gray-400">Dernière connexion</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rôles et permissions -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Rôles et Permissions</h3>
                
                <div class="space-y-6">
                    <!-- Rôles -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Rôles assignés</h4>
                        <div class="flex flex-wrap gap-2">
                            @forelse($user->roles as $role)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                    @if($role->name === 'super_admin') bg-red-100 text-red-800
                                    @elseif($role->name === 'admin') bg-orange-100 text-orange-800
                                    @elseif($role->name === 'teacher') bg-green-100 text-green-800
                                    @elseif($role->name === 'student') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    {{ ucfirst($role->name) }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-500 dark:text-gray-400">Aucun rôle assigné</span>
                            @endforelse
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Permissions ({{ $user->getAllPermissions()->count() }} au total)
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                            @forelse($user->getAllPermissions() as $permission)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="text-sm text-gray-500 dark:text-gray-400">Aucune permission</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations additionnelles -->
        @if($user->date_of_birth || $user->address)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Informations Personnelles</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if($user->date_of_birth)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Date de naissance</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($user->date_of_birth)->format('d/m/Y') }}
                            <span class="text-gray-500 dark:text-gray-400">
                                ({{ \Carbon\Carbon::parse($user->date_of_birth)->age }} ans)
                            </span>
                        </p>
                    </div>
                    @endif

                    @if($user->address)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Adresse</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->address }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Actions administratives -->
        @can('manage_users')
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Actions Administratives</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Basculer le statut -->
                    <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                            @if($user->is_active)
                                <svg class="w-4 h-4 mr-2 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                </svg>
                                Désactiver le compte
                            @else
                                <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Activer le compte
                            @endif
                        </button>
                    </form>

                    <!-- Réinitialiser le mot de passe -->
                    <form method="POST" action="{{ route('users.reset-password', $user) }}" class="inline" 
                          onsubmit="return confirm('Voulez-vous vraiment réinitialiser le mot de passe de cet utilisateur ?')">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Réinitialiser le mot de passe
                        </button>
                    </form>

                    <!-- Supprimer l'utilisateur -->
                    @if($user->id !== auth()->id() && !$user->hasRole('super_admin'))
                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" 
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Supprimer l'utilisateur
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endcan
    </div>
</div>
@endsection