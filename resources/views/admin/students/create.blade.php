@extends('layouts.dashboard')

@section('title', 'Nouvel Étudiant')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nouvel Étudiant</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Créer un nouveau profil étudiant</p>
            </div>
            <a href="{{ route('admin.students.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>

        <form method="POST" action="{{ route('admin.students.store') }}" class="space-y-6">
            @csrf
            
            <x-dashboard.card title="Informations Personnelles">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom Complet *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="Nom et prénoms">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="adresse@email.com">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone</label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="+229 XX XX XX XX">
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Genre</label>
                        <select name="gender" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner...</option>
                            <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Masculin</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('gender')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date de Naissance</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @error('date_of_birth')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lieu de Naissance</label>
                        <input type="text" name="place_of_birth" value="{{ old('place_of_birth') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="Ville, Pays">
                        @error('place_of_birth')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Adresse</label>
                        <textarea name="address" rows="3"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                  placeholder="Adresse complète">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-dashboard.card>

            <x-dashboard.card title="Affectation Académique">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Classe d'Affectation *</label>
                        <select name="class_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner une classe...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} - {{ $class->level->name }} ({{ $class->level->cycle->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Année Académique</label>
                        <input type="text" readonly value="{{ $academicYear ? $academicYear->name : 'Aucune année active' }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-100 dark:text-gray-400">
                    </div>
                </div>
            </x-dashboard.card>

            <x-dashboard.card title="Informations Tuteur/Parent">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom du Tuteur</label>
                        <input type="text" name="guardian_name" value="{{ old('guardian_name') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="Nom complet du parent/tuteur">
                        @error('guardian_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone Tuteur</label>
                        <input type="tel" name="guardian_phone" value="{{ old('guardian_phone') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="+229 XX XX XX XX">
                        @error('guardian_phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Tuteur</label>
                        <input type="email" name="guardian_email" value="{{ old('guardian_email') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="email@tuteur.com">
                        @error('guardian_email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Contact d'Urgence</label>
                        <input type="text" name="emergency_contact" value="{{ old('emergency_contact') }}"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                               placeholder="Personne à contacter en cas d'urgence">
                        @error('emergency_contact')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-dashboard.card>

            <x-dashboard.card title="Informations Complémentaires">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Informations Médicales</label>
                        <textarea name="medical_info" rows="3"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                  placeholder="Allergies, traitements, handicaps, etc.">{{ old('medical_info') }}</textarea>
                        @error('medical_info')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes Administratives</label>
                        <textarea name="notes" rows="3"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                  placeholder="Remarques internes, observations...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </x-dashboard.card>

            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.students.index') }}" 
                   class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                    Créer l'Étudiant
                </button>
            </div>
        </form>
    </div>
@endsection