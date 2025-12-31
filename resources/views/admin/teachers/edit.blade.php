@extends('layouts.dashboard')

@section('title', 'Modifier l\'enseignant')

@section('header')
<div class="flex items-center justify-between">
    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.teachers.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800">Modifier l'enseignant</h1>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('admin.teachers.show', $teacher) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Voir le profil
        </a>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.teachers.update', $teacher) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informations personnelles
            </h3>
        </div>
        <div class="p-6 space-y-6">
            <!-- Informations utilisateur -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $teacher->user->name) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-300 @enderror"
                           required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $teacher->user->email) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-300 @enderror"
                           required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Téléphone
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $teacher->user->phone) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('phone') border-red-300 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">
                        Date de naissance
                    </label>
                    <input type="date" 
                           id="date_of_birth" 
                           name="date_of_birth" 
                           value="{{ old('date_of_birth', $teacher->user->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_of_birth') border-red-300 @enderror">
                    @error('date_of_birth')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse
                </label>
                <textarea id="address" 
                          name="address" 
                          rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('address') border-red-300 @enderror">{{ old('address', $teacher->user->address) }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Informations professionnelles -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                </svg>
                Informations professionnelles
            </h3>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Numéro d'employé <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="employee_id" 
                           name="employee_id" 
                           value="{{ old('employee_id', $teacher->employee_id) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('employee_id') border-red-300 @enderror"
                           required>
                    @error('employee_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="specialization" class="block text-sm font-medium text-gray-700 mb-2">
                        Spécialisation
                    </label>
                    <input type="text" 
                           id="specialization" 
                           name="specialization" 
                           value="{{ old('specialization', $teacher->specialization) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('specialization') border-red-300 @enderror">
                    @error('specialization')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="hire_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date d'embauche <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="hire_date" 
                           name="hire_date" 
                           value="{{ old('hire_date', $teacher->hire_date?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('hire_date') border-red-300 @enderror"
                           required>
                    @error('hire_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Statut <span class="text-red-500">*</span>
                    </label>
                    <select id="status" 
                            name="status" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-300 @enderror"
                            required>
                        <option value="">Sélectionner un statut</option>
                        <option value="active" {{ old('status', $teacher->status) === 'active' ? 'selected' : '' }}>
                            Actif
                        </option>
                        <option value="inactive" {{ old('status', $teacher->status) === 'inactive' ? 'selected' : '' }}>
                            Inactif
                        </option>
                        <option value="on_leave" {{ old('status', $teacher->status) === 'on_leave' ? 'selected' : '' }}>
                            En congé
                        </option>
                        <option value="suspended" {{ old('status', $teacher->status) === 'suspended' ? 'selected' : '' }}>
                            Suspendu
                        </option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="employment_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Type d'emploi
                    </label>
                    <select id="employment_type" 
                            name="employment_type" 
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('employment_type') border-red-300 @enderror">
                        <option value="">Sélectionner un type</option>
                        <option value="full_time" {{ old('employment_type', $teacher->employment_type) === 'full_time' ? 'selected' : '' }}>
                            Temps plein
                        </option>
                        <option value="part_time" {{ old('employment_type', $teacher->employment_type) === 'part_time' ? 'selected' : '' }}>
                            Temps partiel
                        </option>
                        <option value="contract" {{ old('employment_type', $teacher->employment_type) === 'contract' ? 'selected' : '' }}>
                            Contractuel
                        </option>
                        <option value="substitute" {{ old('employment_type', $teacher->employment_type) === 'substitute' ? 'selected' : '' }}>
                            Remplaçant
                        </option>
                    </select>
                    @error('employment_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="qualifications" class="block text-sm font-medium text-gray-700 mb-2">
                    Qualifications et diplômes
                </label>
                <textarea id="qualifications" 
                          name="qualifications" 
                          rows="4" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qualifications') border-red-300 @enderror"
                          placeholder="Diplômes, certifications, formations...">{{ old('qualifications', $teacher->qualifications) }}</textarea>
                @error('qualifications')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                    Notes
                </label>
                <textarea id="notes" 
                          name="notes" 
                          rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notes') border-red-300 @enderror"
                          placeholder="Notes additionnelles...">{{ old('notes', $teacher->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between bg-white rounded-lg shadow-sm border p-6">
        <div class="flex items-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Dernière modification : {{ $teacher->updated_at->format('d/m/Y à H:i') }}
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150">
                Annuler
            </a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mettre à jour
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-génération du numéro d'employé si vide
    const employeeIdField = document.getElementById('employee_id');
    if (!employeeIdField.value) {
        const nameField = document.getElementById('name');
        nameField.addEventListener('blur', function() {
            if (!employeeIdField.value && nameField.value) {
                // Générer un numéro basé sur les initiales et l'année
                const initials = nameField.value.split(' ').map(word => word.charAt(0).toUpperCase()).join('');
                const year = new Date().getFullYear();
                const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
                employeeIdField.value = `${initials}${year}${random}`;
            }
        });
    }
});
</script>
@endpush
@endsection