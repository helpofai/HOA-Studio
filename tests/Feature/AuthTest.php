<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Features\Auth\Livewire\LoginPage;
use App\Features\Auth\Livewire\RegisterPage;
use App\Features\Auth\Livewire\ProfilePage;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_registration_page_renders_successfully(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function test_user_can_register_with_default_quota(): void
    {
        Livewire::test(RegisterPage::class)
            ->set('name', 'admin Doe')
            ->set('email', 'admin@admin.com')
            ->set('password', 'admin@admin.com')
            ->set('password_confirmation', 'admin@admin.com')
            ->set('agree', true)
            ->call('register')
            ->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'admin@admin.com',
            'role' => 'user',
            'plan' => 'starter',
            'monthly_word_quota' => 15000,
            'used_word_quota' => 0,
        ]);

        $this->assertAuthenticated();
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@hoa.studio',
            'password' => bcrypt('secret12345'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', 'test@hoa.studio')
            ->set('password', 'secret12345')
            ->call('login')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_authenticated_user_can_update_profile_and_preferences(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@hoa.studio',
            'preferences' => ['default_model' => 'OmniRoute: DeepSeek-V3'],
        ]);

        $this->actingAs($user);

        Livewire::test(ProfilePage::class)
            ->set('name', 'Updated Name')
            ->set('email', 'original@hoa.studio')
            ->set('default_model', 'OmniRoute: Claude 3.7 Sonnet')
            ->call('updateProfile')
            ->assertSet('statusMessage', 'Profile updated successfully.');

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('OmniRoute: Claude 3.7 Sonnet', $user->preferences['default_model']);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->withSession([])->post('/logout', [
            '_token' => csrf_token(),
        ]);
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}