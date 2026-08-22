<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Inline\Text;

$environment = Environment::createCommonMarkEnvironment();
$environment->addExtension(new TableExtension());
$environment->addExtension(new TaskListExtension());

$converter = new GithubFlavoredMarkdownConverter([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
], $environment);

$markdown = "# Welcome\n\nThis is a *markdown* page.";
$document = $converter->convert($markdown)->getDocument();

echo "=== Document Structure ===\n";
foreach ($document->children() as $node) {
    echo "Node: " . get_class($node) . "\n";
    if (method_exists($node, 'getLevel')) {
        echo "  Level: " . $node->getLevel() . "\n";
    }
    
    $children = iterator_to_array($node->children());
    echo "  Children count: " . count($children) . "\n";
    
    foreach ($children as $child) {
        echo "    Child class: " . get_class($child) . "\n";
        if ($child instanceof Text) {
            echo "      Text content: '" . $child->getContent() . "'\n";
        }
        echo "      Is Text?: " . ($child instanceof Text ? 'YES' : 'NO') . "\n";
    }
}