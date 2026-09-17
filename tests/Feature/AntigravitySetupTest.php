<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntigravitySetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_antigravity_settings_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.ai-settings.antigravity'));

        $response->assertStatus(200);
        $response->assertSee('Antigravity Gateway');
    }

    public function test_non_admin_cannot_access_antigravity_settings_page()
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.ai-settings.antigravity'));

        $response->assertStatus(403);
    }

    public function test_oauth_redirect_route_resolves_controller()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('oauth.antigravity.redirect'));

        // Should redirect to Google OAuth URL (302 redirect)
        $response->assertStatus(302);
    }
}
