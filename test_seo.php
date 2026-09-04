<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$analyzer = new \App\Features\SEO\Services\SeoAnalyzer();
$result = $analyzer->analyze('<p>This is a test paragraph.</p>', 'Test Title', 'test', [], 'Test Meta');
echo json_encode(['score' => $result['score'], 'marked_html' => $result['marked_html'] ?? null]);
