<?php

namespace Database\Seeders;

use App\Features\AI\Models\AiProvider;
use Illuminate\Database\Seeder;

class AntigravityProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiProvider::firstOrCreate(
            ['slug' => 'antigravity'],
            [
                'name' => 'Antigravity (Google Powered)',
                'icon' => '🌌',
                'description' => 'Google OAuth backed multi-account AI routing with dynamic server model discovery and automatic quota rotation.',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'is_local' => false,
                'is_active' => true,
                'allow_user_key' => true,
                'settings' => [
                    'supports_oauth' => true,
                    'auth_type' => 'google_oauth_bearer',
                ],
            ]
        );
    }
}
