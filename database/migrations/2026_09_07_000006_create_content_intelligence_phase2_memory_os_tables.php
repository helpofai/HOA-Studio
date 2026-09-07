<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 2 Cognitive Memory OS Migration
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
        if (! Schema::hasTable('brain_memories')) {
            Schema::create('brain_memories', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('mission_id')->nullable()->constrained('content_missions')->nullOnDelete();
                $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
                $table->string('scope', 32)->default('project')->index(); // site, project, article
                $table->string('layer', 32)->default('semantic')->index(); // working, semantic, episodic, procedural, strategic, brand, site, knowledge
                $table->string('type', 64)->default('technical_fact')->index();
                $table->string('subject')->nullable()->index();
                $table->string('predicate')->nullable()->index();
                $table->text('object')->nullable();
                $table->longText('content');
                $table->foreignId('source_id')->nullable()->constrained('source_intelligences')->nullOnDelete();
                $table->json('provenance')->nullable();
                $table->decimal('confidence', 5, 4)->default(0.9500);
                $table->unsignedTinyInteger('authority')->default(80);
                $table->decimal('freshness_score', 5, 4)->default(1.0000)->index();
                $table->string('status', 32)->default('active')->index(); // active, superseded, decayed, uncertain, contradicted, rejected
                $table->string('epistemic_state', 32)->default('verified')->index();
                $table->json('relationships')->nullable();
                $table->unsignedInteger('version')->default(1);
                $table->json('lineage')->nullable();
                $table->string('lineage_parent_id', 64)->nullable()->index();
                $table->timestamp('verified_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('memory_candidates')) {
            Schema::create('memory_candidates', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mission_id')->nullable()->constrained('content_missions')->nullOnDelete();
                $table->string('scope', 32)->default('project');
                $table->string('layer', 32)->default('semantic');
                $table->string('type', 64)->default('technical_fact');
                $table->string('subject')->nullable()->index();
                $table->string('predicate')->nullable()->index();
                $table->text('object')->nullable();
                $table->text('content');
                $table->text('source_url')->nullable();
                $table->foreignId('source_id')->nullable()->constrained('source_intelligences')->nullOnDelete();
                $table->json('provenance')->nullable();
                $table->decimal('confidence', 5, 4)->default(0.8500);
                $table->decimal('importance_score', 5, 4)->default(0.8500);
                $table->string('gate_status', 32)->default('pending')->index(); // pending, admitted, rejected, merged
                $table->text('rejection_reason')->nullable();
                $table->json('admission_notes')->nullable();
                $table->string('admitted_memory_id', 64)->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('episodic_events')) {
            Schema::create('episodic_events', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('mission_id')->nullable()->constrained('content_missions')->nullOnDelete();
                $table->string('event_type', 64)->index(); // research_completed, critic_rejection, claim_corrected, strategy_succeeded, strategy_failed, user_edit_observed
                $table->json('context')->nullable();
                $table->text('action_taken')->nullable();
                $table->decimal('outcome_score', 5, 4)->default(0.0000);
                $table->text('lessons_learned')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('article_element_nodes')) {
            Schema::create('article_element_nodes', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('document_id')->nullable()->constrained()->cascadeOnDelete();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->unsignedSmallInteger('section_index')->default(0);
                $table->unsignedSmallInteger('paragraph_index')->default(0);
                $table->unsignedSmallInteger('sentence_index')->default(0);
                $table->string('element_type', 32)->default('sentence')->index(); // section, paragraph, sentence, claim
                $table->text('text_content');
                $table->foreignId('claim_id')->nullable()->constrained('claim_nodes')->nullOnDelete();
                $table->string('memory_id', 64)->nullable()->index();
                $table->string('epistemic_state', 32)->default('verified')->index();
                $table->boolean('is_stale')->default(false)->index();
                $table->string('invalidation_reason')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_element_nodes');
        Schema::dropIfExists('episodic_events');
        Schema::dropIfExists('memory_candidates');
        Schema::dropIfExists('brain_memories');
    }
};
