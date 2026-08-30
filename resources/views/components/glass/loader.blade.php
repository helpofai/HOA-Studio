{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Glass Button Loader Component
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
    'style' => 'spinner', // spinner, dots, pulse, dual, bars, ellipsis, orbit, hourglass, square, shimmer
    'class' => '',
])

@php
$loaderClass = match($style) {
    'dots' => 'btn-loader-dots',
    'pulse', 'pulse-ring' => 'btn-loader-pulse',
    'dual' => 'btn-loader-dual',
    'bars' => 'btn-loader-bars',
    'ellipsis' => 'btn-loader-ellipsis',
    'orbit' => 'btn-loader-orbit',
    'hourglass' => 'btn-loader-hourglass',
    'square' => 'btn-loader-square',
    default => 'btn-loader-spinner',
};
@endphp

@if($style === 'dots')
    <span {{ $attributes->merge(['class' => "$loaderClass $class"]) }}>
        <span></span><span></span><span></span>
    </span>
@elseif($style === 'bars')
    <span {{ $attributes->merge(['class' => "$loaderClass $class"]) }}>
        <span></span><span></span><span></span><span></span>
    </span>
@elseif($style === 'ellipsis')
    <span {{ $attributes->merge(['class' => "$loaderClass $class"]) }}>...</span>
@else
    <span {{ $attributes->merge(['class' => "$loaderClass $class"]) }}></span>
@endif
