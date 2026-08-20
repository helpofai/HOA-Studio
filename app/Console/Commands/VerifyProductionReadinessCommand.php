<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Console\Commands;

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class VerifyProductionReadinessCommand extends Command
{
    protected $signature = 'hoa:verify-production';
    protected $description = 'Verify system readiness for shared-hosting or cloud production deployment';

    public function handle(): int
    {
        $this->info('===========================================================');
        $this->info('   HelpOfAi Studio — Production Readiness Diagnostics     ');
        $this->info('===========================================================');

        $hasError = false;

        // 1. PHP Version & Extensions
        $this->comment("\n1. PHP Environment & Extensions:");
        $this->line('  • PHP Version: ' . PHP_VERSION);

        $requiredExts = ['curl', 'mbstring', 'openssl', 'pdo', 'tokenizer', 'xml', 'ctype', 'fileinfo'];
        foreach ($requiredExts as $ext) {
            if (extension_loaded($ext)) {
                $this->line("  ✓ Extension '{$ext}' is enabled.");
            } else {
                $this->error("  ✗ Missing required PHP extension: '{$ext}'");
                $hasError = true;
            }
        }

        // 2. Database Connectivity & Migration State
        $this->comment("\n2. Database Connectivity & Migrations:");
        try {
            DB::connection()->getPdo();
            $this->line('  ✓ Database connection established successfully.');

            $providerCount = AiProvider::count();
            $modelCount = AiModel::count();
            $this->line("  ✓ Database seeded with {$providerCount} AI providers and {$modelCount} AI models.");
        } catch (\Exception $e) {
            $this->error('  ✗ Database connection error: ' . $e->getMessage());
            $hasError = true;
        }

        // 3. Storage and Cache Writable Permissions
        $this->comment("\n3. Directory Permissions & Writable Paths:");
        $paths = [
            storage_path('framework/views'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        foreach ($paths as $path) {
            if (!File::exists($path)) {
                File::makeDirectory($path, 0775, true, true);
            }

            if (is_writable($path)) {
                $this->line("  ✓ Directory writable: " . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path));
            } else {
                $this->error("  ✗ Directory is NOT writable: " . $path);
                $hasError = true;
            }
        }

        // 4. Production Built Assets
        $this->comment("\n4. Production Frontend Build Assets:");
        $manifestPath = public_path('build/manifest.json');
        if (File::exists($manifestPath)) {
            $this->line('  ✓ Vite build manifest found: public/build/manifest.json');
        } else {
            $this->warn('  ! Vite build manifest not found. Run: npm run build');
        }

        // 5. Shared Hosting .htaccess files
        $this->comment("\n5. Shared Hosting .htaccess Redirection Rules:");
        if (File::exists(base_path('.htaccess'))) {
            $this->line('  ✓ Root .htaccess redirection configured.');
        } else {
            $this->error('  ✗ Missing root .htaccess file.');
            $hasError = true;
        }

        if (File::exists(public_path('.htaccess'))) {
            $this->line('  ✓ Public .htaccess compression and routing configured.');
        } else {
            $this->error('  ✗ Missing public/.htaccess file.');
            $hasError = true;
        }

        $this->info("\n===========================================================");
        if ($hasError) {
            $this->error('  FAILED: Some checks did not pass. Please resolve above items.');
            return self::FAILURE;
        }

        $this->info('  SUCCESS: HelpOfAi Studio is 100% PRODUCTION READY! 🚀');
        $this->info("===========================================================\n");

        return self::SUCCESS;
    }
}