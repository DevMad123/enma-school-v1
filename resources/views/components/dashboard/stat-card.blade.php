@props([
    'title' => '',
    'value' => '',
    'icon' => '',
    'color' => 'blue',
    'trend' => null,
    'description' => null,
    'link' => null
])

@php
    $colorClasses = [
        'blue' => 'from-blue-500 to-blue-600 text-blue-600',
        'green' => 'from-green-500 to-green-600 text-green-600',
        'yellow' => 'from-yellow-500 to-yellow-600 text-yellow-600',
        'red' => 'from-red-500 to-red-600 text-red-600',
        'purple' => 'from-purple-500 to-purple-600 text-purple-600',
        'indigo' => 'from-indigo-500 to-indigo-600 text-indigo-600',
        'pink' => 'from-pink-500 to-pink-600 text-pink-600',
    ];

    $colorClass = $colorClasses[$color] ?? $colorClasses['blue'];
    list($gradientClass, $iconColorClass) = explode(' text-', $colorClass, 2);
    $iconColorClass = 'text-' . $iconColorClass;
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-all duration-200 group">
    @if($link)
        <a href="{{ $link }}" class="block">
    @endif
    
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <div class="flex items-center space-x-4">
                @if($icon)
                    <div class="w-12 h-12 bg-gradient-to-br {{ $gradientClass }} rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                        {!! $icon !!}
                    </div>
                @endif
                
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $title }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $value }}</p>
                    
                    @if($description)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $description }}</p>
                    @endif
                </div>
            </div>
        </div>
        
        @if($trend)
            <div class="flex items-center space-x-1">
                @if($trend['type'] === 'up')
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span class="text-sm font-medium text-green-600">{{ $trend['value'] }}</span>
                @elseif($trend['type'] === 'down')
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                    <span class="text-sm font-medium text-red-600">{{ $trend['value'] }}</span>
                @else
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                    </svg>
                    <span class="text-sm font-medium text-gray-600">{{ $trend['value'] }}</span>
                @endif
            </div>
        @endif
    </div>
    
    @if($link)
        </a>
    @endif
</div>