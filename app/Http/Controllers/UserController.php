<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Liste des utilisateurs
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Filtres
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } else {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::all();

        // Calcul des statistiques
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'pending' => User::whereNull('email_verified_at')->count(),
            'blocked' => User::where('is_active', false)->count(),
        ];
        
        return view('users.index', compact('users', 'roles', 'stats'));
    }

    /**
     * Formulaire de création d'un utilisateur
     */
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    /**
     * Enregistrer un nouvel utilisateur avec validation robuste
     */
    public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();
            
            $validatedData = $request->validated();
            
            // Création de l'utilisateur avec données validées
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
                'password' => Hash::make($validatedData['password']),
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'email_verified_at' => $request->has('email_verified') ? now() : null,
                'is_active' => $validatedData['is_active'] ?? true,
                'created_by' => auth()->id(),
                'last_login_at' => null,
                'login_count' => 0
            ]);

            // Attribution du rôle avec vérification
            $role = Role::where('name', $validatedData['role'])->first();
            if (!$role) {
                throw ValidationException::withMessages([
                    'role' => 'Le rôle spécifié n\'existe pas.'
                ]);
            }
            
            $user->assignRole($role);

            // Gestion du statut actif/inactif avec audit
            if (!($validatedData['is_active'] ?? true)) {
                $user->update([
                    'banned_at' => now(),
                    'banned_by' => auth()->id(),
                    'ban_reason' => 'Compte désactivé à la création'
                ]);
            }

            // Log de sécurité pour audit
            try {
                if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                    \Spatie\Activitylog\Models\Activity::create([
                        'log_name' => 'user_management',
                        'description' => 'Utilisateur créé',
                        'subject_type' => get_class($user),
                        'subject_id' => $user->id,
                        'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                        'causer_id' => auth()->id(),
                        'properties' => [
                            'email' => $user->email,
                            'role' => $validatedData['role'],
                            'is_active' => $validatedData['is_active'] ?? true,
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent()
                        ]
                    ]);
                } else {
                    \Log::info('Utilisateur créé', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'role' => $validatedData['role'],
                        'creator_id' => auth()->id()
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Erreur lors du logging de création utilisateur', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id
                ]);
            }
            
            DB::commit();
            
            // Réponse adaptée selon le type de requête
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'L\'utilisateur a été créé avec succès.',
                    'user' => $user->load('roles')
                ], 201);
            }

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été créé avec succès.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            // Log de l'erreur pour débogage
            \Log::error('Erreur lors de la création d\'utilisateur', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->except('password')
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la création de l\'utilisateur.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            return redirect()->back()
                ->withInput($request->except('password'))
                ->with('error', 'Une erreur est survenue lors de la création de l\'utilisateur.');
        }
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions');
        return view('users.show', compact('user'));
    }

    /**
     * Formulaire d'édition d'un utilisateur
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Mettre à jour un utilisateur avec validation robuste
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();
            
            $validatedData = $request->validated();
            $originalData = $user->toArray();
            
            // Préparation des données de mise à jour
            $updateData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone' => $validatedData['phone'] ?? null,
                'date_of_birth' => $validatedData['date_of_birth'] ?? null,
                'address' => $validatedData['address'] ?? null,
                'updated_by' => auth()->id()
            ];

            // Gestion du mot de passe avec sécurité
            if (!empty($validatedData['password'])) {
                $updateData['password'] = Hash::make($validatedData['password']);
                $updateData['password_changed_at'] = now();
                
                // Log changement de mot de passe
                try {
                    if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                        \Spatie\Activitylog\Models\Activity::create([
                            'log_name' => 'security',
                            'description' => 'Mot de passe modifié',
                            'subject_type' => get_class($user),
                            'subject_id' => $user->id,
                            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                            'causer_id' => auth()->id()
                        ]);
                    } else {
                        \Log::warning('Mot de passe modifié', [
                            'user_id' => $user->id,
                            'modified_by' => auth()->id()
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erreur logging modification mot de passe', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id
                    ]);
                }
            }

            // Gestion du statut actif/inactif avec historique
            if (array_key_exists('is_active', $validatedData)) {
                if ($validatedData['is_active']) {
                    $updateData['banned_at'] = null;
                    $updateData['banned_by'] = null;
                    $updateData['ban_reason'] = null;
                } else {
                    $updateData['banned_at'] = now();
                    $updateData['banned_by'] = auth()->id();
                    $updateData['ban_reason'] = 'Compte désactivé par l\'administration';
                }
            }

            $user->update($updateData);

            // Mise à jour du rôle avec vérifications de sécurité
            if (isset($validatedData['role'])) {
                $newRole = Role::where('name', $validatedData['role'])->first();
                if (!$newRole) {
                    throw ValidationException::withMessages([
                        'role' => 'Le rôle spécifié n\'existe pas.'
                    ]);
                }
                
                $oldRoles = $user->roles->pluck('name')->toArray();
                $user->syncRoles([$validatedData['role']]);
                
                // Log changement de rôle
                if ($oldRoles !== [$validatedData['role']]) {
                    try {
                        if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                            \Spatie\Activitylog\Models\Activity::create([
                                'log_name' => 'user_management',
                                'description' => 'Rôle modifié',
                                'subject_type' => get_class($user),
                                'subject_id' => $user->id,
                                'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                                'causer_id' => auth()->id(),
                                'properties' => [
                                    'old_roles' => $oldRoles,
                                    'new_role' => $validatedData['role']
                                ]
                            ]);
                        } else {
                            \Log::info('Rôle modifié', [
                                'user_id' => $user->id,
                                'old_roles' => $oldRoles,
                                'new_role' => $validatedData['role'],
                                'modified_by' => auth()->id()
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Erreur logging changement rôle', [
                            'error' => $e->getMessage(),
                            'user_id' => $user->id
                        ]);
                    }
                }
            }
            
            // Log complet de modification avec diff
            $changes = array_diff_assoc($user->fresh()->toArray(), $originalData);
            if (!empty($changes)) {
                try {
                    if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                        \Spatie\Activitylog\Models\Activity::create([
                            'log_name' => 'user_management',
                            'description' => 'Utilisateur modifié',
                            'subject_type' => get_class($user),
                            'subject_id' => $user->id,
                            'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                            'causer_id' => auth()->id(),
                            'properties' => [
                                'changes' => $changes,
                                'ip_address' => $request->ip(),
                                'user_agent' => $request->userAgent()
                            ]
                        ]);
                    } else {
                        \Log::info('Utilisateur modifié', [
                            'user_id' => $user->id,
                            'changes' => $changes,
                            'modified_by' => auth()->id()
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Erreur logging modification utilisateur', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id
                    ]);
                }
            }

            DB::commit();
            
            // Réponse adaptée selon le type de requête
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'L\'utilisateur a été mis à jour avec succès.',
                    'user' => $user->fresh()->load('roles')
                ]);
            }

            return redirect()->route('users.index')
                ->with('success', 'L\'utilisateur a été mis à jour avec succès.');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            // Log de l'erreur
            \Log::error('Erreur lors de la mise à jour d\'utilisateur', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'target_user_id' => $user->id,
                'data' => $request->except('password')
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur est survenue lors de la mise à jour.',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
            
            return redirect()->back()
                ->withInput($request->except('password'))
                ->with('error', 'Une erreur est survenue lors de la mise à jour.');
        }
    }

    /**
     * Activer/désactiver un utilisateur
     */
    public function toggleStatus(User $user)
    {
        if ($user->banned_at) {
            $user->update(['banned_at' => null]);
            $message = 'L\'utilisateur a été activé avec succès.';
        } else {
            $user->update(['banned_at' => now()]);
            $message = 'L\'utilisateur a été désactivé avec succès.';
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher la suppression du super admin
        if ($user->hasRole('super_admin')) {
            return redirect()->back()
                ->with('error', 'Vous ne pouvez pas supprimer un super administrateur.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été supprimé avec succès.');
    }

    /**
     * Réinitialiser le mot de passe d'un utilisateur
     */
    public function resetPassword(User $user)
    {
        $newPassword = 'password123';
        $user->update([
            'password' => Hash::make($newPassword),
            'password_changed_at' => null, // Forcer le changement au prochain login
        ]);

        return redirect()->back()
            ->with('success', "Le mot de passe de {$user->name} a été réinitialisé à : {$newPassword}");
    }
}