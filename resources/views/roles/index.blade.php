@extends('layouts.dashboard')

@section('title', 'Gestion des Rôles & Permissions')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Rôles & Permissions</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Définir et gérer les rôles utilisateurs et leurs permissions</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('users.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                    Utilisateurs
                </a>
                @can('create_roles')
                <a href="{{ route('roles.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouveau Rôle
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="mx-auto space-y-6">
        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $roles->count() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Rôles</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $permissions->count() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Permissions</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ \App\Models\User::role(array_column($roles->toArray(), 'name'))->count() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Utilisateurs avec Rôles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des rôles -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Rôles du Système</h3>
                
                <div class="grid gap-6">
                    @foreach($roles as $role)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ ucfirst($role->name) }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        @if($role->name === 'super_admin') bg-red-100 text-red-800
                                        @elseif($role->name === 'admin') bg-orange-100 text-orange-800
                                        @elseif($role->name === 'teacher') bg-green-100 text-green-800
                                        @elseif($role->name === 'student') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ $role->users_count ?? 0 }} utilisateur(s)
                                    </span>
                                    @if($role->name === 'super_admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Protégé
                                        </span>
                                    @endif
                                </div>
                                @if($role->description)
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $role->description }}</p>
                                @endif
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('roles.show', $role) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 text-sm">
                                    Voir
                                </a>
                                @can('edit_roles')
                                @if($role->name !== 'super_admin')
                                <a href="{{ route('roles.edit', $role) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 text-sm">
                                    Modifier
                                </a>
                                @endif
                                @endcan
                                @can('delete_roles')
                                @if(!in_array($role->name, ['super_admin', 'admin', 'teacher', 'student']))
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline" 
                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 text-sm">
                                        Supprimer
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </div>

                        <!-- Permissions du rôle -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Permissions ({{ $role->permissions->count() }})
                                </span>
                                @can('edit_roles')
                                @if($role->name !== 'super_admin')
                                <button 
                                    onclick="togglePermissions('{{ $role->id }}')"
                                    class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                    Gérer les permissions
                                </button>
                                @endif
                                @endcan
                            </div>
                            
                            <div class="flex flex-wrap gap-1">
                                @forelse($role->permissions->take(10) as $permission)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Aucune permission assignée</span>
                                @endforelse
                                @if($role->permissions->count() > 10)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        +{{ $role->permissions->count() - 10 }} autres...
                                    </span>
                                @endif
                            </div>

                            <!-- Panel de gestion des permissions (masqué par défaut) -->
                            @can('edit_roles')
                            @if($role->name !== 'super_admin')
                            <div id="permissions-{{ $role->id }}" class="hidden mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <h5 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Gérer les permissions</h5>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($permissions as $permission)
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                               onchange="togglePermission('{{ $role->id }}', '{{ $permission->id }}', this.checked)"
                                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <span class="ml-2 text-xs text-gray-700 dark:text-gray-300">{{ $permission->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @endcan
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Permissions disponibles -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">Permissions Disponibles</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($groupedPermissions as $category => $perms)
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3 capitalize">{{ $category }}</h4>
                        <div class="space-y-1">
                            @foreach($perms as $permission)
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 mr-1 mb-1">
                                {{ $permission->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePermissions(roleId) {
            const panel = document.getElementById('permissions-' + roleId);
            panel.classList.toggle('hidden');
        }

        async function togglePermission(roleId, permissionId, checked) {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('permission_id', permissionId);

            const url = checked 
                ? `{{ url('roles') }}/${roleId}/permissions`
                : `{{ url('roles') }}/${roleId}/permissions/${permissionId}`;
            
            const method = checked ? 'POST' : 'DELETE';

            try {
                const response = await fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }

                // Optionnel : afficher une notification de succès
                console.log('Permission mise à jour avec succès');
            } catch (error) {
                console.error('Erreur:', error);
                // Rétablir l'état précédent de la checkbox en cas d'erreur
                event.target.checked = !checked;
                alert('Erreur lors de la mise à jour de la permission');
            }
        }
    </script>
    </div>
</div>
@endsection