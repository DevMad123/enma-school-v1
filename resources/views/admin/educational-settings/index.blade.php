@extends('layouts.admin')

@section('title', 'Configuration Éducative')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête avec sélecteurs -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        <i class="fas fa-cogs text-blue-600 mr-2"></i>
                        Configuration Éducative
                    </h1>
                    
                    <div class="relative inline-block text-left" x-data="{ open: false }">
                        <button type="button" 
                                class="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-blue-700 bg-white border border-blue-300 rounded-md hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                @click="open = !open">
                            <i class="fas fa-download mr-2"></i> Exporter
                            <svg class="-mr-1 ml-2 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="open" 
                             @click.away="open = false"
                             class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100">
                            <div class="py-1">
                                <a href="{{ route('admin.educational-settings.export', ['school_type' => $schoolType, 'school_id' => $school?->id]) }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Configuration JSON</a>
                                <a href="{{ route('admin.educational-settings.report', ['school_id' => $school?->id]) }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Rapport PDF</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sélecteurs de contexte -->
    <div class="mb-6">
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4">
                <form method="GET" action="{{ route('admin.educational-settings.index') }}" id="contextForm">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Type d'école -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type d'établissement</label>
                            <select name="school_type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                    onchange="document.getElementById('contextForm').submit()">
                                <option value="preuniversity" {{ $schoolType === 'preuniversity' ? 'selected' : '' }}>
                                    Préuniversitaire
                                </option>
                                <option value="university" {{ $schoolType === 'university' ? 'selected' : '' }}>
                                    Universitaire
                                </option>
                            </select>
                        </div>

                        <!-- École spécifique -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">École (optionnel)</label>
                            <select name="school_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                    onchange="document.getElementById('contextForm').submit()">
                                <option value="">Configuration globale</option>
                                @foreach($schools as $schoolItem)
                                    <option value="{{ $schoolItem->id }}" {{ $school?->id == $schoolItem->id ? 'selected' : '' }}>
                                        {{ $schoolItem->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Catégorie -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
                            <select name="category" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                    onchange="document.getElementById('contextForm').submit()">
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Actions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                            <div class="flex space-x-2">
                                <button type="button" 
                                        class="px-3 py-2 text-sm bg-yellow-500 text-white rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500" 
                                        onclick="resetToDefaults()">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                                <button type="button" 
                                        class="px-3 py-2 text-sm bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                        onclick="openImportModal()">
                                    <i class="fas fa-upload mr-1"></i> Importer
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700" onclick="this.parentElement.parentElement.remove()">
                    <svg class="fill-current h-6 w-6" role="button" viewBox="0 0 20 20">
                        <path d="M14.348 14.849a1 1 0 01-1.414 0L10 11.914l-2.934 2.935a1 1 0 11-1.414-1.414L8.586 10 5.652 7.066a1 1 0 111.414-1.414L10 8.586l2.934-2.934a1 1 0 111.414 1.414L11.414 10l2.935 2.934a1 1 0 010 1.415z"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-red-700" onclick="this.parentElement.parentElement.remove()">
                    <svg class="fill-current h-6 w-6" role="button" viewBox="0 0 20 20">
                        <path d="M14.348 14.849a1 1 0 01-1.414 0L10 11.914l-2.934 2.935a1 1 0 11-1.414-1.414L8.586 10 5.652 7.066a1 1 0 111.414-1.414L10 8.586l2.934-2.934a1 1 0 111.414 1.414L11.414 10l2.935 2.934a1 1 0 010 1.415z"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Configuration -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        Paramètres - {{ $categories[$category] }}
                        @if($school)
                            <span class="text-sm text-gray-500 ml-2">pour {{ $school->name }}</span>
                        @else
                            <span class="text-sm text-gray-500 ml-2">(Configuration globale)</span>
                        @endif
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.educational-settings.update') }}" id="settingsForm">
                        @csrf
                        
                        <input type="hidden" name="school_id" value="{{ $school?->id }}">
                        <input type="hidden" name="school_type" value="{{ $schoolType }}">
                        <input type="hidden" name="category" value="{{ $category }}">

                        <div class="space-y-6">
                            @foreach($currentSettings as $key => $value)
                                <div class="setting-group" data-key="{{ $key }}">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ ucfirst(str_replace('_', ' ', $key)) }}
                                        @if(isset($validationRules[$key]) && $validationRules[$key]['required'] ?? false)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    
                                    @if(is_array($value))
                                        <div class="json-editor" data-key="{{ $key }}">
                                            <textarea 
                                                name="settings[{{ $key }}]" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono text-sm json-input"
                                                rows="8"
                                                placeholder="Configuration JSON..."
                                            >{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Format JSON. Utilisez la prévisualisation pour valider.
                                            </p>
                                        </div>
                                    @else
                                        <input 
                                            type="text" 
                                            name="settings[{{ $key }}]" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                            value="{{ $value }}"
                                            @if(isset($validationRules[$key]))
                                                @if($validationRules[$key]['required'] ?? false) required @endif
                                                @if(isset($validationRules[$key]['min'])) min="{{ $validationRules[$key]['min'] }}" @endif
                                                @if(isset($validationRules[$key]['max'])) max="{{ $validationRules[$key]['max'] }}" @endif
                                            @endif
                                        >
                                    @endif

                                    @error("settings.{$key}")
                                        <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        <div class="flex justify-between items-center pt-6 border-t border-gray-200 mt-6">
                            <button type="button" 
                                    class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500" 
                                    onclick="previewChanges()">
                                <i class="fas fa-eye mr-1"></i> Prévisualiser
                            </button>
                            
                            <div class="flex space-x-3">
                                <button type="button" 
                                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500" 
                                        onclick="loadDefaults()">
                                    Charger les défauts
                                </button>
                                <button type="submit" 
                                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-save mr-1"></i> Sauvegarder
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panneau d'aide -->
        <div class="space-y-6">
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Valeurs par défaut
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="space-y-4">
                        @foreach($defaultSettings as $key => $value)
                            <div>
                                <h4 class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $key)) }}:</h4>
                                <pre class="mt-1 text-xs bg-gray-100 p-2 rounded overflow-x-auto">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $value }}</pre>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Documentation -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-book text-green-500 mr-2"></i>
                        Documentation
                    </h3>
                </div>
                <div class="px-6 py-4">
                    <div class="prose prose-sm">
                        @switch($category)
                            @case('evaluation')
                                <p class="text-gray-700"><strong>Seuils d'évaluation :</strong> Définissent les mentions selon les moyennes.</p>
                                <ul class="text-sm text-gray-600">
                                    <li>excellent : ≥ 16/20</li>
                                    <li>tres_bien : ≥ 14/20</li>
                                    <li>bien : ≥ 12/20</li>
                                    <li>assez_bien : ≥ 10/20</li>
                                </ul>
                                @break
                            
                            @case('lmd')
                                <p class="text-gray-700"><strong>Standards LMD :</strong> Conformité au système européen.</p>
                                <ul class="text-sm text-gray-600">
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
<div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" x-data="{ show: false }" x-show="show" @open-import-modal.window="show = true">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Importer une configuration</h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('admin.educational-settings.import') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="school_id" value="{{ $school?->id }}">
                <input type="hidden" name="school_type" value="{{ $schoolType }}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fichier de configuration (JSON)</label>
                    <input type="file" 
                           name="settings_file" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                           accept=".json" required>
                    <p class="mt-1 text-sm text-gray-500">
                        Sélectionnez un fichier JSON exporté précédemment.
                    </p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="show = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">Importer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de prévisualisation -->
<div id="previewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden" x-data="{ show: false }" x-show="show">
    <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Prévisualisation des changements</h3>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="previewContent" class="prose prose-sm max-w-none">
                <!-- Contenu généré par JavaScript -->
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="show = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Fermer</button>
                <button type="button" onclick="document.getElementById('settingsForm').submit()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Confirmer et sauvegarder
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function openImportModal() {
    window.dispatchEvent(new CustomEvent('open-import-modal'));
}

function openPreviewModal() {
    document.querySelector('[x-data*="previewModal"]').__x.show = true;
}

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
        let content = '<h4 class="font-medium text-gray-900 mb-2">Modules affectés :</h4><ul class="list-disc pl-5 mb-4">';
        data.affected_features.forEach(feature => {
            content += `<li>${feature}</li>`;
        });
        content += '</ul>';
        
        if (data.recommendations.length > 0) {
            content += '<h4 class="font-medium text-gray-900 mb-2">Recommandations :</h4><ul class="list-disc pl-5">';
            data.recommendations.forEach(rec => {
                content += `<li class="text-yellow-600">${rec}</li>`;
            });
            content += '</ul>';
        }
        
        document.getElementById('previewContent').innerHTML = content;
        openPreviewModal();
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la prévisualisation');
    });
}

// Validation JSON en temps réel
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.json-input').forEach(input => {
        input.addEventListener('blur', function() {
            try {
                JSON.parse(this.value);
                this.classList.remove('border-red-300', 'ring-red-500');
                this.classList.add('border-green-300', 'ring-green-500');
            } catch (e) {
                this.classList.remove('border-green-300', 'ring-green-500');
                this.classList.add('border-red-300', 'ring-red-500');
            }
        });
    });
});
</script>
@endpush