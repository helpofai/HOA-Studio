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
use App\Features\Documents\Models\DocumentContent;
use App\Features\Documents\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class DocumentImporter
{
    /**
     * Import a document from an uploaded file (.md, .txt, .html)
     */
    public function importFile(User $user, UploadedFile $file, ?int $projectId = null): Document
    {
        $extension = mb_strtolower($file->getClientOriginalExtension());
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $title = Str::headline($filename);
        $content = $file->get();

        $format = match ($extension) {
            'md', 'markdown' => 'markdown',
            'html', 'htm' => 'html',
            default => 'text',
        };

        return $this->importFromText($user, $title, $content, $format, $projectId);
    }

    /**
     * Import raw content into a new Document
     */
    public function importFromText(User $user, string $title, string $rawContent, string $format = 'markdown', ?int $projectId = null): Document
    {
        $title = trim($title) ?: 'Imported Document';
        $htmlContent = $this->convertToHtml($rawContent, $format);
        $plainText = trim(strip_tags($htmlContent));

        $words = preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY);
        $wordCount = count($words);
        $charCount = mb_strlen($plainText);
        $readingTime = max(1, (int) ceil($wordCount / 200));

        $document = Document::create([
            'user_id' => $user->id,
            'project_id' => $projectId,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(6),
            'status' => 'draft',
            'word_count' => $wordCount,
            'character_count' => $charCount,
            'reading_time_minutes' => $readingTime,
        ]);

        DocumentContent::create([
            'document_id' => $document->id,
            'content_html' => $htmlContent,
            'content_markdown' => $format === 'markdown' ? $rawContent : null,
            'content_plain' => $plainText,
        ]);

        DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => 1,
            'title' => $title,
            'content_html' => $htmlContent,
            'operation_type' => 'import',
            'summary' => 'Initial document import (' . strtoupper($format) . ')',
            'word_count' => $wordCount,
            'created_by' => $user->id,
        ]);

        return $document->fresh(['content', 'versions']);
    }

    /**
     * Convert markdown or plain text to clean HTML
     */
    protected function convertToHtml(string $content, string $format): string
    {
        if ($format === 'html') {
            return $content;
        }

        if ($format === 'text') {
            $paragraphs = array_filter(array_map('trim', explode("\n\n", $content)));
            if (empty($paragraphs)) {
                return '<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) . '</p>';
            }

            return implode('', array_map(function ($p) {
                return '<p>' . nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8')) . '</p>';
            }, $paragraphs));
        }

        // Basic Markdown to HTML converter
        $lines = explode("\n", $content);
        $html = '';
        $inList = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (empty($trimmed)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                continue;
            }

            if (preg_match('/^#{1,4}\s+(.*)$/', $trimmed, $m)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $level = strlen(explode(' ', $trimmed)[0]);
                $headingText = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $html .= "<h{$level}>{$headingText}</h{$level}>\n";
            } elseif (preg_match('/^[-*+]\s+(.*)$/', $trimmed, $m)) {
                if (!$inList) {
                    $html .= "<ul>\n";
                    $inList = true;
                }
                $itemText = $this->parseInlineMarkdown($m[1]);
                $html .= "<li>{$itemText}</li>\n";
            } elseif (preg_match('/^>\s+(.*)$/', $trimmed, $m)) {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $quoteText = $this->parseInlineMarkdown($m[1]);
                $html .= "<blockquote><p>{$quoteText}</p></blockquote>\n";
            } else {
                if ($inList) {
                    $html .= "</ul>\n";
                    $inList = false;
                }
                $pText = $this->parseInlineMarkdown($trimmed);
                $html .= "<p>{$pText}</p>\n";
            }
        }

        if ($inList) {
            $html .= "</ul>\n";
        }

        return $html;
    }

    protected function parseInlineMarkdown(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*([^\*]+)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/`([^`]+)`/s', '<code>$1</code>', $text);
        $text = preg_replace('/\[(.*?)\]\((.*?)\)/s', '<a href="$2">$1</a>', $text);

        return $text;
    }
}