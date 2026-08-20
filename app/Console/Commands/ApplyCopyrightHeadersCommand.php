<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class ApplyCopyrightHeadersCommand extends Command
{
    protected $signature = 'hoa:apply-copyright';
    protected $description = 'Inject proprietary copyright headers into all PHP, Blade, JS, and CSS files';

    protected string $copyrightBlock = <<<'HEADER'
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
HEADER;

    public function handle(): int
    {
        $this->info('Injecting HelpOfAi copyright headers across codebase...');
        $count = 0;

        // 1. Process PHP Files
        $phpDirs = [
            app_path(),
            base_path('routes'),
            config_path(),
            database_path('migrations'),
            database_path('seeders'),
            database_path('factories'),
        ];

        foreach ($phpDirs as $dir) {
            if (!File::isDirectory($dir)) continue;

            $finder = (new Finder())->files()->in($dir)->name('*.php');
            foreach ($finder as $file) {
                $path = $file->getRealPath();
                $content = File::get($path);

                if (str_contains($content, 'Copyright (c) 2026 Rajib Adhikary')) {
                    continue;
                }

                if (str_starts_with($content, '<?php')) {
                    $rest = ltrim(substr($content, 5), "\r\n");
                    $newContent = "<?php\n\n" . $this->copyrightBlock . "\n\n" . $rest;
                    File::put($path, $newContent);
                    $this->line("  ✓ PHP: " . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path));
                    $count++;
                }
            }
        }

        // 2. Process Blade View Files
        $viewsDir = resource_path('views');
        if (File::isDirectory($viewsDir)) {
            $finder = (new Finder())->files()->in($viewsDir)->name('*.blade.php');
            foreach ($finder as $file) {
                $path = $file->getRealPath();
                $content = File::get($path);

                if (str_contains($content, 'Copyright (c) 2026 Rajib Adhikary')) {
                    continue;
                }

                $bladeHeader = "{{--\n" . $this->copyrightBlock . "\n--}}\n\n";
                $newContent = $bladeHeader . ltrim($content, "\r\n");
                File::put($path, $newContent);
                $this->line("  ✓ Blade: " . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path));
                $count++;
            }
        }

        // 3. Process JS and CSS Files
        $assetDirs = [
            resource_path('js'),
            resource_path('css'),
        ];

        foreach ($assetDirs as $dir) {
            if (!File::isDirectory($dir)) continue;

            $finder = (new Finder())->files()->in($dir)->name(['*.js', '*.css']);
            foreach ($finder as $file) {
                $path = $file->getRealPath();
                $content = File::get($path);

                if (str_contains($content, 'Copyright (c) 2026 Rajib Adhikary')) {
                    continue;
                }

                $newContent = $this->copyrightBlock . "\n\n" . ltrim($content, "\r\n");
                File::put($path, $newContent);
                $this->line("  ✓ Asset: " . str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path));
                $count++;
            }
        }

        $this->info("\nSUCCESS: Copyright headers injected into {$count} files!");
        return self::SUCCESS;
    }
}