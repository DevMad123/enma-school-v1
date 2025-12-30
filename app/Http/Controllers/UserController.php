<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
     * Enregistrer un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'sometimes|boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'email_verified_at' => $request->has('email_verified') ? now() : null,
        ]);

        $user->assignRole($request->role);

        // Si l'utilisateur n'est pas actif, le bannir
        if (!$request->has('is_active')) {
            $user->update(['banned_at' => now()]);
        }

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été créé avec succès.');
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
     * Mettre à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'is_active' => 'sometimes|boolean',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Gestion du statut actif/inactif
        if ($request->has('is_active')) {
            $updateData['banned_at'] = null;
        } else {
            $updateData['banned_at'] = now();
        }

        $user->update($updateData);

        // Mettre à jour le rôle
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
            ->with('success', 'L\'utilisateur a été mis à jour avec succès.');
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