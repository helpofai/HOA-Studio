<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\AI\Services\OmniRouteClient;

// Let's extend the client to capture the endpoints and headers
class DebugOmniRouteClient extends OmniRouteClient
{
    public function getEndpoints()
    {
        return $this->endpoints;
    }
    
    public function getApiKey()
    {
        return $this->apiKey;
    }
    
    public function getLastHeaders()
    {
        return $this->buildHeaders([]);
    }
}

$client = new DebugOmniRouteClient();
echo "API Key: " . $client->getApiKey() . PHP_EOL;
echo "Endpoints: " . json_encode($client->getEndpoints()) . PHP_EOL;
echo "Base URL: " . $client->endpoints['openai_base'] . PHP_EOL;
echo "Models Endpoint: " . $client->endpoints['models_endpoint'] . PHP_EOL;

// Now test the models endpoint with the same settings as chatCompletion uses
$model = 'auto';
$sessionId = (string) Str::uuid();
$requestId = (string) Str::uuid();
$temperature = 0.7;
$max_tokens = 4096;
$connectTimeout = 10; // from config
$readTimeout = 120;   // from config

$headers = $client->buildHeaders([
    'X-OmniRoute-Session-Id' => $sessionId,
    'X-Request-Id' => $requestId,
    'X-OmniRoute-No-Cache' => 'false', // assuming cache enabled
    'x-omniroute-compression' => 'default',
]);

$payload = [
    'model' => $model,
    'messages' => [['role' => 'user', 'content' => 'Say hello']],
    'temperature' => $temperature,
    'max_tokens' => $max_tokens,
    'stream' => false,
];

echo PHP_EOL . "Testing chatCompletion endpoint with full headers and longer timeout" . PHP_EOL;
try {
    $response = Http::withHeaders($headers)
        ->connectTimeout($connectTimeout)
        ->timeout($readTimeout)
        ->post($client->endpoints['chat_completions_endpoint'], $payload);
    echo "Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}

// Now test the models endpoint with the same headers and timeout
echo PHP_EOL . "Testing models endpoint with same headers and timeout" . PHP_EOL;
try {
    $response = Http::withHeaders($headers)
        ->connectTimeout($connectTimeout)
        ->timeout($readTimeout)
        ->get($client->endpoints['models_endpoint']);
    echo "Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}

// Now test the models endpoint with just basic headers and long timeout
echo PHP_EOL . "Testing models endpoint with basic headers only and long timeout" . PHP_EOL;
$basicHeaders = $client->buildHeaders([]);
try {
    $response = Http::withHeaders($basicHeaders)
        ->connectTimeout($connectTimeout)
        ->timeout($readTimeout)
        ->get($client->endpoints['models_endpoint']);
    echo "Status: " . $response->status() . PHP_EOL;
    if ($response->successful()) {
        echo "Response: " . $response->body() . PHP_EOL;
    } else {
        echo "Error: " . $response->body() . PHP_EOL;
    }
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . PHP_EOL;
}