<?php

namespace Tests\Feature;

use App\Features\AI\Actions\RecordGenerationUsage;
use App\Features\Usage\Actions\AdjustUserQuota;
use App\Features\Usage\Services\QuotaManager;
use App\Features\Usage\Services\TokenCostCalculator;
use App\Features\Usage\Services\UsageAnalyticsService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsageTrackingAndQuotasTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'monthly_word_quota' => 10000,
            'used_word_quota' => 2000,
        ]);
    }

    public function test_token_cost_calculator_resolves_model_rates_and_calculates_cost(): void
    {
        $calculator = new TokenCostCalculator();

        // 1,000 input tokens + 1,000 output tokens on gpt-4o ($2.50 / $10.00 per M)
        $cost = $calculator->calculateCost('openai/gpt-4o', 1000, 1000);
        $this->assertEquals(0.0125, $cost);

        // Free tier model should cost $0.00
        $freeCost = $calculator->calculateCost('auto:free', 5000, 5000);
        $this->assertEquals(0.0, $freeCost);

        // Savings calculation vs gpt-4o baseline
        $savings = $calculator->calculateSavings('deepseek/deepseek-chat', 10000, 10000);
        $this->assertGreaterThan(0.0, $savings);
    }

    public function test_quota_manager_reports_accurate_status(): void
    {
        $quotaManager = new QuotaManager();

        $details = $quotaManager->getQuotaDetails($this->user);

        $this->assertEquals(10000, $details['monthly_limit']);
        $this->assertEquals(2000, $details['used_words']);
        $this->assertEquals(8000, $details['remaining_words']);
        $this->assertEquals(20.0, $details['percentage_used']);
        $this->assertEquals('ok', $details['status']);
        $this->assertFalse($details['is_exhausted']);

        // Exceed quota
        $this->user->update(['used_word_quota' => 10000]);
        $exhaustedDetails = $quotaManager->getQuotaDetails($this->user);
        $this->assertTrue($exhaustedDetails['is_exhausted']);
        $this->assertEquals('exhausted', $exhaustedDetails['status']);
    }

    public function test_user_analytics_aggregates_completions_and_cost(): void
    {
        $recordAction = app(RecordGenerationUsage::class);
        $recordAction->execute($this->user, [
            'words_used' => 500,
            'tokens_used' => 2000,
            'model_slug' => 'cc/claude-3-7-sonnet',
        ]);

        $analyticsService = app(UsageAnalyticsService::class);
        $analytics = $analyticsService->getUserAnalytics($this->user);

        $this->assertGreaterThanOrEqual(1, $analytics['summary']['total_generations']);
        $this->assertGreaterThanOrEqual(500, $analytics['summary']['total_words']);
        $this->assertGreaterThanOrEqual(2000, $analytics['summary']['total_tokens']);
        $this->assertNotEmpty($analytics['model_breakdown']);
    }

    public function test_user_can_view_usage_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get(route('usage.index'));
        $response->assertOk();
        $response->assertSee('Token Accounting');
        $response->assertSee('AI Model Consumption Breakdown');
    }

    public function test_adjust_user_quota_action_updates_limits(): void
    {
        $action = new AdjustUserQuota();
        $updatedUser = $action->execute($this->user, 25000, 5000);

        $this->assertEquals(30000, $updatedUser->monthly_word_quota);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'monthly_word_quota' => 30000,
        ]);
    }
}