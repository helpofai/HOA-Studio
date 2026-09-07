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

    public function test_wordpress_plugin_files_and_manifest_integrity(): void
    {
        $service = app(WordPressPluginService::class);
        $pluginDir = $service->getPluginDirectoryPath();

        $requiredFiles = [
            'hoa-studio-wordpress.php',
            'includes/Core/class-hoa-plugin.php',
            'includes/Core/class-hoa-activator.php',
            'includes/Core/class-hoa-deactivator.php',
            'includes/Core/class-hoa-settings.php',
            'includes/Admin/class-hoa-admin.php',
            'includes/Admin/class-hoa-metabox.php',
            'includes/Admin/class-hoa-seo-generator.php',
            'includes/Editor/class-hoa-studio-editor.php',
            'includes/Gutenberg/class-hoa-gutenberg-blocks.php',
            'includes/Api/class-hoa-ajax-handler.php',
            'includes/Api/class-hoa-rest-api.php',
            'includes/Sync/class-hoa-cloud-sync.php',
            'views/admin-dashboard.php',
            'views/admin-connection.php',
            'views/admin-ai-settings.php',
            'views/admin-editor-settings.php',
            'views/metabox-post-sidebar.php',
            'views/studio-canvas.php',
            'assets/css/hoa-studio.css',
            'assets/css/hoa-editor.css',
            'assets/js/hoa-admin.js',
            'assets/js/hoa-gutenberg.js',
            'assets/js/hoa-tiptap-bundle.js',
        ];

        foreach ($requiredFiles as $file) {
            $this->assertFileExists($pluginDir.'/'.$file, "Required plugin file [{$file}] is missing.");
        }

        $mainFileContent = file_get_contents($pluginDir.'/hoa-studio-wordpress.php');
        $this->assertStringContainsString('Plugin Name:       HOA-Studio AI Editor & Content Suite', $mainFileContent);
        $this->assertStringContainsString("define('HOA_STUDIO_VERSION', '2.6.0')", $mainFileContent);
        $this->assertStringContainsString('HelpOfAi (HOA)', $mainFileContent);
    }
}
