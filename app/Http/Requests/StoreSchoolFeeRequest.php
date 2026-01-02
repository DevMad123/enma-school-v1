<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class StoreSchoolFeeRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette demande
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage_finances') || $this->user()->hasRole(['admin', 'super_admin']);
    }

    /**
     * Règles de validation
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[\p{L}\p{M}\p{N}\s\-\.\(\)]+$/u',
                Rule::unique('school_fees', 'name')->where(function ($query) {
                    return $query->where('academic_year_id', $this->academic_year_id);
                })
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/'
            ],
            'school_class_id' => [
                'nullable',
                'integer',
                'exists:school_classes,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->level_id) {
                        $fail('Vous ne pouvez pas spécifier à la fois une classe et un niveau.');
                    }
                }
            ],
            'level_id' => [
                'nullable',
                'integer',
                'exists:levels,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->cycle_id) {
                        $fail('Vous ne pouvez pas spécifier à la fois un niveau et un cycle.');
                    }
                }
            ],
            'cycle_id' => ['nullable', 'integer', 'exists:cycles,id'],
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
                function ($attribute, $value, $fail) {
                    $academicYear = \App\Models\AcademicYear::find($value);
                    if ($academicYear && $academicYear->end_date < now()) {
                        $fail('Vous ne pouvez pas créer de frais pour une année scolaire terminée.');
                    }
                }
            ],
            'is_mandatory' => ['boolean'],
            'due_date' => [
                'nullable',
                'date',
                'after:today',
                'before:' . now()->addYear()->format('Y-m-d')
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'payment_frequency' => [
                'nullable',
                Rule::in(['once', 'monthly', 'quarterly', 'semester'])
            ],
            'late_fee_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:' . ($this->amount * 0.5) // Max 50% du montant principal
            ]
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Le nom ne peut contenir que des lettres, chiffres, espaces, tirets, points et parenthèses.',
            'name.unique' => 'Un frais avec ce nom existe déjà pour cette année scolaire.',
            'amount.regex' => 'Le montant doit avoir au maximum 2 décimales.',
            'amount.max' => 'Le montant ne peut pas dépasser 999 999,99 €.',
            'due_date.after' => 'La date d\'échéance doit être dans le futur.',
            'due_date.before' => 'La date d\'échéance ne peut pas dépasser une année.',
            'late_fee_amount.max' => 'Les frais de retard ne peuvent pas dépasser 50% du montant principal.'
        ];
    }

    /**
     * Préparation des données pour validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name)
            ]);
        }

        if ($this->has('amount')) {
            $this->merge([
                'amount' => round(floatval(str_replace(',', '.', $this->amount)), 2)
            ]);
        }

        if ($this->has('is_mandatory')) {
            $this->merge([
                'is_mandatory' => filter_var($this->is_mandatory, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }

    /**
     * Configuration après validation
     */
    public function passedValidation(): void
    {
        try {
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                \Spatie\Activitylog\Models\Activity::create([
                    'log_name' => 'financial',
                    'description' => 'Création d\'un nouveau frais scolaire',
                    'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                    'causer_id' => auth()->id(),
                    'properties' => [
                        'school_fee_name' => $this->name,
                        'amount' => $this->amount,
                        'academic_year_id' => $this->academic_year_id
                    ]
                ]);
            } else {
                \Log::info('Création d\'un nouveau frais scolaire', [
                    'school_fee_name' => $this->name,
                    'amount' => $this->amount,
                    'academic_year_id' => $this->academic_year_id,
                    'user_id' => auth()->id()
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Erreur lors du logging de création frais scolaire', [
                'error' => $e->getMessage(),
                'school_fee_name' => $this->name
            ]);
        }
    }
}