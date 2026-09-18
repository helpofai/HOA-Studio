<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;

$models = AiModel::all();
echo "Total models in DB: " . $models->count() . "\n\n";

foreach ($models as $model) {
    echo "ID: {$model->id}, Name: {$model->name}, ModelID: {$model->model_id}, ProviderID: {$model->ai_provider_id}\n";
}

$antigravityProvider = AiProvider::where('slug', 'antigravity')->first();
echo "\nAntigravity Provider ID: " . ($antigravityProvider ? $antigravityProvider->id : 'NOT FOUND') . "\n";
?>