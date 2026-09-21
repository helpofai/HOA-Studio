<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://localhost:20128'; // without /v1
$apiKey = 'omniroute-default-key';

echo "Testing OmniRoute gateway at {$baseUrl}/models" . PHP_EOL;

// Try to get the models endpoint with the key from config
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

// Now with /v1
$baseUrlV1 = 'http://localhost:20128/v1';
echo PHP_EOL . "Testing with {$baseUrlV1}/models" . PHP_EOL;
try {
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
        'Content-Type' => 'application/json',
    ])->get("{$baseUrlV1}/models");
    echo "Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}