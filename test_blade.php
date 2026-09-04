<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$blade_code = file_get_contents(__DIR__ . '/resources/views/editor/partial/Components/content-intelligence-tab-seo.blade.php');
// We just compile it to make sure blade syntax is perfect
$compiler = app('blade.compiler');
$compiled = $compiler->compileString($blade_code);

if (strpos($compiled, 'syntax error') !== false || strpos($compiled, 'Parse error') !== false) {
    echo "Blade Compilation Error Found:\n" . substr($compiled, 0, 500);
} else {
    echo "Blade Compiled Successfully!";
    // Check for common blade errors like missing @endforeach
    if(substr_count($compiled, 'foreach') != substr_count($compiled, 'endforeach')) {
        echo " \nWARNING: foreach mismatch!";
    }
}
