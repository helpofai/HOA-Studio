<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$models = \App\Features\AI\Models\AiModel::where('ai_provider_id', 2)->get();
echo 'Models count: ' . $models->count() . PHP_EOL;
foreach ($models as $m) {
    echo 'ID: ' . $m->id . ' - Name: ' . $m->name . ' - Active: ' . ($m->is_active ? 'Yes' : 'No') . PHP_EOL;
}
?>