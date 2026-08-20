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

<div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-md">
        <!-- Logo Branding Header -->
        <div class="text-center mb-8">
            <a href="/" class="inline-flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-500 via-purple-500 to-cyan-400 p-[1px] shadow-xl shadow-indigo-500/25 group-hover:scale-105 transition-all">
                    <div class="w-full h-full bg-slate-950 rounded-[15px] flex items-center justify-center">
                        <span class="font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-cyan-300 text-sm">HOA</span>
                    </div>
                </div>
            </a>
            <h2 class="text-2xl font-bold text-white tracking-tight mt-4">Reset Password</h2>
            <p class="text-xs text-slate-400 mt-1">We will send you a secure recovery link</p>
        </div>

        <!-- Glass Forgot Password Card -->
        <x-glass.card variant="elevated" class="p-6 sm:p-8 border border-white/15 shadow-2xl">
            @if ($status)
                <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 mb-4">
                    {{ $status }}
                </div>
            @endif

            <form wire:submit="sendResetLink" class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Email Address</label>
                    <x-glass.input
                        wire:model="email"
                        type="email"
                        placeholder="you@example.com"
                        required
                        autofocus
                        :error="$errors->has('email')"
                    />
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-glass.button type="submit" variant="primary" size="md" class="w-full shadow-lg shadow-indigo-500/25 mt-2">
                    <span wire:loading.remove wire:target="sendResetLink">Send Recovery Link</span>
                    <span wire:loading wire:target="sendResetLink" class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
                        Dispatching Link...
                    </span>
                </x-glass.button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/5 text-center text-xs text-slate-400">
                Remember your password?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium ml-1">
                    Sign In
                </a>
            </div>
        </x-glass.card>
    </div>
</div>