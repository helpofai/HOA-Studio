{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Glass Alert Component
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/
--}}

@props([
    'variant' => 'info', // success, error, warning, info
    'dismissible' => true,
    'icon' => null,
    'class' => '',
])

@php
$config = match($variant) {
    'success' => [
        'bg' => 'bg-emerald-500/10 border-emerald-500/25 text-emerald-200',
        'icon' => $icon ?? '✓',
        'iconColor' => 'text-emerald-400 bg-emerald-500/20 border-emerald-500/30',
    ],
    'error', 'danger' => [
        'bg' => 'bg-red-500/10 border-red-500/25 text-red-200',
        'icon' => $icon ?? '⚠️',
        'iconColor' => 'text-red-400 bg-red-500/20 border-red-500/30',
    ],
    'warning' => [
        'bg' => 'bg-amber-500/10 border-amber-500/25 text-amber-200',
        'icon' => $icon ?? '⚡',
        'iconColor' => 'text-amber-400 bg-amber-500/20 border-amber-500/30',
    ],
    default => [
        'bg' => 'bg-indigo-500/10 border-indigo-500/25 text-indigo-200',
        'icon' => $icon ?? 'ℹ️',
        'iconColor' => 'text-indigo-400 bg-indigo-500/20 border-indigo-500/30',
    ],
};
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show" 
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-1 scale-95"
    {{ $attributes->merge([
        'class' => 'p-3.5 rounded-2xl border backdrop-blur-xl shadow-lg flex items-start gap-3 text-xs leading-relaxed ' . $config['bg'] . ' ' . $class
    ]) }}
    role="alert"
>
    @if(!empty($config['icon']))
        <span class="w-6 h-6 rounded-lg flex items-center justify-center font-bold text-xs shrink-0 border {{ $config['iconColor'] }} shadow-sm mt-0.5 select-none">
            {{ $config['icon'] }}
        </span>
    @endif

    <div class="flex-1 min-w-0 font-medium">
        {{ $slot }}
    </div>

    @if($dismissible)
        <button 
            type="button" 
            @click="show = false" 
            class="text-slate-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10 shrink-0 select-none cursor-pointer focus:outline-none"
            aria-label="Dismiss message"
        >
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    @endif
</div>
