@extends('layouts.dashboard')

@section('title', 'Nouvel Enseignant')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">➕ Nouvel Enseignant</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">MODULE A4 - Ajouter un enseignant au système</p>
            </div>
            <div>
                <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto">
    <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Informations personnelles -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">👤 Informations personnelles</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Utilisateur <span class="text-red-500">*</span></label>
                                <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('user_id') border-red-300 @enderror" id="user_id" name="user_id" required>
                                    <option value="">Sélectionner un utilisateur</option>
                                    @foreach($availableUsers as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Sélectionnez un utilisateur existant ou 
                                    <a href="{{ route('users.create') }}" target="_blank" class="text-blue-600 hover:text-blue-500">créez un nouveau compte</a>
                                </p>
                            </div>
                            <div>
                                <label for="school_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">École <span class="text-red-500">*</span></label>
                                <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('school_id') border-red-300 @enderror" id="school_id" name="school_id" required>
                                    <option value="">Sélectionner une école</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('school_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prénom <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('first_name') border-red-300 @enderror" 
                                       id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nom <span class="text-red-500">*</span></label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('last_name') border-red-300 @enderror" 
                                       id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Téléphone</label>
                                <input type="tel" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-300 @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="employee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Numéro d'employé</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('employee_id') border-red-300 @enderror" 
                                       id="employee_id" name="employee_id" value="{{ old('employee_id') }}"
                                       placeholder="Ex: ENS001">
                                @error('employee_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations professionnelles -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">🎓 Informations professionnelles</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="specialization" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Spécialisation</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('specialization') border-red-300 @enderror" 
                                       id="specialization" name="specialization" value="{{ old('specialization') }}"
                                       placeholder="Ex: Mathématiques, Français...">
                                @error('specialization')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="hire_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date d'embauche</label>
                                <input type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hire_date') border-red-300 @enderror" 
                                       id="hire_date" name="hire_date" value="{{ old('hire_date') }}">
                                @error('hire_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="qualifications" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Qualifications / Diplômes</label>
                            <textarea class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qualifications') border-red-300 @enderror" 
                                      id="qualifications" name="qualifications" rows="3"
                                      placeholder="Listez les diplômes, certifications, formations...">{{ old('qualifications') }}</textarea>
                            @error('qualifications')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="teaching_subjects" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Matières enseignées</label>
                            <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('teaching_subjects') border-red-300 @enderror" 
                                   id="teaching_subjects_input" name="teaching_subjects_input" 
                                   value="{{ old('teaching_subjects') ? implode(', ', old('teaching_subjects', [])) : '' }}"
                                   placeholder="Ex: Mathématiques, Physique, Chimie (séparez par des virgules)">
                            <input type="hidden" id="teaching_subjects" name="teaching_subjects">
                            @error('teaching_subjects')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Séparez les matières par des virgules</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Statut -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">⚙️ Paramètres</h3>
                    </div>
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut <span class="text-red-500">*</span></label>
                            <select class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-300 @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>
                                    Actif
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
                                    Inactif
                                </option>
                                <option value="retired" {{ old('status') == 'retired' ? 'selected' : '' }}>
                                    Retraité
                                </option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Summary Card -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                            <h6 class="flex items-center text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Information
                            </h6>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mb-0">
                                L'enseignant sera automatiquement assigné au rôle "enseignant" dans le système.
                                Vous pourrez ensuite lui affecter des classes et matières.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="space-y-3">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Créer l'enseignant
                            </button>
                            <a href="{{ route('admin.teachers.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gérer les matières enseignées comme un array
    const subjectsInput = document.getElementById('teaching_subjects_input');
    const subjectsHidden = document.getElementById('teaching_subjects');
    
    subjectsInput.addEventListener('blur', function() {
        const subjects = this.value.split(',').map(s => s.trim()).filter(s => s.length > 0);
        subjectsHidden.value = JSON.stringify(subjects);
    });
    
    // Pré-remplir le champ caché si on a des données
    if (subjectsInput.value) {
        const subjects = subjectsInput.value.split(',').map(s => s.trim()).filter(s => s.length > 0);
        subjectsHidden.value = JSON.stringify(subjects);
    }
});
</script>
@endpush