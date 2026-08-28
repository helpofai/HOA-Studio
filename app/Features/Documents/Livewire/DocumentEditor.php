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

namespace App\Features\Documents\Livewire;

use App\Features\AI\Models\AiProvider;
use App\Features\Documents\Actions\CreateDocumentShare;
use App\Features\Documents\Actions\RestoreDocumentVersion;
use App\Features\Documents\Actions\RevokeDocumentShare;
use App\Features\Documents\Actions\SaveDocumentVersion;
use App\Features\Documents\Contracts\EditorRegistry;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentShare;
use App\Features\Documents\Models\DocumentVersion;
use App\Features\Projects\Models\Project;
use App\Features\SEO\Actions\AnalyzeDocumentSeo;
use App\Features\SEO\Actions\GenerateSeoMetadata;
use App\Features\SEO\Models\SeoAnalysis;
use App\Features\SEO\Services\SeoAnalyzer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
class DocumentEditor extends Component
{
    public int $documentId;
    public string $title = '';
    public string $contentHtml = '';
    public ?int $projectId = null;
    public string $status = 'draft';
    public string $editorType = 'tiptap';

    public int $wordCount = 0;
    public int $characterCount = 0;
    public int $readingTimeMinutes = 1;

    public bool $isSaving = false;
    public string $saveStatusText = 'All changes saved';
    public ?string $lastSavedAt = null;

    public bool $showVersionHistory = false;

    // Document Sharing State
    public bool $showShareModal = false;
    public ?string $shareToken = null;
    public string $sharePassword = '';
    public bool $shareAllowCopy = true;
    public bool $shareAllowDownload = true;
    public ?int $shareExpiryDays = null;
    public int $shareViewCount = 0;
    public bool $isShareActive = false;
    public string $shareUrl = '';

    // Real-Time SEO Intelligence State
    public bool $showSeoDrawer = false;
    public string $targetKeyword = '';
    public array $secondaryKeywords = [];
    public string $newSecondaryKeyword = '';
    public ?array $seoData = null;
    public bool $isAnalyzingSeo = false;

    // AI SEO Generator State
    public bool $isGeneratingSeo = false;
    public string $aiSeoType = '';
    public array $aiSeoResults = [];
    public string $seoErrorMessage = '';
    public string $metaDescription = '';
    public array $aiTitles = [];
    public array $aiMetaDescriptions = [];
    public array $aiFaqs = [];
    public string $aiQuickAnswer = '';
    public array $aiContentGaps = [];
    public ?array $aiQualityAudit = null;

    public function mount(int $id, SeoAnalyzer $analyzer)
    {
        $document = Document::with(['content', 'project'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->documentId = $document->id;
        $this->title = $document->title;
        $this->contentHtml = $document->content->content_html ?? '<p></p>';
        $this->projectId = $document->project_id;
        $this->status = $document->status;
        $this->editorType = $document->editor_type ?? 'tiptap';
        $this->wordCount = $document->word_count;
        $this->characterCount = $document->character_count;
        $this->readingTimeMinutes = $document->reading_time_minutes;
        $this->lastSavedAt = $document->updated_at->format('H:i:s');

        // Load existing SEO analysis if available
        $existingSeo = SeoAnalysis::where('document_id', $document->id)->first();
        if ($existingSeo) {
            $this->targetKeyword = $existingSeo->target_keyword ?? '';
            $this->secondaryKeywords = $existingSeo->secondary_keywords ?? [];
            $this->seoData = [
                'score' => $existingSeo->score,
                'readability_score' => $existingSeo->readability_score,
                'metrics' => $existingSeo->metrics,
                'recommendations' => $existingSeo->recommendations,
            ];
        } else {
            $this->seoData = $analyzer->analyze($this->contentHtml, $this->title, null, []);
        }
    }

    #[On('autosave')]
    public function autosave(string $html, ?array $json = null, ?SaveDocumentVersion $action = null)
    {
        $this->isSaving = true;
        $this->saveStatusText = 'Saving...';
        $this->contentHtml = $html;

        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($this->documentId);

        $plain = strip_tags($html);
        $this->wordCount = str_word_count($plain);
        $this->characterCount = mb_strlen($plain);
        $this->readingTimeMinutes = max(1, (int) ceil($this->wordCount / 200));

        if ($document->content) {
            $document->content->update([
                'content_html' => $html,
                'content_json' => $json,
                'content_plain' => $plain,
            ]);
        }

        $document->update([
            'title' => $this->title,
            'project_id' => $this->projectId,
            'status' => $this->status,
            'word_count' => $this->wordCount,
            'character_count' => $this->characterCount,
            'reading_time_minutes' => $this->readingTimeMinutes,
        ]);

        $this->isSaving = false;
        $this->lastSavedAt = now()->format('H:i:s');
        $this->saveStatusText = 'Saved at ' . $this->lastSavedAt;
    }

    public function runSeoAudit(?SeoAnalyzer $analyzer = null)
    {
        $analyzer = $analyzer ?? app(SeoAnalyzer::class);
        $this->isAnalyzingSeo = true;
        
        try {
            $this->seoData = $analyzer->analyze(
                $this->contentHtml,
                $this->title,
                $this->targetKeyword ?: null,
                $this->secondaryKeywords
            );
            
            SeoAnalysis::updateOrCreate(
                ['document_id' => $this->documentId],
                [
                    'target_keyword' => $this->targetKeyword,
                    'secondary_keywords' => $this->secondaryKeywords,
                    'score' => $this->seoData['score'] ?? 0,
                    'readability_score' => $this->seoData['readability_score'] ?? 0,
                    'metrics' => $this->seoData['metrics'] ?? [],
                    'recommendations' => $this->seoData['recommendations'] ?? [],
                ]
            );
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isAnalyzingSeo = false;
        }
    }

    public function queueSeoAudit()
    {
        $this->runSeoAudit();
    }

    public function addSecondaryKeyword()
    {
        $kw = trim($this->newSecondaryKeyword);
        if (!empty($kw) && !in_array($kw, $this->secondaryKeywords)) {
            $this->secondaryKeywords[] = $kw;
            $this->newSecondaryKeyword = '';
            $this->queueSeoAudit();
        }
    }

    public function removeSecondaryKeyword(int $index)
    {
        if (isset($this->secondaryKeywords[$index])) {
            unset($this->secondaryKeywords[$index]);
            $this->secondaryKeywords = array_values($this->secondaryKeywords);
            $this->queueSeoAudit();
        }
    }

    public function generateSeoTitles(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'titles';
        $this->seoErrorMessage = '';

        try {
            $this->aiTitles = $generator->generateTitles(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = $this->aiTitles;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateMetaDescriptions(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'metas';
        $this->seoErrorMessage = '';

        try {
            $this->aiMetaDescriptions = $generator->generateMetaDescriptions(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = $this->aiMetaDescriptions;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function suggestLsiKeywords(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'lsi';
        $this->seoErrorMessage = '';

        try {
            $this->aiSeoResults = $generator->suggestKeywords(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateFaqSuggestions(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'faqs';
        $this->seoErrorMessage = '';

        try {
            $this->aiFaqs = $generator->generateFaqs(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateQuickAnswer(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'quick_answer';
        $this->seoErrorMessage = '';

        try {
            $this->aiQuickAnswer = $generator->generateQuickAnswer(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateContentGaps(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'gaps';
        $this->seoErrorMessage = '';

        try {
            $this->aiContentGaps = $generator->generateContentGaps(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateQualityAudit()
    {
        $words = $this->wordCount;
        $score = $this->seoData['score'] ?? 75;
        $read = $this->seoData['readability_score'] ?? 70;

        $this->aiQualityAudit = [
            'search_intent' => min(100, max(60, $score + 5)),
            'topic_coverage' => $words > 1200 ? 95 : ($words > 600 ? 82 : 64),
            'original_value' => 88,
            'readability' => $read,
            'seo_structure' => $score,
            'internal_linking' => 80,
            'eeat_signals' => min(100, max(65, $score + 2)),
            'technical_seo' => 96,
            'overall' => round(($score + $read + 88 + 96) / 4),
        ];
    }

    #[On('updateTitle')]
    public function applyTitle(string $newTitle)
    {
        $this->title = $newTitle;
        Document::where('id', $this->documentId)->update(['title' => $newTitle]);
        $this->queueSeoAudit();
    }

    public function applyMetaDescription(string $meta)
    {
        $this->metaDescription = $meta;
        session()->flash('status', 'Meta description updated!');
    }

    public function addSuggestedKeyword(string $kw)
    {
        if (!in_array($kw, $this->secondaryKeywords)) {
            $this->secondaryKeywords[] = $kw;
            $this->queueSeoAudit();
        }
    }

    public function saveExplicitSnapshot(SaveDocumentVersion $action)
    {
        $user = Auth::user();
        $document = Document::with('content')->where('user_id', $user->id)->findOrFail($this->documentId);

        $action->execute($document, $user, [
            'title' => $this->title,
            'content_html' => $this->contentHtml,
            'operation_type' => 'manual_save',
            'summary' => 'Manual snapshot created',
        ]);

        $this->saveStatusText = 'New snapshot created';
    }

    public function restoreVersion(int $versionId, RestoreDocumentVersion $action)
    {
        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($this->documentId);
        $version = DocumentVersion::where('document_id', $document->id)->findOrFail($versionId);

        $restored = $action->execute($document, $version, $user);

        $this->title = $restored->title;
        $this->contentHtml = $restored->content->content_html;
        $this->wordCount = $restored->word_count;
        $this->characterCount = $restored->character_count;
        $this->readingTimeMinutes = $restored->reading_time_minutes;
        $this->showVersionHistory = false;

        $this->dispatch('editor:setContent', content: $this->contentHtml);
        session()->flash('status', 'Restored to Version #' . $version->version_number);
    }

    #[On('switchEditorType')]
    public function switchEditorType(string $type = '', string $newType = '')
    {
        $target = !empty($type) ? $type : $newType;
        if (EditorRegistry::isValidEditor($target)) {
            $this->editorType = $target;
            Document::where('id', $this->documentId)->update(['editor_type' => $target]);
            $this->dispatch('editor:reload', editorType: $target);
        }
    }

    public function openShareModal()
    {
        $this->loadShareState();
        $this->showShareModal = true;
    }

    public function loadShareState()
    {
        $share = DocumentShare::where('document_id', $this->documentId)
            ->where('is_active', true)
            ->first();

        if ($share) {
            $this->shareToken = $share->share_token;
            $this->isShareActive = true;
            $this->shareAllowCopy = $share->allow_copy;
            $this->shareAllowDownload = $share->allow_download;
            $this->shareViewCount = $share->view_count;
            $this->shareUrl = route('public.share', ['token' => $share->share_token]);
        } else {
            $this->shareToken = null;
            $this->isShareActive = false;
            $this->shareUrl = '';
        }
    }

    public function createOrUpdateShare(CreateDocumentShare $action)
    {
        $document = Document::where('user_id', Auth::id())->findOrFail($this->documentId);

        $share = $action->execute($document, [
            'password' => !empty($this->sharePassword) ? $this->sharePassword : null,
            'allow_copy' => $this->shareAllowCopy,
            'allow_download' => $this->shareAllowDownload,
            'expires_in_days' => $this->shareExpiryDays,
        ]);

        $this->loadShareState();
        session()->flash('share_status', 'Share link generated successfully!');
    }

    public function revokeShare(RevokeDocumentShare $action)
    {
        $share = DocumentShare::where('document_id', $this->documentId)
            ->where('is_active', true)
            ->first();

        if ($share) {
            $action->execute($share);
        }

        $this->loadShareState();
        session()->flash('share_status', 'Share link has been revoked.');
    }

public function render()
    {
        $document = Document::with(['versions.creator', 'project'])->findOrFail($this->documentId);
        $projects = Project::where('user_id', Auth::id())->get();
        $availableEditors = EditorRegistry::getAvailableEditors();

        // Cache AI models & providers for 5 minutes — avoids repeated DB hits on every Livewire re-render
        $availableAiModels = cache()->remember('hoa_ai_models_active', 300, function () {
            return \App\Features\AI\Models\AiModel::where('is_active', true)
                ->orderBy('is_combo', 'desc')
                ->orderBy('id', 'asc')
                ->get();
        });

        $availableProviders = cache()->remember('hoa_ai_providers_active', 300, function () {
            return AiProvider::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'is_local']);
        });

        return view('documents.editor', [
            'document'           => $document,
            'projects'           => $projects,
            'availableEditors'   => $availableEditors,
            'availableAiModels'  => $availableAiModels,
            'availableProviders' => $availableProviders,
        ]);
    }
}