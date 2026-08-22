<?php

namespace Tests\Feature;

use App\Features\AI\Services\AiRateLimiterService;
use App\Features\Auth\Models\UserApiKey;
use App\Features\KnowledgeBase\Services\VectorCacheManager;
use App\Features\KnowledgeBase\Services\VectorSearchEngine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningAndPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'author_' . uniqid() . '@helpofai.com',
            'role' => 'user',
            'plan' => 'starter',
            'is_active' => true,
        ]);

        $this->otherUser = User::factory()->create([
            'email' => 'other_' . uniqid() . '@helpofai.com',
            'role' => 'user',
            'plan' => 'starter',
            'is_active' => true,
        ]);

        RateLimiter::clear('hoa_ai_rate_limit:' . $this->user->id);
    }

    public function test_security_headers_middleware_attaches_strict_headers_to_responses()
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_byok_api_key_is_encrypted_at_rest_with_aes_256_in_database()
    {
        $rawSecret = 'sk-proj-super-secret-openai-key-998877';

        $key = UserApiKey::create([
            'user_id' => $this->user->id,
            'provider_slug' => 'openai',
            'api_key' => $rawSecret,
            'is_active' => true,
        ]);

        // Verify raw database column does NOT contain plain text string
        $rawRow = DB::table('user_api_keys')->where('id', $key->id)->first();
        $this->assertNotEquals($rawSecret, $rawRow->api_key);
        $this->assertStringNotContainsString($rawSecret, $rawRow->api_key);

        // Verify Eloquent model transparently decrypts for authenticated owner
        $this->assertEquals($rawSecret, $key->fresh()->api_key);
    }

    public function test_api_key_owner_can_safely_access_raw_unencrypted_key()
    {
        $rawSecret = 'sk-ant-anthropic-master-key-123456';

        $key = UserApiKey::create([
            'user_id' => $this->user->id,
            'provider_slug' => 'anthropic',
            'api_key' => $rawSecret,
            'is_active' => true,
        ]);

        $this->assertEquals($rawSecret, $key->getRawKeyForOwner($this->user));
        $this->assertNull($key->getRawKeyForOwner($this->otherUser));
    }

    public function test_ai_rate_limiter_enforces_limit_on_shared_gateway_for_starter_users()
    {
        $limiter = app(AiRateLimiterService::class);

        // 15 requests allowed per minute for starter
        for ($i = 0; $i < 15; $i++) {
            $check = $limiter->checkRateLimit($this->user);
            $this->assertTrue($check['allowed']);
            $this->assertFalse($check['is_unlimited']);
        }

        // 16th request rejected
        $exceededCheck = $limiter->checkRateLimit($this->user);
        $this->assertFalse($exceededCheck['allowed']);
        $this->assertGreaterThan(0, $exceededCheck['retry_after']);
    }

    public function test_ai_rate_limiter_allows_unlimited_requests_when_user_has_byok_key()
    {
        $limiter = app(AiRateLimiterService::class);

        UserApiKey::create([
            'user_id' => $this->user->id,
            'provider_slug' => 'openai',
            'api_key' => 'sk-user-custom-key',
            'is_active' => true,
        ]);

        // When user key exists for provider, rate check is always allowed & unlimited
        for ($i = 0; $i < 25; $i++) {
            $check = $limiter->checkRateLimit($this->user, 'openai');
            $this->assertTrue($check['allowed']);
            $this->assertTrue($check['is_unlimited']);
        }
    }

    public function test_ai_rate_limiter_allows_unlimited_requests_when_user_has_local_custom_endpoint()
    {
        $limiter = app(AiRateLimiterService::class);

        UserApiKey::create([
            'user_id' => $this->user->id,
            'provider_slug' => 'custom',
            'api_key' => 'ollama-dummy',
            'custom_base_url' => 'http://127.0.0.1:11434/v1',
            'is_active' => true,
        ]);

        $check = $limiter->checkRateLimit($this->user, 'custom');
        $this->assertTrue($check['allowed']);
        $this->assertTrue($check['is_unlimited']);
    }

    public function test_vector_embedding_cache_manager_caches_embeddings_by_user_ttl_preference()
    {
        $cacheManager = app(VectorCacheManager::class);
        $text = 'The quick brown fox jumps over the lazy dog';
        $model = 'text-embedding-3-small';
        $dummyVector = [0.12, -0.45, 0.78, 0.99];

        $this->assertNull($cacheManager->getCachedVector($text, $model));

        $this->user->preferences = ['embedding_cache_days' => 30];
        $this->user->save();

        $cacheManager->storeVector($text, $model, $dummyVector, $this->user);

        $cached = $cacheManager->getCachedVector($text, $model);
        $this->assertNotNull($cached);
        $this->assertEquals($dummyVector, $cached);
    }

    public function test_vector_search_engine_returns_cached_vector_on_repeat_query_without_api_call()
    {
        Http::fake([
            '*/v1/embeddings' => Http::response([
                'data' => [
                    ['embedding' => [0.1, 0.2, 0.3]],
                ],
            ], 200),
        ]);

        $engine = app(VectorSearchEngine::class);
        $text = 'Unique query about legal contracts';

        // 1st call -> hits endpoint & stores in cache
        $vec1 = $engine->generateEmbedding($text, 'text-embedding-3-small', $this->user);
        $this->assertEquals([0.1, 0.2, 0.3], $vec1);

        // 2nd call -> served from cache in <1ms without calling HTTP endpoint again
        Http::fake([
            '*/v1/embeddings' => Http::response([], 500), // even if gateway fails, cache serves it!
        ]);

        $vec2 = $engine->generateEmbedding($text, 'text-embedding-3-small', $this->user);
        $this->assertEquals([0.1, 0.2, 0.3], $vec2);
    }

    public function test_user_can_manage_byok_keys_and_cache_preference_in_profile()
    {
        Livewire::actingAs($this->user)
            ->test('App\Features\Auth\Livewire\ProfilePage')
            ->set('embedding_cache_days', 30)
            ->set('byok_provider', 'deepseek')
            ->set('byok_api_key', 'sk-ds-custom-key-7788')
            ->call('saveApiKey')
            ->call('updateProfile');

        $this->assertDatabaseHas('user_api_keys', [
            'user_id' => $this->user->id,
            'provider_slug' => 'deepseek',
        ]);

        $this->assertEquals(30, (int) $this->user->fresh()->preferences['embedding_cache_days']);
    }
}