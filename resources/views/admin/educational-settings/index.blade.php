@extends('layouts.admin')

@section('title', 'Configuration Éducative')

@section('content')
<div class="container-fluid">
    <!-- En-tête avec sélecteurs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">
                            <i class="fas fa-cogs text-primary"></i>
                            Configuration Éducative
                        </h1>
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download"></i> Exporter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('admin.educational-settings.export', ['school_type' => $schoolType, 'school_id' => $school?->id]) }}">Configuration JSON</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.educational-settings.report', ['school_id' => $school?->id]) }}">Rapport PDF</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sélecteurs de contexte -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.educational-settings.index') }}" id="contextForm">
                        <div class="row g-3">
                            <!-- Type d'école -->
                            <div class="col-md-3">
                                <label class="form-label">Type d'établissement</label>
                                <select name="school_type" class="form-select" onchange="document.getElementById('contextForm').submit()">
                                    <option value="preuniversity" {{ $schoolType === 'preuniversity' ? 'selected' : '' }}>
                                        Préuniversitaire
                                    </option>
                                    <option value="university" {{ $schoolType === 'university' ? 'selected' : '' }}>
                                        Universitaire
                                    </option>
                                </select>
                            </div>

                            <!-- École spécifique -->
                            <div class="col-md-3">
                                <label class="form-label">École (optionnel)</label>
                                <select name="school_id" class="form-select" onchange="document.getElementById('contextForm').submit()">
                                    <option value="">Configuration globale</option>
                                    @foreach($schools as $schoolItem)
                                        <option value="{{ $schoolItem->id }}" {{ $school?->id == $schoolItem->id ? 'selected' : '' }}>
                                            {{ $schoolItem->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Catégorie -->
                            <div class="col-md-3">
                                <label class="form-label">Catégorie</label>
                                <select name="category" class="form-select" onchange="document.getElementById('contextForm').submit()">
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Actions -->
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="resetToDefaults()">
                                        <i class="fas fa-undo"></i> Reset
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal">
                                        <i class="fas fa-upload"></i> Importer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Configuration -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Paramètres - {{ $categories[$category] }}
                        @if($school)
                            <small class="text-muted">pour {{ $school->name }}</small>
                        @else
                            <small class="text-muted">(Configuration globale)</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.educational-settings.update') }}" id="settingsForm">
                        @csrf
                        
                        <input type="hidden" name="school_id" value="{{ $school?->id }}">
                        <input type="hidden" name="school_type" value="{{ $schoolType }}">
                        <input type="hidden" name="category" value="{{ $category }}">

                        <div class="settings-container">
                            @foreach($currentSettings as $key => $value)
                                <div class="setting-group mb-4" data-key="{{ $key }}">
                                    <label class="form-label fw-bold">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                                        @if(isset($validationRules[$key]) && $validationRules[$key]['required'] ?? false)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    
                                    @if(is_array($value))
                                        <div class="json-editor" data-key="{{ $key }}">
                                            <textarea 
                                                name="settings[{{ $key }}]" 
                                                class="form-control json-input"
                                                rows="8"
                                                placeholder="Configuration JSON..."
                                            >{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                                            <small class="form-text text-muted">
                                                Format JSON. Utilisez la prévisualisation pour valider.
                                            </small>
                                        </div>
                                    @else
                                        <input 
                                            type="text" 
                                            name="settings[{{ $key }}]" 
                                            class="form-control"
                                            value="{{ $value }}"
                                            @if(isset($validationRules[$key]))
                                                @if($validationRules[$key]['required'] ?? false) required @endif
                                                @if(isset($validationRules[$key]['min'])) min="{{ $validationRules[$key]['min'] }}" @endif
                                                @if(isset($validationRules[$key]['max'])) max="{{ $validationRules[$key]['max'] }}" @endif
                                            @endif
                                        >
                                    @endif

                                    @error("settings.{$key}")
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="previewChanges()">
                                <i class="fas fa-eye"></i> Prévisualiser
                            </button>
                            
                            <div>
                                <button type="button" class="btn btn-secondary me-2" onclick="loadDefaults()">
                                    Charger les défauts
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Sauvegarder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panneau d'aide -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Valeurs par défaut
                    </h6>
                </div>
                <div class="card-body">
                    <div class="default-settings">
                        @foreach($defaultSettings as $key => $value)
                            <div class="mb-3">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                <pre class="small mt-1 bg-light p-2 rounded">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value }}</pre>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Documentation -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-book"></i> Documentation
                    </h6>
                </div>
                <div class="card-body">
                    <div class="documentation">
                        @switch($category)
                            @case('evaluation')
                                <p><strong>Seuils d'évaluation :</strong> Définissent les mentions selon les moyennes.</p>
                                <ul class="small">
                                    <li>excellent : ≥ 16/20</li>
                                    <li>tres_bien : ≥ 14/20</li>
                                    <li>bien : ≥ 12/20</li>
                                    <li>assez_bien : ≥ 10/20</li>
                                </ul>
                                @break
                            
                            @case('lmd')
                                <p><strong>Standards LMD :</strong> Conformité au système européen.</p>
                                <ul class="small">
                                    <li>Licence : 180 crédits (6 semestres)</li>
                                    <li>Master : 120 crédits (4 semestres)</li>
                                    <li>Doctorat : 180 crédits minimum</li>
                                </ul>
                                @break
                        @endswitch
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal d'import -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importer une configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.educational-settings.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="school_id" value="{{ $school?->id }}">
                    <input type="hidden" name="school_type" value="{{ $schoolType }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Fichier de configuration (JSON)</label>
                        <input type="file" name="settings_file" class="form-control" accept=".json" required>
                        <small class="form-text text-muted">
                            Sélectionnez un fichier JSON exporté précédemment.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Importer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de prévisualisation -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prévisualisation des changements</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <!-- Contenu généré par JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="document.getElementById('settingsForm').submit()">
                    Confirmer et sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetToDefaults() {
    if (confirm('Êtes-vous sûr de vouloir remettre aux valeurs par défaut ? Cela effacera toute personnalisation.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.educational-settings.reset") }}';
        
        // CSRF token
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        // Paramètres
        const params = {
            school_id: '{{ $school?->id }}',
            school_type: '{{ $schoolType }}',
            category: '{{ $category }}'
        };
        
        Object.keys(params).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = params[key];
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

function loadDefaults() {
    const defaultSettings = @json($defaultSettings);
    
    Object.keys(defaultSettings).forEach(key => {
        const input = document.querySelector(`[name="settings[${key}]"]`);
        if (input) {
            const value = defaultSettings[key];
            if (typeof value === 'object') {
                input.value = JSON.stringify(value, null, 2);
            } else {
                input.value = value;
            }
        }
    });
}

function previewChanges() {
    const formData = new FormData(document.getElementById('settingsForm'));
    const settings = {};
    
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('settings[')) {
            const settingKey = key.match(/settings\[(.+)\]/)[1];
            try {
                settings[settingKey] = JSON.parse(value);
            } catch (e) {
                settings[settingKey] = value;
            }
        }
    }
    
    fetch('{{ route("admin.educational-settings.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            settings: settings,
            school_type: '{{ $schoolType }}',
            category: '{{ $category }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        let content = '<h6>Modules affectés :</h6><ul>';
        data.affected_features.forEach(feature => {
            content += `<li>${feature}</li>`;
        });
        content += '</ul>';
        
        if (data.recommendations.length > 0) {
            content += '<h6>Recommandations :</h6><ul>';
            data.recommendations.forEach(rec => {
                content += `<li class="text-warning">${rec}</li>`;
            });
            content += '</ul>';
        }
        
        document.getElementById('previewContent').innerHTML = content;
        new bootstrap.Modal(document.getElementById('previewModal')).show();
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la prévisualisation');
    });
}

// Validation JSON en temps réel
document.querySelectorAll('.json-input').forEach(input => {
    input.addEventListener('blur', function() {
        try {
            JSON.parse(this.value);
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } catch (e) {
            this.classList.remove('is-valid');
            this.classList.add('is-invalid');
        }
    });
});
</script>
@endpush