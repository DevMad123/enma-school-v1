<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SchoolController extends Controller
{
    public function __construct()
    {
        // Restriction d'accès aux admins/directeurs/super_admin
        $this->middleware(['auth', 'role:super_admin|admin|directeur']);
    }

    /**
     * Affiche les informations de l'établissement
     */
    public function index()
    {
        $school = School::getActiveSchool();
        
        if (!$school) {
            // Rediriger vers la création si aucune école n'existe
            return redirect()->route('admin.schools.create')
                ->with('info', 'Veuillez configurer votre établissement.');
        }

        return view('admin.schools.index', compact('school'));
    }

    /**
     * Formulaire de création d'établissement
     */
    public function create()
    {
        // Vérifier s'il y a déjà une école active
        if (School::getActiveSchool()) {
            return redirect()->route('admin.schools.index')
                ->with('warning', 'Un établissement existe déjà.');
        }

        return view('admin.schools.create');
    }

    /**
     * Enregistrer un nouvel établissement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'type' => ['required', Rule::in(['pre_university', 'university'])],
            'educational_levels' => 'nullable|array',
            'educational_levels.*' => Rule::in(['primary', 'secondary', 'technical']),
            'email' => 'required|email|unique:schools,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'academic_system' => ['required', Rule::in(['trimestre', 'semestre'])],
            'grading_system' => ['required', Rule::in(['20', '100', 'custom'])],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stamp' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validation conditionnelle selon le type
        if ($validated['type'] === 'pre_university') {
            // Pour pré-universitaire, au moins un niveau éducatif requis
            $request->validate([
                'educational_levels' => 'required|array|min:1',
            ], [
                'educational_levels.required' => 'Veuillez sélectionner au moins un niveau éducatif.',
                'educational_levels.min' => 'Veuillez sélectionner au moins un niveau éducatif.',
            ]);
        } else {
            // Pour universitaire, pas de niveaux éducatifs spécifiques
            $validated['educational_levels'] = null;
        }

        // Gérer l'upload des fichiers
        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('schools/logos', 'public');
        }
        
        if ($request->hasFile('stamp')) {
            $validated['stamp_path'] = $request->file('stamp')->store('schools/stamps', 'public');
        }
        
        if ($request->hasFile('signature')) {
            $validated['signature_path'] = $request->file('signature')->store('schools/signatures', 'public');
        }

        // Désactiver toute autre école (par sécurité)
        School::query()->update(['is_active' => false]);

        $school = School::create($validated);

        return redirect()->route('admin.schools.index')
            ->with('success', 'Établissement créé avec succès.');
    }

    /**
     * Formulaire d'édition d'établissement
     */
    public function edit(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    /**
     * Mettre à jour l'établissement
     */
    public function update(Request $request, School $school)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'type' => ['required', Rule::in(['pre_university', 'university'])],
            'educational_levels' => 'nullable|array',
            'educational_levels.*' => Rule::in(['primary', 'secondary', 'technical']),
            'email' => ['required', 'email', Rule::unique('schools')->ignore($school->id)],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'academic_system' => ['required', Rule::in(['trimestre', 'semestre'])],
            'grading_system' => ['required', Rule::in(['20', '100', 'custom'])],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'stamp' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Validation conditionnelle selon le type
        if ($validated['type'] === 'pre_university') {
            // Pour pré-universitaire, au moins un niveau éducatif requis
            $request->validate([
                'educational_levels' => 'required|array|min:1',
            ], [
                'educational_levels.required' => 'Veuillez sélectionner au moins un niveau éducatif.',
                'educational_levels.min' => 'Veuillez sélectionner au moins un niveau éducatif.',
            ]);
        } else {
            // Pour universitaire, pas de niveaux éducatifs spécifiques
            $validated['educational_levels'] = null;
        }

        // Gérer l'upload des nouveaux fichiers
        if ($request->hasFile('logo')) {
            // Supprimer l'ancien logo
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('schools/logos', 'public');
        }
        
        if ($request->hasFile('stamp')) {
            if ($school->stamp_path) {
                Storage::disk('public')->delete($school->stamp_path);
            }
            $validated['stamp_path'] = $request->file('stamp')->store('schools/stamps', 'public');
        }
        
        if ($request->hasFile('signature')) {
            if ($school->signature_path) {
                Storage::disk('public')->delete($school->signature_path);
            }
            $validated['signature_path'] = $request->file('signature')->store('schools/signatures', 'public');
        }

        $school->update($validated);

        return redirect()->route('admin.schools.index')
            ->with('success', 'Établissement mis à jour avec succès.');
    }

    /**
     * Gestion des paramètres de l'école
     */
    public function settings(School $school)
    {
        $settings = $school->settings()->get()->keyBy('key');
        
        return view('admin.schools.settings', compact('school', 'settings'));
    }

    /**
     * Mettre à jour les paramètres
     */
    public function updateSettings(Request $request, School $school)
    {
        $validated = $request->validate([
            'settings' => 'array',
            'settings.*' => 'nullable|string',
        ]);

        foreach ($validated['settings'] ?? [] as $key => $value) {
            $school->setSetting($key, $value);
        }

        return redirect()->back()
            ->with('success', 'Paramètres mis à jour avec succès.');
    }
}
