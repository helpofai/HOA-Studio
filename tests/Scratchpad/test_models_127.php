<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://127.0.0.1:20128/v1';
$apiKey = 'omniroute-default-key';

echo "Testing OmniRoute gateway at {$baseUrl}/models with 127.0.0.1" . PHP_EOL;

try {
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
        'Content-Type' => 'application/json',
    ])->get("{$baseUrl}/models");
    echo "Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}