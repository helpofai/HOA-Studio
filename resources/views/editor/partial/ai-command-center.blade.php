{{--
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - AI Command Center Partial
|--------------------------------------------------------------------------
|
| Features:
| 1. AI Router & Provider Gateway
| 2. Real-Time Token Telemetry (Send, Received, Latency, tok/s)
| 3. Provider & Model Persistence
| 4. Antigravity Gateway Integration
|
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
<div x-data="aiCommandCenter()" x-init="initProviderSelection()" class="space-y-4">
    <!-- Provider Selection -->
    <div class="space-y-2">
        <label class="text-xs text-slate-400 uppercase tracking-wider">AI Provider</label>
        <div class="relative">
            <select
                x-model="selectedProvider"
                x-on:change="fetchModelsForProvider($event.target.value)"
                class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner cursor-pointer"
            >
                <option value="">⚡ Select Provider...</option>
                <template x-for="provider in availableProviders" :key="provider.id || provider.slug">
                    <option 
                        :value="provider.slug" 
                        x-text="provider.name + (provider.is_local ? ' (Local)' : ' (Cloud)')"
                    ></option>
                </template>
            </select>
            <div x-show="loadingProviders" class="absolute inset-0 flex items-center justify-center text-xs text-slate-400">
                Loading providers...
            </div>
        </div>
    </div>

    <!-- Model Selection -->
    <div class="space-y-2">
        <label class="text-xs text-slate-400 uppercase tracking-wider">AI Model</label>
        <div class="relative">
            <select
                x-model="selectedModel"
                x-on:change="saveProviderSelection()"
                class="w-full bg-slate-900 border border-white/15 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono shadow-inner cursor-pointer"
            >
                <option value="">⚡ Select Model...</option>
                <template x-for="model in availableModels" :key="model.id">
                    <option 
                        :value="model.id" 
                        x-text="model.name"
                    ></option>
                </template>
            </select>
            <div x-show="loadingModels" class="absolute inset-0 flex items-center justify-center text-xs text-slate-400">
                Loading models...
            </div>
        </div>
    </div>

    <!-- Current Selection Display -->
    <div x-show="selectedProvider && selectedModel" class="text-xs text-slate-500 flex flex-col space-y-1">
        <div class="flex items-center">
            <span class="font-mono">Provider:</span>
            <span class="ml-2 text-indigo-300" x-text="selectedProviderName"></span>
        </div>
        <div class="flex items-center">
            <span class="font-mono">Model:</span>
            <span class="ml-2 text-indigo-300" x-text="selectedModelName"></span>
        </div>
    </div>

    <!-- Token Telemetry -->
    <div class="grid grid-cols-2 gap-2 text-[9px] text-slate-500">
        <div>
            <span class="text-slate-400">Send</span>
            <span class="font-mono" x-text="sendTokens">0</span>
            <span class="text-[9px] text-slate-500">tok</span>
        </div>
        <div>
            <span class="text-slate-400">Received</span>
            <span class="font-mono" x-text="receivedTokens">0</span>
            <span class="text-[9px] text-slate-500">tok</span>
        </div>
        <div>
            <span class="text-slate-400">Latency</span>
            <span class="font-mono" x-text="latencyMs">0</span>
            <span class="text-[9px] text-slate-500">ms</span>
        </div>
        <div>
            <span class="text-slate-400">Speed</span>
            <span class="font-mono" x-text="tokensPerSecond">0</span>
            <span class="text-[9px] text-slate-500">tok/s</span>
        </div>
    </div>

    <!-- Control Buttons -->
    <div class="flex flex-col sm:flex-row sm:space-x-2 space-y-2 sm:space-y-0">
        <button
            @click.stop="toggleStreaming"
            :disabled="isGenerating"
            x-text="isGenerating ? '■ Stop (Esc)' : '▶️ Start AI'"
            class="w-full sm:w-auto flex-1 px-3 py-2 text-xs font-mono flex items-center justify-center rounded-xl transition-all duration-200"
            :class="{
                'bg-indigo-600 hover:bg-indigo-500 text-white': !isGenerating,
                'bg-red-600 hover:bg-red-500 text-white': isGenerating
            }"
        >
            <template x-if="isGenerating">
                ■ Stop (Esc)
            </template>
            <template x-if="!isGenerating">
                ▶️ Start AI
            </template>
        </button>

        <button
            @click="clearTelemetry"
            x-text="🗑️ Reset"
            class="w-full sm:w-auto flex-1 px-3 py-2 text-xs font-mono flex items-center justify-center rounded-xl border border-white/10 text-slate-400 hover:text-white hover:border-white/20 transition-colors duration-200"
        >
            🗑️ Reset
        </button>
    </div>
</div>

<script>
function aiCommandCenter() {
    return {
        // State
        availableProviders: [],
        availableModels: [],
        selectedProvider: '',
        selectedModel: '',
        loadingProviders: false,
        loadingModels: false,
        isGenerating: false,
        sendTokens: 0,
        receivedTokens: 0,
        latencyMs: 0,
        tokensPerSecond: 0,
        
        // Computed properties
        get selectedProviderName() {
            const provider = this.availableProviders.find(p => p.slug === this.selectedProvider);
            return provider ? provider.name : '';
        },
        
        get selectedModelName() {
            const model = this.availableModels.find(m => m.id === this.selectedModel);
            return model ? model.name : '';
        },

        // Lifecycle
        initProviderSelection() {
            this.loadProviderSelection();
            this.fetchAvailableProviders();
        },

        // Provider persistence
        loadProviderSelection() {
            try {
                const saved = localStorage.getItem('hoa_ai_provider_selection');
                if (saved) {
                    const selection = JSON.parse(saved);
                    if (selection.provider && selection.model) {
                        this.selectedProvider = selection.provider;
                        this.selectedModel = selection.model;
                        // Fetch models for the selected provider
                        this.fetchModelsForProvider(selection.provider);
                    }
                }
            } catch (e) {
                console.warn('Failed to load AI provider selection:', e);
            }
        },

        saveProviderSelection() {
            if (this.selectedProvider && this.selectedModel) {
                try {
                    const selection = {
                        provider: this.selectedProvider,
                        model: this.selectedModel,
                        timestamp: new Date().toISOString()
                    };
                    localStorage.setItem('hoa_ai_provider_selection', JSON.stringify(selection));
                    
                    // Dispatch event to update core script state
                    this.$dispatch('provider-changed', {
                        provider: this.selectedProvider,
                        model: this.selectedModel
                    });
                } catch (e) {
                    console.warn('Failed to save AI provider selection:', e);
                }
            }
        },

        // API Methods
        async fetchAvailableProviders() {
            this.loadingProviders = true;
            try {
                const response = await fetch('/api/ai/providers', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();
                this.availableProviders = data.providers || [];
                
                // If we had a previously selected provider, restore it
                if (this.selectedProvider && !this.availableProviders.find(p => p.slug === this.selectedProvider)) {
                    this.selectedProvider = '';
                    this.selectedModel = '';
                }
                
            } catch (error) {
                console.error('Failed to fetch providers:', error);
                this.availableProviders = [];
            } finally {
                this.loadingProviders = false;
            }
        },

        async fetchModelsForProvider(providerSlug) {
            if (!providerSlug) {
                this.availableModels = [];
                this.selectedModel = '';
                return;
            }

            this.loadingModels = true;
            try {
                const response = await fetch(`/api/ai/providers/${providerSlug}/models`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const data = await response.json();
                this.availableModels = data.models || [];
                
                // If we had a previously selected model for this provider, restore it
                const savedSelection = this.loadProviderSelection();
                if (this.selectedModel && !this.availableModels.find(m => m.id === this.selectedModel)) {
                    this.selectedModel = this.availableModels.length > 0 ? this.availableModels[0].id : '';
                } else if (!this.selectedModel && this.availableModels.length > 0) {
                    // Auto-select first model if none selected
                    this.selectedModel = this.availableModels[0].id;
                }
                
                this.saveProviderSelection();
                
            } catch (error) {
                console.error(`Failed to fetch models for provider ${providerSlug}:`, error);
                this.availableModels = [];
            } finally {
                this.loadingModels = false;
            }
        },

        // Telemetry methods (simplified for brevity)
        updateTelemetry(data) {
            if (data.sendTokens !== undefined) this.sendTokens = data.sendTokens;
            if (data.receivedTokens !== undefined) this.receivedTokens = data.receivedTokens;
            if (data.latencyMs !== undefined) this.latencyMs = data.latencyMs;
            if (data.tokensPerSecond !== undefined) this.tokensPerSecond = data.tokensPerSecond;
        },

        clearTelemetry() {
            this.sendTokens = 0;
            this.receivedTokens = 0;
            this.latencyMs = 0;
            this.tokensPerSecond = 0;
        },

        // Generation control
        async toggleStreaming() {
            if (this.isGenerating) {
                await this.stopGeneration();
            } else {
                await this.startGeneration();
            }
        },

        async startGeneration() {
            if (!this.selectedProvider || !this.selectedModel) {
                alert('Please select both a provider and model');
                return;
            }

            this.isGenerating = true;
            // Implementation would connect to the AI stream endpoint
            // passing selectedProvider and selectedModel as parameters
        },

        async stopGeneration() {
            this.isGenerating = false;
            // Implementation would stop the AI stream
        }
    };
}
</script>