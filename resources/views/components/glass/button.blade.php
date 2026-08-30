{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/
--}}

@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'class' => '',
    'loading' => false,
    'loader' => 'spinner', // spinner, dots, pulse, dual, bars, ellipsis, orbit, hourglass, square
    'loadingText' => null,
    'shimmer' => false,
    'wireTarget' => null,
])

@php
$baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-950 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer active:scale-[0.98] relative overflow-hidden select-none';

$sizeClasses = match($size) {
    'sm' => 'px-3 py-1.5 text-xs gap-1.5',
    'lg' => 'px-6 py-3 text-base gap-2.5',
    default => 'px-4 py-2 text-sm gap-2',
};

$variantClasses = match($variant) {
    'primary' => 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white shadow-lg shadow-indigo-500/25 border border-indigo-400/30 focus:ring-indigo-500',
    'glass' => 'glass-standard hover:bg-white/10 text-slate-100 hover:text-white border border-white/15 shadow-sm hover:border-white/25 focus:ring-white/20',
    'secondary' => 'bg-slate-800/80 hover:bg-slate-700/80 text-slate-200 hover:text-white border border-slate-700/50 focus:ring-slate-500',
    'outline' => 'border border-slate-700 hover:border-slate-500 text-slate-300 hover:text-white bg-transparent focus:ring-slate-500',
    'danger' => 'bg-red-600/80 hover:bg-red-500 text-white shadow-lg shadow-red-600/20 border border-red-500/30 focus:ring-red-500',
    default => 'glass-standard text-white',
};

$shimmerClass = $shimmer ? 'btn-shimmer' : '';
@endphp

<button 
    type="{{ $type }}" 
    @if($wireTarget) wire:loading.attr="disabled" wire:target="{{ $wireTarget }}" @endif
    @if($loading) disabled @endif
    {{ $attributes->merge(['class' => "$baseClasses $sizeClasses $variantClasses $shimmerClass $class"]) }}
>
    @if($wireTarget)
        <!-- Livewire Loading State Indicator -->
        <span wire:loading.flex wire:target="{{ $wireTarget }}" class="items-center gap-2">
            <x-glass.loader :style="$loader" />
            @if($loadingText)
                <span>{{ $loadingText }}</span>
            @endif
        </span>
        <!-- Livewire Default State Slot -->
        <span wire:loading.remove wire:target="{{ $wireTarget }}" class="inline-flex items-center gap-2">
            {{ $slot }}
        </span>
    @elseif($loading)
        <!-- Static / Reactive Loading State -->
        <span class="inline-flex items-center gap-2">
            <x-glass.loader :style="$loader" />
            @if($loadingText)
                <span>{{ $loadingText }}</span>
            @else
                {{ $slot }}
            @endif
        </span>
    @else
        {{ $slot }}
    @endif
</button>
