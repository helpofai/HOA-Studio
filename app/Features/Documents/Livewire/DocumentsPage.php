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

use App\Features\Documents\Actions\CreateDocument;
use App\Features\Documents\Actions\DeleteDocument;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Services\DocumentImporter;
use App\Features\Projects\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.workspace')]
#[Title('Documents — HelpOfAi Studio')]
class DocumentsPage extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public string $selectedProject = '';
    public string $selectedStatus = '';

    // Create Modal state
    public bool $showCreateModal = false;
    public string $newTitle = '';
    public ?int $newProjectId = null;

    // Import Modal state
    public bool $showImportModal = false;
    public $importFile = null;
    public ?int $importProjectId = null;

    protected array $rules = [
        'newTitle' => 'required|string|min:2|max:150',
        'newProjectId' => 'nullable|integer',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset(['newTitle', 'newProjectId']);
        $this->showCreateModal = true;
    }

    public function openImportModal()
    {
        $this->reset(['importFile', 'importProjectId']);
        $this->showImportModal = true;
    }

    public function importDocument(DocumentImporter $importer)
    {
        $this->validate([
            'importFile' => 'required|file|max:10240|mimes:txt,md,markdown,html,htm',
            'importProjectId' => 'nullable|integer',
        ]);

        $user = Auth::user();
        $doc = $importer->importFile($user, $this->importFile, $this->importProjectId);

        $this->showImportModal = false;
        session()->flash('status', "Document '{$doc->title}' imported successfully!");

        return $this->redirect(route('documents.editor', ['id' => $doc->id]), navigate: true);
    }

    public function createDocument(CreateDocument $action)
    {
        $this->validate();
        $user = Auth::user();

        $doc = $action->execute($user, [
            'title' => $this->newTitle,
            'project_id' => $this->newProjectId ?: null,
        ]);

        $this->showCreateModal = false;
        session()->flash('status', 'Document created successfully.');
    }

    public function delete(int $id, DeleteDocument $action)
    {
        $doc = Document::where('user_id', Auth::id())->findOrFail($id);
        $action->execute($doc);
        session()->flash('status', 'Document deleted.');
    }

    public function render()
    {
        $query = Document::with('project')
            ->where('user_id', Auth::id());

        if (! empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        if (! empty($this->selectedProject)) {
            $query->where('project_id', $this->selectedProject);
        }

        if (! empty($this->selectedStatus)) {
            $query->where('status', $this->selectedStatus);
        }

        $documents = $query->latest('updated_at')->paginate(12);
        $projects = Project::where('user_id', Auth::id())->get();

        return view('documents.index', [
            'documents' => $documents,
            'projects' => $projects,
        ]);
    }
}