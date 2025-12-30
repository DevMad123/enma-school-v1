@extends('layouts.app')

@section('title', 'Bulletin de ' . $reportCard->student->full_name)

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- En-tête -->
    <div class="flex justify-between items-start mb-6">
        <div>
            <a href="{{ route('report-cards.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                ← Retour aux bulletins
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">
                Bulletin de {{ $reportCard->student->full_name }}
            </h1>
            <p class="text-gray-600 mt-1">
                {{ $reportCard->gradePeriod->name }} - {{ $reportCard->academicYear->name }}
            </p>
        </div>
        
        <div class="flex space-x-3">
            @if($reportCard->general_average !== null)
                <a href="{{ route('report-cards.pdf', $reportCard) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                    Télécharger PDF
                </a>
            @endif

            @if(!$reportCard->is_final)
                <div class="flex space-x-2">
                    <form method="POST" action="{{ route('report-cards.recalculate', $reportCard) }}" class="inline">
                        @csrf
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                            Recalculer
                        </button>
                    </form>

                    @if($reportCard->status === 'draft' && $reportCard->general_average !== null)
                        <form method="POST" action="{{ route('report-cards.publish', $reportCard) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium">
                                Publier
                            </button>
                        </form>
                    @endif

                    @if($reportCard->status === 'published')
                        <form method="POST" action="{{ route('report-cards.finalize', $reportCard) }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium"
                                    onclick="return confirm('Finaliser le bulletin le rendra non modifiable. Continuer ?')">
                                Finaliser
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('report-cards.edit', $reportCard) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium">
                        Modifier
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Informations générales -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Informations générales</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Étudiant</label>
                        <p class="text-gray-900 font-medium">{{ $reportCard->student->full_name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Classe</label>
                        <p class="text-gray-900">{{ $reportCard->schoolClass->name }} ({{ $reportCard->schoolClass->level->name }})</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Période</label>
                        <p class="text-gray-900">{{ $reportCard->gradePeriod->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Année académique</label>
                        <p class="text-gray-900">{{ $reportCard->academicYear->name }}</p>
                    </div>
                </div>
            </div>

            <!-- Résultats par matière -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Résultats par matière</h2>
                
                @if($subjectAverages->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Matière</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Coefficient</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Moyenne</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Nb Notes</th>
                                    <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Appréciation</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($subjectAverages as $subjectData)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-900">
                                            {{ $subjectData['subject']->name }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ $subjectData['coefficient'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium 
                                                {{ $subjectData['is_passed'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ number_format($subjectData['average'], 2) }}/20
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ $subjectData['grades_count'] }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($subjectData['average'] >= 16)
                                                <span class="text-green-600 font-medium">Excellent</span>
                                            @elseif($subjectData['average'] >= 14)
                                                <span class="text-blue-600 font-medium">Très bien</span>
                                            @elseif($subjectData['average'] >= 12)
                                                <span class="text-yellow-600 font-medium">Bien</span>
                                            @elseif($subjectData['average'] >= 10)
                                                <span class="text-orange-600 font-medium">Passable</span>
                                            @else
                                                <span class="text-red-600 font-medium">Insuffisant</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune note trouvée</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Aucune évaluation n'a été enregistrée pour cette période.
                        </p>
                    </div>
                @endif
            </div>

            @if($reportCard->observations)
                <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Observations</h2>
                    <p class="text-gray-700 whitespace-pre-line">{{ $reportCard->observations }}</p>
                </div>
            @endif
        </div>

        <!-- Résumé et statistiques -->
        <div class="space-y-6">
            <!-- Résultats généraux -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Résultats généraux</h2>
                
                <div class="space-y-4">
                    <div class="text-center">
                        @if($reportCard->general_average !== null)
                            <div class="text-3xl font-bold {{ $reportCard->general_average >= 10 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($reportCard->general_average, 2) }}/20
                            </div>
                            <p class="text-sm text-gray-600">Moyenne générale</p>
                        @else
                            <div class="text-3xl font-bold text-gray-400">--/20</div>
                            <p class="text-sm text-gray-600">Non calculé</p>
                        @endif
                    </div>

                    @if($reportCard->class_rank && $reportCard->total_students_in_class)
                        <div class="text-center border-t pt-4">
                            <div class="text-xl font-semibold text-gray-900">
                                {{ $reportCard->class_rank }}{{ $reportCard->class_rank == 1 ? 'er' : 'ème' }}
                            </div>
                            <p class="text-sm text-gray-600">sur {{ $reportCard->total_students_in_class }} élèves</p>
                        </div>
                    @endif

                    @if($reportCard->general_average !== null)
                        <div class="border-t pt-4">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Mention :</span>
                                <span class="font-semibold 
                                    @if($reportCard->general_average >= 16) text-green-600
                                    @elseif($reportCard->general_average >= 14) text-blue-600  
                                    @elseif($reportCard->general_average >= 12) text-yellow-600
                                    @elseif($reportCard->general_average >= 10) text-orange-600
                                    @else text-red-600
                                    @endif">
                                    {{ $reportCard->mention }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm font-medium text-gray-700">Décision :</span>
                                <span class="font-semibold {{ $reportCard->decision === 'admis' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ ucfirst($reportCard->decision) }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Taux de réussite :</span>
                                <span class="font-medium">{{ $reportCard->passing_rate }}%</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Statistiques</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Matières évaluées :</span>
                        <span class="font-medium">{{ $reportCard->total_subjects }}</span>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Matières réussies :</span>
                        <span class="font-medium text-green-600">{{ $reportCard->subjects_passed }}</span>
                    </div>

                    @if($reportCard->attendance_rate !== null)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Assiduité :</span>
                            <span class="font-medium">{{ $reportCard->attendance_rate }}%</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statut -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Statut du bulletin</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Statut :</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @switch($reportCard->status)
                                @case('draft') bg-gray-100 text-gray-800 @break
                                @case('published') bg-blue-100 text-blue-800 @break
                                @case('archived') bg-yellow-100 text-yellow-800 @break
                            @endswitch">
                            {{ ucfirst($reportCard->status) }}
                            @if($reportCard->is_final)
                                <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/>
                                </svg>
                            @endif
                        </span>
                    </div>

                    @if($reportCard->generated_at)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Généré le :</span>
                            <span class="text-sm">{{ $reportCard->generated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    @endif

                    @if($reportCard->generatedBy)
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Généré par :</span>
                            <span class="text-sm">{{ $reportCard->generatedBy->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection