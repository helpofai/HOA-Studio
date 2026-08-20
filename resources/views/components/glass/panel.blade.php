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
    'title' => null,
    'description' => null,
    'variant' => 'standard',
    'class' => '',
])

<x-glass.card :variant="$variant" :class="'overflow-hidden flex flex-col ' . $class" {{ $attributes->except(['variant', 'class', 'title', 'description']) }}>
    @if($title || isset($header))
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
            @if(isset($header))
                {{ $header }}
            @else
                <div>
                    <h3 class="text-base font-semibold text-white">{{ $title }}</h3>
                    @if($description)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $description }}</p>
                    @endif
                </div>
            @endif

            @if(isset($actions))
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="p-6 flex-1">
        {{ $slot }}
    </div>

    @if(isset($footer))
        <div class="px-6 py-3.5 bg-slate-950/40 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
            {{ $footer }}
        </div>
    @endif
</x-glass.card>
