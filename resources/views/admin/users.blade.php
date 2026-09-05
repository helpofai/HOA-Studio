{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin User & Quota Management
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

<div class="hoa-admin-users-space space-y-6 pb-12" x-data="{ bulkDropdownOpen: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>User & Quota Management</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Enterprise user directory, token quotas, multi-attribute filtering, roles & permissions matrix, and bulk operations.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Export Options -->
            <button 
                type="button"
                wire:click="exportSelectedCsv"
                class="px-3.5 py-2 rounded-xl bg-slate-900/90 border border-white/10 hover:border-violet-500/40 text-slate-200 hover:text-white text-xs font-semibold shadow-md transition-all flex items-center gap-2 cursor-pointer"
                title="Export selected or all users to CSV spreadsheet"
            >
                <span>📥</span>
                <span>Export CSV</span>
            </button>

            <button 
                type="button"
                wire:click="exportSelectedJson"
                class="px-3.5 py-2 rounded-xl bg-slate-900/90 border border-white/10 hover:border-violet-500/40 text-slate-200 hover:text-white text-xs font-semibold shadow-md transition-all flex items-center gap-2 cursor-pointer"
                title="Export selected or all users to JSON format"
            >
                <span>📦</span>
                <span>Export JSON</span>
            </button>

            <button 
                type="button" 
                wire:click="openCreateModal"
                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-bold text-xs shadow-lg shadow-violet-500/25 transition-all flex items-center gap-2 cursor-pointer"
            >
                <span>➕</span>
                <span>Add New User</span>
            </button>
        </div>
    </div>

    <!-- Alert / Feedback Banner -->
    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/30 text-xs sm:text-sm text-emerald-300 flex items-center justify-between gap-3 animate-fade-in shadow-lg shadow-emerald-950/40">
            <div class="flex items-center gap-2.5">
                <span class="text-base">✅</span>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="p-4 rounded-2xl bg-rose-950/80 border border-rose-500/30 text-xs sm:text-sm text-rose-300 flex items-center justify-between gap-3 animate-fade-in shadow-lg shadow-rose-950/40">
            <div class="flex items-center gap-2.5">
                <span class="text-base">❌</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Top KPI Overview Matrix (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Users & Status -->
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Accounts</span>
                <span class="text-xl">👥</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-white tracking-tight">{{ number_format($stats['total_users']) }}</span>
                <span class="text-[11px] text-slate-400 font-mono">registered</span>
            </div>
            <div class="mt-3 flex items-center gap-1.5 flex-wrap text-[10px]">
                <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold">
                    {{ $stats['active_users'] }} Active
                </span>
                @if($stats['banned_users'] > 0)
                    <span class="px-2 py-0.5 rounded-md bg-rose-500/10 text-rose-400 border border-rose-500/20 font-bold">
                        {{ $stats['banned_users'] }} Banned
                    </span>
                @endif
                @if($stats['unverified_count'] > 0)
                    <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold">
                        {{ $stats['unverified_count'] }} Unverified
                    </span>
                @endif
            </div>
        </x-glass.card>

        <!-- Card 2: Privileged Roles -->
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Staff & Creators</span>
                <span class="text-xl">🛡️</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-violet-300 tracking-tight">{{ $stats['admin_count'] }}</span>
                <span class="text-xs text-violet-400 font-semibold">Administrators</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 flex items-center gap-1">
                <span>⚡</span>
                <span>{{ $stats['editor_pro_count'] }} Editors & Pro Power Users</span>
            </div>
        </x-glass.card>

        <!-- Card 3: Word Quota Pool -->
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Allocated Quota Pool</span>
                <span class="text-xl">⚡</span>
            </div>
            <div class="mt-2 flex items-baseline gap-1">
                <span class="text-2xl font-black text-cyan-300 tracking-tight">{{ number_format($stats['total_quota_allocated']) }}</span>
                <span class="text-[11px] text-slate-400 font-mono">words</span>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 flex items-center gap-1">
                <span>🔄</span>
                <span>Monthly reset capacity allocated</span>
            </div>
        </x-glass.card>

        <!-- Card 4: Quota Utilization -->
        @php
            $usagePct = $stats['total_quota_allocated'] > 0 
                ? min(100, round(($stats['total_quota_used'] / $stats['total_quota_allocated']) * 100, 1)) 
                : 0;
        @endphp
        <x-glass.card variant="subtle" class="p-4 relative overflow-hidden border border-white/10">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Consumed Tokens</span>
                <span class="text-xl">📊</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-black text-emerald-400 tracking-tight">{{ number_format($stats['total_quota_used']) }}</span>
                <span class="text-[11px] text-slate-400 font-mono">({{ $usagePct }}%)</span>
            </div>
            <div class="mt-3 w-full bg-slate-950 rounded-full h-1.5 border border-white/5 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 {{ $usagePct > 85 ? 'bg-gradient-to-r from-amber-500 to-rose-500' : 'bg-gradient-to-r from-violet-500 to-emerald-400' }}" style="width: {{ $usagePct }}%"></div>
            </div>
        </x-glass.card>
    </div>

    <!-- Navigation Tabs: Directory vs Roles Matrix -->
    <div class="flex items-center gap-2 border-b border-white/10 pb-2">
        <button 
            type="button" 
            wire:click="$set('activeTab', 'users')"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'users' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/30' : 'bg-slate-900/60 text-slate-400 hover:text-white hover:bg-slate-800/80 border border-white/5' }}"
        >
            <span>👥 User Directory</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'users' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }} font-mono">
                {{ number_format($stats['total_users']) }}
            </span>
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'roles')"
            class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 cursor-pointer {{ $activeTab === 'roles' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/30' : 'bg-slate-900/60 text-slate-400 hover:text-white hover:bg-slate-800/80 border border-white/5' }}"
        >
            <span>🛡️ Roles & Permissions Matrix</span>
            <span class="px-1.5 py-0.5 text-[10px] rounded-full {{ $activeTab === 'roles' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }} font-mono">
                5 Roles
            </span>
        </button>
    </div>

    <!-- TAB 1: USERS DIRECTORY -->
    @if($activeTab === 'users')
        <!-- Search & Filter Controls Matrix -->
        <x-glass.card variant="subtle" class="p-4 space-y-3 border border-white/10">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">SEARCH DIRECTORY</label>
                    <x-glass.input wire:model.live.debounce.300ms="search" placeholder="Search name, email, or user ID..." />
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">FILTER ROLE</label>
                    <select wire:model.live="selectedRole" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                        <option value="">All Roles</option>
                        <option value="admin">Admin</option>
                        <option value="editor">Editor</option>
                        <option value="pro">Pro</option>
                        <option value="user">User</option>
                        <option value="member">Member</option>
                    </select>
                </div>

                <!-- Plan Filter -->
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">FILTER PLAN</label>
                    <select wire:model.live="selectedPlan" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                        <option value="">All Plans</option>
                        <option value="starter">Starter</option>
                        <option value="pro">Pro</option>
                        <option value="enterprise">Enterprise</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">ACCOUNT STATUS</label>
                    <select wire:model.live="selectedStatus" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                        <option value="">All Statuses</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Banned / Suspended</option>
                        <option value="zero_quota">Zero Quota Remaining</option>
                        <option value="unverified">Unverified Email</option>
                    </select>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-[10px] font-semibold text-slate-400 mb-1">SORT BY</label>
                    <select wire:model.live="sortBy" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2 text-xs text-slate-200 focus:outline-none focus:border-violet-500">
                        <option value="latest">Newest First</option>
                        <option value="oldest">Oldest First</option>
                        <option value="name_asc">Name (A–Z)</option>
                        <option value="quota_high">Highest Quota</option>
                        <option value="quota_low">Lowest Quota</option>
                        <option value="words_used">Most Words Consumed</option>
                    </select>
                </div>
            </div>

            <!-- Filter Badges & Quick Resets -->
            <div class="flex flex-wrap items-center justify-between gap-2 pt-1 border-t border-white/5 text-xs text-slate-400">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[11px] text-slate-500">Quick Selection:</span>
                    <button type="button" wire:click="selectAllOnPage" class="text-[11px] text-violet-400 hover:text-violet-300 underline cursor-pointer">Page ({{ count($users) }})</button>
                    <span class="text-slate-700">•</span>
                    <button type="button" wire:click="selectAllMatching" class="text-[11px] text-violet-400 hover:text-violet-300 underline cursor-pointer">All Matching ({{ $users->total() }})</button>
                    <span class="text-slate-700">•</span>
                    <button type="button" wire:click="selectAdmins" class="text-[11px] text-violet-400 hover:text-violet-300 underline cursor-pointer">Admins</button>
                    <span class="text-slate-700">•</span>
                    <button type="button" wire:click="selectInactive" class="text-[11px] text-violet-400 hover:text-violet-300 underline cursor-pointer">Banned</button>
                    <span class="text-slate-700">•</span>
                    <button type="button" wire:click="selectZeroQuota" class="text-[11px] text-violet-400 hover:text-violet-300 underline cursor-pointer">Zero Quota</button>
                    <span class="text-slate-700">•</span>
                    <button type="button" wire:click="invertSelection" class="text-[11px] text-violet-400 hover:text-violet-300 underline cursor-pointer">Invert</button>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] text-slate-500">Per page:</span>
                        <select wire:model.live="perPage" class="bg-slate-900 border border-white/10 rounded-lg px-2 py-1 text-xs text-slate-300 focus:outline-none">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>

                    @if($search !== '' || $selectedRole !== '' || $selectedPlan !== '' || $selectedStatus !== '' || $sortBy !== 'latest')
                        <button 
                            type="button" 
                            wire:click="clearFilters" 
                            class="px-2.5 py-1 rounded-lg bg-slate-800/80 hover:bg-slate-700 border border-white/10 text-[11px] text-slate-300 hover:text-white transition-all cursor-pointer"
                        >
                            ✕ Clear Filters
                        </button>
                    @endif
                </div>
            </div>
        </x-glass.card>

        <!-- FLOATING BULK SELECTION SUITE TOOLBAR -->
        @if(count($selectedUsers) > 0)
            <div class="p-3.5 rounded-2xl bg-gradient-to-r from-violet-950/90 via-slate-900/95 to-indigo-950/90 border border-violet-500/40 backdrop-blur-2xl shadow-2xl flex flex-wrap items-center justify-between gap-3 animate-fade-in">
                <!-- Left: Selected Count -->
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-xl bg-violet-600/30 border border-violet-400/40 flex items-center justify-center text-sm font-bold text-violet-300">
                        ✓
                    </span>
                    <div>
                        <div class="text-xs font-bold text-white flex items-center gap-2">
                            <span>{{ count($selectedUsers) }} Users Selected</span>
                            <span class="px-2 py-0.5 rounded-full bg-violet-500/20 text-violet-300 text-[10px] font-mono">Bulk Suite Active</span>
                        </div>
                        <div class="text-[10px] text-slate-400">Apply batch roles, quotas, plans, or lifecycle actions</div>
                    </div>
                </div>

                <!-- Right: Bulk Operations Actions -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Bulk Role Select -->
                    <div class="relative">
                        <select 
                            onchange="if(this.value){ $wire.bulkAssignRole(this.value); this.value=''; }" 
                            class="bg-slate-900/90 border border-violet-500/30 hover:border-violet-500/60 rounded-xl px-2.5 py-1.5 text-xs text-violet-200 font-semibold focus:outline-none cursor-pointer"
                        >
                            <option value="">Set Role ▾</option>
                            <option value="admin">Make Admin</option>
                            <option value="editor">Make Editor</option>
                            <option value="pro">Make Pro</option>
                            <option value="user">Make User</option>
                            <option value="member">Make Member</option>
                        </select>
                    </div>

                    <!-- Bulk Plan Select -->
                    <div class="relative">
                        <select 
                            onchange="if(this.value){ $wire.bulkChangePlan(this.value); this.value=''; }" 
                            class="bg-slate-900/90 border border-indigo-500/30 hover:border-indigo-500/60 rounded-xl px-2.5 py-1.5 text-xs text-indigo-200 font-semibold focus:outline-none cursor-pointer"
                        >
                            <option value="">Set Plan ▾</option>
                            <option value="starter">Starter Plan</option>
                            <option value="pro">Pro Plan</option>
                            <option value="enterprise">Enterprise Plan</option>
                        </select>
                    </div>

                    <!-- Quota Boost Presets -->
                    <button 
                        type="button"
                        wire:click="bulkGrantBonus(10000)"
                        class="px-2.5 py-1.5 rounded-xl bg-cyan-950/80 border border-cyan-500/30 hover:bg-cyan-900/90 text-cyan-300 text-xs font-bold transition-all cursor-pointer"
                        title="Grant +10,000 bonus words to selected users"
                    >
                        +10k Quota
                    </button>

                    <button 
                        type="button"
                        wire:click="bulkGrantBonus(50000)"
                        class="px-2.5 py-1.5 rounded-xl bg-cyan-950/80 border border-cyan-500/30 hover:bg-cyan-900/90 text-cyan-300 text-xs font-bold transition-all cursor-pointer"
                        title="Grant +50,000 bonus words to selected users"
                    >
                        +50k Quota
                    </button>

                    <!-- Reset Used Quota -->
                    <button 
                        type="button"
                        wire:click="bulkResetUsedQuota"
                        wire:confirm="Are you sure you want to reset the used quota counter to 0 for all selected users?"
                        class="px-2.5 py-1.5 rounded-xl bg-slate-800/80 border border-white/10 hover:border-amber-500/40 text-amber-300 text-xs font-semibold transition-all cursor-pointer"
                        title="Reset used quota to zero"
                    >
                        Reset Usage (0)
                    </button>

                    <!-- Status Actions -->
                    <button 
                        type="button"
                        wire:click="bulkToggleActive(true)"
                        class="px-2.5 py-1.5 rounded-xl bg-emerald-950/80 border border-emerald-500/30 hover:bg-emerald-900/80 text-emerald-300 text-xs font-semibold transition-all cursor-pointer"
                        title="Activate all selected accounts"
                    >
                        Activate
                    </button>

                    <button 
                        type="button"
                        wire:click="bulkToggleActive(false)"
                        wire:confirm="Suspend logins for all selected users?"
                        class="px-2.5 py-1.5 rounded-xl bg-amber-950/80 border border-amber-500/30 hover:bg-amber-900/80 text-amber-300 text-xs font-semibold transition-all cursor-pointer"
                        title="Suspend selected user logins"
                    >
                        Suspend
                    </button>

                    <!-- Delete Selected -->
                    <button 
                        type="button"
                        wire:click="bulkDeleteUsers"
                        wire:confirm="⚠️ DANGER: Permanently delete all selected users? This cannot be undone. (Your own admin account is safely protected)."
                        class="px-2.5 py-1.5 rounded-xl bg-rose-950/80 border border-rose-500/40 hover:bg-rose-900 text-rose-300 text-xs font-bold transition-all cursor-pointer"
                        title="Permanently delete selected users"
                    >
                        🗑️ Delete
                    </button>

                    <!-- Clear Selection -->
                    <button 
                        type="button"
                        wire:click="clearSelection"
                        class="px-2 py-1.5 rounded-xl text-xs text-slate-400 hover:text-white underline cursor-pointer"
                    >
                        Deselect All
                    </button>
                </div>
            </div>
        @endif

        <!-- Users Table -->
        <x-glass.card variant="standard" class="p-0 overflow-hidden border border-white/10">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900/90 text-slate-400 border-b border-white/10 uppercase text-[10px] tracking-wider font-semibold">
                        <tr>
                            <!-- Master Selection Checkbox -->
                            <th class="p-4 w-10 text-center">
                                <input 
                                    type="checkbox" 
                                    wire:click="toggleSelectAll"
                                    @checked(count($users) > 0 && collect($users->items())->pluck('id')->every(fn($id) => in_array($id, $selectedUsers)))
                                    class="rounded bg-slate-950 border-white/20 text-violet-600 focus:ring-violet-500/30 cursor-pointer"
                                    title="Toggle selection for all users on current page"
                                >
                            </th>
                            <th class="p-4">User Identity</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Subscription Plan</th>
                            <th class="p-4">Word Quota Limit</th>
                            <th class="p-4">Consumption & Remaining</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-200">
                        @forelse($users as $user)
                            @php
                                $isSelected = in_array($user->id, $selectedUsers);
                                $totalLimit = $user->monthly_word_quota + ($user->bonus_word_quota ?? 0);
                                $remaining = max(0, $totalLimit - $user->used_word_quota);
                                $percentUsed = $totalLimit > 0 ? min(100, round(($user->used_word_quota / $totalLimit) * 100)) : 0;
                            @endphp
                            <tr class="transition-colors hover:bg-white/[0.03] {{ $isSelected ? 'bg-violet-950/25 border-l-4 border-l-violet-500' : '' }}">
                                <!-- Checkbox -->
                                <td class="p-4 text-center">
                                    <input 
                                        type="checkbox" 
                                        wire:click="toggleUserSelection({{ $user->id }})"
                                        @checked($isSelected)
                                        class="rounded bg-slate-950 border-white/20 text-violet-600 focus:ring-violet-500/30 cursor-pointer"
                                    >
                                </td>

                                <!-- User Details -->
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 border border-white/10 flex items-center justify-center font-bold text-xs text-white shadow-inner flex-shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white flex items-center gap-1.5">
                                                <span>{{ $user->name }}</span>
                                                @if($user->id === auth()->id())
                                                    <span class="px-1.5 py-0.2 rounded bg-violet-500/20 text-violet-300 text-[9px] font-mono border border-violet-500/30">YOU</span>
                                                @endif
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono">{{ $user->email }}</div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-[10px] text-slate-500 font-mono">ID: #{{ $user->id }}</span>
                                                @if($user->email_verified_at)
                                                    <span class="text-[10px] text-emerald-400 font-medium flex items-center gap-0.5">✓ Verified</span>
                                                @else
                                                    <span class="text-[10px] text-amber-400 font-medium flex items-center gap-0.5">⚠ Unverified</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Badge -->
                                <td class="p-4">
                                    @php
                                        $roleBadgeVariant = match($user->role) {
                                            'admin' => 'violet',
                                            'editor' => 'purple',
                                            'pro' => 'cyan',
                                            'user' => 'emerald',
                                            'member' => 'subtle',
                                            default => 'standard',
                                        };
                                    @endphp
                                    <x-glass.badge :variant="$roleBadgeVariant">
                                        {{ ucfirst($user->role) }}
                                    </x-glass.badge>
                                </td>

                                <!-- Plan -->
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-lg bg-slate-900/80 border border-white/10 font-mono text-[11px] text-indigo-300 uppercase font-semibold">
                                        {{ $user->plan }}
                                    </span>
                                </td>

                                <!-- Quota Allocation -->
                                <td class="p-4 font-mono">
                                    <div class="text-white font-semibold">{{ number_format($user->monthly_word_quota) }} words</div>
                                    @if(($user->bonus_word_quota ?? 0) > 0)
                                        <div class="text-[10px] text-cyan-400 font-bold flex items-center gap-0.5 mt-0.5">
                                            <span>🎁</span>
                                            <span>+{{ number_format($user->bonus_word_quota) }} bonus</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Consumption & Remaining -->
                                <td class="p-4">
                                    <div class="flex items-center justify-between text-xs font-mono mb-1">
                                        <span class="text-slate-400">{{ number_format($user->used_word_quota) }} used</span>
                                        <span class="text-emerald-400 font-semibold">{{ number_format($remaining) }} left</span>
                                    </div>
                                    <!-- Progress Bar -->
                                    <div class="w-32 bg-slate-950 rounded-full h-1.5 border border-white/5 overflow-hidden">
                                        <div 
                                            class="h-full rounded-full transition-all duration-300 {{ $percentUsed > 90 ? 'bg-rose-500' : ($percentUsed > 70 ? 'bg-amber-500' : 'bg-emerald-400') }}"
                                            style="width: {{ $percentUsed }}%"
                                        ></div>
                                    </div>
                                    <div class="text-[9px] text-slate-500 font-mono mt-0.5">{{ $percentUsed }}% utilized</div>
                                </td>

                                <!-- Status Toggle -->
                                <td class="p-4">
                                    <button 
                                        type="button"
                                        wire:click="toggleActive({{ $user->id }})" 
                                        class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase transition-all cursor-pointer {{ $user->is_active ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/30 hover:bg-rose-500/20' }}"
                                        title="{{ $user->is_active ? 'Click to suspend account' : 'Click to activate account' }}"
                                    >
                                        {{ $user->is_active ? 'Active' : 'Banned' }}
                                    </button>
                                </td>

                                <!-- Inline Actions -->
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5" x-data="{ rowMenuOpen: false }">
                                        <!-- Quick +10k bonus -->
                                        <button 
                                            type="button" 
                                            wire:click="grantBonusQuota({{ $user->id }}, 10000)" 
                                            class="px-2 py-1 rounded-lg bg-cyan-950/80 border border-cyan-500/30 text-cyan-300 hover:bg-cyan-900 text-[10px] font-bold transition-all cursor-pointer"
                                            title="Grant +10,000 bonus words immediately"
                                        >
                                            +10k
                                        </button>

                                        <!-- Edit User Button -->
                                        <button 
                                            type="button"
                                            wire:click="openEditModal({{ $user->id }})"
                                            class="px-2.5 py-1 rounded-lg bg-violet-600/20 border border-violet-500/30 hover:bg-violet-600 hover:text-white text-violet-300 text-[11px] font-semibold transition-all cursor-pointer"
                                        >
                                            Edit
                                        </button>

                                        <!-- Row Actions Popover Menu -->
                                        <div class="relative">
                                            <button 
                                                type="button"
                                                x-on:click="rowMenuOpen = !rowMenuOpen"
                                                class="px-2 py-1 rounded-lg bg-slate-900 border border-white/10 hover:border-white/25 text-slate-300 text-[11px] cursor-pointer"
                                            >
                                                •••
                                            </button>

                                            <div 
                                                x-show="rowMenuOpen"
                                                x-on:click.outside="rowMenuOpen = false"
                                                x-transition:enter="transition ease-out duration-100"
                                                x-transition:enter-start="transform opacity-0 scale-95"
                                                x-transition:enter-end="transform opacity-100 scale-100"
                                                class="absolute right-0 mt-2 w-48 rounded-2xl bg-slate-900/98 border border-white/20 p-2 shadow-2xl z-[100] space-y-1 backdrop-blur-2xl text-left"
                                                style="display: none;"
                                            >
                                                <!-- Reset Used Quota -->
                                                <button 
                                                    type="button"
                                                    wire:click="resetUserQuota({{ $user->id }})"
                                                    x-on:click="rowMenuOpen = false"
                                                    class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-amber-300 hover:bg-amber-500/10 flex items-center gap-2 cursor-pointer"
                                                >
                                                    <span>🔄</span>
                                                    <span>Reset Quota (0)</span>
                                                </button>

                                                <!-- Verify Email if not verified -->
                                                @if(!$user->email_verified_at)
                                                    <button 
                                                        type="button"
                                                        wire:click="markEmailVerified({{ $user->id }})"
                                                        x-on:click="rowMenuOpen = false"
                                                        class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-emerald-300 hover:bg-emerald-500/10 flex items-center gap-2 cursor-pointer"
                                                    >
                                                        <span>✓</span>
                                                        <span>Mark Email Verified</span>
                                                    </button>
                                                @endif

                                                <!-- Resend Welcome -->
                                                <button 
                                                    type="button"
                                                    wire:click="resendWelcomeEmail({{ $user->id }})"
                                                    x-on:click="rowMenuOpen = false"
                                                    class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-slate-300 hover:bg-white/5 flex items-center gap-2 cursor-pointer"
                                                >
                                                    <span>✉️</span>
                                                    <span>Resend Welcome</span>
                                                </button>

                                                <!-- Permanent Delete -->
                                                @if($user->id !== auth()->id())
                                                    <div class="border-t border-white/10 my-1"></div>
                                                    <button 
                                                        type="button"
                                                        wire:click="deleteUser({{ $user->id }})"
                                                        wire:confirm="Are you sure you want to permanently delete user '{{ $user->name }}'? This action cannot be undone."
                                                        x-on:click="rowMenuOpen = false"
                                                        class="w-full px-2.5 py-1.5 rounded-lg text-left text-xs text-rose-400 hover:bg-rose-500/10 flex items-center gap-2 cursor-pointer"
                                                    >
                                                        <span>🗑️</span>
                                                        <span>Delete User</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-8 text-center text-slate-400">
                                    <div class="text-3xl mb-2">🔍</div>
                                    <div class="font-bold text-white text-sm">No Users Found</div>
                                    <div class="text-xs text-slate-400 mt-1">Try adjusting your search criteria or clearing filters.</div>
                                    <button 
                                        type="button" 
                                        wire:click="clearFilters"
                                        class="mt-3 px-3 py-1.5 rounded-xl bg-violet-600/30 border border-violet-500/40 text-violet-300 text-xs font-semibold hover:bg-violet-600 hover:text-white transition-all cursor-pointer"
                                    >
                                        Reset All Filters
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination Footer -->
            @if($users->hasPages())
                <div class="p-4 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400 font-mono">
                    <div>
                        Showing <span class="text-white font-bold">{{ $users->firstItem() ?? 0 }}</span> to <span class="text-white font-bold">{{ $users->lastItem() ?? 0 }}</span> of <span class="text-white font-bold">{{ $users->total() }}</span> users
                    </div>
                    <div class="flex items-center gap-2 font-sans">
                        @if($users->onFirstPage())
                            <span class="px-3 py-1.5 rounded-xl bg-slate-900/40 border border-white/5 text-slate-600 cursor-not-allowed text-xs">&larr; Previous</span>
                        @else
                            <button type="button" wire:click="previousPage" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-xs cursor-pointer transition-colors">&larr; Previous</button>
                        @endif

                        @if($users->hasMorePages())
                            <button type="button" wire:click="nextPage" class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-xs cursor-pointer transition-colors">Next &rarr;</button>
                        @else
                            <span class="px-3 py-1.5 rounded-xl bg-slate-900/40 border border-white/5 text-slate-600 cursor-not-allowed text-xs">Next &rarr;</span>
                        @endif
                    </div>
                </div>
            @endif
        </x-glass.card>
    @endif

    <!-- TAB 2: ROLES & PERMISSIONS MATRIX -->
    @if($activeTab === 'roles')
        <div class="space-y-6 animate-fade-in">
            <!-- Matrix Introduction Card -->
            <x-glass.card variant="subtle" class="p-5 border border-white/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🛡️ Roles, Access Limits & Quota Matrix</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-1">
                        Review predefined roles, system capabilities, default word quotas, and active account distribution across the HelpOfAi Studio workspace.
                    </p>
                </div>
                <button 
                    type="button" 
                    wire:click="$set('activeTab', 'users')"
                    class="px-3.5 py-1.5 rounded-xl bg-slate-900 border border-white/15 hover:border-violet-500/40 text-xs font-semibold text-slate-200 hover:text-white transition-all cursor-pointer flex items-center gap-1.5"
                >
                    <span>&larr; Back to User Directory</span>
                </button>
            </x-glass.card>

            <!-- 5 Role Matrix Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($rolesMatrix as $roleCard)
                    <x-glass.card variant="elevated" class="p-5 border border-white/10 hover:border-violet-500/30 transition-all flex flex-col justify-between relative overflow-hidden">
                        <!-- Top Accent Banner -->
                        <div>
                            <div class="flex items-center justify-between gap-2 pb-3 border-b border-white/5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-xl bg-slate-800/80 border border-white/10 flex items-center justify-center text-base">
                                        @if($roleCard['key'] === 'admin') 👑
                                        @elseif($roleCard['key'] === 'editor') ✍️
                                        @elseif($roleCard['key'] === 'pro') ⚡
                                        @elseif($roleCard['key'] === 'user') 👤
                                        @else 👥
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-bold text-white tracking-tight">{{ $roleCard['label'] }}</h4>
                                        <span class="text-[10px] font-mono text-slate-400 uppercase">Slug: {{ $roleCard['key'] }}</span>
                                    </div>
                                </div>
                                <x-glass.badge :variant="$roleCard['badgeVariant']">
                                    {{ $roleCard['userCount'] }} {{ Str::plural('User', $roleCard['userCount']) }}
                                </x-glass.badge>
                            </div>

                            <!-- Role Specs & Defaults -->
                            <div class="grid grid-cols-2 gap-2 my-3 p-2.5 rounded-xl bg-slate-950/60 border border-white/5 text-[11px] font-mono">
                                <div>
                                    <span class="text-slate-500 block text-[9px] uppercase">Default Quota:</span>
                                    <span class="text-white font-bold">{{ number_format($roleCard['defaultQuota']) }} words</span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[9px] uppercase">Default Tier:</span>
                                    <span class="text-indigo-300 font-bold uppercase">{{ $roleCard['defaultPlan'] }}</span>
                                </div>
                                <div class="col-span-2 pt-1 border-t border-white/5 flex items-center justify-between text-[10px]">
                                    <span class="text-slate-400">Total Allocated:</span>
                                    <span class="text-cyan-300 font-bold">{{ number_format($roleCard['allocatedWords']) }} words</span>
                                </div>
                            </div>

                            <!-- Capabilities Checklist -->
                            <div class="space-y-2 mt-4">
                                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Granted Capabilities:</div>
                                <ul class="space-y-1.5 text-xs text-slate-300">
                                    @foreach($roleCard['capabilities'] as $cap)
                                        <li class="flex items-start gap-2">
                                            <span class="text-emerald-400 text-xs mt-0.5">✓</span>
                                            <span class="leading-snug text-slate-300 text-[11px]">{{ $cap }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Footer Filter Trigger -->
                        <div class="pt-4 mt-4 border-t border-white/5">
                            <button 
                                type="button"
                                wire:click="filterByRole('{{ $roleCard['key'] }}')"
                                class="w-full py-2 rounded-xl bg-slate-900 border border-white/10 hover:border-violet-500/40 text-xs font-semibold text-slate-200 hover:text-white transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                            >
                                <span>Filter {{ $roleCard['label'] }} Users</span>
                                <span class="text-[10px] font-mono text-violet-400">({{ $roleCard['userCount'] }}) &rarr;</span>
                            </button>
                        </div>
                    </x-glass.card>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- UPGRADED EDIT USER MODAL (Multi-Tab Dark Glassmorphic Dialog)             -->
    <!-- ========================================================================= -->
    <div 
        x-data="{ show: @entangle('showEditModal') }" 
        x-show="show" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4" 
        style="display: none;"
    >
        <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md" x-on:click="show = false"></div>
        <x-glass.card variant="elevated" class="w-full max-w-2xl p-0 z-10 border border-violet-500/30 shadow-2xl relative overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Modal Header with User Identity Summary -->
            <div class="p-6 bg-gradient-to-r from-violet-950/40 via-slate-900/80 to-indigo-950/40 border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 border border-white/20 flex items-center justify-center font-black text-white text-base shadow-lg shadow-violet-500/25">
                        {{ strtoupper(substr($name ?: 'U', 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-white flex items-center gap-2">
                            <span>{{ $name ?: 'Edit User Account' }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-violet-500/20 text-violet-300 text-[10px] font-mono border border-violet-500/30">
                                #{{ $editingUserId }}
                            </span>
                        </h3>
                        <p class="text-xs text-slate-400 font-mono">{{ $email }}</p>
                    </div>
                </div>

                <button 
                    type="button" 
                    x-on:click="show = false"
                    class="w-8 h-8 rounded-xl bg-slate-900 border border-white/10 hover:border-white/25 text-slate-400 hover:text-white flex items-center justify-center text-sm cursor-pointer transition-all"
                >
                    ✕
                </button>
            </div>

            <!-- Modal Sub-Tabs Strip -->
            <div class="flex items-center gap-2 px-6 pt-3 pb-0 border-b border-white/5 bg-slate-950/40 text-xs">
                <button 
                    type="button" 
                    wire:click="$set('editActiveTab', 'profile')"
                    class="pb-2 px-2 font-semibold border-b-2 transition-all cursor-pointer {{ $editActiveTab === 'profile' ? 'border-violet-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200' }}"
                >
                    👤 Profile
                </button>
                <button 
                    type="button" 
                    wire:click="$set('editActiveTab', 'role')"
                    class="pb-2 px-2 font-semibold border-b-2 transition-all cursor-pointer {{ $editActiveTab === 'role' ? 'border-violet-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200' }}"
                >
                    🛡️ Role & Plan
                </button>
                <button 
                    type="button" 
                    wire:click="$set('editActiveTab', 'quota')"
                    class="pb-2 px-2 font-semibold border-b-2 transition-all cursor-pointer {{ $editActiveTab === 'quota' ? 'border-violet-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200' }}"
                >
                    ⚡ Quota Engine
                </button>
                <button 
                    type="button" 
                    wire:click="$set('editActiveTab', 'security')"
                    class="pb-2 px-2 font-semibold border-b-2 transition-all cursor-pointer {{ $editActiveTab === 'security' ? 'border-violet-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200' }}"
                >
                    🔒 Security & Status
                </button>
            </div>

            <!-- Modal Form Body -->
            <form wire:submit="saveUser" class="flex-1 overflow-y-auto p-6 space-y-5">
                <!-- TAB: PROFILE -->
                @if($editActiveTab === 'profile')
                    <div class="space-y-4 animate-fade-in">
                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">Full Name</label>
                            <x-glass.input wire:model="name" required :error="$errors->has('name')" placeholder="John Doe" />
                            @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">Email Address</label>
                            <x-glass.input wire:model="email" type="email" required :error="$errors->has('email')" placeholder="user@example.com" />
                            @error('email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($user_created_at)
                            <div class="p-3 rounded-xl bg-slate-900/60 border border-white/5 text-xs text-slate-400">
                                <span>📅 Account Created:</span>
                                <span class="font-mono text-slate-200 ml-1">{{ $user_created_at }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- TAB: ROLE & PLAN -->
                @if($editActiveTab === 'role')
                    <div class="space-y-4 animate-fade-in">
                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">User System Role</label>
                            <select wire:model="role" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                                @foreach($roles as $r)
                                    <option value="{{ $r->value }}">{{ $r->label() }} ({{ $r->value }})</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Determines system permissions, access to admin panels, and global editing capabilities.
                            </p>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">Subscription Plan Tier</label>
                            <select wire:model="plan" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                                <option value="starter">Starter Plan</option>
                                <option value="pro">Pro Plan</option>
                                <option value="enterprise">Enterprise Plan</option>
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1">
                                Controls available AI models, document export features, and workspace limits.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- TAB: QUOTA ENGINE -->
                @if($editActiveTab === 'quota')
                    <div class="space-y-5 animate-fade-in">
                        <!-- Real-time Quota Bar -->
                        @php
                            $modalTotalLimit = (int)$monthly_word_quota + (int)$bonus_word_quota;
                            $modalRemaining = max(0, $modalTotalLimit - (int)$used_word_quota);
                            $modalPctUsed = $modalTotalLimit > 0 ? min(100, round(((int)$used_word_quota / $modalTotalLimit) * 100)) : 0;
                        @endphp
                        <div class="p-4 rounded-xl bg-slate-950/80 border border-violet-500/20 space-y-2">
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span class="text-slate-400">Total Word Capacity:</span>
                                <span class="text-cyan-300 font-bold">{{ number_format($modalTotalLimit) }} words</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span class="text-slate-400">Words Consumed:</span>
                                <span class="text-white font-bold">{{ number_format((int)$used_word_quota) }} ({{ $modalPctUsed }}%)</span>
                            </div>
                            <div class="flex items-center justify-between text-xs font-mono">
                                <span class="text-slate-400">Remaining Available:</span>
                                <span class="text-emerald-400 font-bold">{{ number_format($modalRemaining) }} words</span>
                            </div>
                            <div class="w-full bg-slate-900 rounded-full h-2 border border-white/5 overflow-hidden mt-2">
                                <div class="h-full rounded-full transition-all duration-300 {{ $modalPctUsed > 90 ? 'bg-rose-500' : ($modalPctUsed > 70 ? 'bg-amber-500' : 'bg-emerald-400') }}" style="width: {{ $modalPctUsed }}%"></div>
                            </div>
                        </div>

                        <!-- Numeric Inputs -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-slate-300 block mb-1">Monthly Quota</label>
                                <x-glass.input wire:model="monthly_word_quota" type="number" required />
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-300 block mb-1">Bonus Quota</label>
                                <x-glass.input wire:model="bonus_word_quota" type="number" required />
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-300 block mb-1">Used Words</label>
                                <x-glass.input wire:model="used_word_quota" type="number" required />
                            </div>
                        </div>

                        <!-- Instant Action Presets -->
                        <div class="space-y-2 pt-2 border-t border-white/5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Instant Quota Boosters & Reset</span>
                            <div class="flex flex-wrap items-center gap-2">
                                <button 
                                    type="button"
                                    wire:click="modalGrantBonus(10000)"
                                    class="px-2.5 py-1.5 rounded-lg bg-cyan-950/80 border border-cyan-500/30 text-cyan-300 hover:bg-cyan-900 text-xs font-bold transition-all cursor-pointer"
                                >
                                    +10,000 Bonus
                                </button>
                                <button 
                                    type="button"
                                    wire:click="modalGrantBonus(50000)"
                                    class="px-2.5 py-1.5 rounded-lg bg-cyan-950/80 border border-cyan-500/30 text-cyan-300 hover:bg-cyan-900 text-xs font-bold transition-all cursor-pointer"
                                >
                                    +50,000 Bonus
                                </button>
                                <button 
                                    type="button"
                                    wire:click="modalResetUsed"
                                    class="px-2.5 py-1.5 rounded-lg bg-amber-950/80 border border-amber-500/30 text-amber-300 hover:bg-amber-900 text-xs font-bold transition-all cursor-pointer"
                                >
                                    Reset Used to 0
                                </button>
                                <button 
                                    type="button"
                                    wire:click="modalSetPlanQuota(15000)"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-white/10 text-slate-300 hover:text-white text-xs cursor-pointer"
                                >
                                    Apply Starter (15k)
                                </button>
                                <button 
                                    type="button"
                                    wire:click="modalSetPlanQuota(100000)"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-900 border border-white/10 text-slate-300 hover:text-white text-xs cursor-pointer"
                                >
                                    Apply Pro (100k)
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- TAB: SECURITY & STATUS -->
                @if($editActiveTab === 'security')
                    <div class="space-y-4 animate-fade-in">
                        <div>
                            <label class="text-xs font-semibold text-slate-300 block mb-1.5">Reset Password (Optional)</label>
                            <x-glass.input wire:model="new_password" type="password" placeholder="Leave blank to keep existing password" />
                            <p class="text-[11px] text-slate-400 mt-1">If provided, must be at least 8 characters long.</p>
                        </div>

                        <div class="pt-2 space-y-3">
                            <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-900/60 border border-white/5 cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="mt-0.5 rounded bg-slate-950 border-white/20 text-violet-600 focus:ring-violet-500/30">
                                <div>
                                    <span class="text-xs font-bold text-white block">Account is Active</span>
                                    <span class="text-[11px] text-slate-400">Allow this user to authenticate and access the HelpOfAi workspace.</span>
                                </div>
                            </label>

                            <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-900/60 border border-white/5 cursor-pointer">
                                <input type="checkbox" wire:model="email_verified" class="mt-0.5 rounded bg-slate-950 border-white/20 text-violet-600 focus:ring-violet-500/30">
                                <div>
                                    <span class="text-xs font-bold text-white block">Email Address is Verified</span>
                                    <span class="text-[11px] text-slate-400">Mark the user's email address as verified without requiring them to click a verification link.</span>
                                </div>
                            </label>
                        </div>
                    </div>
                @endif

                <!-- Modal Footer -->
                <div class="flex items-center justify-between gap-3 pt-4 border-t border-white/10">
                    <x-glass.button type="button" variant="secondary" size="sm" x-on:click="show = false">
                        Cancel
                    </x-glass.button>
                    <x-glass.button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="saveUser">Save User Changes</span>
                        <span wire:loading wire:target="saveUser">Saving...</span>
                    </x-glass.button>
                </div>
            </form>
        </x-glass.card>
    </div>

    <!-- ========================================================================= -->
    <!-- CREATE USER MODAL                                                         -->
    <!-- ========================================================================= -->
    <div 
        x-data="{ show: @entangle('showCreateModal') }" 
        x-show="show" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4" 
        style="display: none;"
    >
        <div class="fixed inset-0 bg-slate-950/85 backdrop-blur-md" x-on:click="show = false"></div>
        <x-glass.card variant="elevated" class="w-full max-w-lg p-6 sm:p-8 z-10 border border-emerald-500/30 shadow-2xl relative">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span>➕</span>
                <span>Create New User Account</span>
            </h3>

            <form wire:submit="createUser" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Full Name</label>
                        <x-glass.input wire:model="new_user_name" placeholder="John Doe" required :error="$errors->has('new_user_name')" />
                        @error('new_user_name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Email Address</label>
                        <x-glass.input wire:model="new_user_email" type="email" placeholder="john@example.com" required :error="$errors->has('new_user_email')" />
                        @error('new_user_email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Initial Password</label>
                    <x-glass.input wire:model="new_user_password" type="password" placeholder="Minimum 8 characters" required :error="$errors->has('new_user_password')" />
                    @error('new_user_password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">User Role</label>
                        <select wire:model="new_user_role" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                            @foreach($roles as $r)
                                <option value="{{ $r->value }}">{{ $r->label() }} ({{ $r->value }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-slate-300 block mb-1.5">Subscription Plan</label>
                        <select wire:model="new_user_plan" class="w-full bg-slate-900 border border-white/10 rounded-xl px-3 py-2.5 text-xs text-slate-100 focus:outline-none focus:border-violet-500">
                            <option value="starter">Starter</option>
                            <option value="pro">Pro</option>
                            <option value="enterprise">Enterprise</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-medium text-slate-300 block mb-1.5">Monthly Word Quota</label>
                    <x-glass.input wire:model="new_user_quota" type="number" required />
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10">
                    <x-glass.button type="button" variant="secondary" size="sm" x-on:click="show = false">
                        Cancel
                    </x-glass.button>
                    <x-glass.button type="submit" variant="primary" size="sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createUser">Create Account</span>
                        <span wire:loading wire:target="createUser">Creating...</span>
                    </x-glass.button>
                </div>
            </form>
        </x-glass.card>
    </div>
</div>