<?php

namespace Tests\Feature;

use App\Features\Projects\Livewire\ProjectsPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_edit_and_delete_project(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create
        Livewire::test(ProjectsPage::class)
            ->set('name', 'Marketing Campaign')
            ->set('description', 'Q3 Launch materials')
            ->set('color', '#10b981')
            ->call('save')
            ->assertSee('Marketing Campaign');

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'Marketing Campaign',
            'color' => '#10b981',
        ]);

        $project = $user->projects()->first();

        // Edit
        Livewire::test(ProjectsPage::class)
            ->call('openEditModal', $project->id)
            ->set('name', 'Updated Marketing Campaign')
            ->call('save');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Marketing Campaign',
        ]);

        // Delete
        Livewire::test(ProjectsPage::class)
            ->call('delete', $project->id);

        $this->assertSoftDeleted('projects', [
            'id' => $project->id,
        ]);
    }
}