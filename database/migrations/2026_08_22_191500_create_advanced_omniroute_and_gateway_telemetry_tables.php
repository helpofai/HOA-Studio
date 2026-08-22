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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade ai_models table with performance metrics & multi-modal capabilities
        Schema::table('ai_models', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_models', 'max_output_tokens')) {
                $table->unsignedInteger('max_output_tokens')->default(8192)->after('context_window');
            }
            if (!Schema::hasColumn('ai_models', 'supports_vision')) {
                $table->boolean('supports_vision')->default(false)->after('supports_streaming');
            }
            if (!Schema::hasColumn('ai_models', 'supports_tools')) {
                $table->boolean('supports_tools')->default(true)->after('supports_vision');
            }
            if (!Schema::hasColumn('ai_models', 'supports_json')) {
                $table->boolean('supports_json')->default(true)->after('supports_tools');
            }
            if (!Schema::hasColumn('ai_models', 'provider_family')) {
                $table->string('provider_family', 64)->nullable()->after('owned_by')->index();
            }
            if (!Schema::hasColumn('ai_models', 'total_calls_count')) {
                $table->unsignedBigInteger('total_calls_count')->default(0)->after('last_test_error');
            }
            if (!Schema::hasColumn('ai_models', 'total_tokens_consumed')) {
                $table->unsignedBigInteger('total_tokens_consumed')->default(0)->after('total_calls_count');
            }
            if (!Schema::hasColumn('ai_models', 'success_rate_percentage')) {
                $table->decimal('success_rate_percentage', 5, 2)->default(100.00)->after('total_tokens_consumed');
            }
        });

        // 2. Upgrade ai_providers table with connection types and live health states
        Schema::table('ai_providers', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_providers', 'connection_type')) {
                $table->string('connection_type', 32)->default('local_daemon')->after('is_local');
            }
            if (!Schema::hasColumn('ai_providers', 'health_status')) {
                $table->string('health_status', 32)->default('unknown')->after('is_active');
            }
            if (!Schema::hasColumn('ai_providers', 'last_ping_latency_ms')) {
                $table->unsignedInteger('last_ping_latency_ms')->nullable()->after('health_status');
            }
            if (!Schema::hasColumn('ai_providers', 'last_health_check_at')) {
                $table->timestamp('last_health_check_at')->nullable()->after('last_ping_latency_ms');
            }
        });

        // 3. Upgrade user_api_keys table with connection types and streaming telemetry
        Schema::table('user_api_keys', function (Blueprint $table) {
            if (!Schema::hasColumn('user_api_keys', 'connection_type')) {
                $table->string('connection_type', 32)->default('local_daemon')->after('custom_base_url');
            }
            if (!Schema::hasColumn('user_api_keys', 'last_verified_at')) {
                $table->timestamp('last_verified_at')->nullable()->after('last_used_at');
            }
            if (!Schema::hasColumn('user_api_keys', 'last_latency_ms')) {
                $table->unsignedInteger('last_latency_ms')->nullable()->after('last_verified_at');
            }
            if (!Schema::hasColumn('user_api_keys', 'total_tokens_streamed')) {
                $table->unsignedBigInteger('total_tokens_streamed')->default(0)->after('last_latency_ms');
            }
        });

        // 4. Create omniroute_telemetry_logs table for real-time inference telemetry
        if (!Schema::hasTable('omniroute_telemetry_logs')) {
            Schema::create('omniroute_telemetry_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('model_id', 128)->index();
                $table->string('provider_family', 64)->nullable()->index();
                $table->unsignedSmallInteger('status_code')->default(200);
                $table->unsignedInteger('latency_ms')->default(0);
                $table->unsignedInteger('tokens_input')->default(0);
                $table->unsignedInteger('tokens_output')->default(0);
                $table->unsignedInteger('total_tokens')->default(0);
                $table->string('routing_decision', 255)->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }

        // 5. Create omniroute_combos table for multi-model cascade governance
        if (!Schema::hasTable('omniroute_combos')) {
            Schema::create('omniroute_combos', function (Blueprint $table) {
                $table->id();
                $table->string('combo_key', 64)->unique();
                $table->string('name', 128);
                $table->text('description')->nullable();
                $table->json('cascade_models');
                $table->string('fallback_strategy', 32)->default('sequential');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('omniroute_combos');
        Schema::dropIfExists('omniroute_telemetry_logs');

        Schema::table('user_api_keys', function (Blueprint $table) {
            $table->dropColumn([
                'connection_type',
                'last_verified_at',
                'last_latency_ms',
                'total_tokens_streamed',
            ]);
        });

        Schema::table('ai_providers', function (Blueprint $table) {
            $table->dropColumn([
                'connection_type',
                'health_status',
                'last_ping_latency_ms',
                'last_health_check_at',
            ]);
        });

        Schema::table('ai_models', function (Blueprint $table) {
            $table->dropColumn([
                'max_output_tokens',
                'supports_vision',
                'supports_tools',
                'supports_json',
                'provider_family',
                'total_calls_count',
                'total_tokens_consumed',
                'success_rate_percentage',
            ]);
        });
    }
};
