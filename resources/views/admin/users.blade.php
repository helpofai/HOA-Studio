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

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">User & Quota Management</h1>
            <p class="text-xs text-slate-400 mt-1">Manage user roles (Admin, Editor, Pro, User, Member), plans, and monthly word quotas.</p>
        </div>
    </div>

    @if (session('status'))
        <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/20 text-xs text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Toolbar Filters -->
    <x-glass.card variant="subtle" class="p-4 flex flex-col sm:flex-row items-center gap-3">
        <div class="w-full sm:flex-1">
            <x-glass.input wire:model.live.debounce.300ms="search" placeholder="Search by name or email..." />
        </div>

        <div class="w-full sm:w-44">
            <select wire:model.live="selectedRole" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="editor">Editor</option>
                <option value="pro">Pro</option>
                <option value="user">User</option>
                <option value="member">Member</option>
            </select>
        </div>

        <div class="w-full sm:w-44">
            <select wire:model.live="selectedPlan" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                <option value="">All Plans</option>
                <option value="starter">Starter</option>
                <option value="pro">Pro</option>
                <option value="enterprise">Enterprise</option>
            </select>
        </div>
    </x-glass.card>

    <!-- Users Table -->
    <x-glass.card variant="standard" class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900/80 text-slate-400 border-b border-white/5 uppercase text-[10px]">
                    <tr>
                        <th class="p-4">User</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Plan</th>
                        <th class="p-4">Word Quota Limit</th>
                        <th class="p-4">Quota Remaining</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-200">
                    @foreach($users as $user)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="p-4 font-medium text-white">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-lg bg-slate-800 border border-white/10 flex items-center justify-center font-bold text-xs text-white">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ $user->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <x-glass.badge :variant="match($user->role) { 'admin' => 'violet', 'editor' => 'cyan', 'pro' => 'amber', default => 'emerald' }">
                                    {{ ucfirst($user->role) }}
                                </x-glass.badge>
                            </td>
                            <td class="p-4 uppercase font-mono text-[11px] text-indigo-300">{{ $user->plan }}</td>
                            <td class="p-4 font-mono">
                                <div>{{ number_format($user->monthly_word_quota) }} words</div>
                                @if(($user->bonus_word_quota ?? 0) > 0)
                                    <div class="text-[10px] text-cyan-400 font-bold">+{{ number_format($user->bonus_word_quota) }} bonus</div>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-emerald-400">
                                {{ number_format(max(0, ($user->monthly_word_quota + ($user->bonus_word_quota ?? 0)) - $user->used_word_quota)) }} words
                            </td>
                            <td class="p-4">
                                <button 
                                    wire:click="toggleActive({{ $user->id }})" 
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase cursor-pointer {{ $user->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30' : 'bg-red-500/10 text-red-400 border border-red-500/30' }}"
                                >
                                    {{ $user->is_active ? 'Active' : 'Banned' }}
                                </button>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        type="button" 
                                        wire:click="grantBonusQuota({{ $user->id }}, 10000)" 
                                        class="px-2 py-1 rounded-lg bg-cyan-950/80 border border-cyan-500/30 text-cyan-300 hover:bg-cyan-900 text-[10px] font-bold transition-all cursor-pointer"
                                        title="Grant +10,000 bonus words immediately"
                                    >
                                        +10k
                                    </button>
                                    <x-glass.button size="sm" variant="secondary" wire:click="openEditModal({{ $user->id }})">
                                        Edit &rarr;
                                    </x-glass.button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-white/5 flex items-center justify-between text-xs text-slate-400 font-mono">
                <div>Showing {{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</div>
                <div class="flex items-center gap-1.5 font-sans">
                    @if($users->onFirstPage())
                        <span class="px-2.5 py-1 rounded-lg bg-slate-900/40 border border-white/5 text-slate-600 cursor-not-allowed text-xs">&larr; Previous</span>
                    @else
                        <button type="button" wire:click="previousPage" class="px-2.5 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-xs cursor-pointer transition-colors">&larr; Previous</button>
                    @endif

                    @if($users->hasMorePages())
                        <button type="button" wire:click="nextPage" class="px-2.5 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-xs cursor-pointer transition-colors">Next &rarr;</button>
                    @else
                        <span class="px-2.5 py-1 rounded-lg bg-slate-900/40 border border-white/5 text-slate-600 cursor-not-allowed text-xs">Next &rarr;</span>
                    @endif
                </div>
            </div>
        @endif
    </x-glass.card>

    <!-- Edit User Modal -->
    <div 
        x-data="{ show: @entangle('showEditModal') }" 
        x-show="show" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4" 
        style="display: none;"
    >
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" x-on:click="show = false"></div>
        <x-glass.card variant="elevated" class="w-full max-w-lg p-6 sm:p-8 z-10 border border-violet-500/20 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-4">Edit User & Word Quota</h3>

            <form wire:submit="saveUser" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Full Name</label>
                        <x-glass.input wire:model="name" required :error="$errors->has('name')" />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Email Address</label>
                        <x-glass.input wire:model="email" type="email" required :error="$errors->has('email')" />
                        @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">User Role</label>
                        <select wire:model="role" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }} ({{ $r->value }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Subscription Plan</label>
                        <select wire:model="plan" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                            <option value="starter">Starter</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Monthly Word Quota</label>
                        <x-glass.input wire:model="monthly_word_quota" type="number" required />
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Used Words</label>
                        <x-glass.input wire:model="used_word_quota" type="number" required />
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Reset Password (Optional)</label>
                    <x-glass.input wire:model="new_password" type="password" placeholder="Leave blank to keep current password" />
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="rounded bg-slate-900 border-white/20 text-violet-600 focus:ring-violet-500/30">
                        <span class="text-xs text-slate-300">Account is active and permitted to login</span>
                    </label>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                    <x-glass.button type="button" variant="secondary" size="sm" x-on:click="show = false">Cancel</x-glass.button>
                    <x-glass.button type="submit" variant="primary" size="sm">Save Changes</x-glass.button>
                </div>
            </form>
        </x-glass.card>
    </div>
</div>