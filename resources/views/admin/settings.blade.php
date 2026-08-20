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

<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-white tracking-tight">System Settings & Gateway Config</h1>
        <p class="text-xs text-slate-400 mt-1">Configure OmniRoute v3.8.50 connection parameters, default tier quotas, and system flags.</p>
    </div>

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="saveSettings" class="space-y-6">
        <!-- OmniRoute Gateway Settings -->
        <x-glass.card variant="elevated" class="p-6 sm:p-8 space-y-4 border border-violet-500/20">
            <h3 class="text-base font-bold text-white pb-3 border-b border-white/5 flex items-center gap-2">
                <span>⚡ OmniRoute Gateway Configuration</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Gateway Base URL</label>
                    <x-glass.input wire:model="gateway_url" required />
                    <p class="text-[10px] text-slate-500 mt-1">Default local instance: http://127.0.0.1:20128</p>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Gateway Master API Key</label>
                    <x-glass.input wire:model="gateway_api_key" type="password" required />
                    <p class="text-[10px] text-slate-500 mt-1">Bearer token for /v1/* inference authentication</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Default Fallback Model / Combo</label>
                    <select wire:model="default_model" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                        <option value="deepseek/deepseek-chat">DeepSeek-V3 (deepseek/deepseek-chat)</option>
                        <option value="cc/claude-3-7-sonnet">Claude 3.7 Sonnet (cc/claude-3-7-sonnet)</option>
                        <option value="openai/gpt-4o">OpenAI GPT-4o (openai/gpt-4o)</option>
                        <option value="glm/glm-4-flash">GLM-4-Flash (Free Pool)</option>
                        <option value="groq/llama-3.3-70b-versatile">Groq Llama 3.3 70B (Fast Free)</option>
                        <option value="combo:creative-pro">Combo: Creative Pro (Auto Cascade)</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Context Compression Mode</label>
                    <select wire:model="compression_mode" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                        <option value="default">Default Panel Profile</option>
                        <option value="engine:rtk">RTK Command Output Engine</option>
                        <option value="off">Off (No Compression)</option>
                    </select>
                </div>
            </div>
        </x-glass.card>

        <!-- Tier Quotas -->
        <x-glass.card variant="standard" class="p-6 sm:p-8 space-y-4">
            <h3 class="text-base font-bold text-white pb-3 border-b border-white/5">
                📦 Default Monthly Word Quotas per Tier
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Starter Tier (Free)</label>
                    <x-glass.input wire:model="starter_quota" type="number" required />
                    <p class="text-[10px] text-slate-500 mt-1">Assigned on registration</p>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Pro Tier</label>
                    <x-glass.input wire:model="pro_quota" type="number" required />
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Enterprise Tier</label>
                    <x-glass.input wire:model="enterprise_quota" type="number" required />
                </div>
            </div>
        </x-glass.card>

        <!-- Platform Options -->
        <x-glass.card variant="standard" class="p-6 sm:p-8 space-y-4">
            <h3 class="text-base font-bold text-white pb-3 border-b border-white/5">
                🌐 General Platform Configuration
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Platform Name</label>
                    <x-glass.input wire:model="site_name" required />
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="allow_registration" class="rounded bg-slate-900 border-white/20 text-violet-600 focus:ring-violet-500/30">
                        <span class="text-xs text-slate-300">Allow public user registration</span>
                    </label>
                </div>
            </div>
        </x-glass.card>

        <div class="flex justify-end pt-2">
            <x-glass.button type="submit" variant="primary" size="md" class="shadow-lg shadow-violet-500/30">
                Save System Settings
            </x-glass.button>
        </div>
    </form>
</div>