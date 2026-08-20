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

namespace App\Features\Projects\Livewire;

use App\Features\Projects\Actions\CreateProject;
use App\Features\Projects\Actions\DeleteProject;
use App\Features\Projects\Actions\UpdateProject;
use App\Features\Projects\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
#[Title('Projects — HelpOfAi Studio')]
class ProjectsPage extends Component
{
    public string $name = '';
    public string $description = '';
    public string $color = '#6366f1';
    public ?int $editingProjectId = null;
    public bool $showModal = false;

    protected array $rules = [
        'name' => 'required|string|min:2|max:100',
        'description' => 'nullable|string|max:500',
        'color' => 'required|string|max:20',
    ];

    public function openCreateModal()
    {
        $this->reset(['name', 'description', 'color', 'editingProjectId']);
        $this->showModal = true;
    }

    public function openEditModal(int $id)
    {
        $project = Project::where('user_id', Auth::id())->findOrFail($id);
        $this->editingProjectId = $project->id;
        $this->name = $project->name;
        $this->description = $project->description ?? '';
        $this->color = $project->color;
        $this->showModal = true;
    }

    public function save(CreateProject $createAction, UpdateProject $updateAction)
    {
        $this->validate();
        $user = Auth::user();

        if ($this->editingProjectId) {
            $project = Project::where('user_id', $user->id)->findOrFail($this->editingProjectId);
            $updateAction->execute($project, [
                'name' => $this->name,
                'description' => $this->description,
                'color' => $this->color,
            ]);
            session()->flash('status', 'Project updated successfully.');
        } else {
            $createAction->execute($user, [
                'name' => $this->name,
                'description' => $this->description,
                'color' => $this->color,
            ]);
            session()->flash('status', 'Project created successfully.');
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'editingProjectId']);
    }

    public function delete(int $id, DeleteProject $deleteAction)
    {
        $project = Project::where('user_id', Auth::id())->findOrFail($id);
        $deleteAction->execute($project);
        session()->flash('status', 'Project deleted successfully.');
    }

    public function render()
    {
        $projects = Project::withCount('documents')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('projects.index', [
            'projects' => $projects,
        ]);
    }
}