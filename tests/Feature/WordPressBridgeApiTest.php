<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Bridge API Feature Test
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

use App\Features\Auth\Models\UserStudioToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordPressBridgeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson(route('api.wordpress.connect'));
        $response->assertStatus(401);
        $response->assertJsonFragment(['success' => false]);
    }

    public function test_valid_studio_token_can_connect_and_receive_user_context(): void
    {
        $user = User::factory()->create([
            'name' => 'WordPress Site Owner',
            'email' => 'wp@example.com',
            'monthly_word_quota' => 50000,
            'used_word_quota' => 5000,
        ]);

        $tokenResult = UserStudioToken::createTokenForUser($user, 'Test WP Site');
        $rawToken = $tokenResult['plainTextToken'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $rawToken,
        ])->postJson(route('api.wordpress.connect'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'connected',
            'user' => [
                'name' => 'WordPress Site Owner',
                'email' => 'wp@example.com',
                'quota' => [
                    'remaining_words' => 45000,
                ],
            ],
        ]);
    }

    public function test_valid_studio_token_can_sync_document(): void
    {
        $user = User::factory()->create();
        $tokenResult = UserStudioToken::createTokenForUser($user, 'Sync Site');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenResult['plainTextToken'],
        ])->postJson(route('api.wordpress.sync-document'), [
            'title' => 'Article Synced From WordPress',
            'content_html' => '<h2>Heading</h2><p>This is a synced post body.</p>',
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Article Synced From WordPress']);

        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'title' => 'Article Synced From WordPress',
        ]);
    }

    public function test_wordpress_connect_is_exempt_from_csrf_verification(): void
    {
        $user = User::factory()->create();
        $tokenResult = UserStudioToken::createTokenForUser($user, 'CSRF Exemption Test Site');

        $response = $this->withMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $tokenResult['plainTextToken'],
                'Accept' => 'application/json',
            ])
            ->post('/api/v1/wordpress/connect');

        $this->assertNotEquals(419, $response->getStatusCode());
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_wordpress_stream_returns_streamed_response(): void
    {
        $user = User::factory()->create([
            'monthly_word_quota' => 10000,
            'used_word_quota' => 0,
        ]);
        $tokenResult = UserStudioToken::createTokenForUser($user, 'Stream Test Site');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $tokenResult['plainTextToken'],
            'Accept' => 'text/event-stream',
        ])->post('/api/v1/wordpress/stream', [
            'text' => 'Testing AI Stream for WordPress TipTap',
            'type' => 'generate',
            'model' => 'auto',
            'custom_instruction' => 'Write a short intro',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
    }
}
