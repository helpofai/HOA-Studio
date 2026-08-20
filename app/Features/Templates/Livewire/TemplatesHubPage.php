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

namespace App\Features\Templates\Livewire;

use App\Features\BrandVoice\Models\BrandProfile;
use App\Features\Documents\Actions\CreateDocument;
use App\Features\Templates\Actions\GenerateFromTemplate;
use App\Features\Templates\Models\Template;
use App\Features\Templates\Models\TemplateCategory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('AI Templates Hub — HelpOfAi Studio')]
class TemplatesHubPage extends Component
{
    public string $selectedCategory = 'all';
    public string $search = '';

    // Template Execution Modal State
    public bool $showRunnerModal = false;
    public ?int $activeTemplateId = null;
    public array $formInputs = [];
    public ?int $selectedBrandVoiceId = null;
    public string $selectedModel = 'auto';

    // Generation State
    public bool $isGenerating = false;
    public ?string $generatedContent = null;
    public ?array $generationTelemetry = null;
    public string $errorMessage = '';

    public function mount()
    {
        $defaultVoice = BrandProfile::where('user_id', Auth::id())->where('is_default', true)->first();
        if ($defaultVoice) {
            $this->selectedBrandVoiceId = $defaultVoice->id;
        }
    }

    public function selectTemplate(int $id)
    {
        $template = Template::findOrFail($id);
        $this->activeTemplateId = $template->id;
        $this->formInputs = [];
        $this->generatedContent = null;
        $this->generationTelemetry = null;
        $this->errorMessage = '';

        if (!empty($template->inputs_schema)) {
            foreach ($template->inputs_schema as $field) {
                $this->formInputs[$field['name']] = '';
            }
        }

        $this->showRunnerModal = true;
    }

    public function generate(GenerateFromTemplate $generateAction)
    {
        $template = Template::findOrFail($this->activeTemplateId);

        $rules = [];
        if (!empty($template->inputs_schema)) {
            foreach ($template->inputs_schema as $field) {
                if (!empty($field['required'])) {
                    $rules["formInputs.{$field['name']}"] = 'required|string';
                }
            }
        }
        $this->validate($rules);

        $this->isGenerating = true;
        $this->errorMessage = '';

        try {
            $brandVoice = $this->selectedBrandVoiceId 
                ? BrandProfile::where('user_id', Auth::id())->find($this->selectedBrandVoiceId) 
                : null;

            $result = $generateAction->execute(
                Auth::user(),
                $template,
                $this->formInputs,
                $brandVoice,
                ['model' => $this->selectedModel]
            );

            $this->generatedContent = $result['content'];
            $this->generationTelemetry = $result;
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function createDocumentFromGeneration(CreateDocument $createDocAction)
    {
        if (empty($this->generatedContent)) {
            return;
        }

        $template = Template::find($this->activeTemplateId);
        $title = ($template ? $template->name : 'Generated Copy') . ' — ' . now()->format('M j, Y');

        // Convert simple markdown newlines to basic HTML for Tiptap
        $html = '<p>' . nl2br(e($this->generatedContent)) . '</p>';

        $document = $createDocAction->execute(Auth::user(), [
            'title' => $title,
            'content_html' => $html,
            'content_markdown' => $this->generatedContent,
            'metadata' => [
                'generated_from_template_id' => $this->activeTemplateId,
                'template_slug' => $template?->slug,
            ],
        ]);

        return redirect()->route('documents.editor', $document->id);
    }

    public function closeRunnerModal()
    {
        $this->showRunnerModal = false;
    }

    public function render()
    {
        $categories = TemplateCategory::orderBy('sort_order', 'asc')->get();

        $query = Template::with('category')->where('is_active', true);

        if ($this->selectedCategory !== 'all') {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->selectedCategory);
            });
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        $templates = $query->orderBy('is_system', 'desc')->orderBy('name', 'asc')->get();
        $brandVoices = BrandProfile::where('user_id', Auth::id())->orderBy('is_default', 'desc')->get();
        $activeTemplate = $this->activeTemplateId ? Template::find($this->activeTemplateId) : null;

        return view('templates.index', [
            'categories' => $categories,
            'templates' => $templates,
            'brandVoices' => $brandVoices,
            'activeTemplate' => $activeTemplate,
        ]);
    }
}