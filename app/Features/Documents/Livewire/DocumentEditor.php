<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Livewire Master Controller: Document Editor
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

namespace App\Features\Documents\Livewire;

use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\AI\Services\UserFeedbackIntelligenceService;
use App\Features\Blog\Models\BlogPost;
use App\Features\Documents\Actions\RestoreDocumentVersion;
use App\Features\Documents\Actions\SaveDocumentVersion;
use App\Features\Documents\Contracts\EditorRegistry;
use App\Features\Documents\Livewire\Concerns\HasBlogPublishing;
use App\Features\Documents\Livewire\Concerns\HasBrainLineage;
use App\Features\Documents\Livewire\Concerns\HasDocumentImporting;
use App\Features\Documents\Livewire\Concerns\HasDocumentSharing;
use App\Features\Documents\Livewire\Concerns\HasSeoAuditing;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentVersion;
use App\Features\Projects\Models\Project;
use App\Features\SEO\Models\SeoAnalysis;
use App\Features\SEO\Services\SeoAnalyzer;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.workspace')]
class DocumentEditor extends Component
{
    use HasBlogPublishing;
    use HasBrainLineage;
    use HasDocumentImporting;
    use HasDocumentSharing;
    use HasSeoAuditing;
    use WithFileUploads;

    public int $documentId;

    public ?Document $document = null;

    public string $title = '';

    public string $contentHtml = '';

    // Universal File Import Studio State
    public bool $showImportModal = false;

    public $importFile = null;

    public bool $isExtracting = false;

    public ?array $extractedDocument = null;

    public string $importInsertMode = 'replace'; // 'replace', 'append', 'cursor', 'new_doc'

    public string $importActiveTab = 'preview'; // 'preview', 'analysis', 'raw'

    public array $importFormatOptions = [
        'clean_whitespace' => true,
        'preserve_headings' => true,
        'smart_typography' => true,
    ];

    public string $importErrorMessage = '';

    public string $importSuccessMessage = '';

    public ?int $projectId = null;

    public string $status = 'draft';

    public string $editorType = 'tiptap';

    public int $wordCount = 0;

    public int $characterCount = 0;

    public int $readingTimeMinutes = 1;

    public string $lastSavedAt = '';

    public bool $isSaving = false;

    public string $saveStatusText = '';

    public bool $hasUnsavedChanges = false;

    // SEO Analysis State
    public bool $showSeoDrawer = false;

    public int $seoScore = 0;

    public array $seoData = [];

    public bool $isAnalyzingSeo = false;

    public bool $isGeneratingSeo = false;

    public string $aiSeoType = '';

    public array $aiSeoResults = [];

    public string $seoErrorMessage = '';

    public string $targetKeyword = '';

    public array $secondaryKeywords = [];

    public string $newSecondaryKeyword = '';

    public string $metaDescription = '';

    public array $aiTitles = [];

    public array $aiMetaDescriptions = [];

    public array $aiLsiKeywords = [];

    public array $aiFaqs = [];

    public string $aiQuickAnswer = '';

    public array $aiContentGaps = [];

    public array $aiQualityAudit = [];

    // Version Control State
    public bool $showVersionHistory = false;

    public ?int $compareVersionId = null;

    public ?string $compareVersionHtml = null;

    // Sharing State
    public bool $showShareModal = false;

    public ?string $shareToken = null;

    public bool $isShareActive = false;

    public bool $shareAllowCopy = true;

    public bool $shareAllowDownload = true;

    public ?string $sharePassword = null;

    public ?int $shareExpiryDays = null;

    public int $shareViewCount = 0;

    public string $shareUrl = '';

    // Blog Publishing State
    public bool $showBlogModal = false;

    public bool $isPublishedToBlog = false;

    public ?int $blogPostId = null;

    public string $blogTitle = '';

    public string $blogSlug = '';

    public string $blogCategory = 'Artificial Intelligence';

    public string $blogTags = '';

    public string $blogFeaturedImage = '';

    public $featuredImageUpload = null;

    public string $blogExcerpt = '';

    public string $blogStatus = 'published';

    public bool $blogIsFeatured = false;

    public int $blogViewsCount = 0;

    public array $blogCategories = [];

    public ?string $blogPublishedUrl = null;

    // Content Brain State
    public string $brainActiveTab = 'lineage';

    public string $brainActiveSubTab = 'rules';

    public ?int $selectedLineageNodeId = null;

    public ?array $brainSelectedTrace = null;

    public int $brainStaleNodesCount = 0;

    public array $brainLineageNodes = [];

    public int $brainHealthScore = 95;

    public string $brainHealthGrade = 'A';

    public array $brainHealthDimensions = [];

    public array $brainHealthRecommendations = [];

    public array $brainStyleRules = [];

    public array $brainCannibalization = [];

    public array $brainInternalLinks = [];

    public ?array $brainGenomeSnapshot = null;

    public string $brainStatusMessage = '';

    public bool $isBrainLoading = false;

    public function mount(int $id, SeoAnalyzer $analyzer)
    {
        $document = Document::with(['content', 'project'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->document = $document;
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

        // Load existing SEO analysis target keyword if available
        $existingSeo = SeoAnalysis::where('document_id', $document->id)->first();
        if ($existingSeo) {
            $this->targetKeyword = $existingSeo->target_keyword ?? '';
            $this->secondaryKeywords = $existingSeo->secondary_keywords ?? [];
            $this->metaDescription = $existingSeo->metrics['meta_description'] ?? '';
        }

        // Always run comprehensive analysis to guarantee rank_math pillars, recommendations exist
        $this->seoData = $analyzer->analyze(
            $this->contentHtml,
            $this->title,
            $this->targetKeyword ?: null,
            $this->secondaryKeywords ?? [],
            $this->metaDescription
        );
        unset($this->seoData['marked_html']);

        $this->generateQualityAudit();
        $this->loadBlogState();
        $this->loadShareState();
        $this->loadBrainState();
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

        $oldPlain = $document->content->content_plain ?? null;

        if ($document->content) {
            $document->content->update([
                'content_html' => $html,
                'content_json' => $json,
                'content_plain' => $plain,
            ]);
        }

        // Continual background learning from manual user prose modifications
        if ($oldPlain && $plain && $oldPlain !== $plain && abs(strlen($plain) - strlen($oldPlain)) > 15) {
            try {
                app(UserFeedbackIntelligenceService::class)->analyzeDiffAndRecordPreference(
                    userId: $user->id,
                    originalText: $oldPlain,
                    editedText: $plain
                );
            } catch (\Throwable $e) {
                // Defensive suppression to protect autosave stability
            }
        }

        $safeTitle = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($this->title ?: 'Untitled Document'))), 0, 190);
        $this->title = $safeTitle;

        $document->update([
            'title' => $safeTitle,
            'project_id' => $this->projectId,
            'status' => $this->status,
            'word_count' => $this->wordCount,
            'character_count' => $this->characterCount,
            'reading_time_minutes' => $this->readingTimeMinutes,
        ]);

        $this->isSaving = false;
        $this->lastSavedAt = now()->format('H:i:s');
        $this->saveStatusText = 'Saved at '.$this->lastSavedAt;
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
        session()->flash('status', 'Restored to Version #'.$version->version_number);
    }

    public function getVersionContent(int $id): string
    {
        $version = DocumentVersion::where('document_id', $this->documentId)->find($id);

        return $version?->content_html ?? '';
    }

    public function render()
    {
        $with = [
            'project',
            'versions' => function ($query) {
                $query->select(['id', 'document_id', 'created_by', 'version_number', 'word_count', 'summary', 'operation_type', 'created_at'])
                    ->orderBy('version_number', 'desc');
            },
        ];
        if ($this->showVersionHistory) {
            $with['versions'] = function ($query) {
                $query->with('creator')
                    ->select(['id', 'document_id', 'created_by', 'version_number', 'word_count', 'summary', 'operation_type', 'created_at'])
                    ->orderBy('version_number', 'desc');
            };
        }

        $document = Document::with($with)->findOrFail($this->documentId);
        $projects = Project::where('user_id', Auth::id())->get();
        $availableEditors = EditorRegistry::getAvailableEditors();

        $availableAiModels = AiModel::where('is_active', true)
            ->orderBy('is_combo', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $availableProviders = AiProvider::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_local']);

        $blogCategories = BlogPost::defaultCategories();

        return view('editor.editor', [
            'document' => $document,
            'projects' => $projects,
            'availableEditors' => $availableEditors,
            'availableAiModels' => $availableAiModels,
            'availableProviders' => $availableProviders,
            'blogCategories' => $blogCategories,
        ]);
    }
}
