<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

// Simulate the headers that chatCompletion sends
$baseUrl = 'http://localhost:20128/v1';
$apiKey = 'omniroute-default-key';
$sessionId = (string) Str::uuid();
$requestId = (string) Str::uuid();

$headers = [
    'Authorization' => "Bearer {$apiKey}",
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
    'X-OmniRoute-Session-Id' => $sessionId,
    'X-Request-Id' => $requestId,
    'X-OmniRoute-No-Cache' => 'false', // assuming cache enabled
    'x-omniroute-compression' => 'default',
];

echo "Testing models endpoint with extra headers" . PHP_EOL;
try {
    $response = Http::withHeaders($headers)
        ->withOptions(['force_ip_resolve' => 'v4'])
        ->timeout(5)
        ->get("{$baseUrl}/models");
    echo "Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}

// Now test without extra headers (like healthCheck)
echo PHP_EOL . "Testing models endpoint with basic headers only" . PHP_EOL;
$basicHeaders = [
    'Authorization' => "Bearer {$apiKey}",
    'Content-Type' => 'application/json',
    'Accept' => 'application/json',
];
try {
    $response = Http::withHeaders($basicHeaders)
        ->withOptions(['force_ip_resolve' => 'v4'])
        ->timeout(5)
        ->get("{$baseUrl}/models");
    echo "Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}