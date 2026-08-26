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
            <h2 class="text-2xl font-bold text-white tracking-tight mt-4">Welcome Back</h2>
            <p class="text-xs text-slate-400 mt-1">Sign in to your HelpOfAi Studio workspace</p>
        </div>

        <!-- Glass Login Card -->
        <x-glass.card variant="elevated" class="p-6 sm:p-8 border border-white/15 shadow-2xl">
            <form wire:submit="login" class="space-y-4">
                @if (session('status'))
                    <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- 🛡️ Invisible Honeypot Anti-Bot Trap (Hidden from real users via absolute off-screen positioning) -->
                <div class="absolute -left-[9999px] -top-[9999px] opacity-0 pointer-events-none" aria-hidden="true" tabindex="-1">
                    <label for="auth_hp_field">Website</label>
                    <input type="text" id="auth_hp_field" wire:model="honeypot" name="company_website_url" autocomplete="off" tabindex="-1">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Email Address</label>
                    <x-glass.input
                        wire:model="email"
                        type="email"
                        placeholder="you@example.com"
                        required
                        autofocus
                        autocomplete="email"
                        :error="$errors->has('email')"
                    />
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-slate-300">Password</label>
                        <a href="{{ route('password.request') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                            Forgot?
                        </a>
                    </div>
                    <x-glass.input
                        wire:model="password"
                        type="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        :error="$errors->has('password')"
                    />
                    @error('password')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="remember" class="rounded bg-slate-900 border-white/20 text-indigo-600 focus:ring-indigo-500/30">
                        <span class="text-xs text-slate-400">Remember me</span>
                    </label>
                </div>

                <!-- Cloudflare Turnstile Challenge (Renders if site key is configured) -->
                @if (!empty($turnstileSiteKey))
                    <div 
                        x-data="{
                            initTurnstile() {
                                if (window.turnstile) {
                                    window.turnstile.render(this.$refs.turnstileContainer, {
                                        sitekey: '{{ $turnstileSiteKey }}',
                                        theme: 'dark',
                                        callback: (token) => {
                                            $wire.set('turnstileToken', token);
                                        }
                                    });
                                }
                            }
                        }"
                        x-init="
                            if (!window.turnstile) {
                                let script = document.createElement('script');
                                script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
                                script.async = true;
                                script.defer = true;
                                script.onload = () => initTurnstile();
                                document.head.appendChild(script);
                            } else {
                                initTurnstile();
                            }
                        "
                        class="flex flex-col items-center justify-center my-2"
                    >
                        <div x-ref="turnstileContainer"></div>
                        @error('turnstile')
                            <p class="text-xs text-red-400 mt-1 text-center">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <x-glass.button type="submit" variant="primary" size="md" class="w-full shadow-lg shadow-indigo-500/25 mt-2">
                    <span wire:loading.remove wire:target="login">Sign In to Studio</span>
                    <span wire:loading wire:target="login" class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full border-2 border-white/20 border-t-white animate-spin"></span>
                        Authenticating...
                    </span>
                </x-glass.button>
            </form>

            <div class="mt-6 pt-6 border-t border-white/5 text-center text-xs text-slate-400">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300 font-medium ml-1">
                    Create one free
                </a>
            </div>
        </x-glass.card>
    </div>
</div>