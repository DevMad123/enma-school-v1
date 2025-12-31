@extends('layouts.dashboard')

@section('title', 'Paramètres Pédagogiques - Module A5')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Paramètres Pédagogiques</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configuration des systèmes de notation, seuils de validation et règles de redoublement</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.schools.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour Gouvernance
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto space-y-8">
    
    <!-- Paramètres Globaux -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Paramètres Globaux
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configuration générale du système de notation et des seuils</p>
        </div>
        
        <form action="{{ route('admin.pedagogy-settings.global.update') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Système de notation -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        Système de notation
                    </label>
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <input id="grading_20" name="grading_system" type="radio" value="20" 
                                   {{ $school->grading_system === '20' ? 'checked' : '' }}
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <label for="grading_20" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sur 20 points (Secondaire)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="grading_100" name="grading_system" type="radio" value="100" 
                                   {{ $school->grading_system === '100' ? 'checked' : '' }}
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <label for="grading_100" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sur 100 points (Universitaire)
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="grading_custom" name="grading_system" type="radio" value="custom" 
                                   {{ $school->grading_system === 'custom' ? 'checked' : '' }}
                                   class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                            <label for="grading_custom" class="ml-3 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Système personnalisé
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Seuils de validation -->
                <div class="space-y-4">
                    <div>
                        <label for="validation_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Seuil global de validation
                        </label>
                        <input type="number" name="validation_threshold" id="validation_threshold" 
                               value="{{ $pedagogy['validation_threshold'] }}" 
                               step="0.1" min="0" max="20"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500">Note minimale pour valider une matière</p>
                    </div>

                    <div>
                        <label for="redoublement_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Seuil de redoublement
                        </label>
                        <input type="number" name="redoublement_threshold" id="redoublement_threshold" 
                               value="{{ $pedagogy['redoublement_threshold'] }}" 
                               step="0.1" min="0" max="20"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500">Moyenne générale minimale pour éviter le redoublement</p>
                    </div>

                    <div>
                        <label for="validation_subjects_required" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Pourcentage de matières à valider
                        </label>
                        <input type="number" name="validation_subjects_required" id="validation_subjects_required" 
                               value="{{ $pedagogy['validation_subjects_required'] }}" 
                               min="0" max="100"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <p class="mt-1 text-xs text-gray-500">Pourcentage minimum de matières à valider pour passer au niveau supérieur</p>
                    </div>
                </div>
            </div>

            <!-- Options avancées -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-4">Options avancées</h4>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input id="automatic_promotion" name="automatic_promotion" type="checkbox" value="1" 
                                   {{ $pedagogy['automatic_promotion'] === 'true' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="automatic_promotion" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Promotion automatique basée sur les seuils
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input id="mention_system" name="mention_system" type="checkbox" value="1" 
                                   {{ $pedagogy['mention_system'] === 'true' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="mention_system" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Activer le système de mentions (Passable, Assez bien, Bien, Très bien)
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="bulletin_footer_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Texte de pied de page des bulletins
                        </label>
                        <textarea name="bulletin_footer_text" id="bulletin_footer_text" rows="3" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                  placeholder="Texte personnalisé affiché en bas des bulletins">{{ $pedagogy['bulletin_footer_text'] }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Texte affiché au bas de tous les bulletins</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les paramètres globaux
                </button>
            </div>
        </form>
    </div>

    <!-- Seuils spécifiques par niveau -->
    @if($levels->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Seuils spécifiques par niveau
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Personnaliser les seuils de validation pour chaque niveau (optionnel)</p>
        </div>
        
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Niveau</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cycle</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seuil personnalisé</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($levels as $level)
                        @php
                            $currentThreshold = $school->getSetting("level_{$level->id}_validation_threshold");
                            $hasCustomThreshold = !is_null($currentThreshold);
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $level->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $level->cycle->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($hasCustomThreshold)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $currentThreshold }} / {{ $school->grading_system === '100' ? '100' : '20' }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Seuil global ({{ $pedagogy['validation_threshold'] }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="openLevelModal({{ $level->id }}, '{{ $level->name }}', '{{ $currentThreshold ?? '' }}')" 
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $hasCustomThreshold ? 'Modifier' : 'Définir' }}
                                </button>
                                @if($hasCustomThreshold)
                                    <form action="{{ route('admin.pedagogy-settings.level.threshold.reset', $level) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Supprimer le seuil personnalisé pour {{ $level->name }} ?')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Réinitialiser
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Seuils spécifiques par matière -->
    @if($subjects->count() > 0)
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Seuils spécifiques par matière
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Personnaliser les seuils de validation pour chaque matière (optionnel)</p>
        </div>
        
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Matière</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Coefficient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Seuil personnalisé</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($subjects as $subject)
                        @php
                            $currentThreshold = $school->getSetting("subject_{$subject->id}_validation_threshold");
                            $hasCustomThreshold = !is_null($currentThreshold);
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $subject->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $subject->code }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $subject->coefficient }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($hasCustomThreshold)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $currentThreshold }} / {{ $school->grading_system === '100' ? '100' : '20' }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Seuil global ({{ $pedagogy['validation_threshold'] }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                <button onclick="openSubjectModal({{ $subject->id }}, '{{ $subject->name }}', '{{ $currentThreshold ?? '' }}')" 
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                    {{ $hasCustomThreshold ? 'Modifier' : 'Définir' }}
                                </button>
                                @if($hasCustomThreshold)
                                    <form action="{{ route('admin.pedagogy-settings.subject.threshold.reset', $subject) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Supprimer le seuil personnalisé pour {{ $subject->name }} ?')"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                            Réinitialiser
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>

<!-- Modal pour niveau -->
<div id="levelModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form id="levelForm" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="levelModalTitle"></h3>
                    <div class="mb-4">
                        <label for="level_threshold" class="block text-sm font-medium text-gray-700 mb-2">
                            Seuil de validation personnalisé
                        </label>
                        <input type="number" name="validation_threshold" id="level_threshold" 
                               step="0.1" min="0" max="20" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Laissez vide pour utiliser le seuil global</p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Enregistrer
                    </button>
                    <button type="button" onclick="closeLevelModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour matière -->
<div id="subjectModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form id="subjectForm" method="POST">
                @csrf
                @method('PUT')
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="subjectModalTitle"></h3>
                    <div class="mb-4">
                        <label for="subject_threshold" class="block text-sm font-medium text-gray-700 mb-2">
                            Seuil de validation personnalisé
                        </label>
                        <input type="number" name="validation_threshold" id="subject_threshold" 
                               step="0.1" min="0" max="20" required
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Laissez vide pour utiliser le seuil global</p>
                    </div>
                </div>
                <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Enregistrer
                    </button>
                    <button type="button" onclick="closeSubjectModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Fonctions pour les modals
    function openLevelModal(levelId, levelName, currentThreshold) {
        document.getElementById('levelModalTitle').textContent = 'Seuil personnalisé - ' + levelName;
        document.getElementById('levelForm').action = '/admin/pedagogy-settings/level/' + levelId + '/threshold';
        document.getElementById('level_threshold').value = currentThreshold || '';
        document.getElementById('levelModal').classList.remove('hidden');
    }

    function closeLevelModal() {
        document.getElementById('levelModal').classList.add('hidden');
    }

    function openSubjectModal(subjectId, subjectName, currentThreshold) {
        document.getElementById('subjectModalTitle').textContent = 'Seuil personnalisé - ' + subjectName;
        document.getElementById('subjectForm').action = '/admin/pedagogy-settings/subject/' + subjectId + '/threshold';
        document.getElementById('subject_threshold').value = currentThreshold || '';
        document.getElementById('subjectModal').classList.remove('hidden');
    }

    function closeSubjectModal() {
        document.getElementById('subjectModal').classList.add('hidden');
    }

    // Fermer les modals en cliquant en dehors
    document.addEventListener('click', function(event) {
        const levelModal = document.getElementById('levelModal');
        const subjectModal = document.getElementById('subjectModal');
        
        if (event.target === levelModal) {
            closeLevelModal();
        }
        if (event.target === subjectModal) {
            closeSubjectModal();
        }
    });
</script>
@endpush