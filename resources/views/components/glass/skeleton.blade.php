{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Glassmorphic Skeleton Loader
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
    'type' => 'card', // 'card', 'table-row', 'text', 'avatar', 'stat', 'badge'
    'rows' => 3,
    'class' => '',
])

@if($type === 'card')
    <div {{ $attributes->merge(['class' => "glass-standard p-5 rounded-2xl space-y-3.5 animate-pulse border border-white/5 bg-slate-900/60 {$class}"]) }}>
        <div class="flex items-center justify-between">
            <div class="h-4 bg-white/10 rounded-md w-1/2"></div>
            <div class="h-4 bg-violet-500/20 rounded-md w-12"></div>
        </div>
        <div class="space-y-2">
            <div class="h-3 bg-white/5 rounded-md w-full"></div>
            <div class="h-3 bg-white/5 rounded-md w-4/5"></div>
        </div>
        <div class="flex items-center justify-between pt-2 border-t border-white/5">
            <div class="h-3 bg-white/10 rounded-md w-1/4"></div>
            <div class="h-5 bg-white/10 rounded-lg w-16"></div>
        </div>
    </div>

@elseif($type === 'table-row')
    @for($i = 0; $i < $rows; $i++)
        <tr {{ $attributes->merge(['class' => "animate-pulse border-b border-white/5 {$class}"]) }}>
            <td class="p-4">
                <div class="h-3.5 bg-white/10 rounded-md w-32"></div>
            </td>
            <td class="p-4">
                <div class="h-3 bg-white/5 rounded-md w-24"></div>
            </td>
            <td class="p-4">
                <div class="h-3 bg-white/5 rounded-md w-16"></div>
            </td>
            <td class="p-4">
                <div class="h-5 bg-violet-500/20 rounded-lg w-20"></div>
            </td>
            <td class="p-4 text-right">
                <div class="h-6 bg-white/10 rounded-lg w-14 ml-auto"></div>
            </td>
        </tr>
    @endfor

@elseif($type === 'stat')
    <div {{ $attributes->merge(['class' => "glass-standard p-5 rounded-2xl space-y-3 animate-pulse border border-white/5 bg-slate-900/60 {$class}"]) }}>
        <div class="flex items-center justify-between">
            <div class="h-3 bg-white/10 rounded-md w-20"></div>
            <div class="w-8 h-8 rounded-xl bg-white/10"></div>
        </div>
        <div class="h-6 bg-white/15 rounded-md w-24"></div>
        <div class="h-2.5 bg-white/5 rounded-md w-32"></div>
    </div>

@elseif($type === 'avatar')
    <div {{ $attributes->merge(['class' => "flex items-center gap-3 animate-pulse {$class}"]) }}>
        <div class="w-9 h-9 rounded-xl bg-white/10 shrink-0"></div>
        <div class="space-y-1.5 flex-1 min-w-0">
            <div class="h-3.5 bg-white/10 rounded-md w-24"></div>
            <div class="h-2.5 bg-white/5 rounded-md w-16"></div>
        </div>
    </div>

@elseif($type === 'badge')
    <div {{ $attributes->merge(['class' => "h-5 bg-white/10 rounded-lg w-16 animate-pulse inline-block {$class}"]) }}></div>

@else
    <!-- Default Text Lines -->
    <div {{ $attributes->merge(['class' => "space-y-2 animate-pulse {$class}"]) }}>
        <div class="h-3.5 bg-white/10 rounded-md w-3/4"></div>
        <div class="h-3 bg-white/5 rounded-md w-full"></div>
        <div class="h-3 bg-white/5 rounded-md w-5/6"></div>
    </div>
@endif
