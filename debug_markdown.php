<?php

require 'vendor/autoload.php';

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
    if (method_exists($node, 'getType')) {
        echo "  Type: " . $node->getType() . "\n";
    }
    
    // Check children
    $children = iterator_to_array($node->children());
    echo "  Children count: " . count($children) . "\n";
    
    foreach ($children as $child) {
        echo "    Child: " . get_class($child) . "\n";
        if (method_exists($child, 'getType')) {
            echo "    Type: " . $child->getType() . "\n";
        }
        if ($child instanceof Text) {
            echo "      Text content: '" . $child->getContent() . "'\n";
        }
        
        // Check grandchildren
        $grandchildren = iterator_to_array($child->children());
        if (!empty($grandchildren)) {
            foreach ($grandchildren as $gc) {
                echo "      Grandchild: " . get_class($gc) . "\n";
                if (method_exists($gc, 'getType')) {
                    echo "      Type: " . $gc->getType() . "\n";
                }
                if ($gc instanceof Text) {
                    echo "        Text content: '" . $gc->getContent() . "'\n";
                }
            }
        }
    }
    
    echo "\n";
}

echo "\n=== Walker ===\n";
$walker = $document->walker();
while ($event = $walker->next()) {
    $node = $event->getNode();
    $prefix = $event->isEntering() ? 'ENTER' : 'LEAVE';
    echo "[$prefix] " . get_class($node) . " - Type: " . $node->getType() . "\n";
    if ($node instanceof Text) {
        echo "  Text: '" . $node->getContent() . "'\n";
    }
}