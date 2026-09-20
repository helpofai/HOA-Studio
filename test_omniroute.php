<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\AI\Services\OmniRouteClient;

$client = new OmniRouteClient();
$health = $client->healthCheck();
echo "Health: " . json_encode($health) . PHP_EOL;

$models = $client->getAvailableModels();
echo "Available Models: " . json_encode($models) . PHP_EOL;

try {
    $result = $client->chatCompletion([['role' => 'user', 'content' => 'Say hello']], ['model' => 'auto']);
    echo "Chat Completion Result: " . json_encode($result) . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}