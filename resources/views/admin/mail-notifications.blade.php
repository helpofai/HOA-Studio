{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Mail & Notification Control Center
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

<div class="hoa-mail-notifications-container space-y-6">

    <!-- Header & Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="p-2 rounded-xl bg-violet-500/10 border border-violet-500/20 text-violet-400 text-xl">📬</span>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Mail Server & Notifications Engine</h1>
                    <p class="text-xs text-slate-400">Configure SMTP/API delivery gateways, trigger-based security alerts, and system-wide broadcast dispatches.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <x-glass.button wire:click="$set('activeTab', 'test_delivery')" variant="secondary" size="sm" class="flex items-center gap-1.5 border-emerald-500/30 text-emerald-300 hover:bg-emerald-500/10">
                <span>⚡</span>
                <span>Send Test Email</span>
            </x-glass.button>

            <x-glass.button wire:click="$set('activeTab', 'broadcast')" variant="primary" size="sm" class="flex items-center gap-1.5 shadow-lg shadow-violet-500/25">
                <span>📢</span>
                <span>Dispatch Broadcast</span>
            </x-glass.button>
        </div>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 flex items-center justify-between shadow-lg shadow-emerald-500/5">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('status') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">✕</button>
        </div>
    @endif

    @if (session('test_success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-xs text-emerald-300 flex items-center justify-between shadow-lg shadow-emerald-500/5">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('test_success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">✕</button>
        </div>
    @endif

    @if (session('test_error'))
        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-300 flex items-center justify-between shadow-lg shadow-rose-500/5">
            <div class="flex items-center gap-2">
                <span>⚠️</span>
                <span>{{ session('test_error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-400 hover:text-rose-200">✕</button>
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Active Gateway Driver -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-violet-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Active Mailer</span>
                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-400 text-sm">🚀</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold font-mono uppercase text-white">{{ $mail_mailer }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Runtime delivery driver</p>
        </x-glass.card>

        <!-- Total Notifications Delivered -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-indigo-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Delivered In-App</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm">🔔</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-white">{{ number_format($totalNotificationsDelivered) }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Platform notification events</p>
        </x-glass.card>

        <!-- Unread Notifications -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-amber-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Unread Alerts</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-400 text-sm">📬</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold text-amber-400">{{ number_format($unreadNotificationsCount) }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Awaiting user acknowledgement</p>
        </x-glass.card>

        <!-- Encryption Status -->
        <x-glass.card variant="subtle" class="p-4 border-white/10 hover:border-emerald-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Transport Security</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 text-sm">🔒</span>
            </div>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl font-bold uppercase text-emerald-400">{{ $mail_encryption }}</span>
            </div>
            <p class="text-[10px] text-slate-500 mt-1">Port: {{ $mail_port }} / {{ $mail_from_address }}</p>
        </x-glass.card>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-white/10 pb-3 overflow-x-auto scrollbar-none">
        <button 
            type="button" 
            wire:click="$set('activeTab', 'mail_server')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'mail_server' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            ⚙️ Mail Server Gateway
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'templates')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'templates' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            📑 System Email Templates
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'notification_channels')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'notification_channels' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            🔔 Security Triggers & Channels
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'broadcast')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'broadcast' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            📢 Broadcast Announcements
        </button>

        <button 
            type="button" 
            wire:click="$set('activeTab', 'test_delivery')"
            class="px-4 py-2 rounded-xl text-xs font-semibold transition-all whitespace-nowrap {{ $activeTab === 'test_delivery' ? 'bg-violet-600 text-white shadow-lg shadow-violet-500/25' : 'text-slate-400 hover:text-white hover:bg-white/5' }}"
        >
            🧪 Test & Diagnostic Prober
        </button>
    </div>

    <!-- TAB 1: MAIL SERVER GATEWAY CONFIGURATION -->
    @if ($activeTab === 'mail_server')
        <form wire:submit="saveMailConfig" class="space-y-6">
            <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🚀</span>
                        <span>Mail Gateway Driver Selection</span>
                    </h3>
                    <p class="text-xs text-slate-400">Choose your preferred email delivery protocol or API service.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach (['smtp' => 'Custom SMTP', 'resend' => 'Resend API', 'mailgun' => 'Mailgun', 'postmark' => 'Postmark', 'ses' => 'Amazon SES', 'log' => 'Dev Log Mode'] as $driverKey => $driverLabel)
                        <label class="p-3.5 rounded-2xl border transition-all cursor-pointer flex flex-col items-center justify-center text-center gap-1.5 {{ $mail_mailer === $driverKey ? 'bg-violet-600/20 border-violet-500 text-white shadow-lg shadow-violet-500/20 ring-1 ring-violet-500' : 'bg-slate-900/60 border-white/10 text-slate-400 hover:border-white/20 hover:text-white' }}">
                            <input type="radio" wire:model.live="mail_mailer" value="{{ $driverKey }}" class="sr-only">
                            <span class="text-lg">
                                @if ($driverKey === 'smtp') 📬
                                @elseif ($driverKey === 'resend') ⚡
                                @elseif ($driverKey === 'mailgun') 🔫
                                @elseif ($driverKey === 'postmark') ✉️
                                @elseif ($driverKey === 'ses') ☁️
                                @else 📝 @endif
                            </span>
                            <span class="text-xs font-bold">{{ $driverLabel }}</span>
                        </label>
                    @endforeach
                </div>

                <!-- Global Sender Details -->
                <div class="pt-4 border-t border-white/10 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sender Email Address (From)</label>
                        <x-glass.input 
                            wire:model="mail_from_address"
                            type="email"
                            placeholder="support@helpofai.com"
                            :error="$errors->has('mail_from_address')"
                        />
                        @error('mail_from_address') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Sender Name</label>
                        <x-glass.input 
                            wire:model="mail_from_name"
                            type="text"
                            placeholder="HelpOfAi Studio"
                            :error="$errors->has('mail_from_name')"
                        />
                        @error('mail_from_name') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Driver-Specific Fields -->
                @if ($mail_mailer === 'smtp')
                    <div class="pt-4 border-t border-white/10 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">SMTP Server Credentials</h4>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                            <div class="md:col-span-2">
                                <label class="block text-slate-300 font-semibold mb-1">SMTP Host</label>
                                <x-glass.input 
                                    wire:model="mail_host"
                                    type="text"
                                    placeholder="smtp.mailtrap.io or smtp.gmail.com"
                                    :error="$errors->has('mail_host')"
                                />
                            </div>

                            <div>
                                <label class="block text-slate-300 font-semibold mb-1">SMTP Port</label>
                                <x-glass.input 
                                    wire:model="mail_port"
                                    type="number"
                                    placeholder="587"
                                    :error="$errors->has('mail_port')"
                                />
                            </div>

                            <div>
                                <label class="block text-slate-300 font-semibold mb-1">Encryption Protocol</label>
                                <select 
                                    wire:model="mail_encryption"
                                    class="w-full bg-slate-950 border border-white/15 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-violet-500 focus:border-violet-500"
                                >
                                    <option value="tls">TLS (Recommended - Port 587)</option>
                                    <option value="ssl">SSL (Port 465)</option>
                                    <option value="none">None (Plaintext - Port 25)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-slate-300 font-semibold mb-1">SMTP Username</label>
                                <x-glass.input 
                                    wire:model="mail_username"
                                    type="text"
                                    placeholder="username or api key"
                                />
                            </div>

                            <div>
                                <label class="block text-slate-300 font-semibold mb-1">SMTP Password</label>
                                <x-glass.input 
                                    wire:model="mail_password"
                                    type="password"
                                    placeholder="••••••••••••"
                                />
                            </div>
                        </div>
                    </div>
                @elseif ($mail_mailer === 'resend')
                    <div class="pt-4 border-t border-white/10 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Resend API Configuration</h4>
                        <div>
                            <label class="block text-xs text-slate-300 font-semibold mb-1">Resend API Key</label>
                            <x-glass.input 
                                wire:model="mail_resend_api_key"
                                type="password"
                                placeholder="re_123456789..."
                            />
                        </div>
                    </div>
                @elseif ($mail_mailer === 'mailgun')
                    <div class="pt-4 border-t border-white/10 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Mailgun API Configuration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs text-slate-300 font-semibold mb-1">Mailgun Domain</label>
                                <x-glass.input wire:model="mail_mailgun_domain" type="text" placeholder="mg.yourdomain.com" />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 font-semibold mb-1">Mailgun Secret Key</label>
                                <x-glass.input wire:model="mail_mailgun_secret" type="password" placeholder="key-..." />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 font-semibold mb-1">Endpoint</label>
                                <x-glass.input wire:model="mail_mailgun_endpoint" type="text" placeholder="api.mailgun.net" />
                            </div>
                        </div>
                    </div>
                @elseif ($mail_mailer === 'postmark')
                    <div class="pt-4 border-t border-white/10 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Postmark Configuration</h4>
                        <div>
                            <label class="block text-xs text-slate-300 font-semibold mb-1">Postmark Server Token</label>
                            <x-glass.input wire:model="mail_postmark_token" type="password" placeholder="xxxx-xxxx-xxxx-xxxx" />
                        </div>
                    </div>
                @elseif ($mail_mailer === 'ses')
                    <div class="pt-4 border-t border-white/10 space-y-4">
                        <h4 class="text-xs font-bold text-indigo-300 uppercase tracking-wider">Amazon SES Configuration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs text-slate-300 font-semibold mb-1">AWS Access Key</label>
                                <x-glass.input wire:model="mail_ses_key" type="text" placeholder="AKIA..." />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 font-semibold mb-1">AWS Secret Key</label>
                                <x-glass.input wire:model="mail_ses_secret" type="password" placeholder="Secret Key..." />
                            </div>
                            <div>
                                <label class="block text-xs text-slate-300 font-semibold mb-1">AWS Region</label>
                                <x-glass.input wire:model="mail_ses_region" type="text" placeholder="us-east-1" />
                            </div>
                        </div>
                    </div>
                @endif

                <div class="pt-4 border-t border-white/10 flex items-center justify-between">
                    <span class="text-[11px] text-slate-500">Settings take effect immediately across all application notification jobs.</span>
                    <x-glass.button type="submit" variant="primary" size="sm" class="shadow-lg shadow-violet-500/25">
                        <span wire:loading.remove wire:target="saveMailConfig">💾 Save Mail Gateway</span>
                        <span wire:loading wire:target="saveMailConfig">Saving...</span>
                    </x-glass.button>
                </div>
            </x-glass.card>
        </form>
    @endif

    <!-- TAB: SYSTEM EMAIL TEMPLATES MANAGER -->
    @if ($activeTab === 'templates')
        @php
            $templatesList = \App\Features\Admin\Services\MailTemplateService::getTemplates();
            $currentTpl = $templatesList[$selectedTemplateKey] ?? null;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left Sidebar: Templates Selector -->
            <div class="lg:col-span-4 space-y-3">
                <x-glass.card variant="elevated" class="p-4 border-white/15 space-y-2">
                    <div class="px-2 py-1">
                        <h4 class="text-xs font-bold text-white uppercase tracking-wider">System Templates</h4>
                        <p class="text-[11px] text-slate-400">Select a template to customize content and placeholders.</p>
                    </div>

                    <div class="space-y-1.5 pt-2">
                        @foreach ($templatesList as $tKey => $tData)
                            <button 
                                type="button" 
                                wire:click="selectTemplate('{{ $tKey }}')"
                                class="w-full text-left p-3 rounded-2xl border transition-all flex items-start gap-3 cursor-pointer {{ $selectedTemplateKey === $tKey ? 'bg-violet-600/20 border-violet-500 text-white shadow-lg shadow-violet-500/10 ring-1 ring-violet-500' : 'bg-slate-900/60 border-white/10 text-slate-300 hover:bg-white/5 hover:border-white/20' }}"
                            >
                                <span class="text-xl p-1.5 rounded-xl bg-slate-900 border border-white/10 shrink-0">{{ $tData['icon'] }}</span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1">
                                        <h5 class="text-xs font-bold truncate">{{ $tData['name'] }}</h5>
                                    </div>
                                    <span class="text-[10px] text-slate-400 block line-clamp-1 mt-0.5">{{ $tData['category'] }}</span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </x-glass.card>
            </div>

            <!-- Right Area: Template Visual Editor & Placeholders -->
            <div class="lg:col-span-8 space-y-6">
                @if ($currentTpl)
                    <form wire:submit="saveTemplate" class="space-y-6">
                        <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-6">
                            <!-- Template Header & Actions -->
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-white/10">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl p-2 rounded-2xl bg-violet-500/10 border border-violet-500/20">{{ $currentTpl['icon'] }}</span>
                                    <div>
                                        <h3 class="text-base font-bold text-white">{{ $currentTpl['name'] }}</h3>
                                        <p class="text-xs text-slate-400">{{ $currentTpl['description'] }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button 
                                        type="button" 
                                        wire:click="previewTemplate"
                                        class="px-3 py-1.5 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/20 text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer shadow-sm"
                                    >
                                        <span>👁️</span>
                                        <span>Live HTML Preview</span>
                                    </button>

                                    <button 
                                        type="button"
                                        x-data="{ copiedTpl: false }"
                                        x-on:click="navigator.clipboard.writeText($wire.template_body); copiedTpl = true; setTimeout(() => copiedTpl = false, 2000)"
                                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 hover:border-violet-500/40 text-slate-300 hover:text-white text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer"
                                        title="Copy body text to clipboard"
                                    >
                                        <span x-show="!copiedTpl">📋 Copy Body</span>
                                        <span x-show="copiedTpl" class="text-emerald-400 font-bold">✓ Copied!</span>
                                    </button>

                                    <button 
                                        type="button" 
                                        wire:click="resetTemplateToDefault"
                                        class="px-3 py-1.5 rounded-xl bg-slate-900 border border-white/10 text-slate-400 hover:text-rose-300 hover:border-rose-500/30 text-xs font-semibold transition-all cursor-pointer"
                                        title="Restore default copy"
                                    >
                                        <span>↺ Defaults</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Subject Line -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Subject Line</label>
                                <x-glass.input 
                                    wire:model="template_subject"
                                    type="text"
                                    placeholder="Subject line..."
                                    :error="$errors->has('template_subject')"
                                />
                                @error('template_subject') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Heading Line -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Main Heading (H1 Inside Email)</label>
                                <x-glass.input 
                                    wire:model="template_heading"
                                    type="text"
                                    placeholder="Heading..."
                                    :error="$errors->has('template_heading')"
                                />
                                @error('template_heading') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Message Body -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Email Body Content (Markdown & Linebreaks Supported)</label>
                                <textarea 
                                    wire:model="template_body"
                                    rows="7"
                                    class="w-full bg-slate-950/80 border border-white/15 rounded-2xl p-4 text-xs font-mono text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all outline-none leading-relaxed"
                                ></textarea>
                                @error('template_body') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <!-- Action Button Text & URL -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Call-To-Action Button Text</label>
                                    <x-glass.input 
                                        wire:model="template_action_text"
                                        type="text"
                                        placeholder="e.g. Open Workspace"
                                    />
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Action Button Target URL</label>
                                    <x-glass.input 
                                        wire:model="template_action_url"
                                        type="text"
                                        placeholder="e.g. {app_url}/dashboard"
                                    />
                                </div>
                            </div>

                            <!-- Dynamic Placeholders Legend & 1-Click Copy -->
                            <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-indigo-300 uppercase tracking-wider flex items-center gap-1.5">
                                        <span>🧩</span>
                                        <span>Available Dynamic Placeholders (Click code to copy)</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400">Click any variable below to insert/copy</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                    @foreach ($currentTpl['placeholders'] as $tag => $tagDesc)
                                        <div 
                                            x-data="{ copied: false }"
                                            x-on:click="navigator.clipboard.writeText('{{ $tag }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="flex items-center justify-between p-2 rounded-xl bg-slate-950/70 border border-white/5 hover:border-violet-500/40 hover:bg-slate-900 transition-all cursor-pointer group"
                                            title="Click to copy {{ $tag }}"
                                        >
                                            <div class="flex items-center gap-2 min-w-0">
                                                <code class="text-violet-300 font-bold font-mono group-hover:text-cyan-300 transition-colors">{{ $tag }}</code>
                                                <span class="text-slate-400 truncate text-[10px]">&rarr; {{ $tagDesc }}</span>
                                            </div>
                                            <span 
                                                x-show="copied" 
                                                x-transition 
                                                class="text-[9px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded"
                                            >
                                                Copied!
                                            </span>
                                            <span 
                                                x-show="!copied" 
                                                class="text-[10px] text-slate-500 opacity-0 group-hover:opacity-100 transition-opacity"
                                            >
                                                📋
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-4 border-t border-white/10 flex items-center justify-between">
                                <span class="text-[11px] text-slate-500">Saved templates immediately govern all automated platform dispatches.</span>
                                <x-glass.button type="submit" variant="primary" size="sm" class="shadow-lg shadow-violet-500/25">
                                    <span wire:loading.remove wire:target="saveTemplate">💾 Save Template</span>
                                    <span wire:loading wire:target="saveTemplate">Saving...</span>
                                </x-glass.button>
                            </div>
                        </x-glass.card>
                    </form>
                @endif
            </div>
        </div>

        <!-- Live HTML Email Preview Modal (High-Fidelity Email Client Shell) -->
        @if ($showPreviewModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-950/85 backdrop-blur-2xl animate-fade-in">
                <div class="w-full max-w-3xl bg-slate-900 border border-white/20 rounded-3xl overflow-hidden shadow-[0_30px_90px_rgba(0,0,0,0.95)] space-y-0 max-h-[92vh] flex flex-col">
                    <!-- Preview Shell Header -->
                    <div class="px-6 py-4 bg-slate-950 border-b border-white/10 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                            </div>
                            <span class="text-slate-600">|</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm">📧</span>
                                <h3 class="text-xs font-bold text-white tracking-tight">Email Preview Shell &bull; {{ $currentTpl['name'] ?? '' }}</h3>
                            </div>
                        </div>
                        <button type="button" wire:click="$set('showPreviewModal', false)" class="text-slate-400 hover:text-white p-1 text-sm cursor-pointer hover:scale-110 active:scale-95 transition-all">✕</button>
                    </div>

                    <!-- Email Metadata Envelope Bar -->
                    <div class="px-6 py-2.5 bg-slate-900/90 border-b border-white/10 text-[11px] font-mono flex flex-wrap items-center justify-between gap-2 shrink-0">
                        <div class="flex items-center gap-2 text-slate-300">
                            <span class="text-slate-500">From:</span>
                            <span class="text-indigo-300 font-bold">{{ $mail_from_name }} &lt;{{ $mail_from_address }}&gt;</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-300">
                            <span class="text-slate-500">Recipient:</span>
                            <span class="text-emerald-400 font-semibold">{{ Auth::user()?->name ?? 'Alex Morgan' }} &lt;{{ Auth::user()?->email ?? 'alex.morgan@helpofai.com' }}&gt;</span>
                        </div>
                    </div>

                    <!-- Rendered HTML Canvas Surface -->
                    <div class="p-4 sm:p-6 flex-1 overflow-y-auto bg-[#030712] scrollbar-none">
                        <div class="border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
                            {!! $previewHtml !!}
                        </div>
                    </div>

                    <!-- Preview Modal Footer -->
                    <div class="px-6 py-3 bg-slate-950 border-t border-white/10 flex items-center justify-between shrink-0 text-xs">
                        <span class="text-slate-500 text-[11px]">✨ Live simulated preview with active user metadata and platform credentials.</span>
                        <x-glass.button wire:click="$set('showPreviewModal', false)" variant="secondary" size="sm">
                            Close Preview
                        </x-glass.button>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- TAB 2: NOTIFICATION POLICIES & SECURITY TRIGGERS -->
    @if ($activeTab === 'notification_channels')
        <form wire:submit="saveNotificationChannels" class="space-y-6">
            <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🔔</span>
                        <span>Security Triggers & Notification Routing</span>
                    </h3>
                    <p class="text-xs text-slate-400">Configure which system events dispatch instant administrator security alerts.</p>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/10 space-y-2">
                    <label class="block text-xs font-semibold text-white">Administrator Alert Recipient Email</label>
                    <x-glass.input 
                        wire:model="admin_alert_email"
                        type="email"
                        placeholder="admin@helpofai.com"
                        :error="$errors->has('admin_alert_email')"
                    />
                    <p class="text-[11px] text-slate-400">High-priority security breaches and system notices will be sent to this address.</p>
                </div>

                <div class="space-y-3">
                    <!-- 1. Auto IP Blacklist Trigger -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-white block">Auto IP Blacklist Alert</span>
                            <span class="text-slate-400 text-[11px]">Dispatch alert when an attacking IP reaches the brute-force threshold and is auto-banned.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="notify_on_ip_autoblock" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>

                    <!-- 2. Failed Login Spike Alert -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-white block">Admin Account Lockout Alert</span>
                            <span class="text-slate-400 text-[11px]">Notify administrator when repeated invalid attempts target staff/admin emails.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="notify_on_failed_login" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>

                    <!-- 3. New User Registration Notice -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-white block">New User Registration Digest</span>
                            <span class="text-slate-400 text-[11px]">Send in-app notice whenever a new account signs up.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="notify_on_user_registered" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>

                    <!-- 4. Low Quota Threshold Notice -->
                    <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-white block">Low Quota Alert for Users</span>
                            <span class="text-slate-400 text-[11px]">Deliver an in-app notice when a user has consumed $\ge 90\%$ of their monthly AI word quota.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="notify_on_quota_low" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/10 flex justify-end">
                    <x-glass.button type="submit" variant="primary" size="sm" class="shadow-lg shadow-violet-500/25">
                        <span wire:loading.remove wire:target="saveNotificationChannels">💾 Save Notification Triggers</span>
                        <span wire:loading wire:target="saveNotificationChannels">Saving...</span>
                    </x-glass.button>
                </div>
            </x-glass.card>
        </form>
    @endif

    <!-- TAB 3: BROADCAST ANNOUNCEMENT DISPATCHER -->
    @if ($activeTab === 'broadcast')
        <form wire:submit="sendBroadcast" class="space-y-6">
            <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>📢</span>
                        <span>Dispatch System-Wide Announcement</span>
                    </h3>
                    <p class="text-xs text-slate-400">Broadcast maintenance alerts, feature releases, or system updates directly to user notification centers.</p>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Target Audience</label>
                            <select 
                                wire:model="broadcast_target"
                                class="w-full bg-slate-950 border border-white/15 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-violet-500 focus:border-violet-500"
                            >
                                <option value="all">All Active Users (Platform-Wide)</option>
                                <option value="pro_users">Pro & Enterprise Users Only</option>
                                <option value="admin">Administrators Only</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-slate-300 font-semibold mb-1">Notification Badge Type</label>
                            <select 
                                wire:model="broadcast_type"
                                class="w-full bg-slate-950 border border-white/15 text-white text-xs rounded-xl px-3 py-2.5 focus:ring-violet-500 focus:border-violet-500"
                            >
                                <option value="announcement">📢 Announcement (Feature / Update)</option>
                                <option value="info">ℹ️ Information</option>
                                <option value="warning">⚠️ Warning / Maintenance Notice</option>
                                <option value="success">✨ Reward / Promotion</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Announcement Title</label>
                        <x-glass.input 
                            wire:model="broadcast_title"
                            type="text"
                            placeholder="e.g. Scheduled AI Engine Maintenance on Saturday"
                            :error="$errors->has('broadcast_title')"
                        />
                        @error('broadcast_title') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-slate-300 font-semibold mb-1">Announcement Message Body</label>
                        <textarea 
                            wire:model="broadcast_message"
                            rows="4"
                            placeholder="Type your message here..."
                            class="w-full bg-slate-950/80 border border-white/15 rounded-2xl p-3.5 text-xs text-white placeholder-slate-500 focus:ring-2 focus:ring-violet-500 focus:border-transparent transition-all outline-none"
                        ></textarea>
                        @error('broadcast_message') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-900/60 border border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-white block">Also Send via Email</span>
                            <span class="text-slate-400 text-[11px]">Dispatches a copy to each recipient's registered email inbox in addition to their in-app bell.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="broadcast_send_email" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                        </label>
                    </div>
                </div>

                <div class="pt-4 border-t border-white/10 flex justify-end">
                    <x-glass.button type="submit" variant="primary" size="sm" class="shadow-lg shadow-violet-500/25">
                        <span wire:loading.remove wire:target="sendBroadcast">📢 Dispatch Broadcast Now</span>
                        <span wire:loading wire:target="sendBroadcast">Dispatching...</span>
                    </x-glass.button>
                </div>
            </x-glass.card>
        </form>
    @endif

    <!-- TAB 4: TEST & DIAGNOSTIC PROBER -->
    @if ($activeTab === 'test_delivery')
        <form wire:submit="sendTestMail" class="space-y-6">
            <x-glass.card variant="elevated" class="p-6 border-white/15 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <span>🧪</span>
                        <span>Mail Gateway Test & Delivery Diagnostic</span>
                    </h3>
                    <p class="text-xs text-slate-400">Trigger an immediate test email to verify credentials, DNS MX records, and SMTP handshake integrity.</p>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900/60 border border-white/10 space-y-3">
                    <label class="block text-xs font-semibold text-white">Target Recipient Email Address</label>
                    <x-glass.input 
                        wire:model="test_recipient_email"
                        type="email"
                        placeholder="your-email@example.com"
                        :error="$errors->has('test_recipient_email')"
                    />
                    @error('test_recipient_email') <p class="text-xs text-rose-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-between pt-2">
                    <span class="text-[11px] text-slate-500 font-mono">Mailer: {{ strtoupper($mail_mailer) }} | Host: {{ $mail_host }}:{{ $mail_port }}</span>
                    <x-glass.button type="submit" variant="primary" size="sm" class="bg-emerald-600 hover:bg-emerald-500 border-emerald-500/50 shadow-lg shadow-emerald-500/20">
                        <span wire:loading.remove wire:target="sendTestMail">⚡ Trigger Test Delivery</span>
                        <span wire:loading wire:target="sendTestMail">Testing Connection...</span>
                    </x-glass.button>
                </div>
            </x-glass.card>
        </form>
    @endif

</div>
