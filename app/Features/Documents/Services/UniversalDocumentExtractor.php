<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Universal Document Extractor
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Documents\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use ZipArchive;

class UniversalDocumentExtractor
{
    /**
     * Supported file extensions
     */
    public const SUPPORTED_EXTENSIONS = [
        'docx', 'pdf', 'md', 'markdown', 'txt', 'html', 'htm', 'csv', 'json'
    ];

    /**
     * Extract structured content from an uploaded file or file path
     *
     * @param UploadedFile|string $file
     * @param array $options Formatting and parsing options
     * @return array ['title' => string, 'html' => string, 'plain_text' => string, 'format' => string, 'metadata' => array]
     */
    public function extract($file, array $options = []): array
    {
        $options = array_merge([
            'clean_whitespace' => true,
            'preserve_headings' => true,
            'smart_typography' => true,
            'sanitize' => true,
        ], $options);

        if ($file instanceof UploadedFile) {
            $extension = mb_strtolower($file->getClientOriginalExtension());
            $originalName = $file->getClientOriginalName();
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $filePath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getClientMimeType();
        } else {
            $extension = mb_strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $originalName = basename($file);
            $filename = pathinfo($originalName, PATHINFO_FILENAME);
            $filePath = $file;
            $fileSize = file_exists($file) ? filesize($file) : 0;
            $mimeType = 'application/octet-stream';
        }

        $title = Str::headline($filename);

        $result = match ($extension) {
            'docx' => $this->extractDocx($filePath, $options),
            'pdf' => $this->extractPdf($filePath, $options),
            'md', 'markdown' => $this->extractMarkdown(file_get_contents($filePath), $options),
            'html', 'htm' => $this->extractHtml(file_get_contents($filePath), $options),
            'csv' => $this->extractCsv(file_get_contents($filePath), $options),
            'json' => $this->extractJson(file_get_contents($filePath), $options),
            default => $this->extractPlainText(file_get_contents($filePath), $options),
        };

        $html = $result['html'] ?? '<p></p>';
        if ($options['smart_typography']) {
            $html = $this->applySmartTypography($html);
        }

        $plainText = trim(strip_tags(str_replace(['</p>', '</h1>', '</h2>', '</h3>', '</h4>', '</li>', '</tr>'], "\n", $html)));
        $plainText = preg_replace("/\n{3,}/", "\n\n", $plainText);

        return [
            'title' => $result['title'] ?? $title,
            'html' => $html,
            'plain_text' => $plainText,
            'format' => $extension,
            'metadata' => array_merge([
                'original_name' => $originalName,
                'file_size' => $fileSize,
                'file_size_formatted' => $this->formatBytes($fileSize),
                'mime_type' => $mimeType,
                'extension' => strtoupper($extension),
            ], $result['metadata'] ?? []),
        ];
    }

    /**
     * Extract Microsoft Word (.docx) documents using native ZipArchive and XML parsing
     */
    protected function extractDocx(string $filePath, array $options): array
    {
        if (!class_exists('ZipArchive')) {
            throw new Exception("ZipArchive PHP extension is required to extract .docx documents.");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new Exception("Unable to open .docx archive.");
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xmlContent) {
            throw new Exception("The .docx file does not contain a valid word/document.xml component.");
        }

        // Clean namespaces for simplified XPath
        $xmlContent = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $xmlContent);
        $xmlContent = preg_replace('/[a-zA-Z0-9]+:([a-zA-Z0-9]+)/', '$1', $xmlContent);

        $htmlParts = [];
        $title = null;

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadXML($xmlContent, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);

        // Process elements in sequential body flow
        $bodyNodes = $xpath->query('//body/*');
        foreach ($bodyNodes as $node) {
            $nodeName = mb_strtolower($node->nodeName);

            if ($nodeName === 'p') {
                // Check if paragraph is a heading
                $styleNode = $xpath->query('.//pStyle/@val', $node)->item(0);
                $styleVal = $styleNode ? mb_strtolower($styleNode->nodeValue) : '';

                $pText = '';
                $runs = $xpath->query('.//r', $node);
                foreach ($runs as $r) {
                    $textNode = $xpath->query('.//t', $r)->item(0);
                    if (!$textNode) continue;
                    $chunk = htmlspecialchars($textNode->nodeValue, ENT_QUOTES, 'UTF-8');
                    $isBold = $xpath->query('.//b', $r)->length > 0;
                    $isItalic = $xpath->query('.//i', $r)->length > 0;
                    $isUnderline = $xpath->query('.//u', $r)->length > 0;
                    $isStrike = $xpath->query('.//strike', $r)->length > 0;

                    if ($isBold) $chunk = "<strong>{$chunk}</strong>";
                    if ($isItalic) $chunk = "<em>{$chunk}</em>";
                    if ($isUnderline) $chunk = "<u>{$chunk}</u>";
                    if ($isStrike) $chunk = "<s>{$chunk}</s>";

                    $pText .= $chunk;
                }

                $pText = trim($pText);
                if (empty($pText)) continue;

                if (str_contains($styleVal, 'heading1') || str_contains($styleVal, 'title')) {
                    if (!$title) $title = strip_tags($pText);
                    $htmlParts[] = "<h2>{$pText}</h2>";
                } elseif (str_contains($styleVal, 'heading2')) {
                    $htmlParts[] = "<h3>{$pText}</h3>";
                } elseif (str_contains($styleVal, 'heading3') || str_contains($styleVal, 'subtitle')) {
                    $htmlParts[] = "<h4>{$pText}</h4>";
                } else {
                    $htmlParts[] = "<p>{$pText}</p>";
                }
            } elseif ($nodeName === 'tbl') {
                // Process Word Table
                $tableHtml = '<div class="overflow-x-auto my-4"><table class="w-full border-collapse border border-white/15">';
                $rows = $xpath->query('.//tr', $node);
                $isFirstRow = true;

                foreach ($rows as $tr) {
                    $tableHtml .= '<tr>';
                    $cells = $xpath->query('.//tc', $tr);
                    foreach ($cells as $tc) {
                        $cellText = '';
                        $tcParas = $xpath->query('.//p', $tc);
                        foreach ($tcParas as $tcp) {
                            $cellText .= htmlspecialchars(trim($tcp->textContent), ENT_QUOTES, 'UTF-8') . ' ';
                        }
                        $cellText = trim($cellText);
                        $tag = $isFirstRow ? 'th' : 'td';
                        $classes = $isFirstRow ? 'border border-white/15 px-3 py-2 bg-white/10 font-bold text-white text-left' : 'border border-white/15 px-3 py-2 text-slate-300';
                        $tableHtml .= "<{$tag} class=\"{$classes}\">{$cellText}</{$tag}>";
                    }
                    $tableHtml .= '</tr>';
                    $isFirstRow = false;
                }
                $tableHtml .= '</table></div>';
                $htmlParts[] = $tableHtml;
            }
        }

        $html = implode("\n", $htmlParts);

        return [
            'title' => $title,
            'html' => !empty($html) ? $html : '<p>No readable content found in Word document.</p>',
            'metadata' => [
                'paragraphs_count' => count($htmlParts),
            ],
        ];
    }

    /**
     * Extract PDF document text using pure-PHP stream decoding
     */
    protected function extractPdf(string $filePath, array $options): array
    {
        $content = file_get_contents($filePath);
        if (!$content) {
            throw new Exception("Unable to read PDF file.");
        }

        // Extract text streams from PDF objects
        $text = '';
        $streamRegex = '/stream[\r\n]+(.*?)[\r\n]+endstream/s';

        if (preg_match_all($streamRegex, $content, $matches)) {
            foreach ($matches[1] as $stream) {
                // Try decompressing FlateDecode stream
                $uncompressed = false;
                if (function_exists('gzuncompress')) {
                    $uncompressed = @gzuncompress($stream);
                }
                $data = ($uncompressed !== false) ? $uncompressed : $stream;

                // Extract text chunks between BT (Begin Text) and ET (End Text)
                if (preg_match_all('/BT[\r\n]+(.*?)[\r\n]+ET/s', $data, $textBlocks)) {
                    foreach ($textBlocks[1] as $block) {
                        // Match strings in parentheses (text) or hex <...>
                        if (preg_match_all('/\((.*?)\)\s*T[jJ]/s', $block, $tMatches)) {
                            foreach ($tMatches[1] as $tm) {
                                $clean = stripcslashes($tm);
                                $text .= $clean . ' ';
                            }
                            $text .= "\n";
                        } elseif (preg_match_all('/\[(.*?)\]\s*TJ/s', $block, $tjMatches)) {
                            foreach ($tjMatches[1] as $tj) {
                                if (preg_match_all('/\((.*?)\)/s', $tj, $subMatches)) {
                                    foreach ($subMatches[1] as $sm) {
                                        $text .= stripcslashes($sm);
                                    }
                                }
                            }
                            $text .= "\n";
                        }
                    }
                }
            }
        }

        // Fallback: search for direct text strings in PDF
        if (empty(trim($text))) {
            preg_match_all('/\(([^\)]{4,})\)/s', $content, $rawStrings);
            if (!empty($rawStrings[1])) {
                $text = implode("\n", array_slice($rawStrings[1], 0, 100));
            }
        }

        $text = trim($text);
        if (empty($text)) {
            $html = '<p class="text-slate-400 italic">This PDF contains scanned images or encrypted text. Extracted text was empty.</p>';
        } else {
            // Convert lines into paragraphs and detect potential headings
            $lines = explode("\n", $text);
            $htmlParts = [];
            $para = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed)) {
                    if (!empty($para)) {
                        $htmlParts[] = '<p>' . htmlspecialchars(implode(' ', $para), ENT_QUOTES, 'UTF-8') . '</p>';
                        $para = [];
                    }
                    continue;
                }

                // Short lines in title case or uppercase can be treated as headings
                if (strlen($trimmed) < 65 && !str_ends_with($trimmed, '.') && count($para) === 0) {
                    $htmlParts[] = '<h3>' . htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8') . '</h3>';
                } else {
                    $para[] = $trimmed;
                }
            }

            if (!empty($para)) {
                $htmlParts[] = '<p>' . htmlspecialchars(implode(' ', $para), ENT_QUOTES, 'UTF-8') . '</p>';
            }

            $html = implode("\n", $htmlParts);
        }

        return [
            'title' => null,
            'html' => $html,
            'metadata' => [
                'extracted_chars' => strlen($text),
            ],
        ];
    }

    /**
     * Extract and parse Markdown content into clean semantic HTML
     */
    protected function extractMarkdown(string $content, array $options): array
    {
        $lines = explode("\n", $content);
        $html = '';
        $inList = false;
        $listType = 'ul';
        $inCodeBlock = false;
        $codeLang = '';
        $codeBuffer = [];
        $inTable = false;
        $tableRows = [];
        $tableHasHeader = false;
        $title = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            // Handle Code Fences
            if (str_starts_with($trimmed, '```')) {
                if ($inTable) {
                    $html .= $this->renderMarkdownTable($tableRows, $tableHasHeader);
                    $tableRows = [];
                    $tableHasHeader = false;
                    $inTable = false;
                }
                if ($inCodeBlock) {
                    $codeText = htmlspecialchars(implode("\n", $codeBuffer), ENT_QUOTES, 'UTF-8');
                    $langClass = $codeLang ? " class=\"language-{$codeLang}\"" : '';
                    $html .= "<pre{$langClass}><code{$langClass}>{$codeText}</code></pre>\n";
                    $inCodeBlock = false;
                    $codeLang = '';
                    $codeBuffer = [];
                    continue;
                } else {
                    if ($inList) {
                        $html .= "</{$listType}>\n";
                        $inList = false;
                    }
                    $inCodeBlock = true;
                    $codeLang = trim(substr($trimmed, 3));
                    continue;
                }
            }

            if ($inCodeBlock) {
                $codeBuffer[] = $line;
                continue;
            }

            if (empty($trimmed)) {
                if ($inList) {
                    $html .= "</{$listType}>\n";
                    $inList = false;
                }
                if ($inTable) {
                    $html .= $this->renderMarkdownTable($tableRows, $tableHasHeader);
                    $tableRows = [];
                    $tableHasHeader = false;
                    $inTable = false;
                }
                continue;
            }

            // Markdown Tables (| cell | cell |)
            if (str_starts_with($trimmed, '|') && str_ends_with($trimmed, '|')) {
                if ($inList) {
                    $html .= "</{$listType}>\n";
                    $inList = false;
                }
                // Check if this is delimiter row (| --- | :---: |)
                if (preg_match('/^\|(\s*:?-+:?\s*\|)+$/', $trimmed)) {
                    $tableHasHeader = true;
                    continue;
                }

                $cells = array_map('trim', explode('|', trim($trimmed, '|')));
                $tableRows[] = $cells;
                $inTable = true;
                continue;
            }

            if ($inTable) {
                $html .= $this->renderMarkdownTable($tableRows, $tableHasHeader);
                $tableRows = [];
                $tableHasHeader = false;
                $inTable = false;
            }

            // Headings (# to ######)
            if (preg_match('/^(#{1,6})\s+(.*)$/', $trimmed, $m)) {
                if ($inList) {
                    $html .= "</{$listType}>\n";
                    $inList = false;
                }
                $level = strlen($m[1]);
                $headingText = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
                if (!$title && $level <= 2) {
                    $title = $m[2];
                }
                $html .= "<h{$level}>{$headingText}</h{$level}>\n";
                continue;
            }

            // Horizontal Rule
            if (preg_match('/^(\-{3,}|\*{3,}|_{3,})$/', $trimmed)) {
                if ($inList) {
                    $html .= "</{$listType}>\n";
                    $inList = false;
                }
                $html .= "<hr />\n";
                continue;
            }

            // Blockquotes
            if (str_starts_with($trimmed, '>')) {
                if ($inList) {
                    $html .= "</{$listType}>\n";
                    $inList = false;
                }
                $quoteText = $this->parseInlineMarkdown(trim(substr($trimmed, 1)));
                $html .= "<blockquote><p>{$quoteText}</p></blockquote>\n";
                continue;
            }

            // Unordered Lists
            if (preg_match('/^[-*+]\s+(.*)$/', $trimmed, $m)) {
                if (!$inList || $listType !== 'ul') {
                    if ($inList) $html .= "</{$listType}>\n";
                    $html .= "<ul>\n";
                    $inList = true;
                    $listType = 'ul';
                }
                $itemText = $this->parseInlineMarkdown($m[1]);
                $html .= "<li>{$itemText}</li>\n";
                continue;
            }

            // Ordered Lists
            if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $m)) {
                if (!$inList || $listType !== 'ol') {
                    if ($inList) $html .= "</{$listType}>\n";
                    $html .= "<ol>\n";
                    $inList = true;
                    $listType = 'ol';
                }
                $itemText = $this->parseInlineMarkdown($m[1]);
                $html .= "<li>{$itemText}</li>\n";
                continue;
            }

            // Standard Paragraph
            if ($inList) {
                $html .= "</{$listType}>\n";
                $inList = false;
            }

            $pText = $this->parseInlineMarkdown($trimmed);
            $html .= "<p>{$pText}</p>\n";
        }

        if ($inList) {
            $html .= "</{$listType}>\n";
        }

        if ($inTable) {
            $html .= $this->renderMarkdownTable($tableRows, $tableHasHeader);
        }

        return [
            'title' => $title,
            'html' => $html,
            'metadata' => [
                'markdown_lines' => count($lines),
            ],
        ];
    }

    protected function renderMarkdownTable(array $rows, bool $hasHeader): string
    {
        if (empty($rows)) return '';

        $html = '<div class="overflow-x-auto my-4"><table class="w-full border-collapse border border-white/15">';

        if ($hasHeader && !empty($rows)) {
            $headerRow = array_shift($rows);
            $html .= '<thead><tr class="bg-white/10">';
            foreach ($headerRow as $cell) {
                $cellHtml = $this->parseInlineMarkdown($cell);
                $html .= "<th class=\"border border-white/20 px-3.5 py-2.5 text-left text-xs font-bold text-white uppercase tracking-wider\">{$cellHtml}</th>";
            }
            $html .= '</tr></thead>';
        }

        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr class="border-b border-white/10 hover:bg-white/5 transition-colors">';
            foreach ($row as $cell) {
                $cellHtml = $this->parseInlineMarkdown($cell);
                $html .= "<td class=\"border border-white/15 px-3.5 py-2 text-xs text-slate-300\">{$cellHtml}</td>";
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>' . "\n";

        return $html;
    }

    /**
     * Extract and sanitize HTML documents
     */
    protected function extractHtml(string $content, array $options): array
    {
        $title = null;
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $content, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1])));
        }

        // Extract body if present
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $m)) {
            $content = $m[1];
        }

        if ($options['sanitize']) {
            $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
            $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content);
            $content = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $content);
            $content = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $content);
            $content = preg_replace('/<embed\b[^>]*>(.*?)<\/embed>/is', '', $content);
            // Remove on* event handlers
            $content = preg_replace('/\s*on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $content);
        }

        return [
            'title' => $title,
            'html' => trim($content),
            'metadata' => [],
        ];
    }

    /**
     * Extract CSV data and convert into interactive TipTap-ready table grid
     */
    protected function extractCsv(string $content, array $options): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        if (empty($lines)) {
            return ['html' => '<p>Empty CSV file.</p>', 'metadata' => []];
        }

        $delimiter = str_contains($lines[0], ';') ? ';' : (str_contains($lines[0], "\t") ? "\t" : ',');

        $html = '<div class="overflow-x-auto my-6"><table class="w-full border-collapse border border-white/20 rounded-2xl overflow-hidden">';
        $isFirst = true;
        $rowCount = 0;
        $colCount = 0;

        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            $row = str_getcsv($line, $delimiter);
            if (empty($row)) continue;

            $rowCount++;
            if ($isFirst) {
                $colCount = count($row);
                $html .= '<thead><tr class="bg-white/10">';
                foreach ($row as $cell) {
                    $cellVal = htmlspecialchars(trim($cell), ENT_QUOTES, 'UTF-8');
                    $html .= "<th class=\"border border-white/20 px-3.5 py-2.5 text-left text-xs font-bold text-white uppercase tracking-wider\">{$cellVal}</th>";
                }
                $html .= '</tr></thead><tbody>';
                $isFirst = false;
            } else {
                $html .= '<tr class="border-b border-white/10 hover:bg-white/5 transition-colors">';
                foreach ($row as $cell) {
                    $cellVal = htmlspecialchars(trim($cell), ENT_QUOTES, 'UTF-8');
                    $html .= "<td class=\"border border-white/15 px-3.5 py-2 text-xs text-slate-300\">{$cellVal}</td>";
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></div>';

        return [
            'title' => null,
            'html' => $html,
            'metadata' => [
                'rows_count' => $rowCount,
                'columns_count' => $colCount,
            ],
        ];
    }

    /**
     * Extract JSON document or TipTap AST
     */
    protected function extractJson(string $content, array $options): array
    {
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->extractPlainText($content, $options);
        }

        // Check if this is a TipTap JSON document
        if (isset($data['type']) && $data['type'] === 'doc' && isset($data['content']) && is_array($data['content'])) {
            $html = $this->convertTipTapNodeToHtml($data);
            return [
                'title' => null,
                'html' => $html,
                'metadata' => ['is_tiptap_ast' => true],
            ];
        }

        // Regular JSON formatted as readable code
        $formatted = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $html = '<pre class="language-json"><code class="language-json">' . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8') . '</code></pre>';

        return [
            'title' => null,
            'html' => $html,
            'metadata' => ['keys_count' => count($data)],
        ];
    }

    /**
     * Extract plain text document into formatted paragraphs and lists
     */
    protected function extractPlainText(string $content, array $options): array
    {
        $paragraphs = array_filter(array_map('trim', explode("\n\n", $content)));
        if (empty($paragraphs)) {
            return ['html' => '<p>' . nl2br(htmlspecialchars($content, ENT_QUOTES, 'UTF-8')) . '</p>', 'metadata' => []];
        }

        $html = '';
        foreach ($paragraphs as $p) {
            $lines = explode("\n", $p);
            $isList = false;
            foreach ($lines as $l) {
                if (preg_match('/^(\*|-|•|\d+\.)\s+/', trim($l))) {
                    $isList = true;
                    break;
                }
            }

            if ($isList) {
                $html .= "<ul>\n";
                foreach ($lines as $l) {
                    $item = preg_replace('/^(\*|-|•|\d+\.)\s+/', '', trim($l));
                    $html .= "<li>" . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . "</li>\n";
                }
                $html .= "</ul>\n";
            } else {
                $html .= '<p>' . nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8')) . '</p>' . "\n";
            }
        }

        return [
            'title' => null,
            'html' => $html,
            'metadata' => ['paragraphs_count' => count($paragraphs)],
        ];
    }

    /**
     * Convert TipTap JSON document node to HTML
     */
    protected function convertTipTapNodeToHtml(array $node): string
    {
        $type = $node['type'] ?? '';
        $content = $node['content'] ?? [];
        $attrs = $node['attrs'] ?? [];
        $text = $node['text'] ?? '';

        if ($type === 'text') {
            $str = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
            if (!empty($node['marks'])) {
                foreach ($node['marks'] as $mark) {
                    $mType = $mark['type'] ?? '';
                    if ($mType === 'bold') $str = "<strong>{$str}</strong>";
                    elseif ($mType === 'italic') $str = "<em>{$str}</em>";
                    elseif ($mType === 'strike') $str = "<s>{$str}</s>";
                    elseif ($mType === 'code') $str = "<code>{$str}</code>";
                    elseif ($mType === 'link' && !empty($mark['attrs']['href'])) {
                        $href = htmlspecialchars($mark['attrs']['href'], ENT_QUOTES, 'UTF-8');
                        $str = "<a href=\"{$href}\">{$str}</a>";
                    }
                }
            }
            return $str;
        }

        $innerHtml = '';
        foreach ($content as $child) {
            $innerHtml .= $this->convertTipTapNodeToHtml($child);
        }

        return match ($type) {
            'doc' => $innerHtml,
            'paragraph' => "<p>{$innerHtml}</p>\n",
            'heading' => "<h" . ($attrs['level'] ?? 2) . ">{$innerHtml}</h" . ($attrs['level'] ?? 2) . ">\n",
            'bulletList' => "<ul>\n{$innerHtml}</ul>\n",
            'orderedList' => "<ol>\n{$innerHtml}</ol>\n",
            'listItem' => "<li>{$innerHtml}</li>\n",
            'blockquote' => "<blockquote>{$innerHtml}</blockquote>\n",
            'codeBlock' => "<pre><code>{$innerHtml}</code></pre>\n",
            'horizontalRule' => "<hr />\n",
            default => $innerHtml,
        };
    }

    protected function parseInlineMarkdown(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*([^\*]+)\*/s', '<em>$1</em>', $text);
        $text = preg_replace('/~~(.*?)~~/s', '<s>$1</s>', $text);
        $text = preg_replace('/`([^`]+)`/s', '<code>$1</code>', $text);
        $text = preg_replace('/\[(.*?)\]\((.*?)\)/s', '<a href="$2">$1</a>', $text);

        return $text;
    }

    protected function applySmartTypography(string $html): string
    {
        // Only apply curly quotes and typography outside of HTML tags
        $parts = preg_split('/(<[^>]+>)/s', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return $html;
        }

        foreach ($parts as $i => $part) {
            if ($i % 2 === 0) {
                // Curly double quotes
                $parts[$i] = preg_replace('/"([^"]+)"/', '“$1”', $parts[$i]);
                // Em-dashes
                $parts[$i] = str_replace(' -- ', ' — ', $parts[$i]);
                // Ellipsis
                $parts[$i] = str_replace('...', '…', $parts[$i]);
            }
        }

        return implode('', $parts);
    }

    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}