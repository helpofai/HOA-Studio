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

namespace App\Features\Documents\Http\Controllers;

use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentShare;
use App\Features\Documents\Services\DocumentExporter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ExportDocumentController extends Controller
{
    public function __construct(
        protected DocumentExporter $exporter
    ) {}

    /**
     * Export document for authenticated users
     */
    public function export(Request $request, int $id, string $format): Response
    {
        $document = Document::with('content')->where('user_id', Auth::id())->findOrFail($id);

        return $this->generateDownloadResponse($document, $format);
    }

    /**
     * Export document from public share link
     */
    public function exportPublic(Request $request, string $token, string $format): Response
    {
        $share = DocumentShare::with(['document.content'])
            ->where('share_token', $token)
            ->where('is_active', true)
            ->firstOrFail();

        if ($share->isExpired()) {
            abort(410, 'This shared document link has expired.');
        }

        if (!$share->allow_download) {
            abort(403, 'Downloads are disabled for this shared document.');
        }

        return $this->generateDownloadResponse($share->document, $format);
    }

    /**
     * Render print-ready PDF layout
     */
    public function printPdf(Request $request, int $id): Response
    {
        $document = Document::with('content')->where('user_id', Auth::id())->findOrFail($id);
        $html = $this->exporter->exportHtml($document);

        // Inject auto print trigger script
        $autoPrintScript = '<script>window.addEventListener("DOMContentLoaded", () => setTimeout(() => window.print(), 300));</script>';
        $html = str_replace('</body>', $autoPrintScript . '</body>', $html);

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Generate response with proper Content-Type and download headers
     */
    protected function generateDownloadResponse(Document $document, string $format): Response
    {
        $slug = Str::slug($document->title) ?: 'document';

        switch (mb_strtolower($format)) {
            case 'md':
            case 'markdown':
                $content = $this->exporter->exportMarkdown($document);
                return response($content)
                    ->header('Content-Type', 'text/markdown; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $slug . '.md"');

            case 'html':
                $content = $this->exporter->exportHtml($document);
                return response($content)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $slug . '.html"');

            case 'txt':
            case 'text':
                $content = $this->exporter->exportPlainText($document);
                return response($content)
                    ->header('Content-Type', 'text/plain; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $slug . '.txt"');

            case 'docx':
            case 'doc':
                $content = $this->exporter->exportDocx($document);
                return response($content)
                    ->header('Content-Type', 'application/vnd.ms-word; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $slug . '.doc"');

            case 'json':
            case 'ast':
                $content = $this->exporter->exportJson($document);
                return response($content)
                    ->header('Content-Type', 'application/json; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $slug . '.json"');

            default:
                abort(400, 'Unsupported export format.');
        }
    }
}