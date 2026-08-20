<?php

namespace Tests\Feature;

use App\Features\Admin\Actions\SeedDefaultAiProviders;
use App\Features\AI\Models\AiModel;
use App\Features\AI\Models\AiProvider;
use App\Features\AI\Services\AiCircuitBreaker;
use App\Features\AI\Services\ModelGovernanceService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AdminControlPanelAndModelGovernanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@helpofai.com',
            'role' => 'admin',
            'plan' => 'enterprise',
            'is_active' => true,
        ]);

        $this->regularUser = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'user',
            'plan' => 'starter',
            'monthly_word_quota' => 15000,
            'used_word_quota' => 0,
            'is_active' => true,
        ]);

        app(SeedDefaultAiProviders::class)->execute();
    }

    public function test_non_admin_users_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->regularUser)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_admin_dashboard()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Admin Overview');
    }

    public function test_admin_can_view_model_governance_matrix()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.ai-settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Model Governance');
        $response->assertSee('gpt-4o');
    }

    public function test_admin_can_toggle_model_active_state()
    {
        $model = AiModel::where('model_id', 'gpt-4o')->firstOrFail();
        $this->assertTrue((bool) $model->is_active);

        $service = app(ModelGovernanceService::class);
        $service->toggleActive($model);

        $this->assertFalse((bool) $model->fresh()->is_active);
    }

    public function test_admin_can_set_default_primary_model()
    {
        $gpt = AiModel::where('model_id', 'gpt-4o')->firstOrFail();
        $deepseek = AiModel::where('model_id', 'deepseek-chat')->firstOrFail();

        $service = app(ModelGovernanceService::class);
        $service->setDefaultModel($deepseek);

        $this->assertTrue((bool) $deepseek->fresh()->is_default);
        $this->assertFalse((bool) $gpt->fresh()->is_default);
    }

    public function test_admin_can_toggle_model_free_tier_access()
    {
        $model = AiModel::where('model_id', 'gpt-4o')->firstOrFail();
        $initial = (bool) $model->is_free_tier;

        $service = app(ModelGovernanceService::class);
        $service->toggleFreeTier($model);

        $this->assertEquals(!$initial, (bool) $model->fresh()->is_free_tier);
    }

    public function test_admin_can_ping_model_health()
    {
        Http::fake([
            '*/v1/chat/completions' => Http::response([
                'choices' => [
                    ['message' => ['content' => 'pong']],
                ],
            ], 200),
        ]);

        $model = AiModel::where('model_id', 'gpt-4o')->firstOrFail();
        $service = app(ModelGovernanceService::class);

        $res = $service->pingModel($model);

        $this->assertEquals('healthy', $res['status']);
        $this->assertEquals('healthy', $model->fresh()->last_test_status);
        $this->assertNotNull($model->fresh()->last_tested_at);
    }

    public function test_admin_can_trip_and_reset_emergency_ai_circuit_breaker()
    {
        $breaker = app(AiCircuitBreaker::class);
        $this->assertFalse($breaker->isTripped());

        $breaker->trip('Scheduled database migration maintenance', 'Master Admin');
        $this->assertTrue($breaker->isTripped());
        $this->assertEquals('Scheduled database migration maintenance', $breaker->getStatus()['reason']);

        $breaker->reset();
        $this->assertFalse($breaker->isTripped());
    }

    public function test_ai_transform_is_rejected_when_circuit_breaker_is_tripped()
    {
        $breaker = app(AiCircuitBreaker::class);
        $breaker->trip('Billing threshold exceeded emergency hold', 'Finance Admin');

        $response = $this->actingAs($this->regularUser)->postJson(route('ai.transform'), [
            'text' => 'Hello world',
            'type' => 'rewrite',
        ]);

        $response->assertStatus(503);
        $response->assertJsonFragment([
            'success' => false,
        ]);

        $breaker->reset();
    }

    public function test_admin_can_grant_bonus_word_quota_to_user()
    {
        $this->assertEquals(0, (int) $this->regularUser->bonus_word_quota);

        Livewire::actingAs($this->admin)
            ->test('App\Features\Admin\Livewire\AdminUsersPage')
            ->call('grantBonusQuota', $this->regularUser->id, 25000);

        $this->assertEquals(25000, (int) $this->regularUser->fresh()->bonus_word_quota);
    }

    public function test_admin_can_toggle_user_active_and_banned_status()
    {
        $this->assertTrue((bool) $this->regularUser->is_active);

        Livewire::actingAs($this->admin)
            ->test('App\Features\Admin\Livewire\AdminUsersPage')
            ->call('toggleActive', $this->regularUser->id);

        $this->assertFalse((bool) $this->regularUser->fresh()->is_active);
    }
}