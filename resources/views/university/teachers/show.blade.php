@extends('layouts.university')

@section('title', 'Détails de l\'enseignant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        {{ $teacher->first_name }} {{ $teacher->last_name }}
                        <span class="badge badge-{{ $teacher->status == 'active' ? 'success' : ($teacher->status == 'inactive' ? 'warning' : 'secondary') }} ml-2">
                            {{ ucfirst($teacher->status) }}
                        </span>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('university.teachers.edit', $teacher) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        <a href="{{ route('university.teachers.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Informations personnelles -->
                        <div class="col-md-6">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-user mr-2"></i>
                                        Informations personnelles
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Nom complet</strong></td>
                                            <td>{{ $teacher->first_name }} {{ $teacher->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td>
                                                <a href="mailto:{{ $teacher->user->email }}">
                                                    {{ $teacher->user->email }}
                                                </a>
                                            </td>
                                        </tr>
                                        @if($teacher->phone)
                                        <tr>
                                            <td><strong>Téléphone</strong></td>
                                            <td>
                                                <a href="tel:{{ $teacher->phone }}">
                                                    {{ $teacher->phone }}
                                                </a>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Statut</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $teacher->status == 'active' ? 'success' : ($teacher->status == 'inactive' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($teacher->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Créé le</strong></td>
                                            <td>{{ $teacher->created_at->format('d/m/Y à H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Modifié le</strong></td>
                                            <td>{{ $teacher->updated_at->format('d/m/Y à H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Informations académiques -->
                        <div class="col-md-6">
                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-university mr-2"></i>
                                        Informations académiques
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        @if($teacher->ufr)
                                        <tr>
                                            <td><strong>UFR</strong></td>
                                            <td>{{ $teacher->ufr->name }}</td>
                                        </tr>
                                        @endif
                                        @if($teacher->department)
                                        <tr>
                                            <td><strong>Département</strong></td>
                                            <td>{{ $teacher->department->name }}</td>
                                        </tr>
                                        @endif
                                        @if($teacher->academic_rank)
                                        <tr>
                                            <td><strong>Rang académique</strong></td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    @switch($teacher->academic_rank)
                                                        @case('assistant')
                                                            Assistant
                                                            @break
                                                        @case('maitre_assistant')
                                                            Maître Assistant
                                                            @break
                                                        @case('maitre_de_conferences')
                                                            Maître de Conférences
                                                            @break
                                                        @case('professeur')
                                                            Professeur
                                                            @break
                                                        @case('professeur_titulaire')
                                                            Professeur Titulaire
                                                            @break
                                                        @default
                                                            {{ $teacher->academic_rank }}
                                                    @endswitch
                                                </span>
                                            </td>
                                        </tr>
                                        @endif
                                        @if($teacher->specialization)
                                        <tr>
                                            <td><strong>Spécialisation</strong></td>
                                            <td>{{ $teacher->specialization }}</td>
                                        </tr>
                                        @endif
                                        @if($teacher->research_interests)
                                        <tr>
                                            <td><strong>Recherche</strong></td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ Str::limit($teacher->research_interests, 100) }}
                                                </small>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations professionnelles -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-briefcase mr-2"></i>
                                        Informations professionnelles
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        @if($teacher->employee_id)
                                        <tr>
                                            <td><strong>N° employé</strong></td>
                                            <td><code>{{ $teacher->employee_id }}</code></td>
                                        </tr>
                                        @endif
                                        @if($teacher->hire_date)
                                        <tr>
                                            <td><strong>Date d'embauche</strong></td>
                                            <td>{{ $teacher->hire_date->format('d/m/Y') }}</td>
                                        </tr>
                                        @endif
                                        @if($teacher->office_location)
                                        <tr>
                                            <td><strong>Bureau</strong></td>
                                            <td>{{ $teacher->office_location }}</td>
                                        </tr>
                                        @endif
                                        @if($teacher->salary)
                                        <tr>
                                            <td><strong>Salaire</strong></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    {{ number_format($teacher->salary, 0, ',', ' ') }} FCFA
                                                </span>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Programmes enseignés -->
                        <div class="col-md-6">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chalkboard-teacher mr-2"></i>
                                        Programmes
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if($teacher->programs && $teacher->programs->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($teacher->programs as $program)
                                                <div class="list-group-item p-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>{{ $program->name }}</span>
                                                        <small class="text-muted">{{ $program->code }}</small>
                                                    </div>
                                                    @if($program->level)
                                                        <small class="text-info">{{ $program->level->name }}</small>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            Aucun programme assigné
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Qualifications et recherche -->
                    @if($teacher->qualifications || $teacher->research_interests)
                    <div class="row">
                        @if($teacher->qualifications)
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-graduation-cap mr-2"></i>
                                        Qualifications
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="text-sm">
                                        {!! nl2br(e($teacher->qualifications)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($teacher->research_interests)
                        <div class="col-md-6">
                            <div class="card card-outline card-purple">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-flask mr-2"></i>
                                        Intérêts de recherche
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="text-sm">
                                        {!! nl2br(e($teacher->research_interests)) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('university.teachers.edit', $teacher) }}" class="btn btn-warning">
                                <i class="fas fa-edit mr-2"></i>
                                Modifier
                            </a>
                            <button type="button" 
                                    class="btn btn-{{ $teacher->status == 'active' ? 'danger' : 'success' }}"
                                    onclick="toggleStatus({{ $teacher->id }})">
                                <i class="fas fa-{{ $teacher->status == 'active' ? 'pause' : 'play' }} mr-2"></i>
                                {{ $teacher->status == 'active' ? 'Désactiver' : 'Activer' }}
                            </button>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" 
                                    class="btn btn-danger"
                                    onclick="deleteTeacher({{ $teacher->id }})">
                                <i class="fas fa-trash mr-2"></i>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Confirmer la suppression</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cet enseignant ?</p>
                <p class="text-danger">
                    <strong>Cette action est irréversible !</strong>
                </p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash mr-2"></i>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(teacherId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cet enseignant ?')) {
        fetch(`/university/teachers/${teacherId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors du changement de statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du changement de statut');
        });
    }
}

function deleteTeacher(teacherId) {
    const form = document.getElementById('deleteForm');
    form.action = `/university/teachers/${teacherId}`;
    $('#deleteModal').modal('show');
}
</script>
@endpush
@endsection