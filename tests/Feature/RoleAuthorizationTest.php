<?php

namespace Tests\Feature;

use App\Features\Auth\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin'])->get('/test-admin-only', function () {
            return response()->json(['message' => 'admin area']);
        });

        Route::middleware(['web', 'auth', 'role:editor,admin'])->get('/test-editor-area', function () {
            return response()->json(['message' => 'editor area']);
        });

        Route::middleware(['web', 'auth', 'role:pro,admin'])->get('/test-pro-area', function () {
            return response()->json(['message' => 'pro area']);
        });
    }

    public function test_user_role_methods_evaluate_correctly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $editor = User::factory()->create(['role' => 'editor']);
        $pro = User::factory()->create(['role' => 'pro']);
        $user = User::factory()->create(['role' => 'user']);

        // Admin
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isEditor());
        $this->assertTrue($admin->isPro());
        $this->assertTrue($admin->hasRole('admin'));
        $this->assertTrue($admin->hasRole('editor')); // Admin bypasses checks

        // Editor
        $this->assertFalse($editor->isAdmin());
        $this->assertTrue($editor->isEditor());
        $this->assertTrue($editor->hasRole('editor'));
        $this->assertFalse($editor->hasRole('admin'));

        // Pro
        $this->assertFalse($pro->isAdmin());
        $this->assertTrue($pro->isPro());
        $this->assertTrue($pro->hasRole('pro'));

        // User
        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isEditor());
    }

    public function test_role_middleware_permits_authorized_roles(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $editor = User::factory()->create(['role' => 'editor']);
        $user = User::factory()->create(['role' => 'user']);

        // Admin can access admin-only
        $this->actingAs($admin)->getJson('/test-admin-only')->assertStatus(200);

        // Editor can access editor area
        $this->actingAs($editor)->getJson('/test-editor-area')->assertStatus(200);

        // Standard user blocked from admin area
        $this->actingAs($user)->getJson('/test-admin-only')->assertStatus(403);

        // Standard user blocked from editor area
        $this->actingAs($user)->getJson('/test-editor-area')->assertStatus(403);
    }
}