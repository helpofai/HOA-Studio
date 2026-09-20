<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://localhost:20128'; // without /v1
$apiKey = 'omniroute-default-key';

echo "Testing OmniRoute gateway health at {$baseUrl}/api/health" . PHP_EOL;

// Try to get the health endpoint with the key from config
try {
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
        'Content-Type' => 'application/json',
    ])->get("{$baseUrl}/api/health");
    echo "Health Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Health Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Health Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Health Exception: " . $e->getMessage() . PHP_EOL;
}

// Now test the models endpoint again to confirm
echo PHP_EOL . "Testing models endpoint at {$baseUrl}/v1/models" . PHP_EOL;
try {
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
        'Content-Type' => 'application/json',
    ])->get("{$baseUrl}/v1/models");
    echo "Models Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Models Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Models Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Models Exception: " . $e->getMessage() . PHP_EOL;
}