{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Notification Center Bell Dropdown
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

<div class="relative hoa-notification-bell-container" x-data="{ open: false }" @click.outside="open = false">
    <!-- Bell Button with Dynamic Badge -->
    <button 
        type="button" 
        @click="open = !open" 
        class="relative flex items-center justify-center w-9 h-9 rounded-xl bg-slate-900/80 hover:bg-slate-800 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white transition-all cursor-pointer shadow-sm hover:scale-105 active:scale-95"
        title="Notifications Center"
    >
        <span class="text-base">🔔</span>

        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-600 text-[9px] font-bold text-white shadow-lg shadow-rose-500/50 animate-pulse border border-slate-950">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Glassmorphic Dropdown Panel -->
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
        class="absolute right-0 mt-3 w-80 sm:w-96 rounded-3xl bg-slate-950/95 border border-white/15 shadow-[0_20px_60px_rgba(0,0,0,0.8)] backdrop-blur-2xl z-50 overflow-hidden font-sans text-xs select-none"
        style="display: none;"
    >
        <!-- Header -->
        <div class="px-4 py-3.5 bg-slate-900/80 border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm">🔔</span>
                <h4 class="font-bold text-white text-xs">Notifications</h4>
                @if ($unreadCount > 0)
                    <span class="px-1.5 py-0.5 rounded-md bg-rose-500/20 text-rose-300 text-[10px] font-bold border border-rose-500/30">
                        {{ $unreadCount }} new
                    </span>
                @endif
            </div>

            @if ($notifications->isNotEmpty())
                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        wire:click="markAllAsRead" 
                        class="text-[10px] text-indigo-400 hover:text-indigo-300 font-semibold transition-colors"
                    >
                        Mark all read
                    </button>
                    <span class="text-slate-600">•</span>
                    <button 
                        type="button" 
                        wire:click="clearAll" 
                        class="text-[10px] text-slate-400 hover:text-rose-400 transition-colors"
                    >
                        Clear
                    </button>
                </div>
            @endif
        </div>

        <!-- Notification List -->
        <div class="max-h-80 overflow-y-auto divide-y divide-white/5 scrollbar-none">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $severity = $data['severity'] ?? ($data['type'] ?? 'info');
                    $icon = match($severity) {
                        'critical', 'error' => '🚨',
                        'warning' => '⚠️',
                        'success' => '✨',
                        default => '📢'
                    };
                @endphp
                <div 
                    wire:click="markAsRead('{{ $notification->id }}')"
                    class="p-3.5 hover:bg-white/5 transition-all cursor-pointer flex gap-3 items-start {{ $isUnread ? 'bg-indigo-500/5' : '' }}"
                >
                    <div class="text-base p-1.5 rounded-xl bg-slate-900 border border-white/10 shrink-0">
                        {{ $icon }}
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <h5 class="font-bold text-white text-[11px] truncate {{ $isUnread ? 'text-indigo-200' : '' }}">
                                {{ $data['title'] ?? 'Notification' }}
                            </h5>
                            <span class="text-[9px] text-slate-500 whitespace-nowrap">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <p class="text-[11px] text-slate-400 line-clamp-2 leading-relaxed">
                            {{ $data['description'] ?? '' }}
                        </p>

                        @if (!empty($data['action_url']))
                            <div class="mt-2">
                                <a 
                                    href="{{ $data['action_url'] }}" 
                                    class="inline-flex items-center gap-1 text-[10px] text-indigo-400 hover:text-indigo-300 font-bold"
                                    @click.stop
                                >
                                    <span>{{ $data['action_text'] ?? 'View Details' }}</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    @if ($isUnread)
                        <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0 mt-1 shadow-[0_0_8px_#6366f1]"></span>
                    @endif
                </div>
            @empty
                <div class="py-10 px-4 text-center text-slate-500 space-y-1">
                    <div class="text-2xl mb-1">✨</div>
                    <p class="text-xs font-semibold text-slate-400">All caught up!</p>
                    <p class="text-[10px] text-slate-500">No new notifications at this time.</p>
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        @if (auth()->user()->isAdmin())
            <div class="px-4 py-2.5 bg-slate-900/60 border-t border-white/10 text-center">
                <a href="{{ route('admin.mail-notifications') }}" wire:navigate class="text-[10px] text-slate-400 hover:text-violet-300 font-semibold transition-colors">
                    ⚙️ Configure Mail & Notification Channels &rarr;
                </a>
            </div>
        @endif
    </div>
</div>
