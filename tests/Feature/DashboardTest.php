<?php

namespace Tests\Feature;

use App\Features\Dashboard\Livewire\DashboardPage;
use App\Features\Documents\Actions\CreateDocument;
use App\Features\Projects\Actions\CreateProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_user_statistics(): void
    {
        $user = User::factory()->create([
            'monthly_word_quota' => 25000,
            'used_word_quota' => 5000,
        ]);

        $projectAction = new CreateProject();
        $project = $projectAction->execute($user, ['name' => 'Tech Blog']);

        $docAction = new CreateDocument();
        $docAction->execute($user, [
            'title' => 'Article One',
            'project_id' => $project->id,
            'content_html' => '<p>Hello world AI test.</p>',
        ]);

        $this->actingAs($user);

        Livewire::test(DashboardPage::class)
            ->assertStatus(200)
            ->assertSee('Welcome back, ' . $user->name)
            ->assertSee('Tech Blog')
            ->assertSee('Article One')
            ->assertSee('20,000'); // remaining quota
    }
}