<?php

namespace Tests\Feature;

use App\Features\BrandVoice\Models\BrandProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandVoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_view_brand_voices_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('brand-voices.index'));
        $response->assertOk();
        $response->assertSee('Brand Voice Profiles');
    }

    public function test_user_can_create_brand_voice(): void
    {
        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\BrandVoice\Livewire\BrandVoicePage::class)
            ->set('name', 'Acme Visionary Tech')
            ->set('tone_description', 'High-energy, authoritative, future-focused')
            ->set('target_audience', 'CTOs and Software Architects')
            ->set('guidelines', 'Always use active voice and concrete performance metrics')
            ->set('forbidden_words_input', 'synergy, paradigm, utilize')
            ->set('is_default', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('brand_profiles', [
            'user_id' => $this->user->id,
            'name' => 'Acme Visionary Tech',
            'is_default' => true,
        ]);

        $profile = BrandProfile::where('name', 'Acme Visionary Tech')->first();
        $this->assertEquals(['synergy', 'paradigm', 'utilize'], $profile->forbidden_words);
        $this->assertStringContainsString('=== BRAND VOICE GUIDELINES: Acme Visionary Tech ===', $profile->toSystemPromptSnippet());
    }

    public function test_user_can_update_and_delete_brand_voice(): void
    {
        $profile = BrandProfile::create([
            'user_id' => $this->user->id,
            'name' => 'Old Voice',
            'tone_description' => 'Casual',
            'is_default' => false,
        ]);

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\BrandVoice\Livewire\BrandVoicePage::class)
            ->call('openEditModal', $profile->id)
            ->set('name', 'Updated Modern Voice')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('brand_profiles', [
            'id' => $profile->id,
            'name' => 'Updated Modern Voice',
        ]);

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\BrandVoice\Livewire\BrandVoicePage::class)
            ->call('delete', $profile->id);

        $this->assertDatabaseMissing('brand_profiles', [
            'id' => $profile->id,
        ]);
    }
}