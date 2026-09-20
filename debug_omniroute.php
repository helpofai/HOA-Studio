<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\AI\Services\OmniRouteClient;

// Let's extend the client to capture the headers
class DebugOmniRouteClient extends OmniRouteClient
{
    public function getLastHeaders()
    {
        return $this->buildHeaders([]);
    }
}

$client = new DebugOmniRouteClient();
$headers = $client->getLastHeaders();
echo "Headers: " . json_encode($headers) . PHP_EOL;

// Now test the health check
$health = $client->healthCheck();
echo "Health: " . json_encode($health) . PHP_EOL;

// Test getting models
$models = $client->getAvailableModels();
echo "Available Models count: " . count($models) . PHP_EOL;

// Test chat completion
try {
    $result = $client->chatCompletion([['role' => 'user', 'content' => 'Say hello']], ['model' => 'auto']);
    echo "Chat Completion Result: " . json_encode($result) . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}