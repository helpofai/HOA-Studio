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

use App\Features\AI\Models\AiProvider;
use App\Features\Auth\Actions\UpdateUserProfile;
use App\Features\Auth\Models\UserApiKey;
use App\Features\Auth\Models\UserStudioToken;
use App\Features\Documents\Models\Document;
use App\Features\Projects\Models\Project;
use App\Features\Usage\Services\UsageAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.workspace')]
#[Title('User Settings & Controls — HelpOfAi Studio')]
class ProfilePage extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $activeTab = 'profile'; // profile, tokens, content, byok, preferences

    // Profile State
    public string $name = '';
    public string $email = '';
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    public ?string $statusMessage = null;
    public ?string $errorMessage = null;

    // AI & Studio Preferences
    public string $default_model = 'OmniRoute: DeepSeek-V3';
    public int $embedding_cache_days = 7;
    public string $default_editor_engine = 'tiptap';
    public bool $auto_seo_audit = true;
    public bool $email_notifications = true;

    // Content Management Filter State
    public string $contentSearch = '';
    public string $contentStatusFilter = 'all';
    public string $contentSortBy = 'updated_at';

    // BYOK Key Management
    public string $byok_provider = 'openai';
    public string $byok_api_key = '';
    public string $byok_custom_url = '';
    public array $visibleKeys = [];

    // Studio Connect Key (WordPress / External API) State
    public string $newTokenName = 'WordPress Production Site';
    public ?string $generatedPlainTextToken = null;

    public function mount()
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        
        $prefs = $user->preferences ?? [];
        $this->default_model = $prefs['default_model'] ?? 'OmniRoute: DeepSeek-V3';
        $this->embedding_cache_days = (int) ($prefs['embedding_cache_days'] ?? 7);
        $this->default_editor_engine = $prefs['default_editor_engine'] ?? 'tiptap';
        $this->auto_seo_audit = (bool) ($prefs['auto_seo_audit'] ?? true);
        $this->email_notifications = (bool) ($prefs['email_notifications'] ?? true);
    }

    public function switchTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->statusMessage = null;
        $this->errorMessage = null;
        $this->resetPage();
    }

    public function updateProfile(UpdateUserProfile $action)
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $prefs = $user->preferences ?? [];
        $prefs['default_model'] = $this->default_model;
        $prefs['embedding_cache_days'] = (int) $this->embedding_cache_days;
        $prefs['default_editor_engine'] = $this->default_editor_engine;
        $prefs['auto_seo_audit'] = $this->auto_seo_audit;
        $prefs['email_notifications'] = $this->email_notifications;

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

    public function updatePreferences()
    {
        $user = Auth::user();

        $prefs = $user->preferences ?? [];
        $prefs['default_model'] = $this->default_model;
        $prefs['embedding_cache_days'] = (int) $this->embedding_cache_days;
        $prefs['default_editor_engine'] = $this->default_editor_engine;
        $prefs['auto_seo_audit'] = $this->auto_seo_audit;
        $prefs['email_notifications'] = $this->email_notifications;

        $user->update(['preferences' => $prefs]);

        $this->statusMessage = 'Studio preferences and AI defaults updated successfully.';
    }

    public function saveApiKey()
    {
        $user = Auth::user();

        $provider = AiProvider::where('slug', $this->byok_provider)->first();
        if ($provider && (!$provider->allow_user_key || !$provider->is_active)) {
            $this->errorMessage = "Administrator has disabled custom BYOK keys for provider '{$this->byok_provider}'.";
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
                'api_key' => $this->byok_api_key,
                'custom_base_url' => !empty($this->byok_custom_url) ? $this->byok_custom_url : null,
                'is_active' => true,
            ]
        );

        $this->reset(['byok_api_key', 'byok_custom_url']);
        $this->statusMessage = "API Key for '" . strtoupper($this->byok_provider) . "' saved securely (AES-256-GCM encrypted).";
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

    public function generateStudioToken()
    {
        $user = Auth::user();
        if (!$user) {
            $this->errorMessage = 'Session expired. Please log in again.';
            return;
        }

        $this->validate([
            'newTokenName' => 'required|string|min:2|max:60',
        ]);

        try {
            $result = UserStudioToken::createTokenForUser($user, $this->newTokenName);

            $this->generatedPlainTextToken = $result['plainTextToken'];
            $this->statusMessage = "Studio Connect Key '{$this->newTokenName}' created successfully! Copy your key now — it won't be shown again in full.";
            $this->newTokenName = 'WordPress Integration';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Failed to generate token: ' . $e->getMessage();
        }
    }

    public function deleteStudioToken(int $tokenId)
    {
        $user = Auth::user();
        $token = UserStudioToken::where('user_id', $user->id)->find($tokenId);
        if ($token) {
            $token->delete();
            $this->statusMessage = "Studio Connect Key '{$token->name}' revoked.";
        }
    }

    public function deleteDocument(int $docId)
    {
        $user = Auth::user();
        $doc = Document::where('user_id', $user->id)->find($docId);
        if ($doc) {
            $doc->delete();
            $this->statusMessage = "Document '{$doc->title}' moved to trash.";
        }
    }

    public function render(UsageAnalyticsService $analyticsService)
    {
        $user = Auth::user();

        // 1. Quota & Analytics Data
        $analytics = $analyticsService->getUserAnalytics($user);

        // 2. Content / My Documents query
        $documentsQuery = Document::where('user_id', $user->id)
            ->with(['project'])
            ->when($this->contentSearch, function ($q) {
                $q->where('title', 'like', '%' . $this->contentSearch . '%');
            })
            ->when($this->contentStatusFilter !== 'all', function ($q) {
                $q->where('status', $this->contentStatusFilter);
            });

        if ($this->contentSortBy === 'word_count') {
            $documentsQuery->orderBy('word_count', 'desc');
        } elseif ($this->contentSortBy === 'title') {
            $documentsQuery->orderBy('title', 'asc');
        } else {
            $documentsQuery->orderBy('updated_at', 'desc');
        }

        $documents = $documentsQuery->paginate(8);

        // 3. Document statistics summary
        $contentStats = [
            'total_documents' => Document::where('user_id', $user->id)->count(),
            'total_words_written' => (int) Document::where('user_id', $user->id)->sum('word_count'),
            'total_projects' => Project::where('user_id', $user->id)->count(),
            'published_count' => Document::where('user_id', $user->id)->where('status', 'published')->count(),
            'draft_count' => Document::where('user_id', $user->id)->where('status', 'draft')->count(),
        ];

        // 4. BYOK Keys & Providers
        $apiKeys = $user->apiKeys()->latest()->get();
        $allowedProviders = AiProvider::where('allow_user_key', true)->where('is_active', true)->get();

        // 5. Studio Connect Tokens (WordPress / External)
        $studioTokens = UserStudioToken::where('user_id', $user->id)->latest()->get();

        return view('auth.profile', [
            'user' => $user,
            'quota' => $analytics['quota'],
            'summary' => $analytics['summary'],
            'modelBreakdown' => $analytics['model_breakdown'],
            'recentLogs' => $analytics['recent_logs'],
            'documents' => $documents,
            'contentStats' => $contentStats,
            'apiKeys' => $apiKeys,
            'allowedProviders' => $allowedProviders,
            'studioTokens' => $studioTokens,
        ]);
    }
}