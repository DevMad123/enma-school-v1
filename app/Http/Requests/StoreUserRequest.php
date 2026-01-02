<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette demande
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_users');
    }

    /**
     * Règles de validation pour la création d'un utilisateur
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{M}\p{N}\s\-\.]+$/u'],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                'unique:users,email',
                'not_regex:/^[a-zA-Z0-9._%+-]+@(tempmail|10minutemail|guerrillamail|mailinator)\./'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^(\+33|0)[1-9]([0-9]{8})$/',
                'unique:users,phone'
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['sometimes', 'boolean'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'address' => ['nullable', 'string', 'max:500'],
            'avatar' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000'
            ]
        ];
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets et points.',
            'email.not_regex' => 'Les adresses email temporaires ne sont pas autorisées.',
            'phone.regex' => 'Format de téléphone invalide. Utilisez le format français (+33 ou 0).',
            'password.uncompromised' => 'Ce mot de passe a été trouvé dans des fuites de données. Veuillez en choisir un autre.',
            'avatar.dimensions' => 'L\'avatar doit avoir une taille comprise entre 100x100 et 2000x2000 pixels.'
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

        if ($this->has('email')) {
            $this->merge([
                'email' => strtolower(trim($this->email))
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9+]/', '', $this->phone)
            ]);
        }
    }

    /**
     * Configuration après validation
     */
    public function passedValidation(): void
    {
        // Log de sécurité pour les tentatives de création d'utilisateur
        try {
            if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                \Spatie\Activitylog\Models\Activity::create([
                    'log_name' => 'user_management',
                    'description' => 'Tentative de création d\'utilisateur',
                    'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                    'causer_id' => auth()->id(),
                    'properties' => [
                        'email' => $this->email,
                        'role' => $this->role,
                        'ip' => $this->ip(),
                        'user_agent' => $this->userAgent()
                    ]
                ]);
            } else {
                \Log::info('Tentative de création d\'utilisateur', [
                    'email' => $this->email,
                    'role' => $this->role,
                    'ip' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                    'user_id' => auth()->id()
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Erreur lors du logging de création utilisateur', [
                'error' => $e->getMessage(),
                'email' => $this->email
            ]);
        }
    }
}