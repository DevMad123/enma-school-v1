@extends('layouts.dashboard')

@section('title', 'Détails Étudiant - ' . $student->user->name)

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $student->user->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Étudiant #{{ $student->student_number }} - 
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $student->status === 'active' ? 'bg-green-100 text-green-800' : 
                           ($student->status === 'inactive' ? 'bg-red-100 text-red-800' : 
                            ($student->status === 'graduated' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                        {{ ucfirst($student->status) }}
                    </span>
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.students.edit', $student) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('admin.students.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informations Principales -->
            <div class="lg:col-span-2 space-y-6">
                <x-dashboard.card title="Informations Personnelles">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nom Complet</label>
                            <p class="mt-1 text-gray-900 dark:text-white font-medium">{{ $student->user->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->user->email ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->user->phone ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Genre</label>
                            <p class="mt-1 text-gray-900 dark:text-white">
                                {{ $student->gender === 'male' ? 'Masculin' : ($student->gender === 'female' ? 'Féminin' : 'N/A') }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Date de Naissance</label>
                            <p class="mt-1 text-gray-900 dark:text-white">
                                {{ $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : 'N/A' }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Lieu de Naissance</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->place_of_birth ?? 'N/A' }}</p>
                        </div>
                        
                        @if($student->address)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Adresse</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->address }}</p>
                        </div>
                        @endif
                    </div>
                </x-dashboard.card>

                <x-dashboard.card title="Informations Tuteur/Parent">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nom du Tuteur</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->guardian_name ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone Tuteur</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->guardian_phone ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email Tuteur</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->guardian_email ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Contact d'Urgence</label>
                            <p class="mt-1 text-gray-900 dark:text-white">{{ $student->emergency_contact ?? 'N/A' }}</p>
                        </div>
                    </div>
                </x-dashboard.card>

                @if($student->medical_info || $student->notes)
                <x-dashboard.card title="Informations Complémentaires">
                    @if($student->medical_info)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Informations Médicales</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $student->medical_info }}</p>
                    </div>
                    @endif
                    
                    @if($student->notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notes Administratives</label>
                        <p class="mt-1 text-gray-900 dark:text-white">{{ $student->notes }}</p>
                    </div>
                    @endif
                </x-dashboard.card>
                @endif
            </div>

            <!-- Panneau latéral -->
            <div class="space-y-6">
                <!-- Affectation actuelle -->
                <x-dashboard.card title="Affectation Actuelle">
                    @if($student->currentClass)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Classe</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $student->currentClass->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Niveau</label>
                                <p class="mt-1 text-gray-900 dark:text-white">{{ $student->currentClass->level->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Cycle</label>
                                <p class="mt-1 text-gray-900 dark:text-white">{{ $student->currentClass->level->cycle->name }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Effectif Classe</label>
                                <p class="mt-1 text-gray-900 dark:text-white">{{ $student->currentClass->current_enrollment }} étudiants</p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 dark:text-gray-400">Aucune classe affectée</p>
                        </div>
                    @endif
                </x-dashboard.card>

                <!-- Historique des inscriptions -->
                <x-dashboard.card title="Historique Inscriptions">
                    @if($student->enrollments->count() > 0)
                        <div class="space-y-3">
                            @foreach($student->enrollments as $enrollment)
                            <div class="border-b border-gray-200 dark:border-gray-700 last:border-b-0 pb-3 last:pb-0">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $enrollment->academicYear->name ?? 'Année inconnue' }}</p>
                                <p class="text-sm text-gray-500">{{ $enrollment->class->name ?? 'Classe inconnue' }}</p>
                                <p class="text-xs text-gray-400">{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'Date inconnue' }}</p>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 dark:text-gray-400">Aucune inscription</p>
                        </div>
                    @endif
                </x-dashboard.card>

                <!-- Actions rapides -->
                <x-dashboard.card title="Actions Rapides">
                    <div class="space-y-3">
                        <form method="POST" action="{{ route('admin.students.toggle-status', $student) }}" class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-medium"
                                    onclick="return confirm('Voulez-vous changer le statut de cet étudiant ?')">
                                {{ $student->status === 'active' ? 'Désactiver' : 'Activer' }} Étudiant
                            </button>
                        </form>
                        
                        <a href="#" 
                           class="block w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium text-center">
                            Voir Bulletin
                        </a>
                        
                        <a href="#" 
                           class="block w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium text-center">
                            Paiements
                        </a>
                    </div>
                </x-dashboard.card>

                <!-- Méta-informations -->
                <x-dashboard.card title="Informations Système">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Créé le</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Modifié le</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Numéro Étudiant</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $student->student_number }}</p>
                        </div>
                    </div>
                </x-dashboard.card>
            </div>
        </div>
    </div>
@endsection