<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Documents\Services;

use App\Features\Documents\Models\Document;

class DocumentExporter
{
    /**
     * Export document as formatted Markdown (.md)
     */
    public function exportMarkdown(Document $document): string
    {
        $html = $document->content->content_html ?? '';
        $title = $document->title;

        $md = "# {$title}\n\n";

        // Replace headings
        $html = preg_replace('/<h1[^>]*>(.*?)<\/h1>/si', "# $1\n\n", $html);
        $html = preg_replace('/<h2[^>]*>(.*?)<\/h2>/si', "## $1\n\n", $html);
        $html = preg_replace('/<h3[^>]*>(.*?)<\/h3>/si', "### $1\n\n", $html);
        $html = preg_replace('/<h4[^>]*>(.*?)<\/h4>/si', "#### $1\n\n", $html);

        // Replace bold & italic
        $html = preg_replace('/<(strong|b)[^>]*>(.*?)<\/(strong|b)>/si', "**$2**", $html);
        $html = preg_replace('/<(em|i)[^>]*>(.*?)<\/(em|i)>/si', "*$2*", $html);

        // Replace code & code blocks
        $html = preg_replace('/<pre><code>(.*?)<\/code><\/pre>/si', "```\n$1\n```\n\n", $html);
        $html = preg_replace('/<code[^>]*>(.*?)<\/code>/si', "`$1`", $html);

        // Replace blockquotes
        $html = preg_replace('/<blockquote[^>]*>(.*?)<\/blockquote>/si', "> $1\n\n", $html);

        // Replace list items
        $html = preg_replace('/<li[^>]*>(.*?)<\/li>/si', "- $1\n", $html);
        $html = preg_replace('/<\/(ul|ol)>/si', "\n", $html);

        // Replace paragraphs & line breaks
        $html = preg_replace('/<p[^>]*>(.*?)<\/p>/si', "$1\n\n", $html);
        $html = preg_replace('/<br\s*\/?>/si', "\n", $html);

        // Replace links
        $html = preg_replace('/<a\s+[^>]*href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/si', "[$2]($1)", $html);

        // Strip remaining HTML tags
        $cleanMd = trim(strip_tags($html));
        $cleanMd = html_entity_decode($cleanMd, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $md . $cleanMd . "\n";
    }

    /**
     * Export document as styled, self-contained standalone HTML (.html)
     */
    public function exportHtml(Document $document): string
    {
        $title = htmlspecialchars($document->title, ENT_QUOTES, 'UTF-8');
        $bodyHtml = $document->content->content_html ?? '';
        $wordCount = number_format($document->word_count);
        $readingTime = $document->reading_time_minutes;
        $date = $document->updated_at->format('F d, Y');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} — HelpOfAi Studio</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap');
        
        :root {
            --bg-color: #0d1117;
            --text-color: #e6edf3;
            --text-muted: #8b949e;
            --border-color: #30363d;
            --accent-color: #6366f1;
        }

        @media (prefers-color-scheme: light) {
            :root {
                --bg-color: #ffffff;
                --text-color: #1f2328;
                --text-muted: #656d76;
                --border-color: #d0d7de;
                --accent-color: #4f46e5;
            }
        }

        body {
            font-family: 'Instrument Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.7;
            margin: 0;
            padding: 40px 20px;
        }

        .document-container {
            max-width: 780px;
            margin: 0 auto;
        }

        header.doc-meta {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 24px;
            margin-bottom: 32px;
        }

        h1.doc-title {
            font-size: 2.25rem;
            font-weight: 800;
            line-height: 1.25;
            margin: 0 0 12px 0;
            letter-spacing: -0.02em;
        }

        .meta-tags {
            font-size: 0.85rem;
            color: var(--text-muted);
            display: flex;
            gap: 16px;
        }

        .content h1, .content h2, .content h3, .content h4 {
            font-weight: 700;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
            letter-spacing: -0.01em;
        }

        .content h2 { border-bottom: 1px solid var(--border-color); padding-bottom: 0.3em; }
        .content p { margin-bottom: 1.25rem; }
        .content blockquote {
            border-left: 3px solid var(--accent-color);
            padding-left: 1rem;
            margin-left: 0;
            color: var(--text-muted);
            font-style: italic;
        }

        .content pre {
            background-color: rgba(125, 125, 125, 0.1);
            padding: 16px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: monospace;
        }

        .content code {
            font-family: monospace;
            background-color: rgba(125, 125, 125, 0.15);
            padding: 2px 6px;
            border-radius: 4px;
        }

        @media print {
            body { background: #fff !important; color: #000 !important; padding: 0 !important; }
            .document-container { max-width: 100% !important; }
            header.doc-meta { border-bottom: 1px solid #ccc !important; }
        }
    </style>
</head>
<body>
    <div class="document-container">
        <header class="doc-meta">
            <h1 class="doc-title">{$title}</h1>
            <div class="meta-tags">
                <span>{$date}</span>
                <span>•</span>
                <span>{$wordCount} words</span>
                <span>•</span>
                <span>{$readingTime} min read</span>
            </div>
        </header>

        <article class="content">
            {$bodyHtml}
        </article>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Export document as clean Plain Text (.txt)
     */
    public function exportPlainText(Document $document): string
    {
        $title = $document->title;
        $html = $document->content->content_html ?? '';

        // Add line breaks after block elements
        $spacedHtml = preg_replace('/<\/(h[1-6]|p|div|li|blockquote)>/i', "$0\n\n", $html);
        $plain = trim(strip_tags($spacedHtml));
        $plain = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return "{$title}\n" . str_repeat('=', mb_strlen($title)) . "\n\n" . $plain . "\n";
    }

    /**
     * Export document as Microsoft Word XML / HTML MIME Document (.docx / .doc)
     */
    public function exportDocx(Document $document): string
    {
        $title = htmlspecialchars($document->title, ENT_QUOTES, 'UTF-8');
        $body = $document->content->content_html ?? '';

        return <<<XML
<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <title>{$title}</title>
    <style>
        body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; line-height: 1.5; color: #111; }
        h1 { font-size: 20pt; font-weight: bold; color: #1f2937; margin-bottom: 8pt; }
        h2 { font-size: 14pt; font-weight: bold; color: #374151; margin-top: 14pt; margin-bottom: 6pt; border-bottom: 1pt solid #e5e7eb; }
        h3 { font-size: 12pt; font-weight: bold; color: #4b5563; margin-top: 10pt; }
        p { margin-bottom: 8pt; }
        blockquote { border-left: 3pt solid #6366f1; padding-left: 8pt; margin-left: 0; color: #6b7280; font-style: italic; }
        code { font-family: Consolas, monospace; background-color: #f3f4f6; padding: 2pt 4pt; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <hr/>
    {$body}
</body>
</html>
XML;
    }
}