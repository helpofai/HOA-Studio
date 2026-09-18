{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Antigravity Models View
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

<div class="p-6 max-w-7xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white tracking-tight">🤖 Antigravity Gateway Models</h1>
        @if($account)
            <x-glass.badge variant="success">Active Account: {{ $account->email }}</x-glass.badge>
        @else
            <x-glass.badge variant="danger">No Active Account</x-glass.badge>
        @endif
    </div>

    @if($models->isEmpty())
        <x-glass.card variant="elevated" class="p-8 text-center text-slate-400">
            No Antigravity models are currently available. Please contact an administrator.
        </x-glass.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($models as $model)
                <x-glass.card variant="elevated" class="p-5 border border-white/10">
                    <h3 class="font-bold text-white">{{ $model->name }}</h3>
                    <p class="text-xs text-slate-400 mt-1 font-mono">{{ $model->model_id }}</p>
                    <div class="mt-4 flex items-center justify-between text-xs">
                        <span class="text-slate-500">Context: {{ number_format($model->context_window) }}</span>
                        @if($model->is_default)
                            <x-glass.badge variant="primary">Default</x-glass.badge>
                        @endif
                    </div>
                </x-glass.card>
            @endforeach
        </div>
    @endif
</div>
