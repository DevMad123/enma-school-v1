<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Level;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PedagogicalSettingsController extends Controller
{
    public function __construct()
    {
        // Restriction d'accès aux admins/directeurs/super_admin
        $this->middleware(['auth', 'role:super_admin|admin|directeur']);
    }

    /**
     * Affiche la page des paramètres pédagogiques
     */
    public function index()
    {
        $school = School::getActiveSchool();
        
        if (!$school) {
            return redirect()->route('admin.schools.index')
                ->with('error', 'Veuillez d\'abord configurer votre établissement.');
        }

        // Récupérer les paramètres pédagogiques existants
        $pedagogy = [
            // Paramètres globaux
            'validation_threshold' => $school->getSetting('validation_threshold', '10'),
            'redoublement_threshold' => $school->getSetting('redoublement_threshold', '8'),
            'bulletin_footer_text' => $school->getSetting('bulletin_footer_text', ''),
            'automatic_promotion' => $school->getSetting('automatic_promotion', 'false'),
            'mention_system' => $school->getSetting('mention_system', 'true'),
            'validation_subjects_required' => $school->getSetting('validation_subjects_required', '80'),
        ];

        // Niveaux et matières pour configuration spécifique
        $levels = $school->levels()->with('subjects')->get();
        $subjects = $school->subjects()->get();

        return view('admin.pedagogy-settings.index', compact('school', 'pedagogy', 'levels', 'subjects'));
    }

    /**
     * Met à jour les paramètres pédagogiques globaux
     */
    public function updateGlobal(Request $request)
    {
        $validated = $request->validate([
            'grading_system' => ['required', Rule::in(['20', '100', 'custom'])],
            'validation_threshold' => 'required|numeric|min:0|max:100',
            'redoublement_threshold' => 'required|numeric|min:0|max:100',
            'bulletin_footer_text' => 'nullable|string|max:1000',
            'automatic_promotion' => 'boolean',
            'mention_system' => 'boolean',
            'validation_subjects_required' => 'required|numeric|min:0|max:100',
        ]);

        $school = School::getActiveSchool();
        
        if (!$school) {
            return back()->with('error', 'École non trouvée.');
        }

        // Mettre à jour le système de notation dans la table schools
        $school->update(['grading_system' => $validated['grading_system']]);

        // Mettre à jour les paramètres dans school_settings
        $settings = [
            'validation_threshold' => $validated['validation_threshold'],
            'redoublement_threshold' => $validated['redoublement_threshold'],
            'bulletin_footer_text' => $validated['bulletin_footer_text'] ?? '',
            'automatic_promotion' => $validated['automatic_promotion'] ? 'true' : 'false',
            'mention_system' => $validated['mention_system'] ? 'true' : 'false',
            'validation_subjects_required' => $validated['validation_subjects_required'],
        ];

        foreach ($settings as $key => $value) {
            $school->setSetting($key, $value);
        }

        return back()->with('success', 'Paramètres pédagogiques mis à jour avec succès.');
    }

    /**
     * Met à jour les seuils spécifiques pour un niveau
     */
    public function updateLevelThreshold(Request $request, Level $level)
    {
        $validated = $request->validate([
            'validation_threshold' => 'required|numeric|min:0|max:100',
        ]);

        $school = School::getActiveSchool();
        
        // Vérifier que le niveau appartient à l'école active
        if (!$level || $level->school_id !== $school->id) {
            return back()->with('error', 'Niveau non trouvé ou non autorisé.');
        }

        // Utiliser les paramètres de l'école pour stocker les seuils spécifiques
        $school->setSetting("level_{$level->id}_validation_threshold", $validated['validation_threshold']);

        return back()->with('success', "Seuil de validation mis à jour pour le niveau {$level->name}.");
    }

    /**
     * Met à jour les seuils spécifiques pour une matière
     */
    public function updateSubjectThreshold(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'validation_threshold' => 'required|numeric|min:0|max:100',
        ]);

        $school = School::getActiveSchool();
        
        // Vérifier que la matière appartient à l'école active
        if (!$subject || $subject->school_id !== $school->id) {
            return back()->with('error', 'Matière non trouvée ou non autorisée.');
        }

        // Utiliser les paramètres de l'école pour stocker les seuils spécifiques
        $school->setSetting("subject_{$subject->id}_validation_threshold", $validated['validation_threshold']);

        return back()->with('success', "Seuil de validation mis à jour pour la matière {$subject->name}.");
    }

    /**
     * Récupère le seuil de validation pour un niveau spécifique
     */
    public function getLevelThreshold(Level $level)
    {
        $school = School::getActiveSchool();
        return $school->getSetting("level_{$level->id}_validation_threshold", $school->getSetting('validation_threshold', '10'));
    }

    /**
     * Récupère le seuil de validation pour une matière spécifique
     */
    public function getSubjectThreshold(Subject $subject)
    {
        $school = School::getActiveSchool();
        return $school->getSetting("subject_{$subject->id}_validation_threshold", $school->getSetting('validation_threshold', '10'));
    }

    /**
     * Remet à zéro les seuils spécifiques d'un niveau
     */
    public function resetLevelThreshold(Level $level)
    {
        $school = School::getActiveSchool();
        
        if (!$level || $level->school_id !== $school->id) {
            return back()->with('error', 'Niveau non trouvé ou non autorisé.');
        }

        // Supprimer le paramètre spécifique
        $setting = $school->settings()->where('key', "level_{$level->id}_validation_threshold")->first();
        if ($setting) {
            $setting->delete();
        }

        return back()->with('success', "Seuil personnalisé supprimé pour le niveau {$level->name}. Le seuil global sera utilisé.");
    }

    /**
     * Remet à zéro les seuils spécifiques d'une matière
     */
    public function resetSubjectThreshold(Subject $subject)
    {
        $school = School::getActiveSchool();
        
        if (!$subject || $subject->school_id !== $school->id) {
            return back()->with('error', 'Matière non trouvée ou non autorisée.');
        }

        // Supprimer le paramètre spécifique
        $setting = $school->settings()->where('key', "subject_{$subject->id}_validation_threshold")->first();
        if ($setting) {
            $setting->delete();
        }

        return back()->with('success', "Seuil personnalisé supprimé pour la matière {$subject->name}. Le seuil global sera utilisé.");
    }
}