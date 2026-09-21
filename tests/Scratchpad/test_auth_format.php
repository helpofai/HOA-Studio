<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;

$baseUrl = 'http://localhost:20128/v1';
$apiKey = 'omniroute-default-key';

echo "Testing OmniRoute gateway at {$baseUrl}/models with different auth formats" . PHP_EOL;

// Format 1: Bearer token
try {
    $response = Http::withHeaders([
        'Authorization' => "Bearer {$apiKey}",
        'Content-Type' => 'application/json',
    ])->get("{$baseUrl}/models");
    echo "Bearer - Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Bearer - Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Bearer - Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Bearer - Exception: " . $e->getMessage() . PHP_EOL;
}

// Format 2: Just the key
try {
    $response = Http::withHeaders([
        'Authorization' => $apiKey,
        'Content-Type' => 'application/json',
    ])->get("{$baseUrl}/models");
    echo "Raw key - Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Raw key - Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Raw key - Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Raw key - Exception: " . $e->getMessage() . PHP_EOL;
}

// Format 3: Token (without Bearer)
try {
    $response = Http::withHeaders([
        'Authorization' => "Token {$apiKey}",
        'Content-Type' => 'application/json',
    ])->get("{$baseUrl}/models");
    echo "Token - Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Token - Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Token - Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Token - Exception: " . $e->getMessage() . PHP_EOL;
}

// Format 4: X-API-Key header
try {
    $response = Http::withHeaders([
        'X-API-Key' => $apiKey,
        'Content-Type' => 'application/json',
    ])->get("{$baseUrl}/models");
    echo "X-API-Key - Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "X-API-Key - Response: " . $response->body() . PHP_EOL;
    } else {
        echo "X-API-Key - Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "X-API-Key - Exception: " . $e->getMessage() . PHP_EOL;
}