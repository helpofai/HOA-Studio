<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 1 Content Intelligence Migration
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('content_missions')) {
            Schema::create('content_missions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
                $table->string('topic');
                $table->text('primary_objective');
                $table->json('secondary_objectives')->nullable();
                $table->json('target_audience');
                $table->string('market_geo')->default('Global');
                $table->string('language')->default('en');
                $table->string('content_type')->default('comprehensive_guide');
                $table->string('business_goal')->default('authority_and_engagement');
                $table->string('search_goal')->default('organic_search_rank_1');
                $table->foreignId('brand_profile_id')->nullable()->constrained('brand_profiles')->nullOnDelete();
                $table->string('freshness_requirement')->default('current_standard');
                $table->string('trust_requirement')->default('authoritative_sources_only');
                $table->string('evidence_requirement')->default('verified_factual_citations');
                $table->unsignedInteger('target_word_count_min')->default(1500);
                $table->unsignedInteger('target_word_count_max')->default(3000);
                $table->string('risk_level')->default('medium')->index();
                $table->string('research_budget_tier')->default('standard')->index();
                $table->json('success_criteria')->nullable();
                $table->json('custom_constraints')->nullable();
                $table->string('status')->default('draft')->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('workflow_runs')) {
            Schema::create('workflow_runs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
                $table->string('current_node')->default('mission')->index();
                $table->string('status')->default('running')->index();
                $table->json('graph_state')->nullable();
                $table->unsignedInteger('total_tokens')->default(0);
                $table->decimal('total_cost', 10, 4)->default(0.0000);
                $table->decimal('overall_confidence', 5, 4)->default(1.0000);
                $table->text('error_message')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('workflow_nodes')) {
            Schema::create('workflow_nodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
                $table->string('node_name')->index();
                $table->json('input_payload')->nullable();
                $table->json('output_payload')->nullable();
                $table->string('status')->default('success')->index();
                $table->decimal('confidence', 5, 4)->default(1.0000);
                $table->unsignedInteger('latency_ms')->default(0);
                $table->unsignedInteger('token_count')->default(0);
                $table->text('error_log')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_nodes');
        Schema::dropIfExists('workflow_runs');
        Schema::dropIfExists('content_missions');
    }
};
