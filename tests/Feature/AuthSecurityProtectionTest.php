<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Authentication Security Feature Test
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

use App\Features\Auth\Livewire\LoginPage;
use App\Features\Auth\Livewire\RegisterPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class AuthSecurityProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_cleanly_with_honeypot()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
        $response->assertSee('name="company_website_url"', false);
    }

    public function test_register_page_renders_cleanly_with_honeypot_and_strength_meter()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
        $response->assertSee('name="user_website_trap"', false);
        $response->assertSee('strengthLabel', false);
    }

    public function test_bot_filling_honeypot_is_blocked_on_login()
    {
        $user = User::factory()->create([
            'email' => 'victim@example.com',
            'password' => Hash::make('CorrectPassword123!'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', 'victim@example.com')
            ->set('password', 'CorrectPassword123!')
            ->set('honeypot', 'http://malicious-bot-link.com') // Bot filled the invisible trap
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_bot_filling_honeypot_is_blocked_on_register()
    {
        Livewire::test(RegisterPage::class)
            ->set('name', 'Spam Bot')
            ->set('email', 'spambot@example.com')
            ->set('password', 'ValidP@ssw0rd123!')
            ->set('password_confirmation', 'ValidP@ssw0rd123!')
            ->set('agree', true)
            ->set('honeypot', 'bot-value')
            ->call('register')
            ->assertHasErrors(['email']);

        $this->assertDatabaseMissing('users', ['email' => 'spambot@example.com']);
    }

    public function test_login_brute_force_rate_limiter_triggers_after_failed_attempts()
    {
        $targetEmail = 'victim_rl_' . uniqid() . '@example.com';
        RateLimiter::clear("login:account:{$targetEmail}|127.0.0.1");

        $user = User::factory()->create([
            'email' => $targetEmail,
            'password' => Hash::make('RealSecretPass!99'),
        ]);

        // Attempt 5 incorrect logins to trigger Rate Limiter
        for ($i = 0; $i < 5; $i++) {
            Livewire::test(LoginPage::class)
                ->set('email', $targetEmail)
                ->set('password', 'WrongPassword' . $i)
                ->call('login')
                ->assertHasErrors(['email']);
        }

        // The 6th attempt should be blocked with rate limiting message
        Livewire::test(LoginPage::class)
            ->set('email', $targetEmail)
            ->set('password', 'RealSecretPass!99') // Even with correct password, blocked by cooldown
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_valid_user_can_login_securely()
    {
        RateLimiter::clear('login:account:secureuser@example.com|127.0.0.1');
        RateLimiter::clear('login:ip:127.0.0.1');

        $user = User::factory()->create([
            'email' => 'secureuser@example.com',
            'password' => Hash::make('ComplexPassword123!'),
        ]);

        Livewire::test(LoginPage::class)
            ->set('email', 'secureuser@example.com')
            ->set('password', 'ComplexPassword123!')
            ->set('formLoadedAt', time() - 3) // 3 seconds elapsed (normal human)
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }
}
