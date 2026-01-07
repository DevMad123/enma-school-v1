<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DefaultEducationalSetting;
use App\Services\EducationalConfigurationService;
use App\Repositories\EducationalSettingsRepository;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use App\Traits\HasEducationalSettings;

class EducationalSettingsController extends Controller
{
    use HasEducationalSettings;
    
    public function __construct(
        private EducationalConfigurationService $configService,
        private EducationalSettingsRepository $settingsRepository
    ) {
        $this->middleware(['auth', 'can:manage_educational_settings']);
    }

    /**
     * Vue principale de gestion des paramètres
     */
    public function index(Request $request): View
    {
        $schoolType = $request->get('school_type', 'preuniversity');
        $schoolId = $request->get('school_id');
        $category = $request->get('category', 'evaluation');
        
        $school = $schoolId ? School::find($schoolId) : null;
        $schools = School::where('type', $schoolType)->get();
        
        // Récupération des paramètres actuels
        $currentSettings = $this->getCurrentSettings($school, $schoolType, $category);
        $defaultSettings = $this->getDefaultSettings($schoolType, $category);
        $validationRules = $this->getValidationRules($schoolType, $category);
        
        $data = [
            'schoolType' => $schoolType,
            'school' => $school,
            'schools' => $schools,
            'category' => $category,
            'categories' => $this->configService->getAvailableCategories($schoolType),
            'educationalLevels' => $this->configService->getEducationalLevels($schoolType),
            'currentSettings' => $currentSettings,
            'defaultSettings' => $defaultSettings,
            'validationRules' => $validationRules,
        ];
        
        return view('admin.educational-settings.index', $data);
    }

    /**
     * Mise à jour des paramètres
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => 'nullable|exists:schools,id',
            'school_type' => 'required|in:preuniversity,university',
            'category' => 'required|string',
            'settings' => 'required|array',
            'educational_level' => 'nullable|string',
        ]);

        $school = $validated['school_id'] ? School::find($validated['school_id']) : null;
        $settings = $validated['settings'];
        
        // Validation des paramètres
        $errors = $this->configService->validateSettings(
            [$validated['category'] => $settings], 
            $validated['school_type'], 
            $validated['educational_level'] ?? null
        );
        
        if (!empty($errors)) {
            return back()
                ->withErrors($errors)
                ->withInput()
                ->with('error', 'Erreurs de validation détectées');
        }

        try {
            // Sauvegarde des paramètres
            foreach ($settings as $key => $value) {
                $this->settingsRepository->setValue(
                    $validated['school_id'],
                    $validated['school_type'],
                    $validated['category'],
                    $key,
                    $value,
                    $validated['educational_level'] ?? null,
                    auth()->id()
                );
            }

            // Invalidation du cache
            $this->settingsRepository->invalidateCache($validated['school_id'], $validated['school_type'], $validated['category']);
            
            // Log de l'activité (nécessite spatie/laravel-activitylog)
            // activity()
            //     ->performedOn($school)
            //     ->causedBy(auth()->user())
            //     ->withProperties([
            //         'category' => $validated['category'],
            //         'settings' => $settings,
            //         'educational_level' => $validated['educational_level'],
            //     ])
            //     ->log('Mise à jour des paramètres éducatifs');

            return back()->with('success', 'Configuration mise à jour avec succès');
            
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Prévisualisation des changements
     */
    public function preview(Request $request): JsonResponse
    {
        $settings = $request->input('settings', []);
        $schoolType = $request->input('school_type', 'preuniversity');
        $category = $request->input('category', 'evaluation');
        
        // Analyse de l'impact des modifications
        $preview = $this->analyzeSettingsImpact($settings, $schoolType);
        $affectedFeatures = $this->getAffectedFeatures($settings);
        
        return response()->json([
            'preview' => $preview,
            'affected_features' => $affectedFeatures,
            'recommendations' => $this->getRecommendations($settings, $schoolType),
        ]);
    }

    /**
     * Reset aux valeurs par défaut
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'school_id' => 'nullable|exists:schools,id',
            'school_type' => 'required|in:preuniversity,university',
            'category' => 'required|string',
        ]);

        try {
            $school = $request->school_id ? School::find($request->school_id) : null;
            $defaultSettings = $this->getDefaultSettings($request->school_type, $request->category);
            
            foreach ($defaultSettings as $key => $value) {
                $this->settingsRepository->setValue(
                    $request->school_id,
                    $request->school_type,
                    $request->category,
                    $key,
                    $value,
                    null,
                    auth()->id()
                );
            }

            $this->settingsRepository->invalidateCache($request->school_id, $request->school_type, $request->category);
            
            // activity()
            //     ->performedOn($school)
            //     ->causedBy(auth()->user())
            //     ->withProperties(['category' => $request->category])
            //     ->log('Réinitialisation des paramètres aux valeurs par défaut');

            return back()->with('success', 'Configuration remise aux valeurs par défaut');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Export des configurations
     */
    public function export(Request $request)
    {
        $schoolId = $request->get('school_id');
        $schoolType = $request->get('school_type', 'preuniversity');
        
        $school = $schoolId ? School::find($schoolId) : null;
        $settings = $this->configService->exportSettings($school);
        
        $filename = $school 
            ? "settings_{$school->name}_{$schoolType}.json"
            : "settings_global_{$schoolType}.json";
        
        return response()->json($settings)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Import de configurations
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'school_id' => 'nullable|exists:schools,id',
            'school_type' => 'required|in:preuniversity,university',
            'settings_file' => 'required|file|mimes:json',
        ]);

        try {
            $school = $request->school_id ? School::find($request->school_id) : null;
            $file = $request->file('settings_file');
            $settings = json_decode($file->get(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()->with('error', 'Fichier JSON invalide');
            }

            $result = $this->configService->importSettings($school, $settings, auth()->id());
            
            if (!empty($result['errors'])) {
                return back()
                    ->withErrors($result['errors'])
                    ->with('warning', 'Import partiel avec des erreurs');
            }
            
            // activity()
            //     ->performedOn($school)
            //     ->causedBy(auth()->user())
            //     ->withProperties(['imported_count' => count($result['imported'])])
            //     ->log('Import de paramètres éducatifs');

            return back()->with('success', 'Configuration importée avec succès');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * API pour récupérer les paramètres d'une catégorie
     */
    public function getSettings(Request $request): JsonResponse
    {
        $schoolId = $request->get('school_id');
        $schoolType = $request->get('school_type', 'preuniversity');
        $category = $request->get('category', 'evaluation');
        
        $settings = $this->settingsRepository->getSettingsByCategory($schoolId, $schoolType, $category);
        
        return response()->json($settings);
    }

    /**
     * Génère un rapport de configuration
     */
    public function generateReport(Request $request): View
    {
        $schoolId = $request->get('school_id');
        $school = $schoolId ? School::find($schoolId) : null;
        
        if (!$school) {
            abort(404, 'École non trouvée');
        }
        
        $report = $this->configService->generateConfigurationReport($school);
        
        return view('admin.educational-settings.report', compact('report', 'school'));
    }

    /**
     * Compare les paramètres de deux écoles
     */
    public function compare(Request $request): View
    {
        $request->validate([
            'school1_id' => 'required|exists:schools,id',
            'school2_id' => 'required|exists:schools,id',
        ]);

        $school1 = School::find($request->school1_id);
        $school2 = School::find($request->school2_id);
        
        $comparison = $this->configService->compareSchoolSettings($school1, $school2);
        
        return view('admin.educational-settings.compare', compact('comparison', 'school1', 'school2'));
    }

    /**
     * Méthodes privées utilitaires
     */
    private function getCurrentSettings(?School $school, string $schoolType, string $category): array
    {
        if ($school) {
            return $this->settingsRepository->getSchoolSettingsByCategory($school->id, $schoolType, $category);
        }
        return $this->settingsRepository->getGlobalSettings($schoolType, $category);
    }

    private function getDefaultSettings(string $schoolType, string $category): array
    {
        return $this->settingsRepository->getDefaultSettingsByCategory($schoolType, $category);
    }

    private function getValidationRules(string $schoolType, string $category): array
    {
        return [];
        // return DefaultEducationalSetting::where('school_type', $schoolType)
        //     ->where('setting_category', $category)
        //     ->whereNotNull('validation_rules')
        //     ->pluck('validation_rules', 'setting_key')
        //     ->toArray();
    }

    private function getAffectedFeatures(array $settings): array
    {
        $affected = [];
        
        foreach (array_keys($settings) as $key) {
            switch ($key) {
                case 'thresholds':
                    $affected[] = 'Calcul des moyennes et mentions';
                    $affected[] = 'Génération des bulletins';
                    break;
                case 'coefficients':
                    $affected[] = 'Calcul des moyennes pondérées';
                    break;
                case 'structure':
                    $affected[] = 'Facturation et paiements';
                    break;
                case 'documents':
                    $affected[] = 'Validation des inscriptions';
                    break;
            }
        }
        
        return array_unique($affected);
    }

    private function analyzeSettingsImpact(array $settings, string $schoolType): array
    {
        // Analyse de l'impact des modifications
        return [
            'warnings' => [],
            'recommendations' => [],
            'affected_modules' => $this->getAffectedFeatures($settings),
        ];
    }
    
    private function getRecommendations(array $settings, string $schoolType): array
    {
        $recommendations = [];
        
        // Recommandations basées sur le type et les valeurs
        if ($schoolType === 'preuniversity' && isset($settings['thresholds'])) {
            $recommendations[] = 'Vérifiez que les seuils sont conformes au système éducatif ivoirien';
        }
        
        if ($schoolType === 'university' && isset($settings['standards'])) {
            $recommendations[] = 'Assurez-vous que les standards LMD respectent les directives officielles';
        }
        
        return $recommendations;
    }
}