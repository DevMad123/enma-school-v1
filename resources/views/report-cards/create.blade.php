@extends('layouts.dashboard')

@section('title', 'Générer un Bulletin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('report-cards.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Retour aux bulletins
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Générer un Bulletin</h1>
            <p class="text-gray-600 mt-1">
                Sélectionnez un étudiant et une période pour générer son bulletin scolaire.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <form method="POST" action="{{ route('report-cards.store') }}" class="space-y-6">
                @csrf

                <!-- Sélection de l'étudiant -->
                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Étudiant <span class="text-red-500">*</span>
                    </label>
                    <select name="student_id" id="student_id" required 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner un étudiant</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                {{ $student->full_name }}
                                @if($student->currentClass())
                                    - {{ $student->currentClass()->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sélection de la période -->
                <div>
                    <label for="grade_period_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Période <span class="text-red-500">*</span>
                    </label>
                    <select name="grade_period_id" id="grade_period_id" required 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner une période</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ old('grade_period_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->name }}
                                (du {{ $period->start_date->format('d/m/Y') }} au {{ $period->end_date->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('grade_period_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sélection de la classe (optionnel) -->
                <div>
                    <label for="school_class_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Classe <span class="text-gray-400">(optionnel, sera détectée automatiquement)</span>
                    </label>
                    <select name="school_class_id" id="school_class_id" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Détecter automatiquement</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('school_class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->name }} - {{ $class->level->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_class_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informations importantes -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-blue-800">Informations importantes</h3>
                            <div class="mt-1 text-sm text-blue-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Le bulletin sera automatiquement calculé à partir des notes existantes</li>
                                    <li>Si un bulletin existe déjà pour cette période, il sera mis à jour</li>
                                    <li>Assurez-vous que toutes les évaluations sont saisies avant génération</li>
                                    <li>Le bulletin sera d'abord créé en brouillon</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Erreurs détectées</h3>
                                <ul class="mt-1 text-sm text-red-700 list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Boutons d'action -->
                <div class="flex justify-end space-x-3 pt-6 border-t">
                    <a href="{{ route('report-cards.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg font-medium">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                        Générer le Bulletin
                    </button>
                </div>
            </form>
        </div>

        <!-- Aide contextuelle -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-800 mb-2">Aide</h3>
            <div class="text-sm text-gray-600 space-y-1">
                <p><strong>Étudiant :</strong> Sélectionnez l'élève pour lequel vous voulez générer le bulletin.</p>
                <p><strong>Période :</strong> Choisissez la période d'évaluation (trimestre, semestre).</p>
                <p><strong>Classe :</strong> Si non spécifiée, la classe actuelle de l'étudiant sera utilisée.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-détecter la classe quand un étudiant est sélectionné
document.getElementById('student_id').addEventListener('change', function() {
    const studentId = this.value;
    if (studentId) {
        // Ici on pourrait faire un appel AJAX pour récupérer la classe de l'étudiant
        // Pour simplifier, on laisse le champ classe tel quel
    }
});

// Validation côté client
document.querySelector('form').addEventListener('submit', function(e) {
    const studentId = document.getElementById('student_id').value;
    const periodId = document.getElementById('grade_period_id').value;
    
    if (!studentId || !periodId) {
        e.preventDefault();
        alert('Veuillez sélectionner un étudiant et une période.');
        return;
    }
    
    // Confirmation de génération
    if (!confirm('Générer le bulletin pour cet étudiant ? Cette action calculera automatiquement toutes les moyennes.')) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection