@extends('layouts.dashboard')

@section('title', 'Modifier le Programme')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Modifier le programme</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        {{ $program->name }} ({{ $program->code }})
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('university.programs.show', $program) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Retour aux détails
                    </a>
                    <a href="{{ route('university.programs.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Liste des programmes
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <form action="{{ route('university.programs.update', $program) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="p-6 space-y-6">
                    <!-- Département -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Rattachement au département</h3>
                        <div>
                            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Département <span class="text-red-500">*</span>
                            </label>
                            <select id="department_id" name="department_id" 
                                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                    required>
                                <option value="">Sélectionnez un département...</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', $program->department_id) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }} ({{ $department->ufr->name }})
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Informations de base -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informations générales</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nom complet -->
                            <div class="md:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom complet du programme <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="name" name="name" value="{{ old('name', $program->name) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Licence en Informatique et Systèmes d'Information"
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Code -->
                            <div>
                                <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Code du programme <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="code" name="code" value="{{ old('code', $program->code) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: LIC-INFO"
                                       required>
                                @error('code')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Nom court -->
                            <div>
                                <label for="short_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Nom court (optionnel)
                                </label>
                                <input type="text" id="short_name" name="short_name" value="{{ old('short_name', $program->short_name) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: L3-INFO">
                                @error('short_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Niveau -->
                            <div>
                                <label for="level" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Niveau d'études <span class="text-red-500">*</span>
                                </label>
                                <select id="level" name="level" 
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                        required>
                                    <option value="">Sélectionnez un niveau...</option>
                                    <option value="dut" {{ old('level', $program->level) == 'dut' ? 'selected' : '' }}>DUT (Diplôme Universitaire de Technologie)</option>
                                    <option value="bts" {{ old('level', $program->level) == 'bts' ? 'selected' : '' }}>BTS (Brevet de Technicien Supérieur)</option>
                                    <option value="licence" {{ old('level', $program->level) == 'licence' ? 'selected' : '' }}>Licence (Bac+3)</option>
                                    <option value="master" {{ old('level', $program->level) == 'master' ? 'selected' : '' }}>Master (Bac+5)</option>
                                    <option value="doctorat" {{ old('level', $program->level) == 'doctorat' ? 'selected' : '' }}>Doctorat (Bac+8)</option>
                                </select>
                                @error('level')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Durée en semestres -->
                            <div>
                                <label for="duration_semesters" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Durée (semestres) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="duration_semesters" name="duration_semesters" value="{{ old('duration_semesters', $program->duration_semesters) }}" 
                                       min="1" max="10"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                       required>
                                @error('duration_semesters')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total crédits ECTS -->
                            <div>
                                <label for="total_credits" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total crédits ECTS <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="total_credits" name="total_credits" value="{{ old('total_credits', $program->total_credits) }}" 
                                       min="1" max="500"
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                       required>
                                <p class="text-sm text-gray-500 mt-1">
                                    Licence: 180, Master: 120, DUT/BTS: 120
                                </p>
                                @error('total_credits')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Titre du diplôme -->
                            <div class="md:col-span-2">
                                <label for="diploma_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Titre du diplôme <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="diploma_title" name="diploma_title" value="{{ old('diploma_title', $program->diploma_title) }}" 
                                       class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                       placeholder="ex: Licence en Informatique et Systèmes d'Information"
                                       required>
                                @error('diploma_title')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="md:col-span-2">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Description du programme (optionnel)
                                </label>
                                <textarea id="description" name="description" rows="4" 
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                          placeholder="Décrivez les objectifs, les compétences développées et les débouchés du programme...">{{ old('description', $program->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Objectifs -->
                            <div class="md:col-span-2">
                                <label for="objectives_text" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Objectifs pédagogiques (optionnel)
                                </label>
                                <textarea id="objectives_text" name="objectives_text" rows="3" 
                                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-green-500 focus:ring-green-500 dark:bg-gray-700 dark:text-white" 
                                          placeholder="Listez les objectifs pédagogiques du programme (un par ligne)">{{ old('objectives_text', $objectivesText) }}</textarea>
                                <p class="text-sm text-gray-500 mt-1">
                                    Saisissez un objectif par ligne
                                </p>
                                @error('objectives_text')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-3 flex justify-between">
                    <a href="{{ route('university.programs.show', $program) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Mettre à jour le programme
                    </button>
                </div>
            </form>
        </div>

        <!-- Aide -->
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                        Modification des programmes
                    </h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300">
                        <p>
                            La modification d'un programme actif peut affecter les étudiants déjà inscrits. 
                            Assurez-vous de coordonner avec les services pédagogiques avant d'apporter des changements majeurs.
                        </p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li><strong>Code :</strong> Peut affecter les systèmes d'inscription existants</li>
                            <li><strong>Crédits ECTS :</strong> Impact sur la validation des parcours étudiants</li>
                            <li><strong>Niveau :</strong> Influence la classification des diplômes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-remplissage des crédits selon le niveau
document.getElementById('level').addEventListener('change', function() {
    const level = this.value;
    const creditsField = document.getElementById('total_credits');
    const semestersField = document.getElementById('duration_semesters');
    
    // Ne pas écraser les valeurs existantes, juste suggérer
    if (creditsField.value === '' || semestersField.value === '') {
        switch(level) {
            case 'dut':
            case 'bts':
                if (creditsField.value === '') creditsField.value = 120;
                if (semestersField.value === '') semestersField.value = 4;
                break;
            case 'licence':
                if (creditsField.value === '') creditsField.value = 180;
                if (semestersField.value === '') semestersField.value = 6;
                break;
            case 'master':
                if (creditsField.value === '') creditsField.value = 120;
                if (semestersField.value === '') semestersField.value = 4;
                break;
            case 'doctorat':
                if (creditsField.value === '') creditsField.value = 180;
                if (semestersField.value === '') semestersField.value = 6;
                break;
        }
    }
});
</script>
@endsection