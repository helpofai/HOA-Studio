<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Livewire Concern: Document Importing
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

namespace App\Features\Documents\Livewire\Concerns;

use App\Features\Documents\Models\Document;
use App\Features\Documents\Services\DocumentImporter;
use App\Features\Documents\Services\DocumentTextAnalyzer;
use App\Features\Documents\Services\UniversalDocumentExtractor;
use Exception;
use Illuminate\Support\Facades\Auth;

trait HasDocumentImporting
{
    public function openImportModal(): void
    {
        $this->resetImportState();
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->resetImportState();
    }

    public function resetImportState(): void
    {
        $this->importFile = null;
        $this->isExtracting = false;
        $this->extractedDocument = null;
        $this->importInsertMode = 'replace';
        $this->importActiveTab = 'preview';
        $this->importErrorMessage = '';
        $this->importSuccessMessage = '';
    }

    public function updatedImportFile(): void
    {
        $this->validate([
            'importFile' => 'required|file|max:20480',
        ]);

        $this->processUploadedImportFile();
    }

    public function reprocessImport(): void
    {
        if ($this->importFile) {
            $this->processUploadedImportFile();
        }
    }

    protected function processUploadedImportFile(): void
    {
        $this->isExtracting = true;
        $this->importErrorMessage = '';
        $this->importSuccessMessage = '';

        try {
            $extractor = app(UniversalDocumentExtractor::class);
            $analyzer = app(DocumentTextAnalyzer::class);

            $extracted = $extractor->extract($this->importFile, $this->importFormatOptions);
            $analysis = $analyzer->analyze($extracted['plain_text'], $extracted['html']);

            $this->extractedDocument = [
                'title' => $extracted['title'],
                'html' => $extracted['html'],
                'plain_text' => $extracted['plain_text'],
                'format' => $extracted['format'],
                'metadata' => $extracted['metadata'],
                'metrics' => $analysis['metrics'],
                'readability' => $analysis['readability'],
                'tone' => $analysis['tone'],
                'keywords' => $analysis['keywords'],
                'summary' => $analysis['summary'],
            ];

            $this->importSuccessMessage = 'Document extracted and analyzed successfully.';
        } catch (Exception $e) {
            $this->importErrorMessage = 'Extraction Error: '.$e->getMessage();
            $this->extractedDocument = null;
        } finally {
            $this->isExtracting = false;
        }
    }

    public function confirmImport(): void
    {
        if (! $this->extractedDocument || empty($this->extractedDocument['html'])) {
            $this->importErrorMessage = 'No valid extracted content found to import.';

            return;
        }

        $html = $this->extractedDocument['html'];
        $title = $this->extractedDocument['title'];
        $mode = $this->importInsertMode;

        if ($mode === 'new_doc') {
            $importer = app(DocumentImporter::class);
            $user = Auth::user();
            $newDoc = $importer->importFromText(
                $user,
                $title ?: 'Imported '.strtoupper($this->extractedDocument['format']).' Document',
                $this->extractedDocument['plain_text'],
                'html',
                $this->projectId
            );

            if ($newDoc->content) {
                $newDoc->content->update([
                    'content_html' => $html,
                    'content_plain' => $this->extractedDocument['plain_text'],
                ]);
            }

            session()->flash('status', "Document '{$newDoc->title}' created successfully from import.");
            $this->showImportModal = false;
            $this->resetImportState();
            $this->redirectRoute('documents.editor', ['id' => $newDoc->id]);

            return;
        }

        if ($mode === 'replace' && ! empty($title) && (empty($this->title) || $this->title === 'Untitled Document')) {
            $this->title = $title;
            Document::where('id', $this->documentId)
                ->where('user_id', Auth::id())
                ->update(['title' => $title]);
        }

        $this->dispatch('editor:insertImportedContent', [
            'content' => $html,
            'mode' => $mode,
            'title' => $title,
        ]);

        $this->showImportModal = false;
        $this->resetImportState();
        session()->flash('status', 'Content successfully imported into editor canvas.');
    }
}
