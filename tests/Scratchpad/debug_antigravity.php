<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\AI\Models\AiProvider;

$provider = AiProvider::where('slug', 'antigravity')->first();
if (!$provider) {
    echo "Antigravity provider not found\n";
    exit;
}

$total = $provider->models()->count();
$failed = $provider->models()->where('last_test_status', '!=', 'working')->count();

echo "Total Models: $total\n";
echo "Failed Models: $failed\n";

$failedModels = $provider->models()->where('last_test_status', '!=', 'working')->get();
foreach ($failedModels as $model) {
    echo "- {$model->model_id}: {$model->last_test_error}\n";
}
