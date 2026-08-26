<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Settings Feature Test
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

namespace Tests\Feature;

use App\Features\Auth\Livewire\ProfilePage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_settings_and_profile_page(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'plan' => 'pro',
            'monthly_word_quota' => 50000,
            'used_word_quota' => 12000,
        ]);

        $response = $this->actingAs($user)->get(route('settings'));
        $response->assertStatus(200);
        $response->assertSee('User Settings & Controls');
    }

    public function test_user_can_switch_tabs_in_settings_suite(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ProfilePage::class)
            ->assertSet('activeTab', 'profile')
            ->call('switchTab', 'tokens')
            ->assertSet('activeTab', 'tokens')
            ->call('switchTab', 'content')
            ->assertSet('activeTab', 'content')
            ->call('switchTab', 'byok')
            ->assertSet('activeTab', 'byok')
            ->call('switchTab', 'preferences')
            ->assertSet('activeTab', 'preferences');
    }

    public function test_user_can_update_profile_info(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@helpofai.com',
        ]);

        Livewire::actingAs($user)
            ->test(ProfilePage::class)
            ->set('name', 'Updated Name')
            ->set('email', 'updated@helpofai.com')
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', 'Profile updated successfully.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@helpofai.com',
        ]);
    }

    public function test_user_can_update_studio_preferences(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ProfilePage::class)
            ->set('default_model', 'OmniRoute: Claude 3.7 Sonnet')
            ->set('embedding_cache_days', 30)
            ->set('default_editor_engine', 'tiptap')
            ->call('updatePreferences')
            ->assertHasNoErrors()
            ->assertSet('statusMessage', 'Studio preferences and AI defaults updated successfully.');

        $fresh = $user->fresh();
        $this->assertEquals('OmniRoute: Claude 3.7 Sonnet', $fresh->preferences['default_model']);
        $this->assertEquals(30, $fresh->preferences['embedding_cache_days']);
    }

    public function test_user_can_generate_and_revoke_studio_connect_token(): void
    {
        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(ProfilePage::class)
            ->set('newTokenName', 'My Production Blog')
            ->call('generateStudioToken')
            ->assertHasNoErrors();

        $plainToken = $component->get('generatedPlainTextToken');
        $this->assertNotEmpty($plainToken);
        $this->assertStringStartsWith('hoa_live_', $plainToken);

        $this->assertDatabaseHas('user_studio_tokens', [
            'user_id' => $user->id,
            'name' => 'My Production Blog',
        ]);

        $tokenRecord = \App\Features\Auth\Models\UserStudioToken::where('user_id', $user->id)->first();
        $this->assertNotNull($tokenRecord);

        $component->call('deleteStudioToken', $tokenRecord->id);
        $this->assertDatabaseMissing('user_studio_tokens', [
            'id' => $tokenRecord->id,
        ]);
    }
}
