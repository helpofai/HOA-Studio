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

namespace App\Features\BrandVoice\Livewire;

use App\Features\BrandVoice\Actions\CreateBrandProfile;
use App\Features\BrandVoice\Actions\DeleteBrandProfile;
use App\Features\BrandVoice\Actions\UpdateBrandProfile;
use App\Features\BrandVoice\Models\BrandProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('Brand Voices — HelpOfAi Studio')]
class BrandVoicePage extends Component
{
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $tone_description = '';
    public string $target_audience = '';
    public string $guidelines = '';
    public string $forbidden_words_input = '';
    public string $sample_content = '';
    public bool $is_default = false;

    // Quick Tone Presets
    public array $presets = [
        [
            'name' => 'Tech Innovator & SaaS',
            'tone' => 'Visionary, precise, authoritative yet accessible, focusing on efficiency, developer empowerment, and future-forward architecture.',
            'audience' => 'CTOs, Software Engineers, Product Managers, Tech Founders',
            'guidelines' => 'Use active voice. Emphasize measurable impact (latency, throughput, cost). Avoid corporate buzzwords like synergy or paradigm shift.',
        ],
        [
            'name' => 'Direct Response Copywriter',
            'tone' => 'Persuasive, punchy, high-energy, action-oriented with strong sensory hooks and clear benefit-driven propositions.',
            'audience' => 'Entrepreneurs, E-commerce Buyers, Direct Marketers',
            'guidelines' => 'Short sentences. Focus heavily on pain points and transformative solutions. Use bullet points and power verbs.',
        ],
        [
            'name' => 'Empathetic & Friendly Mentor',
            'tone' => 'Warm, supportive, conversational, encouraging, and easy to understand without condescension.',
            'audience' => 'Beginners, Students, Small Business Owners, Creators',
            'guidelines' => 'Use welcoming phrases. Break down complex steps simply. Avoid overly dense jargon.',
        ],
        [
            'name' => 'Executive & Management Brief',
            'tone' => 'Structured, analytical, objective, data-informed, and focused on strategic ROI and risk mitigation.',
            'audience' => 'CEOs, Board Members, Enterprise Decision Makers',
            'guidelines' => 'Bottom-line first (BLUF). Use clear executive summaries. Provide data-backed recommendations.',
        ],
    ];

    public function applyPreset(int $index)
    {
        if (isset($this->presets[$index])) {
            $p = $this->presets[$index];
            $this->name = $p['name'];
            $this->tone_description = $p['tone'];
            $this->target_audience = $p['audience'];
            $this->guidelines = $p['guidelines'];
        }
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id)
    {
        $profile = BrandProfile::where('user_id', Auth::id())->findOrFail($id);
        $this->editingId = $profile->id;
        $this->name = $profile->name;
        $this->tone_description = $profile->tone_description;
        $this->target_audience = $profile->target_audience ?? '';
        $this->guidelines = $profile->guidelines ?? '';
        $this->forbidden_words_input = is_array($profile->forbidden_words) ? implode(', ', $profile->forbidden_words) : '';
        $this->sample_content = $profile->sample_content ?? '';
        $this->is_default = (bool) $profile->is_default;

        $this->showEditModal = true;
    }

    public function save(CreateBrandProfile $createAction, UpdateBrandProfile $updateAction)
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'tone_description' => 'required|string|max:1000',
            'target_audience' => 'nullable|string|max:500',
            'guidelines' => 'nullable|string|max:2000',
            'sample_content' => 'nullable|string|max:5000',
        ]);

        $data = [
            'name' => $this->name,
            'tone_description' => $this->tone_description,
            'target_audience' => $this->target_audience,
            'guidelines' => $this->guidelines,
            'forbidden_words' => $this->forbidden_words_input,
            'sample_content' => $this->sample_content,
            'is_default' => $this->is_default,
        ];

        if ($this->editingId) {
            $profile = BrandProfile::where('user_id', Auth::id())->findOrFail($this->editingId);
            $updateAction->execute($profile, $data);
            session()->flash('status', "Brand Voice '{$this->name}' updated successfully.");
        } else {
            $createAction->execute(Auth::user(), $data);
            session()->flash('status', "Brand Voice '{$this->name}' created successfully.");
        }

        $this->resetForm();
        $this->showCreateModal = false;
        $this->showEditModal = false;
    }

    public function setDefault(int $id, UpdateBrandProfile $updateAction)
    {
        $profile = BrandProfile::where('user_id', Auth::id())->findOrFail($id);
        $updateAction->execute($profile, ['is_default' => true]);
        session()->flash('status', "Brand Voice '{$profile->name}' is now your default.");
    }

    public function delete(int $id, DeleteBrandProfile $deleteAction)
    {
        $profile = BrandProfile::where('user_id', Auth::id())->findOrFail($id);
        $name = $profile->name;
        $deleteAction->execute($profile);
        session()->flash('status', "Brand Voice '{$name}' deleted.");
    }

    public function resetForm()
    {
        $this->editingId = null;
        $this->name = '';
        $this->tone_description = '';
        $this->target_audience = '';
        $this->guidelines = '';
        $this->forbidden_words_input = '';
        $this->sample_content = '';
        $this->is_default = false;
    }

    public function render()
    {
        $brandVoices = BrandProfile::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();

        return view('brand-voice.index', [
            'brandVoices' => $brandVoices,
        ]);
    }
}