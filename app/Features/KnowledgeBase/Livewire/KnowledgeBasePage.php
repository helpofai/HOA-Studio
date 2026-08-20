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

namespace App\Features\KnowledgeBase\Livewire;

use App\Features\KnowledgeBase\Actions\CreateKnowledgeSource;
use App\Features\KnowledgeBase\Actions\DeleteKnowledgeSource;
use App\Features\KnowledgeBase\Actions\ProcessKnowledgeSource;
use App\Features\KnowledgeBase\Actions\RetrieveRagContext;
use App\Features\KnowledgeBase\Models\KnowledgeSource;
use App\Features\Projects\Models\Project;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('Knowledge Base & RAG Pipeline — HelpOfAi Studio')]
class KnowledgeBasePage extends Component
{
    public bool $showIngestModal = false;
    public string $activeTab = 'text'; // 'text', 'markdown', 'url'

    // Form fields
    public string $title = '';
    public string $content = '';
    public string $urlInput = '';
    public ?int $projectId = null;
    public bool $isIngesting = false;
    public string $ingestErrorMessage = '';

    // Semantic Vector Search Playground
    public string $searchQuery = '';
    public array $searchResults = [];
    public bool $isSearching = false;
    public ?string $previewSnippet = null;

    public function openIngestModal()
    {
        $this->resetForm();
        $this->showIngestModal = true;
    }

    public function fetchFromUrl()
    {
        $this->validate([
            'urlInput' => 'required|url',
        ]);

        $this->isIngesting = true;
        $this->ingestErrorMessage = '';

        try {
            $response = Http::withOptions(['force_ip_resolve' => 'v4', 'timeout' => 15])->get($this->urlInput);
            if (!$response->successful()) {
                throw new Exception("Unable to fetch URL. HTTP status: " . $response->status());
            }

            $html = $response->body();
            // Basic HTML to readable text conversion
            $cleanText = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
            $cleanText = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleanText);
            $cleanText = trim(strip_tags($cleanText));
            $cleanText = preg_replace('/\s+/u', ' ', $cleanText);

            $this->content = $cleanText;
            if (empty($this->title)) {
                $this->title = 'Imported from ' . parse_url($this->urlInput, PHP_URL_HOST);
            }
        } catch (Exception $e) {
            $this->ingestErrorMessage = 'URL Import Failed: ' . $e->getMessage();
        } finally {
            $this->isIngesting = false;
        }
    }

    public function saveSource(CreateKnowledgeSource $createAction)
    {
        $this->validate([
            'title' => 'required|string|max:150',
            'content' => 'required|string|min:20',
        ]);

        $this->isIngesting = true;
        $this->ingestErrorMessage = '';

        try {
            $createAction->execute(Auth::user(), [
                'title' => $this->title,
                'content' => $this->content,
                'source_type' => $this->activeTab,
                'project_id' => $this->projectId,
            ]);

            session()->flash('status', "Knowledge source '{$this->title}' indexed and vectorized successfully.");
            $this->resetForm();
            $this->showIngestModal = false;
        } catch (Exception $e) {
            $this->ingestErrorMessage = $e->getMessage();
        } finally {
            $this->isIngesting = false;
        }
    }

    public function reindex(int $sourceId, ProcessKnowledgeSource $processAction)
    {
        $source = KnowledgeSource::where('user_id', Auth::id())->findOrFail($sourceId);
        $processAction->execute($source);
        session()->flash('status', "Re-indexed and vectorized '{$source->title}'.");
    }

    public function deleteSource(int $sourceId, DeleteKnowledgeSource $deleteAction)
    {
        $source = KnowledgeSource::where('user_id', Auth::id())->findOrFail($sourceId);
        $title = $source->title;
        $deleteAction->execute($source);
        session()->flash('status', "Knowledge source '{$title}' deleted.");
    }

    public function performSemanticSearch(RetrieveRagContext $ragAction)
    {
        if (empty(trim($this->searchQuery))) {
            $this->searchResults = [];
            $this->previewSnippet = null;
            return;
        }

        $this->isSearching = true;

        try {
            $result = $ragAction->execute(Auth::user(), $this->searchQuery, 5);
            $this->searchResults = $result['chunks'];
            $this->previewSnippet = $result['prompt_snippet'];
        } catch (Exception $e) {
            $this->searchResults = [];
        } finally {
            $this->isSearching = false;
        }
    }

    public function resetForm()
    {
        $this->title = '';
        $this->content = '';
        $this->urlInput = '';
        $this->projectId = null;
        $this->ingestErrorMessage = '';
        $this->activeTab = 'text';
    }

    public function render()
    {
        $sources = KnowledgeSource::withCount('chunks')
            ->with(['chunks' => function ($q) {
                $q->selectRaw('knowledge_source_id, sum(token_count) as total_tokens')->groupBy('knowledge_source_id');
            }])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $projects = Project::where('user_id', Auth::id())->get();

        return view('knowledge-base.index', [
            'sources' => $sources,
            'projects' => $projects,
        ]);
    }
}