<?php

namespace Tests\Feature;

use App\Features\AI\Models\AiProvider;
use App\Features\BrandVoice\Models\BrandProfile;
use App\Features\Templates\Database\Seeders\TemplateSeeder;
use App\Features\Templates\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TemplateEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'monthly_word_quota' => 50000,
            'used_word_quota' => 0,
        ]);

        $this->seed(TemplateSeeder::class);

        AiProvider::create([
            'name' => 'OmniRoute Gateway',
            'slug' => 'omniroute',
            'base_url' => 'http://localhost:20128/v1',
            'api_key_encrypted' => 'test-key',
            'is_local' => true,
            'is_active' => true,
        ]);
    }

    public function test_user_can_view_templates_hub(): void
    {
        $response = $this->actingAs($this->user)->get(route('templates.index'));
        $response->assertOk();
        $response->assertSee('Complete SEO Long-Form Article');
        $response->assertSee('High-Converting Landing Page Copy');
    }

    public function test_user_can_generate_copy_from_template_with_brand_voice(): void
    {
        $brandVoice = BrandProfile::create([
            'user_id' => $this->user->id,
            'name' => 'Cyberpunk Edgy',
            'tone_description' => 'Edgy, futuristic, fast-paced',
            'is_default' => true,
        ]);

        $template = Template::where('slug', 'cold-email-sequence')->firstOrFail();

        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'Subject: Quick question about AI latency\n\nHey Alex, noticed your team is scaling fast...',
                        ],
                    ],
                ],
                'usage' => [
                    'total_tokens' => 80,
                ],
                'model' => 'cc/claude-3-7-sonnet',
            ], 200),
        ]);

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\Templates\Livewire\TemplatesHubPage::class)
            ->call('selectTemplate', $template->id)
            ->set('formInputs.offer', 'AI latency optimizer plugin')
            ->set('formInputs.prospect_role', 'VP of Engineering')
            ->set('formInputs.industry', 'Fintech SaaS')
            ->set('selectedBrandVoiceId', $brandVoice->id)
            ->call('generate')
            ->assertHasNoErrors()
            ->assertSet('generatedContent', 'Subject: Quick question about AI latency\n\nHey Alex, noticed your team is scaling fast...');

        $this->assertDatabaseHas('generation_usage', [
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_can_convert_generated_template_into_document(): void
    {
        $template = Template::firstOrFail();

        \Livewire\Livewire::actingAs($this->user)
            ->test(\App\Features\Templates\Livewire\TemplatesHubPage::class)
            ->set('activeTemplateId', $template->id)
            ->set('generatedContent', 'This is generated sample article copy.')
            ->call('createDocumentFromGeneration')
            ->assertRedirect();

        $this->assertDatabaseHas('documents', [
            'user_id' => $this->user->id,
        ]);
    }
}