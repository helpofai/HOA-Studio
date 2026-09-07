<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 3 World Model & Evidence Migration
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
        // 1. World Entities (Ontology of the domain)
        if (! Schema::hasTable('world_entities')) {
            Schema::create('world_entities', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name')->index();
                $table->string('slug')->index();
                $table->string('category', 64)->default('technology')->index(); // technology, framework, concept, protocol, organization
                $table->text('description')->nullable();
                $table->json('aliases')->nullable();
                $table->json('attributes')->nullable();
                $table->date('temporal_valid_from')->nullable();
                $table->date('temporal_valid_until')->nullable();
                $table->timestamps();
            });
        }

        // 2. World Relationships (Directed semantic graph edges between entities)
        if (! Schema::hasTable('world_relationships')) {
            Schema::create('world_relationships', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->string('subject_entity_id', 64)->index();
                $table->string('predicate', 64)->index(); // requires, traps, monitors, supersedes, incompatible_with, complements
                $table->string('object_entity_id', 64)->index();
                $table->decimal('confidence', 5, 4)->default(0.9500);
                $table->decimal('strength', 5, 4)->default(1.0000);
                $table->text('explanation')->nullable();
                $table->timestamps();

                $table->foreign('subject_entity_id')->references('id')->on('world_entities')->cascadeOnDelete();
                $table->foreign('object_entity_id')->references('id')->on('world_entities')->cascadeOnDelete();
            });
        }

        // 3. Evidence Snippets (Granular primary & secondary verbatim extracts from sources)
        if (! Schema::hasTable('evidence_snippets')) {
            Schema::create('evidence_snippets', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('source_id')->constrained('source_intelligences')->cascadeOnDelete();
                $table->foreignId('mission_id')->nullable()->constrained('content_missions')->nullOnDelete();
                $table->text('extract_text');
                $table->text('verbatim_quote')->nullable();
                $table->string('section_or_heading')->nullable();
                $table->unsignedInteger('page_number')->nullable();
                $table->decimal('confidence_score', 5, 4)->default(0.9500);
                $table->string('epistemic_state', 32)->default('verified')->index();
                $table->timestamp('verified_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Claim-Evidence Links (Strict N:M deep grounding between Claims & Evidence)
        if (! Schema::hasTable('claim_evidence_links')) {
            Schema::create('claim_evidence_links', function (Blueprint $table) {
                $table->id();
                $table->foreignId('claim_id')->constrained('claim_nodes')->cascadeOnDelete();
                $table->string('evidence_id', 64)->index();
                $table->string('relation_type', 32)->default('supports')->index(); // supports, refutes, qualifies, contextualizes
                $table->decimal('support_weight', 5, 4)->default(1.0000);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('evidence_id')->references('id')->on('evidence_snippets')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_evidence_links');
        Schema::dropIfExists('evidence_snippets');
        Schema::dropIfExists('world_relationships');
        Schema::dropIfExists('world_entities');
    }
};
