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
    'variant' => 'standard',
    'glow' => null,
    'class' => '',
])

@php
$hasCustomPadding = preg_match('/\bp-\d+\b/', $class);

$tierClasses = match($variant) {
    'subtle' => 'glass-subtle rounded-xl text-slate-200' . ($hasCustomPadding ? '' : ' p-4'),
    'elevated' => 'glass-elevated rounded-2xl text-slate-100' . ($hasCustomPadding ? '' : ' p-6'),
    'premium' => 'glass-premium rounded-2xl text-white' . ($hasCustomPadding ? '' : ' p-6'),
    default => 'glass-standard rounded-xl text-slate-100' . ($hasCustomPadding ? '' : ' p-5'),
};

$glowClasses = match($glow) {
    'cyan' => 'border-cyan-500/30 shadow-[0_0_25px_rgba(6,182,212,0.15)]',
    'violet' => 'border-violet-500/30 shadow-[0_0_25px_rgba(139,92,246,0.15)]',
    'emerald' => 'border-emerald-500/30 shadow-[0_0_25px_rgba(16,185,129,0.15)]',
    'amber' => 'border-amber-500/30 shadow-[0_0_25px_rgba(245,158,11,0.15)]',
    'purple' => 'border-purple-500/30 shadow-[0_0_25px_rgba(168,85,247,0.15)]',
    default => '',
};
@endphp

<div {{ $attributes->merge(['class' => trim("$tierClasses $glowClasses transition-all duration-200 $class")]) }}>
    {{ $slot }}
</div>
