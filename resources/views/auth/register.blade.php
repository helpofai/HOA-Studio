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
                <x-glass.logo size="lg" text="HOA" />
            </a>
            <h2 class="text-2xl font-bold text-white tracking-tight mt-4">Create Account</h2>
            <p class="text-xs text-slate-400 mt-1">Get 15,000 monthly AI words & full editor access</p>
        </div>

        <!-- Glass Register Card -->
        <x-glass.card variant="elevated" class="p-6 sm:p-8 border border-white/15 shadow-2xl">
            <form wire:submit="register" class="space-y-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Full Name</label>
                    <x-glass.input
                        wire:model="name"
                        type="text"
                        placeholder="Alex Morgan"
                        required
                        autofocus
                        :error="$errors->has('name')"
                    />
                    @error('name')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Email Address</label>
                    <x-glass.input
                        wire:model="email"
                        type="email"
                        placeholder="alex@company.com"
                        required
                        :error="$errors->has('email')"
                    />
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Password</label>
                    <x-glass.input
                        wire:model="password"
                        type="password"
                        placeholder="Minimum 8 characters"
                        required
                        :error="$errors->has('password')"
                    />
                    @error('password')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Confirm Password</label>
                    <x-glass.input
                        wire:model="password_confirmation"
                        type="password"
                        placeholder="Repeat password"
                        required
                    />
                </div>

                <div class="pt-1">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="agree" class="mt-0.5 rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-indigo-500/30">
                        <span class="text-xs text-slate-400 leading-tight">
                            I agree to the Terms of Service and Privacy Policy.
                        </span>
                    </label>
                    @error('agree')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <x-glass.button type="submit" variant="primary" size="md" class="w-full shadow-lg shadow-indigo-500/25 mt-2">
                    <span wire:loading.remove wire:target="register">Create Free Account</span>
                    <span wire:loading wire:target="register" class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
                        Creating Account...
                    </span>
                </x-glass.button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/5 text-center text-xs text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="text-indigo-400 hover:text-indigo-300 font-medium ml-1">
                    Sign In
                </a>
            </div>
        </x-glass.card>
    </div>
</div>