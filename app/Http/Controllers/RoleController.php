<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Liste des rôles et permissions
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy(function($permission) {
            return explode('_', $permission->name)[1] ?? 'general';
        });

        return view('roles.index', compact('roles', 'permissions', 'groupedPermissions'));
    }

    /**
     * Formulaire de création de rôle
     */
    public function create()
    {
        $permissions = Permission::all();
        $groupedPermissions = $permissions->groupBy(function($permission) {
            return explode('_', $permission->name)[1] ?? 'general';
        });

        return view('roles.create', compact('permissions', 'groupedPermissions'));
    }

    /**
     * Enregistrer un nouveau rôle
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->has('permissions')) {
            $role->givePermissionTo($request->permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Le rôle a été créé avec succès.');
    }

    /**
     * Afficher un rôle
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        return view('roles.show', compact('role'));
    }

    /**
     * Formulaire d'édition d'un rôle
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('_', $permission->name)[1] ?? 'general';
        });

        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Request $request, Role $role)
    {
        // Empêcher la modification du super_admin
        if ($role->name === 'super_admin') {
            return redirect()->back()
                ->with('error', 'Le rôle super administrateur ne peut pas être modifié.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        // Synchroniser les permissions
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Le rôle a été mis à jour avec succès.');
    }

    /**
     * Supprimer un rôle
     */
    public function destroy(Role $role)
    {
        // Empêcher la suppression de rôles protégés
        $protectedRoles = ['super_admin', 'admin', 'enseignant', 'eleve'];
        
        if (in_array($role->name, $protectedRoles)) {
            return redirect()->back()
                ->with('error', 'Ce rôle est protégé et ne peut pas être supprimé.');
        }

        // Vérifier si des utilisateurs ont ce rôle
        if ($role->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Ce rôle ne peut pas être supprimé car il est assigné à des utilisateurs.');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Le rôle a été supprimé avec succès.');
    }

    /**
     * Assigner une permission à un rôle
     */
    public function storePermission(Request $request, Role $role)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $permission = Permission::find($request->permission_id);
        $role->givePermissionTo($permission);

        return response()->json(['success' => true, 'message' => 'Permission ajoutée avec succès.']);
    }

    /**
     * Retirer une permission d'un rôle
     */
    public function destroyPermission(Role $role, Permission $permission)
    {
        $role->revokePermissionTo($permission);

        return response()->json(['success' => true, 'message' => 'Permission supprimée avec succès.']);
    }
}