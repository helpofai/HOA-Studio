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
                <!-- 🛡️ Invisible Honeypot Anti-Bot Trap (Hidden from real users via absolute off-screen positioning) -->
                <div class="absolute -left-[9999px] -top-[9999px] opacity-0 pointer-events-none" aria-hidden="true" tabindex="-1">
                    <label for="auth_hp_field_reg">Website</label>
                    <input type="text" id="auth_hp_field_reg" wire:model="honeypot" name="user_website_trap" autocomplete="off" tabindex="-1">
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Full Name</label>
                    <x-glass.input
                        wire:model="name"
                        type="text"
                        placeholder="Alex Morgan"
                        required
                        autofocus
                        autocomplete="name"
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
                        autocomplete="email"
                        :error="$errors->has('email')"
                    />
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input with Live Alpine.js Security Strength Meter -->
                <div 
                    x-data="{
                        password: @entangle('password'),
                        get strength() {
                            if (!this.password) return 0;
                            let score = 0;
                            if (this.password.length >= 8) score++;
                            if (/[a-z]/.test(this.password) && /[A-Z]/.test(this.password)) score++;
                            if (/\d/.test(this.password)) score++;
                            if (/[^A-Za-z0-9]/.test(this.password)) score++;
                            return score;
                        },
                        get strengthLabel() {
                            switch(this.strength) {
                                case 1: return { text: 'Weak', color: 'text-rose-400', bar: 'bg-rose-500 w-1/4' };
                                case 2: return { text: 'Fair', color: 'text-amber-400', bar: 'bg-amber-500 w-2/4' };
                                case 3: return { text: 'Good', color: 'text-blue-400', bar: 'bg-blue-500 w-3/4' };
                                case 4: return { text: 'Strong', color: 'text-emerald-400', bar: 'bg-emerald-500 w-full' };
                                default: return { text: '', color: '', bar: 'w-0' };
                            }
                        }
                    }"
                >
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-slate-300">Password</label>
                        <span x-show="password.length > 0" class="text-[11px] font-semibold" :class="strengthLabel.color" x-text="strengthLabel.text"></span>
                    </div>
                    <x-glass.input
                        wire:model="password"
                        type="password"
                        placeholder="Min 8 chars, uppercase, number & symbol"
                        required
                        autocomplete="new-password"
                        :error="$errors->has('password')"
                    />

                    <!-- Visual Strength Bar -->
                    <div x-show="password.length > 0" class="h-1 w-full bg-slate-800 rounded-full mt-2 overflow-hidden">
                        <div class="h-full transition-all duration-300 rounded-full" :class="strengthLabel.bar"></div>
                    </div>

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
                        autocomplete="new-password"
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