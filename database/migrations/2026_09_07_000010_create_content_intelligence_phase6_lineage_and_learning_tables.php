<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 6 Migration
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
        // 1. Content Lineage Nodes (7-tier trace: Source -> Evidence -> Claim -> Sentence -> Paragraph -> Section -> Article -> Published URL)
        if (! Schema::hasTable('content_lineage_nodes')) {
            Schema::create('content_lineage_nodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->foreignId('workflow_run_id')->nullable()->constrained('workflow_runs')->nullOnDelete();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->foreignId('source_id')->nullable()->constrained('source_intelligences')->nullOnDelete();
                $table->foreignId('evidence_snippet_id')->nullable()->constrained('evidence_snippets')->nullOnDelete();
                $table->foreignId('claim_id')->nullable()->constrained('claim_nodes')->nullOnDelete();
                $table->unsignedSmallInteger('section_index')->default(0);
                $table->unsignedSmallInteger('paragraph_index')->default(0);
                $table->unsignedSmallInteger('sentence_index')->default(0);
                $table->text('sentence_text');
                $table->string('published_url')->nullable();
                $table->boolean('is_stale')->default(false);
                $table->text('invalidation_reason')->nullable();
                $table->timestamps();

                $table->index(['document_id', 'is_stale'], 'idx_lineage_doc_stale');
                $table->index('source_id', 'idx_lineage_source');
                $table->index('claim_id', 'idx_lineage_claim');
            });
        }

        // 2. Strategy Memories Table (Observation -> Candidate -> Validated -> Adopted)
        if (! Schema::hasTable('strategy_memories')) {
            Schema::create('strategy_memories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('strategy_key', 100);
                $table->string('category', 50)->default('structure'); // structure, sources, tone, workflow, seo
                $table->unsignedInteger('evidence_count')->default(1);
                $table->decimal('confidence', 4, 2)->default(0.50);
                $table->string('status', 30)->default('observation'); // observation, candidate, validated, adopted, rejected
                $table->json('learning_payload');
                $table->timestamp('adopted_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status'], 'idx_strat_user_status');
                $table->index('strategy_key', 'idx_strat_key');
            });
        }

        // 3. User Style Preferences Table (Learned from user manual edits)
        if (! Schema::hasTable('user_style_preferences')) {
            Schema::create('user_style_preferences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('preference_key', 100);
                $table->unsignedInteger('observed_diff_count')->default(1);
                $table->decimal('confidence', 4, 2)->default(0.50);
                $table->text('rule_description');
                $table->boolean('is_active')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_active'], 'idx_style_user_active');
                $table->index('preference_key', 'idx_style_key');
            });
        }

        // 4. Site Topic Clusters Table (Site-Level Intelligence & Cannibalization Analysis)
        if (! Schema::hasTable('site_topic_clusters')) {
            Schema::create('site_topic_clusters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->string('cluster_name');
                $table->string('core_topic');
                $table->unsignedTinyInteger('coverage_score')->default(50);
                $table->json('cannibalization_risks')->nullable();
                $table->json('uncovered_subtopics')->nullable();
                $table->json('internal_link_matrix')->nullable();
                $table->json('recommendations')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'project_id'], 'idx_clusters_user_project');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_topic_clusters');
        Schema::dropIfExists('user_style_preferences');
        Schema::dropIfExists('strategy_memories');
        Schema::dropIfExists('content_lineage_nodes');
    }
};
