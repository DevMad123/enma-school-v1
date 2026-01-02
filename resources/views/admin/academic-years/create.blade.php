@extends('layouts.dashboard')

@section('title', 'Nouvelle Année Académique')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Nouvelle Année Académique</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Créer une nouvelle année académique pour un établissement</p>
            </div>
            <div>
                <a href="{{ route('admin.academic-years.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Form -->
<div class="mx-auto">
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <form action="{{ route('admin.academic-years.store') }}" method="POST" class="space-y-6 p-6">
            @csrf
            
            <!-- École (fixe pour la V1) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    École
                </label>
                <div class="w-full p-3 bg-gray-50 dark:bg-gray-600 border border-gray-300 dark:border-gray-600 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-gray-900 dark:text-white">{{ $mainSchool->name }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Type : 
                                @if($mainSchool->type === 'university')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400">
                                        🎓 Universitaire
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                                        🏫 Pré-universitaire
                                    </span>
                                @endif
                                | Système : {{ ucfirst($mainSchool->academic_system) }}
                            </p>
                        </div>
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                </div>
                
                <!-- Champ caché pour l'ID de l'école -->
                <input type="hidden" name="school_id" value="{{ $mainSchool->id }}">
                
                @if($mainSchool->type === 'university')
                    <div class="mt-2 p-3 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-purple-800 dark:text-purple-200">
                                🎓 Mode universitaire : Gestion des UFR, départements, programmes et semestres
                            </p>
                        </div>
                    </div>
                @else
                    <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                🏫 Mode pré-universitaire : Gestion des cycles, niveaux et classes
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Nom de l'année -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nom de l'année académique <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       placeholder="ex: 2024-2025"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de début <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('start_date') border-red-500 @enderror">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de fin <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('end_date') border-red-500 @enderror">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Options -->
            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 @error('is_active') border-red-500 @enderror">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300">
                            Activer cette année académique
                        </label>
                        <p class="text-gray-500 dark:text-gray-400">
                            Si cochée, cette année sera définie comme l'année académique active de l'école (désactive les autres).
                        </p>
                    </div>
                    @error('is_active')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="is_current" id="is_current" value="1" {{ old('is_current') ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-purple-600 focus:ring-purple-500 @error('is_current') border-red-500 @enderror">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="is_current" class="font-medium text-gray-700 dark:text-gray-300">
                            Définir comme année courante
                        </label>
                        <p class="text-gray-500 dark:text-gray-400">
                            L'année courante est utilisée par défaut dans les opérations (créations semestres, etc.).
                        </p>
                    </div>
                    @error('is_current')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="create_periods" id="create_periods" value="1" checked
                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="create_periods" class="font-medium text-gray-700 dark:text-gray-300">
                            Créer automatiquement les périodes
                        </label>
                        <p class="text-gray-500 dark:text-gray-400">
                            @if($mainSchool->academic_system === 'trimestre')
                                Crée automatiquement 3 trimestres pour l'année académique.
                            @else
                                Crée automatiquement 2 semestres pour l'année académique.
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Preview des périodes -->
            <div id="periods-preview" class="hidden">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Aperçu des périodes qui seront créées :</h3>
                <div id="periods-list" class="space-y-2"></div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.academic-years.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Annuler
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Créer l'année académique
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const schoolSelect = document.getElementById('school_id');
    const schoolInfo = document.getElementById('school-info');
    const systemInfo = document.getElementById('system-info');
    const createPeriodsCheckbox = document.getElementById('create_periods');
    const periodsPreview = document.getElementById('periods-preview');
    const periodsList = document.getElementById('periods-list');

    function updateSchoolInfo() {
        const selectedOption = schoolSelect.selectedOptions[0];
        if (selectedOption && selectedOption.value) {
            const academicSystem = selectedOption.dataset.academicSystem;
            
            // Show school info
            schoolInfo.classList.remove('hidden');
            systemInfo.textContent = `Cette école utilise le système ${academicSystem}. ${academicSystem === 'trimestre' ? '3 trimestres' : '2 semestres'} seront créé(s).`;
            
            // Update periods preview
            updatePeriodsPreview(academicSystem);
        } else {
            schoolInfo.classList.add('hidden');
            periodsPreview.classList.add('hidden');
        }
    }

    function updatePeriodsPreview(academicSystem) {
        if (!createPeriodsCheckbox.checked) {
            periodsPreview.classList.add('hidden');
            return;
        }

        let periods = [];
        if (academicSystem === 'trimestre') {
            periods = [
                { name: 'Trimestre 1', order: 1 },
                { name: 'Trimestre 2', order: 2 },
                { name: 'Trimestre 3', order: 3 }
            ];
        } else if (academicSystem === 'semestre') {
            periods = [
                { name: 'Semestre 1', order: 1 },
                { name: 'Semestre 2', order: 2 }
            ];
        }

        // Generate preview HTML
        periodsList.innerHTML = periods.map(period => `
            <div class="flex items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-gray-700 dark:text-gray-300">${period.name}</span>
            </div>
        `).join('');

        periodsPreview.classList.remove('hidden');
    }

    // Event listeners
    schoolSelect.addEventListener('change', updateSchoolInfo);
    createPeriodsCheckbox.addEventListener('change', function() {
        updateSchoolInfo(); // This will call updatePeriodsPreview internally
    });

    // Initialize on page load
    updateSchoolInfo();

    // Auto-generate name based on current year
    if (!document.getElementById('name').value) {
        const currentYear = new Date().getFullYear();
        document.getElementById('name').value = `${currentYear}-${currentYear + 1}`;
    }

    // Auto-set dates for academic year (September to June)
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (!startDateInput.value) {
        const currentYear = new Date().getFullYear();
        const currentMonth = new Date().getMonth() + 1; // 1-12
        
        // If we're in July-December, academic year starts this September, else next September
        const academicYearStart = currentMonth >= 7 ? currentYear : currentYear - 1;
        
        startDateInput.value = `${academicYearStart}-09-01`;
        endDateInput.value = `${academicYearStart + 1}-06-30`;
    }
});
</script>
@endpush