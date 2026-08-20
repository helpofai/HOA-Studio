<?php

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

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\UpdateUserProfile;
use App\Features\Auth\Models\UserApiKey;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('User Profile & BYOK Keys — HelpOfAi Studio')]
class ProfilePage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    public string $default_model = 'OmniRoute: DeepSeek-V3';
    public int $embedding_cache_days = 7;
    public ?string $statusMessage = null;

    // BYOK Key Management
    public string $byok_provider = 'openai';
    public string $byok_api_key = '';
    public string $byok_custom_url = '';
    public array $visibleKeys = [];

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->default_model = $user->preferences['default_model'] ?? 'OmniRoute: DeepSeek-V3';
        $this->embedding_cache_days = (int) ($user->preferences['embedding_cache_days'] ?? 7);
    }

    public function updateProfile(UpdateUserProfile $action)
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'new_password' => 'nullable|string|min:8|confirmed',
            'embedding_cache_days' => 'required|in:1,7,30',
        ]);

        $prefs = $user->preferences ?? [];
        $prefs['default_model'] = $this->default_model;
        $prefs['embedding_cache_days'] = (int) $this->embedding_cache_days;

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'preferences' => $prefs,
        ];

        if (!empty($this->new_password)) {
            $data['password'] = $this->new_password;
        }

        $action->execute($user, $data);

        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->statusMessage = 'Profile updated successfully.';
    }

    public function saveApiKey()
    {
        $user = Auth::user();

        // Check if provider is explicitly disabled for BYOK by administrator
        $provider = \App\Features\AI\Models\AiProvider::where('slug', $this->byok_provider)->first();
        if ($provider && (!$provider->allow_user_key || !$provider->is_active)) {
            session()->flash('error', "Administrator has disabled custom BYOK keys for provider '{$this->byok_provider}'.");
            return;
        }

        $this->validate([
            'byok_provider' => 'required|string|max:50',
            'byok_api_key' => 'required|string|min:4|max:500',
            'byok_custom_url' => 'nullable|string|url|max:255',
        ]);

        UserApiKey::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider_slug' => $this->byok_provider,
            ],
            [
                'api_key' => $this->byok_api_key, // Encrypted at rest via AES-256-GCM
                'custom_base_url' => !empty($this->byok_custom_url) ? $this->byok_custom_url : null,
                'is_active' => true,
            ]
        );

        $this->reset(['byok_api_key', 'byok_custom_url']);
        $this->statusMessage = "API Key for '" . strtoupper($this->byok_provider) . "' saved securely (AES-256-GCM encrypted). Unlimited rate limits now unlocked.";
    }

    public function toggleKeyVisibility(int $keyId)
    {
        if (in_array($keyId, $this->visibleKeys, true)) {
            $this->visibleKeys = array_values(array_diff($this->visibleKeys, [$keyId]));
        } else {
            $this->visibleKeys[] = $keyId;
        }
    }

    public function deleteApiKey(int $keyId)
    {
        $user = Auth::user();
        UserApiKey::where('id', $keyId)->where('user_id', $user->id)->delete();
        $this->statusMessage = 'API Key removed. Platform fallback limits will apply.';
    }

    public function render()
    {
        $user = Auth::user();
        $apiKeys = $user->apiKeys()->latest()->get();
        $allowedProviders = \App\Features\AI\Models\AiProvider::where('allow_user_key', true)->where('is_active', true)->get();

        return view('auth.profile', [
            'user' => $user,
            'apiKeys' => $apiKeys,
            'allowedProviders' => $allowedProviders,
        ]);
    }
}