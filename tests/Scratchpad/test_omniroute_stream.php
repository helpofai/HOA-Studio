<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\AI\Services\OmniRouteClient;

$client = new OmniRouteClient();
$messages = [['role' => 'user', 'content' => 'Write a short poem about AI.']];
$options = ['model' => 'auto'];

echo "Starting stream..." . PHP_EOL;
$count = 0;
foreach ($client->streamChatCompletion($messages, $options) as $chunk) {
    echo $chunk['token'];
    $count++;
    if ($count > 100) { // safety break
        break;
    }
}
echo PHP_EOL . "Stream ended. Total tokens: " . $count . PHP_EOL;