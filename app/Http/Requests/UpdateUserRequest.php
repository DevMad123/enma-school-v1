<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette demande
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        
        // L'utilisateur peut modifier son propre profil ou avoir la permission
        return $this->user()->id === $user->id || 
               $this->user()->can('edit_users') ||
               ($this->user()->hasRole('admin') && !$user->hasRole('super_admin'));
    }

    /**
     * Règles de validation pour la mise à jour d'un utilisateur
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id;
        
        return [
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[\p{L}\p{M}\p{N}\s\-\.]+$/u'],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                'not_regex:/^[a-zA-Z0-9._%+-]+@(tempmail|10minutemail|guerrillamail|mailinator)\./'
            ],
            'phone' => [
                'nullable',
                'string',
                'regex:/^(\+33|0)[1-9]([0-9]{8})$/',
                Rule::unique('users', 'phone')->ignore($userId)
            ],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],
            'role' => [
                'sometimes',
                'string',
                'exists:roles,name',
                function ($attribute, $value, $fail) {
                    // Empêcher la modification du rôle super_admin sauf par un super_admin
                    if ($value === 'super_admin' && !$this->user()->hasRole('super_admin')) {
                        $fail('Vous n\'avez pas l\'autorisation d\'attribuer le rôle super administrateur.');
                    }
                    
                    // Empêcher de retirer le rôle super_admin à soi-même
                    $currentUser = $this->route('user');
                    if ($currentUser->hasRole('super_admin') && 
                        $this->user()->id === $currentUser->id && 
                        $value !== 'super_admin') {
                        $fail('Vous ne pouvez pas retirer votre propre rôle de super administrateur.');
                    }
                }
            ],
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
}