<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CourseUnitElement;

class UpdateECUERequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // À adapter selon vos permissions
    }



    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $courseUnit = $this->route('courseUnit');
        $element = $this->route('element');
        
        return [
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('course_unit_elements', 'code')
                    ->where('course_unit_id', $courseUnit->id)
                    ->ignore($element->id)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'credits' => 'required|integer|min:1|max:30',
            'coefficient' => 'required|numeric|min:0.1|max:10',
            'hours_cm' => 'required|integer|min:0|max:200',
            'hours_td' => 'required|integer|min:0|max:200',
            'hours_tp' => 'required|integer|min:0|max:200',
            'evaluation_type' => 'required|in:controle_continu,examen_final,mixte',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code ECUE est obligatoire.',
            'code.unique' => 'Ce code ECUE existe déjà dans cette UE.',
            'code.regex' => 'Le code doit contenir uniquement des lettres majuscules, chiffres, tirets et underscores.',
            'name.required' => 'Le nom de l\'ECUE est obligatoire.',
            'name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'credits.required' => 'Le nombre de crédits est obligatoire.',
            'credits.min' => 'Le nombre de crédits doit être au moins 1.',
            'credits.max' => 'Le nombre de crédits ne peut pas dépasser 30.',
            'coefficient.required' => 'Le coefficient est obligatoire.',
            'coefficient.min' => 'Le coefficient doit être au moins 0.1.',
            'coefficient.max' => 'Le coefficient ne peut pas dépasser 10.',
            'hours_cm.required' => 'Les heures CM sont obligatoires.',
            'hours_td.required' => 'Les heures TD sont obligatoires.',
            'hours_tp.required' => 'Les heures TP sont obligatoires.',
            'hours_cm.max' => 'Les heures CM ne peuvent pas dépasser 200.',
            'hours_td.max' => 'Les heures TD ne peuvent pas dépasser 200.',
            'hours_tp.max' => 'Les heures TP ne peuvent pas dépasser 200.',
            'evaluation_type.required' => 'Le type d\'évaluation est obligatoire.',
            'evaluation_type.in' => 'Type d\'évaluation invalide.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Statut invalide.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $courseUnit = $this->route('courseUnit');
            $element = $this->route('element');
            
            // Validation métier : total des heures doit être > 0
            $totalHours = $this->hours_cm + $this->hours_td + $this->hours_tp;
            if ($totalHours == 0) {
                $validator->errors()->add('hours_total', 'Le total des heures ne peut pas être zéro.');
            }
            
            // Validation métier : crédits ne doivent pas dépasser l'UE
            if ($courseUnit && $element) {
                $existingCredits = CourseUnitElement::where('course_unit_id', $courseUnit->id)
                                                  ->where('status', 'active')
                                                  ->where('id', '!=', $element->id)
                                                  ->sum('credits');
                $totalCreditsAfter = $existingCredits + $this->credits;
                
                if ($totalCreditsAfter > $courseUnit->credits) {
                    $validator->errors()->add('credits', 
                        "Le total des crédits ECUE ({$totalCreditsAfter}) dépasserait les crédits de l'UE ({$courseUnit->credits})."
                    );
                }
                
                // Vérifier si l'ECUE a des évaluations avant changement de statut
                if ($this->status === 'inactive' && $element->hasEvaluations()) {
                    $validator->errors()->add('status', 
                        'Impossible de désactiver cet ECUE car il contient des évaluations.'
                    );
                }
            }
        });
    }
}
