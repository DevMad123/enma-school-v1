@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestion des Départements
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Départements par UFR
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $departments->count() }} département{{ $departments->count() > 1 ? 's' : '' }}
                    </span>
                    <a href="{{ route('university.departments.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Nouveau Département
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Départements -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        @if($departments->count() > 0)
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($departments as $department)
                <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $department->name }}
                                        </h3>
                                        @if($department->short_name)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $department->short_name }}
                                            </span>
                                        @endif
                                        @if(!$department->is_active)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        <span class="font-medium">{{ $department->code }}</span>
                                        <span class="mx-2">•</span>
                                        <span class="text-blue-600 dark:text-blue-400">{{ $department->ufr->name }}</span>
                                        @if($department->head_of_department)
                                            <span class="mx-2">•</span>
                                            <span>Chef : {{ $department->head_of_department }}</span>
                                        @endif
                                    </div>
                                    @if($department->description)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            {{ Str::limit($department->description, 100) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $department->programs_count }} programme{{ $department->programs_count > 1 ? 's' : '' }}
                                </div>
                                @if($department->contact_email)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $department->contact_email }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('university.departments.show', $department) }}" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('university.departments.edit', $department) }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun département</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Commencez par créer votre premier département.
                </p>
                <div class="mt-6">
                    <a href="{{ route('university.departments.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                        Créer un département
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection