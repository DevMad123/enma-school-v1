@extends('layouts.university')

@section('title', 'Ajouter un enseignant')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-2"></i>
                        Ajouter un enseignant universitaire
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('university.teachers.index') }}" class="btn btn-default btn-sm">
                            <i class="fas fa-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('university.teachers.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <!-- Informations personnelles -->
                            <div class="col-md-6">
                                <h4 class="text-primary">
                                    <i class="fas fa-user mr-2"></i>
                                    Informations personnelles
                                </h4>
                                
                                <div class="form-group">
                                    <label for="first_name">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="first_name" 
                                           id="first_name" 
                                           class="form-control @error('first_name') is-invalid @enderror"
                                           value="{{ old('first_name') }}" 
                                           required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Nom <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="last_name" 
                                           id="last_name" 
                                           class="form-control @error('last_name') is-invalid @enderror"
                                           value="{{ old('last_name') }}" 
                                           required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           name="email" 
                                           id="email" 
                                           class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Téléphone</label>
                                    <input type="text" 
                                           name="phone" 
                                           id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Informations académiques -->
                            <div class="col-md-6">
                                <h4 class="text-primary">
                                    <i class="fas fa-university mr-2"></i>
                                    Informations académiques
                                </h4>

                                <div class="form-group">
                                    <label for="ufr_id">UFR <span class="text-danger">*</span></label>
                                    <select name="ufr_id" 
                                            id="ufr_id" 
                                            class="form-control @error('ufr_id') is-invalid @enderror" 
                                            required>
                                        <option value="">-- Sélectionner une UFR --</option>
                                        @foreach($ufrs as $ufr)
                                            <option value="{{ $ufr->id }}" {{ old('ufr_id') == $ufr->id ? 'selected' : '' }}>
                                                {{ $ufr->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ufr_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="department_id">Département</label>
                                    <select name="department_id" 
                                            id="department_id" 
                                            class="form-control @error('department_id') is-invalid @enderror">
                                        <option value="">-- Sélectionner un département --</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->id }}" 
                                                    data-ufr="{{ $department->ufr_id }}"
                                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                                {{ $department->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('department_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="academic_rank">Rang académique</label>
                                    <select name="academic_rank" 
                                            id="academic_rank" 
                                            class="form-control @error('academic_rank') is-invalid @enderror">
                                        <option value="">-- Sélectionner un rang --</option>
                                        <option value="assistant" {{ old('academic_rank') == 'assistant' ? 'selected' : '' }}>
                                            Assistant
                                        </option>
                                        <option value="maitre_assistant" {{ old('academic_rank') == 'maitre_assistant' ? 'selected' : '' }}>
                                            Maître Assistant
                                        </option>
                                        <option value="maitre_de_conferences" {{ old('academic_rank') == 'maitre_de_conferences' ? 'selected' : '' }}>
                                            Maître de Conférences
                                        </option>
                                        <option value="professeur" {{ old('academic_rank') == 'professeur' ? 'selected' : '' }}>
                                            Professeur
                                        </option>
                                        <option value="professeur_titulaire" {{ old('academic_rank') == 'professeur_titulaire' ? 'selected' : '' }}>
                                            Professeur Titulaire
                                        </option>
                                    </select>
                                    @error('academic_rank')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="specialization">Spécialisation</label>
                                    <input type="text" 
                                           name="specialization" 
                                           id="specialization" 
                                           class="form-control @error('specialization') is-invalid @enderror"
                                           value="{{ old('specialization') }}">
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Informations professionnelles -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="text-primary">
                                    <i class="fas fa-briefcase mr-2"></i>
                                    Informations professionnelles
                                </h4>

                                <div class="form-group">
                                    <label for="employee_id">Numéro d'employé</label>
                                    <input type="text" 
                                           name="employee_id" 
                                           id="employee_id" 
                                           class="form-control @error('employee_id') is-invalid @enderror"
                                           value="{{ old('employee_id') }}">
                                    @error('employee_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="hire_date">Date d'embauche</label>
                                    <input type="date" 
                                           name="hire_date" 
                                           id="hire_date" 
                                           class="form-control @error('hire_date') is-invalid @enderror"
                                           value="{{ old('hire_date') }}">
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="office_location">Bureau</label>
                                    <input type="text" 
                                           name="office_location" 
                                           id="office_location" 
                                           class="form-control @error('office_location') is-invalid @enderror"
                                           value="{{ old('office_location') }}">
                                    @error('office_location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="salary">Salaire</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               name="salary" 
                                               id="salary" 
                                               class="form-control @error('salary') is-invalid @enderror"
                                               value="{{ old('salary') }}" 
                                               step="0.01">
                                        <div class="input-group-append">
                                            <span class="input-group-text">FCFA</span>
                                        </div>
                                    </div>
                                    @error('salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h4 class="text-primary">
                                    <i class="fas fa-cog mr-2"></i>
                                    Informations complémentaires
                                </h4>

                                <div class="form-group">
                                    <label for="research_interests">Intérêts de recherche</label>
                                    <textarea name="research_interests" 
                                              id="research_interests" 
                                              class="form-control @error('research_interests') is-invalid @enderror"
                                              rows="4">{{ old('research_interests') }}</textarea>
                                    @error('research_interests')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="qualifications">Qualifications</label>
                                    <textarea name="qualifications" 
                                              id="qualifications" 
                                              class="form-control @error('qualifications') is-invalid @enderror"
                                              rows="4">{{ old('qualifications') }}</textarea>
                                    @error('qualifications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Statut</label>
                                    <select name="status" 
                                            id="status" 
                                            class="form-control @error('status') is-invalid @enderror">
                                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
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
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Enregistrer
                        </button>
                        <a href="{{ route('university.teachers.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-2"></i>
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Filtrer les départements selon l'UFR sélectionnée
    $('#ufr_id').on('change', function() {
        const ufrId = $(this).val();
        const departmentSelect = $('#department_id');
        
        departmentSelect.find('option:not(:first)').hide();
        
        if (ufrId) {
            departmentSelect.find('option[data-ufr="' + ufrId + '"]').show();
        } else {
            departmentSelect.find('option:not(:first)').show();
        }
        
        departmentSelect.val('');
    });
    
    // Déclencher le filtrage au chargement si une UFR est déjà sélectionnée
    $('#ufr_id').trigger('change');
});
</script>
@endpush
@endsection