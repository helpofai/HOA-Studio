<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SharedHostingDeploymentVerificationTest extends TestCase
{
    use RefreshDatabase;
    public function test_root_htaccess_file_exists_with_proper_redirection_and_security_rules()
    {
        $path = base_path('.htaccess');
        $this->assertTrue(File::exists($path), 'Root .htaccess file is missing.');

        $content = File::get($path);
        $this->assertStringContainsString('RewriteEngine On', $content);
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI} !^/public/', $content);
        $this->assertStringContainsString('RewriteRule ^(.*)$ public/$1 [L,QSA]', $content);
        $this->assertStringContainsString('Options -Indexes', $content);
    }

    public function test_public_htaccess_file_exists_with_deflate_compression_and_expires_rules()
    {
        $path = public_path('.htaccess');
        $this->assertTrue(File::exists($path), 'Public .htaccess file is missing.');

        $content = File::get($path);
        $this->assertStringContainsString('mod_rewrite.c', $content);
        $this->assertStringContainsString('mod_deflate.c', $content);
        $this->assertStringContainsString('mod_expires.c', $content);
        $this->assertStringContainsString('index.php', $content);
    }

    public function test_production_verification_artisan_command_passes_successfully()
    {
        $this->artisan('hoa:verify-production')
            ->expectsOutputToContain('Production Readiness Diagnostics')
            ->expectsOutputToContain('SUCCESS: HelpOfAi Studio is 100% PRODUCTION READY! 🚀')
            ->assertExitCode(0);
    }

    public function test_public_build_manifest_exists_and_is_valid_json()
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertTrue(File::exists($manifestPath), 'Vite build manifest is missing. Run npm run build.');

        $json = json_decode(File::get($manifestPath), true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('resources/js/app.js', $json);
        $this->assertArrayHasKey('resources/css/app.css', $json);
    }

    public function test_all_critical_storage_directories_are_writable()
    {
        $paths = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($paths as $path) {
            $this->assertTrue(File::isDirectory($path), "Directory {$path} does not exist.");
            $this->assertTrue(is_writable($path), "Directory {$path} is not writable.");
        }
    }
}