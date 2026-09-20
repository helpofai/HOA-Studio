<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$key = DB::table('settings')->where('key', 'omniroute_api_key')->value('value');
echo "Key from settings: " . ($key ?? 'NULL') . PHP_EOL;

$key2 = DB::table('ai_providers')->where('slug', 'omniroute')->value('api_key_encrypted');
echo "Key from ai_providers: " . ($key2 ?? 'NULL') . PHP_EOL;

$key3 = config('omniroute.api_key');
echo "Key from config: " . ($key3 ?? 'NULL') . PHP_EOL;