<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Feature Test
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

use App\Features\WordPress\Actions\VerifyWordPressHandshake;
use App\Features\WordPress\Services\WordPressPluginService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WordPressPluginFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_download_plugin_zip(): void
    {
        $response = $this->get(route('dashboard.wordpress.download'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_download_plugin_zip(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.wordpress.download'));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
    }

    public function test_wordpress_plugin_service_metadata_and_archive_generation(): void
    {
        $service = app(WordPressPluginService::class);
        $info = $service->getPluginInfo();

        $this->assertEquals('HOA-Studio AI Editor & Content Suite', $info['name']);
        $this->assertEquals('2.6.0', $info['version']);
        $this->assertTrue($info['directory_exists']);

        $zipPath = $service->ensureZipArchive();
        $this->assertFileExists($zipPath);
        $this->assertGreaterThan(1000, filesize($zipPath));
    }

    public function test_verify_wordpress_handshake_action(): void
    {
        $user = User::factory()->create([
            'name' => 'WordPress Administrator',
            'email' => 'wpadmin@helpofai.com',
            'monthly_word_quota' => 60000,
            'used_word_quota' => 10000,
        ]);

        $action = app(VerifyWordPressHandshake::class);
        $result = $action->execute($user);

        $this->assertTrue($result['success']);
        $this->assertEquals('connected', $result['status']);
        $this->assertEquals('WordPress Administrator', $result['user']['name']);
        $this->assertEquals(50000, $result['user']['quota']['remaining_words']);
        $this->assertEquals('2.6.0', $result['protocol_version']);
    }
}
