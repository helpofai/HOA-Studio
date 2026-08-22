<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Features\Documents\Services\EditorManager;

$manager = app(EditorManager::class);
$adapter = $manager->adapter('markdown');

$markdown = "# Welcome\n\nThis is a *markdown* page.";
$canonical = $adapter->toCanonical($markdown);

echo "=== CANONICAL OUTPUT ===\n";
echo json_encode($canonical, JSON_PRETTY_PRINT) . "\n";