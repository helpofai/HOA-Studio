{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Animated Brand Logo Component
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
    'size' => 'md', // 'xs' (32px), 'sm' (40px), 'md' (48px), 'lg' (56px), 'xl' (64px)
    'showSubtitle' => true,
    'text' => 'HOA',
    'subtitle' => 'STUDIO',
    'class' => '',
])

@php
$sizeClasses = match($size) {
    'xs' => 'w-8 h-8 rounded-lg p-[1px]',
    'sm' => 'w-10 h-10 rounded-xl p-[1.5px]',
    'lg' => 'w-14 h-14 rounded-2xl p-[2px]',
    'xl' => 'w-16 h-16 rounded-2xl p-[2px]',
    default => 'w-12 h-12 rounded-xl p-[1.5px]', // md
};

$innerRadiusClass = match($size) {
    'xs' => 'rounded-[7px]',
    'sm' => 'rounded-[10px]',
    'lg' => 'rounded-[14px]',
    'xl' => 'rounded-[14px]',
    default => 'rounded-[11px]',
};

$titleSizeClass = match($size) {
    'xs' => 'text-[9px] font-black',
    'sm' => 'text-[11px] font-black',
    'lg' => 'text-[15px] font-black',
    'xl' => 'text-[17px] font-black',
    default => 'text-[13px] font-black',
};

$subSizeClass = match($size) {
    'xs' => 'text-[5.5px] font-extrabold tracking-[0.18em]',
    'sm' => 'text-[6.5px] font-extrabold tracking-[0.2em]',
    'lg' => 'text-[8.5px] font-extrabold tracking-[0.22em]',
    'xl' => 'text-[9.5px] font-extrabold tracking-[0.22em]',
    default => 'text-[7.5px] font-extrabold tracking-[0.2em]',
};
@endphp

<div class="hoa-animated-logo-outer aspect-square shrink-0 {{ $sizeClasses }} {{ $class }}">
    <div class="hoa-animated-logo-inner {{ $innerRadiusClass }}">
        <div class="flex flex-col items-center justify-center leading-none text-center select-none w-full h-full py-0.5">
            <span class="hoa-animated-logo-text {{ $titleSizeClass }} tracking-wider">
                {{ $text }}
            </span>
            @if($showSubtitle && $subtitle)
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 via-indigo-200 to-purple-300 uppercase {{ $subSizeClass }} mt-[1.5px] -mb-0.5 scale-95 font-mono">
                    {{ $subtitle }}
                </span>
            @endif
        </div>
    </div>
</div>
