@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Gestion des Programmes
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400">
                        Licence, Master, Doctorat, DUT, BTS
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $programs->count() }} programme{{ $programs->count() > 1 ? 's' : '' }}
                    </span>
                    <a href="{{ route('university.programs.create') }}" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Nouveau Programme
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Programmes -->
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-lg">
        @if($programs->count() > 0)
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($programs as $program)
                <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                        @switch($program->level)
                                            @case('licence')
                                                <span class="text-purple-600 dark:text-purple-400 font-bold text-sm">L</span>
                                                @break
                                            @case('master')
                                                <span class="text-purple-600 dark:text-purple-400 font-bold text-sm">M</span>
                                                @break
                                            @case('doctorat')
                                                <span class="text-purple-600 dark:text-purple-400 font-bold text-sm">D</span>
                                                @break
                                            @default
                                                <span class="text-purple-600 dark:text-purple-400 font-bold text-xs">{{ strtoupper(substr($program->level, 0, 2)) }}</span>
                                        @endswitch
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $program->name }}
                                        </h3>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @switch($program->level)
                                                @case('licence') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @break
                                                @case('master') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 @break  
                                                @case('doctorat') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @break
                                                @default bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200
                                            @endswitch
                                        ">
                                            {{ ucfirst($program->level) }}
                                        </span>
                                        @if($program->short_name)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $program->short_name }}
                                            </span>
                                        @endif
                                        @if(!$program->is_active)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">
                                        <span class="font-medium">{{ $program->code }}</span>
                                        <span class="mx-2">•</span>
                                        <span class="text-green-600 dark:text-green-400">{{ $program->department->name }}</span>
                                        <span class="mx-2">•</span>
                                        <span class="text-blue-600 dark:text-blue-400">{{ $program->department->ufr->name }}</span>
                                    </div>
                                    <div class="mt-1 flex items-center text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $program->duration_semesters }} semestres</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ $program->total_credits }} crédits</span>
                                        @if($program->diploma_title)
                                            <span class="mx-2">•</span>
                                            <span>{{ $program->diploma_title }}</span>
                                        @endif
                                    </div>
                                    @if($program->description)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                            {{ Str::limit($program->description, 100) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $program->semesters_count }} semestre{{ $program->semesters_count > 1 ? 's' : '' }}
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('university.programs.show', $program) }}" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('university.programs.edit', $program) }}" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400">
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4v12l-4-2-4 2V4M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun programme</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Commencez par créer votre premier programme.
                </p>
                <div class="mt-6">
                    <a href="{{ route('university.programs.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                        Créer un programme
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection